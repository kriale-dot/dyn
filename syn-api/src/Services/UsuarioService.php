<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\UsuarioNaoEncontradoException;
use App\Repositories\UsuarioRepository;
use DateTimeImmutable;
use RuntimeException;

/**
 * Service do módulo de usuários.
 */
final class UsuarioService
{
    public function __construct(
        private UsuarioRepository $usuarioRepository
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarTodos(): array
    {
        $usuarios = $this->usuarioRepository->listarTodos();

        return array_map(
            fn (array $usuario): array =>
                $this->formatarUsuario($usuario),
            $usuarios
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function buscarPorId(int $id): array
    {
        $usuario = $this->usuarioRepository->buscarPorId($id);

        if ($usuario === null) {
            throw new UsuarioNaoEncontradoException($id);
        }

        $resultado = $this->formatarUsuario($usuario);

        $funcoes = $this->usuarioRepository
            ->listarFuncoesPorUsuarioId($id);

        $resultado['funcoes'] = array_map(
            fn (array $funcao): array =>
                $this->formatarFuncao($funcao),
            $funcoes
        );

        return $resultado;
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function criar(array $dados): array
    {
        $dadosValidados =
            $this->validarDadosCriacao($dados);

        $senhaHash = password_hash(
            $dadosValidados['senha'],
            PASSWORD_DEFAULT
        );

        if ($senhaHash === false) {
            throw new RuntimeException(
                'Não foi possível gerar o hash seguro da senha.'
            );
        }

        $novoId = $this->usuarioRepository->criar([
            'nome' =>
                $dadosValidados['nome'],
            'data_nascimento' =>
                $dadosValidados['data_nascimento'],
            'telefone' =>
                $dadosValidados['telefone'],
            'email' =>
                $dadosValidados['email'],
            'senha_hash' =>
                $senhaHash,
            'foto' =>
                $dadosValidados['foto'],
            'papel_id' =>
                $dadosValidados['papel_id'],
        ]);

        return $this->buscarPorId($novoId);
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function atualizar(
        int $id,
        array $dados
    ): array {
        if (
            $this->usuarioRepository->buscarPorId($id)
            === null
        ) {
            throw new UsuarioNaoEncontradoException($id);
        }

        $dadosValidados =
            $this->validarDadosAtualizacao(
                $id,
                $dados
            );

        $this->usuarioRepository->atualizar(
            $id,
            $dadosValidados
        );

        return $this->buscarPorId($id);
    }

    /**
     * @return array<string, mixed>
     */
    public function desativar(int $id): array
    {
        $usuarioAtual =
            $this->usuarioRepository->buscarPorId($id);

        if ($usuarioAtual === null) {
            throw new UsuarioNaoEncontradoException($id);
        }

        $participacoesFuturas =
            $this->usuarioRepository
                ->listarParticipacoesFuturasAtivas($id);

        $jaEstavaInativo =
            $usuarioAtual['status'] === 'INATIVO';

        if (!$jaEstavaInativo) {
            $this->usuarioRepository->desativar($id);
        }

        return [
            'usuario' =>
                $this->buscarPorId($id),
            'ja_estava_inativo' =>
                $jaEstavaInativo,
            'possui_escalas_futuras' =>
                $participacoesFuturas !== [],
            'total_escalas_futuras' =>
                count($participacoesFuturas),
            'escalas_futuras' =>
                array_map(
                    fn (array $participacao): array =>
                        $this->formatarParticipacaoFutura(
                            $participacao
                        ),
                    $participacoesFuturas
                ),
        ];
    }

    /**
     * Monta a tela conceitual "Minha Semana".
     *
     * A data de referência é opcional:
     * - null -> hoje;
     * - YYYY-MM-DD -> semana que contém essa data.
     *
     * A semana é considerada de segunda-feira a domingo.
     *
     * @return array<string, mixed>
     */
    public function minhaSemana(
        int $usuarioId,
        ?string $dataReferencia
    ): array {
        $usuario =
            $this->usuarioRepository
                ->buscarPorId($usuarioId);

        if ($usuario === null) {
            throw new UsuarioNaoEncontradoException(
                $usuarioId
            );
        }

        $referencia =
            $this->resolverDataReferencia(
                $dataReferencia
            );

        $inicioSemana =
            $referencia
                ->modify('monday this week')
                ->setTime(0, 0, 0);

        $fimSemana =
            $inicioSemana->modify('+7 days');

        $compromissos =
            $this->usuarioRepository
                ->listarCompromissosDaSemana(
                    $usuarioId,
                    $inicioSemana->format(
                        'Y-m-d H:i:s'
                    ),
                    $fimSemana->format(
                        'Y-m-d H:i:s'
                    )
                );

        return [
            'usuario' => [
                'id' =>
                    (int) $usuario['id'],
                'nome' =>
                    $usuario['nome'],
                'foto' =>
                    $usuario['foto'],
                'status' =>
                    $usuario['status'],
            ],

            'semana' => [
                'data_referencia' =>
                    $referencia->format('Y-m-d'),
                'inicio' =>
                    $inicioSemana->format('Y-m-d'),
                'fim' =>
                    $fimSemana
                        ->modify('-1 day')
                        ->format('Y-m-d'),
            ],

            'total_compromissos' =>
                count($compromissos),

            'compromissos' =>
                array_map(
                    fn (array $compromisso): array =>
                        $this->formatarCompromissoSemana(
                            $compromisso
                        ),
                    $compromissos
                ),
        ];
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    private function validarDadosCriacao(
        array $dados
    ): array {
        $erros = [];

        $dadosComuns =
            $this->validarCamposComuns(
                $dados,
                $erros
            );

        $email = $dadosComuns['email'];

        if (
            $email !== ''
            && filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
            && $this->usuarioRepository
                ->emailExiste($email)
        ) {
            $erros['email'] =
                'Já existe um usuário cadastrado com este e-mail.';
        }

        $senha = (string) ($dados['senha'] ?? '');

        if ($senha === '') {
            $erros['senha'] =
                'A senha é obrigatória.';
        } elseif (mb_strlen($senha) < 8) {
            $erros['senha'] =
                'A senha deve possuir pelo menos 8 caracteres.';
        }

        if ($erros !== []) {
            throw new DadosInvalidosException(
                $erros
            );
        }

        $dadosComuns['senha'] = $senha;

        return $dadosComuns;
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    private function validarDadosAtualizacao(
        int $usuarioId,
        array $dados
    ): array {
        $erros = [];

        $dadosValidados =
            $this->validarCamposComuns(
                $dados,
                $erros
            );

        $email =
            $dadosValidados['email'];

        if (
            $email !== ''
            && filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
            && $this->usuarioRepository
                ->emailExisteParaOutroUsuario(
                    $email,
                    $usuarioId
                )
        ) {
            $erros['email'] =
                'Já existe outro usuário cadastrado com este e-mail.';
        }

        if ($erros !== []) {
            throw new DadosInvalidosException(
                $erros
            );
        }

        return $dadosValidados;
    }

    /**
     * @param array<string, mixed> $dados
     * @param array<string, string> $erros
     * @return array<string, mixed>
     */
    private function validarCamposComuns(
        array $dados,
        array &$erros
    ): array {
        $nome = trim(
            (string) ($dados['nome'] ?? '')
        );

        if ($nome === '') {
            $erros['nome'] =
                'O nome é obrigatório.';
        } elseif (mb_strlen($nome) > 150) {
            $erros['nome'] =
                'O nome deve possuir no máximo 150 caracteres.';
        }

        $email = mb_strtolower(
            trim((string) ($dados['email'] ?? ''))
        );

        if ($email === '') {
            $erros['email'] =
                'O e-mail é obrigatório.';
        } elseif (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            $erros['email'] =
                'Informe um endereço de e-mail válido.';
        }

        $papelId = filter_var(
            $dados['papel_id'] ?? null,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1,
                ],
            ]
        );

        if ($papelId === false) {
            $erros['papel_id'] =
                'Informe um papel de acesso válido.';
            $papelId = null;
        } elseif (
            !$this->usuarioRepository
                ->papelExiste((int) $papelId)
        ) {
            $erros['papel_id'] =
                'O papel de acesso informado não existe.';
        }

        $dataNascimento =
            $this->textoOpcional(
                $dados['data_nascimento'] ?? null
            );

        if (
            $dataNascimento !== null
            && !$this->dataValida(
                $dataNascimento
            )
        ) {
            $erros['data_nascimento'] =
                'Informe a data de nascimento no formato YYYY-MM-DD.';
        }

        return [
            'nome' =>
                $nome,
            'data_nascimento' =>
                $dataNascimento,
            'telefone' =>
                $this->textoOpcional(
                    $dados['telefone'] ?? null
                ),
            'email' =>
                $email,
            'foto' =>
                $this->textoOpcional(
                    $dados['foto'] ?? null
                ),
            'papel_id' =>
                $papelId === null
                    ? null
                    : (int) $papelId,
        ];
    }

    /**
     * @throws DadosInvalidosException
     */
    private function resolverDataReferencia(
        ?string $dataReferencia
    ): DateTimeImmutable {
        if (
            $dataReferencia === null
            || trim($dataReferencia) === ''
        ) {
            return new DateTimeImmutable('today');
        }

        $dataReferencia =
            trim($dataReferencia);

        $data =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $dataReferencia
            );

        if (
            $data === false
            || $data->format('Y-m-d')
                !== $dataReferencia
        ) {
            throw new DadosInvalidosException([
                'data_referencia' =>
                    'Use uma data válida no formato YYYY-MM-DD.',
            ]);
        }

        return $data;
    }

    private function dataValida(
        string $data
    ): bool {
        $objetoData =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $data
            );

        return $objetoData !== false
            && $objetoData->format('Y-m-d')
                === $data;
    }

    private function textoOpcional(
        mixed $valor
    ): ?string {
        if ($valor === null) {
            return null;
        }

        $texto = trim((string) $valor);

        return $texto === ''
            ? null
            : $texto;
    }

    /**
     * @param array<string, mixed> $usuario
     * @return array<string, mixed>
     */
    private function formatarUsuario(
        array $usuario
    ): array {
        return [
            'id' =>
                (int) $usuario['id'],
            'nome' =>
                $usuario['nome'],
            'data_nascimento' =>
                $usuario['data_nascimento'],
            'telefone' =>
                $usuario['telefone'],
            'email' =>
                $usuario['email'],
            'foto' =>
                $usuario['foto'],
            'status' =>
                $usuario['status'],
            'papel' => [
                'id' =>
                    (int) $usuario['papel_id'],
                'codigo' =>
                    $usuario['papel_codigo'],
                'nome' =>
                    $usuario['papel_nome'],
            ],
            'ultimo_login_em' =>
                $usuario['ultimo_login_em'],
            'desativado_em' =>
                $usuario['desativado_em'],
            'criado_em' =>
                $usuario['criado_em'],
            'atualizado_em' =>
                $usuario['atualizado_em'],
        ];
    }

    /**
     * @param array<string, mixed> $funcao
     * @return array<string, mixed>
     */
    private function formatarFuncao(
        array $funcao
    ): array {
        $departamento = null;

        if ($funcao['departamento_id'] !== null) {
            $departamento = [
                'id' =>
                    (int) $funcao['departamento_id'],
                'nome' =>
                    $funcao['departamento_nome'],
                'ativo' =>
                    (bool) $funcao[
                        'departamento_ativo'
                    ],
            ];
        }

        return [
            'id' =>
                (int) $funcao['id'],
            'nome' =>
                $funcao['nome'],
            'descricao' =>
                $funcao['descricao'],
            'ativo' =>
                (bool) $funcao['ativo'],
            'atribuido_em' =>
                $funcao['atribuido_em'],
            'departamento' =>
                $departamento,
        ];
    }

    /**
     * @param array<string, mixed> $participacao
     * @return array<string, mixed>
     */
    private function formatarParticipacaoFutura(
        array $participacao
    ): array {
        return [
            'participacao_id' =>
                (int) $participacao[
                    'participacao_id'
                ],
            'status' =>
                $participacao[
                    'participacao_status'
                ],
            'funcao' =>
                $participacao[
                    'funcao_nome_historico'
                ],
            'programacao' => [
                'id' =>
                    (int) $participacao[
                        'programacao_id'
                    ],
                'titulo' =>
                    $participacao['titulo'],
                'inicio_em' =>
                    $participacao['inicio_em'],
                'fim_em' =>
                    $participacao['fim_em'],
                'local' =>
                    $participacao[
                        'local_nome_historico'
                    ],
            ],
        ];
    }

    /**
     * Formato pensado para a futura interface mobile-first
     * "Minha Semana".
     *
     * @param array<string, mixed> $compromisso
     * @return array<string, mixed>
     */
    private function formatarCompromissoSemana(
        array $compromisso
    ): array {
        return [
            'participacao_id' =>
                (int) $compromisso[
                    'participacao_id'
                ],
            'programacao_id' =>
                (int) $compromisso[
                    'programacao_id'
                ],

            'titulo' =>
                $compromisso['titulo'],
            'descricao' =>
                $compromisso['descricao'],

            'inicio' =>
                $compromisso['inicio_em'],
            'fim' =>
                $compromisso['fim_em'],

            'programacao_status' =>
                $compromisso[
                    'programacao_status'
                ],

            'participacao_status' =>
                $compromisso[
                    'participacao_status'
                ],

            'pendente_confirmacao' =>
                $compromisso[
                    'participacao_status'
                ] === 'ESCALADO',

            'permite_resposta' =>
                (bool) $compromisso[
                    'permite_resposta'
                ],

            'tipo_programacao' =>
                $compromisso[
                    'tipo_programacao_nome_historico'
                ],

            'funcao' =>
                $compromisso[
                    'funcao_nome_historico'
                ],

            'departamento' =>
                $compromisso[
                    'departamento_nome_historico'
                ],

            'local' =>
                $compromisso[
                    'local_nome_historico'
                ],

            'organizador' =>
                $compromisso[
                    'organizador_nome_historico'
                ],
        ];
    }
}
