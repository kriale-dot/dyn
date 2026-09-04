<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\CadastroException;
use App\Repositories\CadastroRepository;
use DateTimeImmutable;
use Throwable;

/**
 * Regras do cadastro público com aprovação.
 *
 * Regra central:
 *
 * cadastro público != usuário autorizado.
 *
 * O papel MEMBRO só nasce no momento da aprovação.
 */
final class CadastroService
{
    private const PERMISSAO_APROVAR =
        'CADASTROS_APROVAR';

    private const CONFIRMACAO_EMAIL_HORAS =
        24;

    public function __construct(
        private CadastroRepository $repository,
        private EmailService $emailService
    ) {
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function solicitar(
        array $dados
    ): array {
        $normalizado =
            $this->validarCadastro(
                $dados
            );

        $email =
            $normalizado['email'];

        /**
         * Mantém o estado operacional da tabela atualizado sem exigir
         * cron job para o funcionamento básico do cadastro.
         */
        $this->repository
            ->expirarConfirmacoesVencidas();

        if (
            $this->repository
                ->usuarioExistePorEmail(
                    $email
                )
        ) {
            throw new CadastroException(
                'Não foi possível criar uma nova solicitação para este e-mail. Se você já possui uma conta, utilize a área de login ou a recuperação de senha.',
                409
            );
        }

        $existente =
            $this->repository
                ->buscarPorEmail(
                    $email
                );

        /**
         * Se o cadastro já está aguardando confirmação, não substituímos
         * silenciosamente os dados pessoais. A pessoa pode usar o reenvio
         * do link.
         */
        if (
            $existente !== null
            && $existente['status']
                === 'AGUARDANDO_EMAIL'
        ) {
            return [
                'id' =>
                    (int)
                    $existente['id'],
                'status' =>
                    'AGUARDANDO_EMAIL',
                'email' =>
                    $email,
                'mensagem' =>
                    'Já existe uma solicitação para este e-mail aguardando confirmação. Verifique sua caixa de entrada ou solicite o reenvio do link.',
            ];
        }

        if (
            $existente !== null
            && $existente['status']
                === 'PENDENTE'
        ) {
            throw new CadastroException(
                'Já existe uma solicitação de cadastro aguardando análise para este e-mail.',
                409
            );
        }

        if (
            $existente !== null
            && $existente['status']
                === 'APROVADO'
        ) {
            throw new CadastroException(
                'Não foi possível criar uma nova solicitação para este e-mail. Utilize a área de login ou a recuperação de senha.',
                409
            );
        }

        $token =
            bin2hex(
                random_bytes(
                    32
                )
            );

        $expiraEm =
            (new DateTimeImmutable())
                ->modify(
                    '+'
                    . self::CONFIRMACAO_EMAIL_HORAS
                    . ' hours'
                )
                ->format(
                    'Y-m-d H:i:s'
                );

        $normalizado['senha_hash'] =
            password_hash(
                $normalizado['senha'],
                PASSWORD_DEFAULT
            );

        $normalizado[
            'email_confirmacao_token_hash'
        ] =
            hash(
                'sha256',
                $token
            );

        $normalizado[
            'email_confirmacao_expira_em'
        ] =
            $expiraEm;

        unset(
            $normalizado['senha']
        );

        if ($existente === null) {
            $id =
                $this->repository
                    ->criar(
                        $normalizado
                    );
        } else {
            /**
             * REJEITADO ou EXPIRADO:
             *
             * uma nova tentativa reaproveita o histórico da solicitação,
             * gera nova senha pendente e novo token de confirmação.
             */
            $id =
                (int)
                $existente['id'];

            $this->repository
                ->reabrir(
                    $id,
                    $normalizado
                );
        }

        $emailEnviado =
            $this->enviarConfirmacaoEmail(
                $email,
                (string)
                $normalizado['nome'],
                $token
            );

        return [
            'id' =>
                $id,
            'status' =>
                'AGUARDANDO_EMAIL',
            'email' =>
                $email,
            'email_confirmacao_enviado' =>
                $emailEnviado,
            'mensagem' =>
                $emailEnviado
                    ? 'Solicitação recebida. Enviamos um link para confirmar seu e-mail. Somente após essa confirmação o cadastro seguirá para aprovação.'
                    : 'Solicitação recebida, mas não foi possível enviar o e-mail de confirmação agora. Utilize a opção de reenviar o link.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function listar(
        array $auth,
        ?string $status
    ): array {
        $this->exigirAprovador(
            $auth
        );

        $this->repository
            ->expirarConfirmacoesVencidas();

        $status =
            mb_strtoupper(
                trim(
                    $status
                    ?? 'PENDENTE'
                )
            );

        $validos = [
            'AGUARDANDO_EMAIL',
            'PENDENTE',
            'APROVADO',
            'REJEITADO',
            'EXPIRADO',
        ];

        if (
            !in_array(
                $status,
                $validos,
                true
            )
        ) {
            throw new CadastroException(
                'Status de cadastro inválido.',
                422
            );
        }

        $itens =
            $this->repository
                ->listar(
                    $status
                );

        return [
            'status_filtro' =>
                $status,
            'total' =>
                count($itens),
            'cadastros' =>
                array_map(
                    fn (
                        array $item
                    ): array =>
                        $this
                            ->formatar(
                                $item
                            ),
                    $itens
                ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function obter(
        array $auth,
        int $id
    ): array {
        $this->exigirAprovador(
            $auth
        );

        $item =
            $this->repository
                ->buscarPorId(
                    $id
                );

        if ($item === null) {
            throw new CadastroException(
                'Solicitação de cadastro não encontrada.',
                404
            );
        }

        return $this->formatar(
            $item
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function aprovar(
        array $auth,
        int $id
    ): array {
        $this->exigirAprovador(
            $auth
        );

        /**
         * Guardamos os dados antes da aprovação porque o Repository
         * remove o hash pendente e altera o estado da solicitação.
         *
         * Nome e e-mail são usados somente para a notificação.
         */
        $solicitacao =
            $this->repository
                ->buscarPorId(
                    $id
                );

        if ($solicitacao === null) {
            throw new CadastroException(
                'Solicitação de cadastro não encontrada.',
                404
            );
        }

        $aprovadorId =
            (int)
            ($auth['id'] ?? 0);

        $resultado =
            $this->repository
                ->aprovar(
                    $id,
                    $aprovadorId
                );

        if (
            $resultado['resultado']
            !== 'APROVADO'
        ) {
            return match (
                $resultado['resultado']
            ) {
                'NAO_ENCONTRADO' =>
                    throw new CadastroException(
                        'Solicitação de cadastro não encontrada.',
                        404
                    ),

                'NAO_PENDENTE' =>
                    throw new CadastroException(
                        'Esta solicitação já foi analisada.',
                        409
                    ),

                'EMAIL_EXISTE' =>
                    throw new CadastroException(
                        'Já existe um usuário com este e-mail.',
                        409
                    ),

                'PAPEL_MEMBRO_NAO_ENCONTRADO' =>
                    throw new CadastroException(
                        'O papel MEMBRO não está configurado no sistema.',
                        500
                    ),

                'SEM_SENHA' =>
                    throw new CadastroException(
                        'A solicitação não possui uma senha pendente válida. Peça ao usuário que refaça o cadastro.',
                        409
                    ),

                default =>
                    throw new CadastroException(
                        'Não foi possível aprovar esta solicitação.',
                        500
                    ),
            };
        }

        /**
         * A aprovação já foi confirmada no banco.
         *
         * Se o Gmail/SMTP estiver temporariamente indisponível, a conta
         * NÃO deve voltar ao estado pendente. Registramos a falha no log
         * e informamos o Administrador na resposta.
         */
        $emailEnviado =
            $this->notificarAprovacao(
                (string)
                $solicitacao['email'],
                (string)
                $solicitacao['nome']
            );

        return [
            'cadastro_id' =>
                $id,
            'status' =>
                'APROVADO',
            'usuario_id' =>
                (int)
                $resultado[
                    'usuario_id'
                ],
            'papel_criado' =>
                'MEMBRO',
            'email_notificacao_enviado' =>
                $emailEnviado,
            'mensagem' =>
                $emailEnviado
                    ? 'Cadastro aprovado. O usuário foi criado como MEMBRO e recebeu um e-mail informando que o acesso está liberado.'
                    : 'Cadastro aprovado e usuário criado como MEMBRO, mas não foi possível enviar o e-mail de notificação. O acesso já está liberado.',
        ];
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function rejeitar(
        array $auth,
        int $id,
        array $dados
    ): array {
        $this->exigirAprovador(
            $auth
        );

        $motivo =
            isset(
                $dados['motivo']
            )
                ? trim(
                    (string)
                    $dados['motivo']
                )
                : null;

        if ($motivo === '') {
            $motivo = null;
        }

        if (
            $motivo !== null
            && mb_strlen(
                $motivo
            ) > 500
        ) {
            throw new CadastroException(
                'O motivo da rejeição deve possuir no máximo 500 caracteres.',
                422
            );
        }

        $solicitacao =
            $this->repository
                ->buscarPorId(
                    $id
                );

        if ($solicitacao === null) {
            throw new CadastroException(
                'Solicitação de cadastro não encontrada.',
                404
            );
        }

        $resultado =
            $this->repository
                ->rejeitar(
                    $id,
                    (int)
                    ($auth['id'] ?? 0),
                    $motivo
                );

        if (
            $resultado
            === 'NAO_ENCONTRADO'
        ) {
            throw new CadastroException(
                'Solicitação de cadastro não encontrada.',
                404
            );
        }

        if (
            $resultado
            === 'NAO_PENDENTE'
        ) {
            throw new CadastroException(
                'Esta solicitação já foi analisada.',
                409
            );
        }

        $emailEnviado =
            $this->notificarRejeicao(
                (string)
                $solicitacao['email'],
                (string)
                $solicitacao['nome'],
                $motivo
            );

        return [
            'cadastro_id' =>
                $id,
            'status' =>
                'REJEITADO',
            'email_notificacao_enviado' =>
                $emailEnviado,
            'mensagem' =>
                $emailEnviado
                    ? 'Solicitação rejeitada. O solicitante recebeu um e-mail com a atualização e poderá refazer o cadastro.'
                    : 'Solicitação rejeitada, mas não foi possível enviar o e-mail de notificação. O solicitante ainda poderá refazer o cadastro.',
        ];
    }

    /**
     * A falha de e-mail é operacional e não altera a decisão já gravada.
     */
    private function notificarAprovacao(
        string $email,
        string $nome
    ): bool {
        try {
            $this->emailService
                ->enviarCadastroAprovado(
                    $email,
                    $nome
                );

            return true;
        } catch (Throwable $e) {
            error_log(
                '[SYN] Falha ao enviar e-mail de cadastro aprovado para '
                . $email
                . ': '
                . $e->getMessage()
            );

            return false;
        }
    }

    /**
     * A rejeição também continua válida se o SMTP falhar.
     */
    private function notificarRejeicao(
        string $email,
        string $nome,
        ?string $motivo
    ): bool {
        try {
            $this->emailService
                ->enviarCadastroRejeitado(
                    $email,
                    $nome,
                    $motivo
                );

            return true;
        } catch (Throwable $e) {
            error_log(
                '[SYN] Falha ao enviar e-mail de cadastro rejeitado para '
                . $email
                . ': '
                . $e->getMessage()
            );

            return false;
        }
    }

    /**
     * Confirma o token enviado ao endereço informado no cadastro.
     *
     * Após a confirmação o status passa de AGUARDANDO_EMAIL para
     * PENDENTE. Só então a solicitação aparece como pronta para análise.
     *
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function confirmarEmail(
        array $dados
    ): array {
        $token =
            trim(
                (string)
                ($dados['token'] ?? '')
            );

        if (
            !preg_match(
                '/^[a-f0-9]{64}$/',
                $token
            )
        ) {
            throw new CadastroException(
                'O link de confirmação é inválido ou expirou.',
                422
            );
        }

        $tokenHash =
            hash(
                'sha256',
                $token
            );

        $solicitacao =
            $this->repository
                ->buscarPorTokenConfirmacaoHash(
                    $tokenHash
                );

        if (
            $solicitacao === null
            || $solicitacao['status']
                !== 'AGUARDANDO_EMAIL'
        ) {
            throw new CadastroException(
                'O link de confirmação é inválido ou já foi utilizado.',
                422
            );
        }

        $expiraEm =
            new DateTimeImmutable(
                (string)
                $solicitacao[
                    'email_confirmacao_expira_em'
                ]
            );

        if (
            $expiraEm
            < new DateTimeImmutable()
        ) {
            $this->repository
                ->expirarPorId(
                    (int)
                    $solicitacao['id']
                );

            throw new CadastroException(
                'O link de confirmação expirou. Faça uma nova solicitação de cadastro.',
                422
            );
        }

        $confirmou =
            $this->repository
                ->confirmarEmail(
                    (int)
                    $solicitacao['id'],
                    $tokenHash
                );

        if (!$confirmou) {
            throw new CadastroException(
                'Não foi possível confirmar este e-mail. Solicite um novo link.',
                409
            );
        }

        return [
            'cadastro_id' =>
                (int)
                $solicitacao['id'],
            'status' =>
                'PENDENTE',
            'mensagem' =>
                'E-mail confirmado. Seu cadastro agora está aguardando aprovação.',
        ];
    }

    /**
     * Reenvia a confirmação sem informar ao público se o endereço existe
     * ou não na base.
     *
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function reenviarConfirmacao(
        array $dados
    ): array {
        $email =
            mb_strtolower(
                trim(
                    (string)
                    ($dados['email'] ?? '')
                )
            );

        if (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            throw new CadastroException(
                'Informe um e-mail válido.',
                422
            );
        }

        $resposta = [
            'mensagem' =>
                'Se houver uma solicitação aguardando confirmação para este e-mail, um novo link será enviado. Se o cadastro tiver expirado, faça uma nova solicitação.',
        ];

        $this->repository
            ->expirarConfirmacoesVencidas();

        $solicitacao =
            $this->repository
                ->buscarPorEmail(
                    $email
                );

        if (
            $solicitacao === null
            || $solicitacao['status']
                !== 'AGUARDANDO_EMAIL'
        ) {
            return $resposta;
        }

        $token =
            bin2hex(
                random_bytes(
                    32
                )
            );

        $expiraEm =
            (new DateTimeImmutable())
                ->modify(
                    '+'
                    . self::CONFIRMACAO_EMAIL_HORAS
                    . ' hours'
                )
                ->format(
                    'Y-m-d H:i:s'
                );

        $atualizou =
            $this->repository
                ->atualizarTokenConfirmacao(
                    (int)
                    $solicitacao['id'],
                    hash(
                        'sha256',
                        $token
                    ),
                    $expiraEm
                );

        if ($atualizou) {
            $this->enviarConfirmacaoEmail(
                $email,
                (string)
                $solicitacao['nome'],
                $token
            );
        }

        return $resposta;
    }

    /**
     * Monta o link do frontend e envia pelo mesmo SMTP já utilizado pelo
     * fluxo de recuperação de senha.
     */
    private function enviarConfirmacaoEmail(
        string $email,
        string $nome,
        string $token
    ): bool {
        $baseUrl =
            rtrim(
                (string)
                ($_ENV['APP_WEB_URL']
                    ?? getenv('APP_WEB_URL')
                    ?: 'http://localhost:5173'),
                '/'
            );

        $url =
            $baseUrl
            . '/cadastro/confirmar-email?token='
            . rawurlencode(
                $token
            );

        try {
            $this->emailService
                ->enviarConfirmacaoCadastro(
                    $email,
                    $nome,
                    $url,
                    self::CONFIRMACAO_EMAIL_HORAS
                );

            return true;
        } catch (Throwable $e) {
            error_log(
                '[SYN] Falha ao enviar confirmação de e-mail do cadastro para '
                . $email
                . ': '
                . $e->getMessage()
            );

            return false;
        }
    }

    private function exigirAprovador(
        array $auth
    ): void {
        $papel =
            (string)
            (
                $auth['papel']['codigo']
                ?? ''
            );

        if (
            $papel
            === 'ADMINISTRADOR'
        ) {
            return;
        }

        if (
            $papel
            !== 'ORGANIZADOR'
        ) {
            throw new CadastroException(
                'Você não possui permissão para analisar cadastros.',
                403
            );
        }

        $usuarioId =
            (int)
            ($auth['id'] ?? 0);

        if (
            !$this->repository
                ->usuarioPossuiPermissaoEspecial(
                    $usuarioId,
                    self::PERMISSAO_APROVAR
                )
        ) {
            throw new CadastroException(
                'Este Organizador não possui a permissão especial "Aprovar cadastros".',
                403
            );
        }
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    private function validarCadastro(
        array $dados
    ): array {
        $erros = [];

        $nome =
            trim(
                (string)
                ($dados['nome'] ?? '')
            );

        if (
            mb_strlen(
                $nome
            ) < 2
        ) {
            $erros[] =
                'Informe o nome completo.';
        }

        if (
            mb_strlen(
                $nome
            ) > 150
        ) {
            $erros[] =
                'O nome deve possuir no máximo 150 caracteres.';
        }

        $email =
            mb_strtolower(
                trim(
                    (string)
                    ($dados['email'] ?? '')
                )
            );

        if (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            $erros[] =
                'Informe um e-mail válido.';
        }

        if (
            mb_strlen(
                $email
            ) > 150
        ) {
            $erros[] =
                'O e-mail deve possuir no máximo 150 caracteres.';
        }

        $senha =
            (string)
            ($dados['senha'] ?? '');

        if (
            mb_strlen(
                $senha
            ) < 5
        ) {
            $erros[] =
                'A senha deve possuir pelo menos 5 caracteres.';
        }

        $telefone =
            isset(
                $dados['telefone']
            )
                ? trim(
                    (string)
                    $dados['telefone']
                )
                : null;

        if ($telefone === '') {
            $telefone = null;
        }

        if (
            $telefone !== null
            && mb_strlen(
                $telefone
            ) > 30
        ) {
            $erros[] =
                'O telefone deve possuir no máximo 30 caracteres.';
        }

        $dataNascimento =
            isset(
                $dados[
                    'data_nascimento'
                ]
            )
                ? trim(
                    (string)
                    $dados[
                        'data_nascimento'
                    ]
                )
                : null;

        if ($dataNascimento === '') {
            $dataNascimento = null;
        }

        if (
            $dataNascimento !== null
        ) {
            $data =
                DateTimeImmutable
                    ::createFromFormat(
                        '!Y-m-d',
                        $dataNascimento
                    );

            if (
                $data === false
                || $data->format(
                    'Y-m-d'
                )
                    !== $dataNascimento
            ) {
                $erros[] =
                    'A data de nascimento é inválida.';
            } elseif (
                $data >
                new DateTimeImmutable(
                    'today'
                )
            ) {
                $erros[] =
                    'A data de nascimento não pode estar no futuro.';
            }
        }

        if ($erros !== []) {
            throw new CadastroException(
                implode(
                    ' ',
                    $erros
                ),
                422
            );
        }

        return [
            'nome' =>
                $nome,
            'email' =>
                $email,
            'senha' =>
                $senha,
            'telefone' =>
                $telefone,
            'data_nascimento' =>
                $dataNascimento,
        ];
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function formatar(
        array $item
    ): array {
        return [
            'id' =>
                (int)
                $item['id'],
            'nome' =>
                $item['nome'],
            'email' =>
                $item['email'],
            'telefone' =>
                $item['telefone'],
            'data_nascimento' =>
                $item[
                    'data_nascimento'
                ],
            'status' =>
                $item['status'],
            'tentativas' =>
                (int)
                ($item['tentativas']
                ?? 1),
            'motivo_rejeicao' =>
                $item[
                    'motivo_rejeicao'
                ]
                ?? null,
            'solicitado_em' =>
                $item[
                    'solicitado_em'
                ],
            'email_confirmado_em' =>
                $item[
                    'email_confirmado_em'
                ]
                ?? null,
            'email_confirmacao_expira_em' =>
                $item[
                    'email_confirmacao_expira_em'
                ]
                ?? null,
            'analisado_em' =>
                $item[
                    'analisado_em'
                ]
                ?? null,
            'analisado_por' =>
                $item[
                    'analisado_por_nome'
                ]
                ?? null,
            'usuario_criado_id' =>
                isset(
                    $item[
                        'usuario_criado_id'
                    ]
                )
                    ? (int)
                    $item[
                        'usuario_criado_id'
                    ]
                    : null,
            'dias_aguardando' =>
                isset(
                    $item[
                        'dias_aguardando'
                    ]
                )
                    ? (int)
                    $item[
                        'dias_aguardando'
                    ]
                    : null,
        ];
    }
}
