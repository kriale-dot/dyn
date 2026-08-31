<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\LocalNaoEncontradoException;
use App\Repositories\LocalRepository;

/**
 * Service do módulo de locais.
 *
 * Regras principais:
 *
 * - nome obrigatório;
 * - capacidade opcional, mas positiva quando informada;
 * - nome não duplicado;
 * - desativação lógica;
 * - programações futuras não são apagadas/canceladas em cascata.
 */
final class LocalService
{
    public function __construct(
        private LocalRepository $localRepository
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarTodos(): array
    {
        $locais = $this->localRepository->listarTodos();

        return array_map(
            fn (array $local): array =>
                $this->formatarLocal($local),
            $locais
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function buscarPorId(int $id): array
    {
        $local = $this->localRepository->buscarPorId($id);

        if ($local === null) {
            throw new LocalNaoEncontradoException($id);
        }

        $resultado = $this->formatarLocal($local);

        $resultado['programacoes_futuras'] =
            array_map(
                fn (array $programacao): array =>
                    $this->formatarProgramacaoFutura($programacao),
                $this->localRepository
                    ->listarProgramacoesFuturas($id)
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
            $this->localRepository->nomeExiste(
                $dadosValidados['nome']
            )
        ) {
            throw new DadosInvalidosException([
                'nome' =>
                    'Já existe um local cadastrado com este nome.',
            ]);
        }

        $id = $this->localRepository->criar(
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
        if ($this->localRepository->buscarPorId($id) === null) {
            throw new LocalNaoEncontradoException($id);
        }

        $dadosValidados = $this->validarDados($dados);

        if (
            $this->localRepository->nomeExiste(
                $dadosValidados['nome'],
                $id
            )
        ) {
            throw new DadosInvalidosException([
                'nome' =>
                    'Já existe outro local cadastrado com este nome.',
            ]);
        }

        $this->localRepository->atualizar(
            $id,
            $dadosValidados
        );

        return $this->buscarPorId($id);
    }

    /**
     * Desativa sem excluir.
     *
     * Se houver programações futuras, elas permanecem registradas
     * e são devolvidas para tratamento posterior.
     *
     * @return array<string, mixed>
     */
    public function desativar(int $id): array
    {
        $local = $this->localRepository->buscarPorId($id);

        if ($local === null) {
            throw new LocalNaoEncontradoException($id);
        }

        $programacoesFuturas =
            $this->localRepository
                ->listarProgramacoesFuturas($id);

        $jaEstavaInativo = !(bool) $local['ativo'];

        if (!$jaEstavaInativo) {
            $this->localRepository->desativar($id);
        }

        return [
            'local' => $this->buscarPorId($id),
            'ja_estava_inativo' => $jaEstavaInativo,
            'possui_programacoes_futuras' =>
                $programacoesFuturas !== [],
            'total_programacoes_futuras' =>
                count($programacoesFuturas),
            'programacoes_futuras' =>
                array_map(
                    fn (array $programacao): array =>
                        $this->formatarProgramacaoFutura(
                            $programacao
                        ),
                    $programacoesFuturas
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
                'O nome do local é obrigatório.';
        } elseif (mb_strlen($nome) > 120) {
            $erros['nome'] =
                'O nome do local deve possuir no máximo 120 caracteres.';
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

        $capacidade = null;

        if (
            array_key_exists('capacidade', $dados)
            && $dados['capacidade'] !== null
            && $dados['capacidade'] !== ''
        ) {
            $capacidadeValidada = filter_var(
                $dados['capacidade'],
                FILTER_VALIDATE_INT,
                [
                    'options' => [
                        'min_range' => 1,
                        'max_range' => 65535,
                    ],
                ]
            );

            if ($capacidadeValidada === false) {
                $erros['capacidade'] =
                    'A capacidade deve ser um número inteiro entre 1 e 65535.';
            } else {
                $capacidade = (int) $capacidadeValidada;
            }
        }

        if ($erros !== []) {
            throw new DadosInvalidosException($erros);
        }

        return [
            'nome' => $nome,
            'descricao' => $descricao,
            'capacidade' => $capacidade,
        ];
    }

    private function textoOpcional(
        mixed $valor
    ): ?string {
        if ($valor === null) {
            return null;
        }

        $texto = trim((string) $valor);

        return $texto === '' ? null : $texto;
    }

    /**
     * @param array<string, mixed> $local
     * @return array<string, mixed>
     */
    private function formatarLocal(array $local): array
    {
        return [
            'id' => (int) $local['id'],
            'nome' => $local['nome'],
            'descricao' => $local['descricao'],
            'capacidade' =>
                $local['capacidade'] === null
                    ? null
                    : (int) $local['capacidade'],
            'ativo' => (bool) $local['ativo'],
            'desativado_em' => $local['desativado_em'],
            'total_programacoes' =>
                (int) $local['total_programacoes'],
            'total_programacoes_futuras' =>
                (int) $local['total_programacoes_futuras'],
            'criado_em' => $local['criado_em'],
            'atualizado_em' => $local['atualizado_em'],
        ];
    }

    /**
     * @param array<string, mixed> $programacao
     * @return array<string, mixed>
     */
    private function formatarProgramacaoFutura(
        array $programacao
    ): array {
        return [
            'id' => (int) $programacao['id'],
            'titulo' => $programacao['titulo'],
            'inicio_em' => $programacao['inicio_em'],
            'fim_em' => $programacao['fim_em'],
            'status' => $programacao['status'],
            'local' => $programacao['local_nome_historico'],
            'tipo_programacao' =>
                $programacao['tipo_programacao_nome_historico'],
            'organizador' =>
                $programacao['organizador_nome_historico'],
        ];
    }
}
