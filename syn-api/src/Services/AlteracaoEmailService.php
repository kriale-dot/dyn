<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AutenticacaoException;
use App\Repositories\AlteracaoEmailRepository;
use DateTimeImmutable;
use Throwable;

/**
 * Fluxo seguro de troca do e-mail de login.
 */
final class AlteracaoEmailService
{
    private const TOKEN_HORAS =
        24;

    public function __construct(
        private AlteracaoEmailRepository $repository,
        private EmailService $emailService
    ) {
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function solicitar(
        int $usuarioId,
        array $dados
    ): array {
        if ($usuarioId < 1) {
            throw new AutenticacaoException(
                'Usuário autenticado inválido.'
            );
        }

        $senhaAtual =
            (string)
            ($dados['senha_atual'] ?? '');

        $novoEmail =
            mb_strtolower(
                trim(
                    (string)
                    ($dados['novo_email'] ?? '')
                )
            );

        if ($senhaAtual === '') {
            throw new AutenticacaoException(
                'Informe sua senha atual.'
            );
        }

        if (
            !filter_var(
                $novoEmail,
                FILTER_VALIDATE_EMAIL
            )
            || mb_strlen(
                $novoEmail
            ) > 150
        ) {
            throw new AutenticacaoException(
                'Informe um novo endereço de e-mail válido.'
            );
        }

        $usuario =
            $this->repository
                ->buscarUsuarioAtivo(
                    $usuarioId
                );

        if ($usuario === null) {
            throw new AutenticacaoException(
                'Usuário não encontrado ou inativo.'
            );
        }

        if (
            !password_verify(
                $senhaAtual,
                (string)
                $usuario['senha_hash']
            )
        ) {
            throw new AutenticacaoException(
                'A senha atual está incorreta.'
            );
        }

        $emailAtual =
            mb_strtolower(
                (string)
                $usuario['email']
            );

        if (
            $novoEmail === $emailAtual
        ) {
            throw new AutenticacaoException(
                'O novo e-mail deve ser diferente do e-mail atual.'
            );
        }

        if (
            $this->repository
                ->emailExiste(
                    $novoEmail,
                    $usuarioId
                )
        ) {
            throw new AutenticacaoException(
                'Este endereço de e-mail já está sendo utilizado por outro usuário.'
            );
        }

        $token =
            bin2hex(
                random_bytes(
                    32
                )
            );

        $tokenHash =
            hash(
                'sha256',
                $token
            );

        $expiraEm =
            (new DateTimeImmutable())
                ->modify(
                    '+'
                    . self::TOKEN_HORAS
                    . ' hours'
                )
                ->format(
                    'Y-m-d H:i:s'
                );

        $this->repository
            ->cancelarPendentes(
                $usuarioId
            );

        $alteracaoId =
            $this->repository
                ->criar(
                    $usuarioId,
                    (string)
                    $usuario['email'],
                    $novoEmail,
                    $tokenHash,
                    $expiraEm
                );

        $url =
            $this->urlFrontend(
                '/conta/confirmar-email?token='
                . rawurlencode(
                    $token
                )
            );

        /**
         * O novo endereço precisa provar que é controlado pelo usuário.
         */
        $emailNovoEnviado =
            $this->enviarSeguro(
                fn () =>
                    $this->emailService
                        ->enviarConfirmacaoAlteracaoEmail(
                            $novoEmail,
                            (string)
                            $usuario['nome'],
                            $url,
                            self::TOKEN_HORAS
                        ),
                'confirmação de novo e-mail',
                $novoEmail
            );

        /**
         * O endereço antigo recebe um aviso de segurança.
         */
        $this->enviarSeguro(
            fn () =>
                $this->emailService
                    ->enviarAvisoSolicitacaoAlteracaoEmail(
                        (string)
                        $usuario['email'],
                        (string)
                        $usuario['nome'],
                        $novoEmail
                    ),
            'aviso de alteração de e-mail',
            (string)
            $usuario['email']
        );

        return [
            'alteracao_id' =>
                $alteracaoId,
            'novo_email' =>
                $novoEmail,
            'status' =>
                'PENDENTE',
            'confirmacao_enviada' =>
                $emailNovoEnviado,
            'expira_em' =>
                $expiraEm,
            'mensagem' =>
                $emailNovoEnviado
                    ? 'Enviamos um link de confirmação para o novo endereço. O e-mail da conta só será alterado depois da confirmação.'
                    : 'A solicitação foi criada, mas não foi possível enviar a confirmação agora. Tente solicitar a alteração novamente mais tarde.',
        ];
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function confirmar(
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
            throw new AutenticacaoException(
                'O link de alteração de e-mail é inválido ou expirou.'
            );
        }

        $tokenHash =
            hash(
                'sha256',
                $token
            );

        $alteracao =
            $this->repository
                ->buscarPorTokenHash(
                    $tokenHash
                );

        if (
            $alteracao === null
            || $alteracao['status']
                !== 'PENDENTE'
        ) {
            throw new AutenticacaoException(
                'O link de alteração de e-mail é inválido ou já foi utilizado.'
            );
        }

        $expiraEm =
            new DateTimeImmutable(
                (string)
                $alteracao['expira_em']
            );

        if (
            $expiraEm
            < new DateTimeImmutable()
        ) {
            $this->repository
                ->marcarExpirado(
                    (int)
                    $alteracao['id']
                );

            throw new AutenticacaoException(
                'O link de alteração de e-mail expirou.'
            );
        }

        $resultado =
            $this->repository
                ->confirmar(
                    (int)
                    $alteracao['id'],
                    $tokenHash
                );

        if (
            $resultado['resultado']
            !== 'CONFIRMADO'
        ) {
            $mensagem =
                match (
                    $resultado['resultado']
                ) {
                    'EMAIL_EM_USO' =>
                        'O novo endereço passou a ser utilizado por outro usuário. Solicite a alteração novamente.',

                    'USUARIO_INATIVO' =>
                        'Este usuário está inativo.',

                    'EMAIL_ATUAL_MUDOU' =>
                        'O e-mail atual da conta mudou desde a solicitação. Faça uma nova solicitação.',

                    default =>
                        'Não foi possível confirmar esta alteração de e-mail.',
                };

            throw new AutenticacaoException(
                $mensagem
            );
        }

        /**
         * A alteração já foi confirmada no banco. Falhas de e-mail não
         * devem desfazer a troca.
         */
        $this->enviarSeguro(
            fn () =>
                $this->emailService
                    ->enviarEmailAlterado(
                        (string)
                        $resultado[
                            'novo_email'
                        ],
                        (string)
                        $resultado['nome']
                    ),
            'confirmação final de novo e-mail',
            (string)
            $resultado['novo_email']
        );

        $this->enviarSeguro(
            fn () =>
                $this->emailService
                    ->enviarAvisoEmailAlterado(
                        (string)
                        $resultado[
                            'email_anterior'
                        ],
                        (string)
                        $resultado['nome'],
                        (string)
                        $resultado[
                            'novo_email'
                        ]
                    ),
            'aviso ao e-mail anterior',
            (string)
            $resultado['email_anterior']
        );

        return [
            'status' =>
                'CONFIRMADO',
            'novo_email' =>
                $resultado['novo_email'],
            'sessoes_encerradas' =>
                true,
            'mensagem' =>
                'E-mail alterado com sucesso. Por segurança, todas as sessões foram encerradas. Entre novamente usando o novo endereço.',
        ];
    }

    private function urlFrontend(
        string $caminho
    ): string {
        $base =
            rtrim(
                (string)
                (
                    $_ENV['APP_WEB_URL']
                    ?? getenv(
                        'APP_WEB_URL'
                    )
                    ?: 'http://localhost:5173'
                ),
                '/'
            );

        return $base . $caminho;
    }

    private function enviarSeguro(
        callable $callback,
        string $tipo,
        string $destinatario
    ): bool {
        try {
            $callback();

            return true;
        } catch (Throwable $e) {
            error_log(
                '[SYN] Falha no envio de '
                . $tipo
                . ' para '
                . $destinatario
                . ': '
                . $e->getMessage()
            );

            return false;
        }
    }
}
