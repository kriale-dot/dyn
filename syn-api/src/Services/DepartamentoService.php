<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\DepartamentoNaoEncontradoException;
use App\Repositories\DepartamentoRepository;

/**
 * Service do módulo de departamentos.
 *
 * Aqui ficam as validações e regras de negócio.
 */
final class DepartamentoService
{
    public function __construct(
        private DepartamentoRepository $departamentoRepository
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarTodos(): array
    {
        $departamentos =
            $this->departamentoRepository->listarTodos();

        return array_map(
            fn (array $departamento): array =>
                $this->formatarDepartamento($departamento),
            $departamentos
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function buscarPorId(int $id): array
    {
        $departamento =
            $this->departamentoRepository->buscarPorId($id);

        if ($departamento === null) {
            throw new DepartamentoNaoEncontradoException($id);
        }

        $resultado =
            $this->formatarDepartamento($departamento);

        $funcoes =
            $this->departamentoRepository
                ->listarFuncoesDoDepartamento($id);

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
        $dadosValidados = $this->validarDados($dados);

        if (
            $this->departamentoRepository->nomeExiste(
                $dadosValidados['nome']
            )
        ) {
            throw new DadosInvalidosException([
                'nome' =>
                    'Já existe um departamento com este nome.',
            ]);
        }

        $id = $this->departamentoRepository->criar(
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
        if (
            $this->departamentoRepository->buscarPorId($id)
            === null
        ) {
            throw new DepartamentoNaoEncontradoException($id);
        }

        $dadosValidados = $this->validarDados($dados);

        if (
            $this->departamentoRepository->nomeExiste(
                $dadosValidados['nome'],
                $id
            )
        ) {
            throw new DadosInvalidosException([
                'nome' =>
                    'Já existe outro departamento com este nome.',
            ]);
        }

        $this->departamentoRepository->atualizar(
            $id,
            $dadosValidados
        );

        return $this->buscarPorId($id);
    }

    /**
     * Desativa o departamento sem apagar seu registro.
     *
     * Funções relacionadas NÃO são desativadas automaticamente.
     * A resposta lista essas funções para deixar claro o impacto
     * da operação.
     *
     * @return array<string, mixed>
     */
    public function desativar(int $id): array
    {
        $departamento =
            $this->departamentoRepository->buscarPorId($id);

        if ($departamento === null) {
            throw new DepartamentoNaoEncontradoException($id);
        }

        $funcoes =
            $this->departamentoRepository
                ->listarFuncoesDoDepartamento($id);

        $funcoesAtivas = array_values(
            array_filter(
                $funcoes,
                fn (array $funcao): bool =>
                    (bool) $funcao['ativo']
            )
        );

        $jaEstavaInativo =
            !(bool) $departamento['ativo'];

        if (!$jaEstavaInativo) {
            $this->departamentoRepository
                ->desativar($id);
        }

        return [
            'departamento' =>
                $this->buscarPorId($id),
            'ja_estava_inativo' =>
                $jaEstavaInativo,
            'possui_funcoes_ativas' =>
                $funcoesAtivas !== [],
            'total_funcoes_ativas' =>
                count($funcoesAtivas),
            'funcoes_ativas' =>
                array_map(
                    fn (array $funcao): array =>
                        $this->formatarFuncao($funcao),
                    $funcoesAtivas
                ),
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
                'O nome do departamento é obrigatório.';
        } elseif (mb_strlen($nome) > 120) {
            $erros['nome'] =
                'O nome do departamento deve possuir no máximo 120 caracteres.';
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

        if ($erros !== []) {
            throw new DadosInvalidosException($erros);
        }

        return [
            'nome' => $nome,
            'descricao' => $descricao,
        ];
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
     * @param array<string, mixed> $departamento
     * @return array<string, mixed>
     */
    private function formatarDepartamento(
        array $departamento
    ): array {
        return [
            'id' =>
                (int) $departamento['id'],
            'nome' =>
                $departamento['nome'],
            'descricao' =>
                $departamento['descricao'],
            'ativo' =>
                (bool) $departamento['ativo'],
            'desativado_em' =>
                $departamento['desativado_em'],
            'total_funcoes' =>
                (int) $departamento['total_funcoes'],
            'total_funcoes_ativas' =>
                (int) $departamento['total_funcoes_ativas'],
            'criado_em' =>
                $departamento['criado_em'],
            'atualizado_em' =>
                $departamento['atualizado_em'],
        ];
    }

    /**
     * @param array<string, mixed> $funcao
     * @return array<string, mixed>
     */
    private function formatarFuncao(
        array $funcao
    ): array {
        return [
            'id' => (int) $funcao['id'],
            'nome' => $funcao['nome'],
            'descricao' => $funcao['descricao'],
            'ativo' => (bool) $funcao['ativo'],
            'desativado_em' =>
                $funcao['desativado_em'],
        ];
    }
}
