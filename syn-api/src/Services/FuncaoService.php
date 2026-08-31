<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\FuncaoNaoEncontradaException;
use App\Exceptions\UsuarioNaoEncontradoException;
use App\Repositories\FuncaoRepository;

/**
 * Service do módulo de funções.
 *
 * Aqui ficam:
 * - validações;
 * - regras de negócio;
 * - decisões sobre atribuição/removal de funções.
 */
final class FuncaoService
{
    public function __construct(
        private FuncaoRepository $funcaoRepository
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarTodas(): array
    {
        $funcoes = $this->funcaoRepository->listarTodas();

        return array_map(
            fn (array $funcao): array =>
                $this->formatarFuncao($funcao),
            $funcoes
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function buscarPorId(int $id): array
    {
        $funcao = $this->funcaoRepository->buscarPorId($id);

        if ($funcao === null) {
            throw new FuncaoNaoEncontradaException($id);
        }

        return $this->formatarFuncao($funcao);
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function criar(array $dados): array
    {
        $dadosValidados = $this->validarDados(
            $dados
        );

        if (
            $this->funcaoRepository->nomeExisteNoDepartamento(
                $dadosValidados['nome'],
                $dadosValidados['departamento_id']
            )
        ) {
            throw new DadosInvalidosException([
                'nome' =>
                    'Já existe uma função com este nome no departamento informado.',
            ]);
        }

        $id = $this->funcaoRepository->criar(
            $dadosValidados
        );

        return $this->buscarPorId($id);
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function atualizar(
        int $id,
        array $dados
    ): array {
        $funcaoAtual = $this->funcaoRepository->buscarPorId($id);

        if ($funcaoAtual === null) {
            throw new FuncaoNaoEncontradaException($id);
        }

        $dadosValidados = $this->validarDados(
            $dados
        );

        if (
            $this->funcaoRepository->nomeExisteNoDepartamento(
                $dadosValidados['nome'],
                $dadosValidados['departamento_id'],
                $id
            )
        ) {
            throw new DadosInvalidosException([
                'nome' =>
                    'Já existe outra função com este nome no departamento informado.',
            ]);
        }

        $this->funcaoRepository->atualizar(
            $id,
            $dadosValidados
        );

        return $this->buscarPorId($id);
    }

    /**
     * Desativa a função sem apagá-la.
     *
     * As relações existentes em usuarios_funcoes não são apagadas
     * automaticamente. Como a função passa a ativo=false, ela não
     * poderá receber novas atribuições.
     *
     * @return array<string, mixed>
     */
    public function desativar(int $id): array
    {
        $funcao = $this->funcaoRepository->buscarPorId($id);

        if ($funcao === null) {
            throw new FuncaoNaoEncontradaException($id);
        }

        $jaEstavaInativa = !(bool) $funcao['ativo'];

        $totalUsuarios = $this->funcaoRepository
            ->contarUsuariosComFuncao($id);

        if (!$jaEstavaInativa) {
            $this->funcaoRepository->desativar($id);
        }

        return [
            'funcao' => $this->buscarPorId($id),
            'ja_estava_inativa' => $jaEstavaInativa,
            'usuarios_com_funcao_atual' => $totalUsuarios,
        ];
    }

    /**
     * Atribui uma função atual a um usuário.
     *
     * Regras:
     * - usuário precisa existir;
     * - usuário precisa estar ATIVO;
     * - função precisa existir;
     * - função precisa estar ativa;
     * - atribuição repetida não cria duplicidade.
     *
     * @return array<string, mixed>
     */
    public function atribuirAoUsuario(
        int $usuarioId,
        int $funcaoId
    ): array {
        $usuario = $this->funcaoRepository
            ->buscarUsuarioPorId($usuarioId);

        if ($usuario === null) {
            throw new UsuarioNaoEncontradoException($usuarioId);
        }

        $funcao = $this->funcaoRepository
            ->buscarPorId($funcaoId);

        if ($funcao === null) {
            throw new FuncaoNaoEncontradaException($funcaoId);
        }

        if ($usuario['status'] !== 'ATIVO') {
            throw new DadosInvalidosException([
                'usuario' =>
                    'Usuários inativos não podem receber novas funções.',
            ]);
        }

        if (!(bool) $funcao['ativo']) {
            throw new DadosInvalidosException([
                'funcao' =>
                    'Uma função inativa não pode ser atribuída a usuários.',
            ]);
        }

        $jaPossuia = $this->funcaoRepository
            ->usuarioPossuiFuncao(
                $usuarioId,
                $funcaoId
            );

        if (!$jaPossuia) {
            $this->funcaoRepository->atribuirAoUsuario(
                $usuarioId,
                $funcaoId
            );
        }

        return [
            'usuario' => [
                'id' => (int) $usuario['id'],
                'nome' => $usuario['nome'],
                'status' => $usuario['status'],
            ],
            'funcao' => $this->buscarPorId($funcaoId),
            'ja_possuia_funcao' => $jaPossuia,
        ];
    }

    /**
     * Remove a função ATUAL do usuário.
     *
     * Isto executa DELETE apenas em usuarios_funcoes.
     *
     * A tabela participacoes não é alterada, portanto o histórico
     * continua registrando a função que a pessoa exerceu no passado.
     *
     * DELETE é tratado de forma idempotente:
     * repetir a chamada mantém o mesmo estado final.
     *
     * @return array<string, mixed>
     */
    public function removerDoUsuario(
        int $usuarioId,
        int $funcaoId
    ): array {
        $usuario = $this->funcaoRepository
            ->buscarUsuarioPorId($usuarioId);

        if ($usuario === null) {
            throw new UsuarioNaoEncontradoException($usuarioId);
        }

        $funcao = $this->funcaoRepository
            ->buscarPorId($funcaoId);

        if ($funcao === null) {
            throw new FuncaoNaoEncontradaException($funcaoId);
        }

        $possuiaFuncao = $this->funcaoRepository
            ->usuarioPossuiFuncao(
                $usuarioId,
                $funcaoId
            );

        if ($possuiaFuncao) {
            $this->funcaoRepository->removerDoUsuario(
                $usuarioId,
                $funcaoId
            );
        }

        return [
            'usuario' => [
                'id' => (int) $usuario['id'],
                'nome' => $usuario['nome'],
                'status' => $usuario['status'],
            ],
            'funcao' => $this->buscarPorId($funcaoId),
            'funcao_estava_atribuida' => $possuiaFuncao,
        ];
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    private function validarDados(array $dados): array
    {
        $erros = [];

        $nome = trim(
            (string) ($dados['nome'] ?? '')
        );

        if ($nome === '') {
            $erros['nome'] =
                'O nome da função é obrigatório.';
        } elseif (mb_strlen($nome) > 120) {
            $erros['nome'] =
                'O nome da função deve possuir no máximo 120 caracteres.';
        }

        $descricao = $this->textoOpcional(
            $dados['descricao'] ?? null
        );

        if (
            $descricao !== null
            && mb_strlen($descricao) > 500
        ) {
            $erros['descricao'] =
                'A descrição deve possuir no máximo 500 caracteres.';
        }

        $departamentoId = null;

        if (
            array_key_exists('departamento_id', $dados)
            && $dados['departamento_id'] !== null
            && $dados['departamento_id'] !== ''
        ) {
            $departamentoIdValidado = filter_var(
                $dados['departamento_id'],
                FILTER_VALIDATE_INT,
                [
                    'options' => [
                        'min_range' => 1,
                    ],
                ]
            );

            if ($departamentoIdValidado === false) {
                $erros['departamento_id'] =
                    'Informe um departamento válido.';
            } else {
                $departamentoId =
                    (int) $departamentoIdValidado;

                $departamento =
                    $this->funcaoRepository
                        ->buscarDepartamentoPorId(
                            $departamentoId
                        );

                if ($departamento === null) {
                    $erros['departamento_id'] =
                        'O departamento informado não existe.';
                } elseif (!(bool) $departamento['ativo']) {
                    $erros['departamento_id'] =
                        'Não é possível vincular uma função a um departamento inativo.';
                }
            }
        }

        if ($erros !== []) {
            throw new DadosInvalidosException($erros);
        }

        return [
            'nome' => $nome,
            'descricao' => $descricao,
            'departamento_id' => $departamentoId,
        ];
    }

    private function textoOpcional(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $texto = trim((string) $valor);

        return $texto === '' ? null : $texto;
    }

    /**
     * @param array<string, mixed> $funcao
     * @return array<string, mixed>
     */
    private function formatarFuncao(array $funcao): array
    {
        $departamento = null;

        if ($funcao['departamento_id'] !== null) {
            $departamento = [
                'id' =>
                    (int) $funcao['departamento_id'],
                'nome' =>
                    $funcao['departamento_nome'],
                'ativo' =>
                    (bool) $funcao['departamento_ativo'],
            ];
        }

        return [
            'id' => (int) $funcao['id'],
            'nome' => $funcao['nome'],
            'descricao' => $funcao['descricao'],
            'ativo' => (bool) $funcao['ativo'],
            'desativado_em' => $funcao['desativado_em'],
            'departamento' => $departamento,
            'criado_em' => $funcao['criado_em'],
            'atualizado_em' => $funcao['atualizado_em'],
        ];
    }
}
