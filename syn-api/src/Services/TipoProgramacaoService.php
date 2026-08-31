<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\FuncaoNaoEncontradaException;
use App\Exceptions\TipoProgramacaoNaoEncontradoException;
use App\Repositories\TipoProgramacaoRepository;

/**
 * Service de Tipos de Programação.
 *
 * Aqui fica a regra central de elegibilidade:
 *
 * USUÁRIO
 *   -> possui FUNÇÃO atual
 *   -> FUNÇÃO é autorizada para o TIPO DE PROGRAMAÇÃO
 *   -> usuário pode aparecer como candidato normal.
 */
final class TipoProgramacaoService
{
    public function __construct(
        private TipoProgramacaoRepository $repository
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarTodos(): array
    {
        $tipos = $this->repository->listarTodos();

        return array_map(
            fn (array $tipo): array =>
                $this->formatarTipo($tipo),
            $tipos
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function buscarPorId(int $id): array
    {
        $tipo = $this->repository->buscarPorId($id);

        if ($tipo === null) {
            throw new TipoProgramacaoNaoEncontradoException($id);
        }

        $resultado = $this->formatarTipo($tipo);

        $funcoes =
            $this->repository
                ->listarFuncoesAutorizadas($id);

        $resultado['funcoes_autorizadas'] =
            array_map(
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
            $this->repository->nomeExiste(
                $dadosValidados['nome']
            )
        ) {
            throw new DadosInvalidosException([
                'nome' =>
                    'Já existe um tipo de programação com este nome.',
            ]);
        }

        $id = $this->repository->criar(
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
        if ($this->repository->buscarPorId($id) === null) {
            throw new TipoProgramacaoNaoEncontradoException($id);
        }

        $dadosValidados = $this->validarDados($dados);

        if (
            $this->repository->nomeExiste(
                $dadosValidados['nome'],
                $id
            )
        ) {
            throw new DadosInvalidosException([
                'nome' =>
                    'Já existe outro tipo de programação com este nome.',
            ]);
        }

        $this->repository->atualizar(
            $id,
            $dadosValidados
        );

        return $this->buscarPorId($id);
    }

    /**
     * Desativa sem apagar programações passadas ou relações.
     *
     * @return array<string, mixed>
     */
    public function desativar(int $id): array
    {
        $tipo = $this->repository->buscarPorId($id);

        if ($tipo === null) {
            throw new TipoProgramacaoNaoEncontradoException($id);
        }

        $jaEstavaInativo = !(bool) $tipo['ativo'];

        if (!$jaEstavaInativo) {
            $this->repository->desativar($id);
        }

        return [
            'tipo_programacao' =>
                $this->buscarPorId($id),
            'ja_estava_inativo' =>
                $jaEstavaInativo,
            'total_programacoes_historicas_ou_atuais' =>
                (int) $tipo['total_programacoes'],
        ];
    }

    /**
     * Autoriza uma função para novas escalas desse tipo.
     *
     * @return array<string, mixed>
     */
    public function autorizarFuncao(
        int $tipoId,
        int $funcaoId
    ): array {
        $tipo = $this->repository->buscarPorId($tipoId);

        if ($tipo === null) {
            throw new TipoProgramacaoNaoEncontradoException($tipoId);
        }

        $funcao = $this->repository
            ->buscarFuncaoPorId($funcaoId);

        if ($funcao === null) {
            throw new FuncaoNaoEncontradaException($funcaoId);
        }

        if (!(bool) $tipo['ativo']) {
            throw new DadosInvalidosException([
                'tipo_programacao' =>
                    'Não é possível autorizar funções para um tipo de programação inativo.',
            ]);
        }

        if (!(bool) $funcao['ativo']) {
            throw new DadosInvalidosException([
                'funcao' =>
                    'Não é possível autorizar uma função inativa.',
            ]);
        }

        $jaEstavaAutorizada =
            $this->repository->funcaoEstaAutorizada(
                $tipoId,
                $funcaoId
            );

        if (!$jaEstavaAutorizada) {
            $this->repository->autorizarFuncao(
                $tipoId,
                $funcaoId
            );
        }

        return [
            'tipo_programacao' =>
                $this->buscarPorId($tipoId),
            'funcao' =>
                $this->formatarFuncaoBasica($funcao),
            'ja_estava_autorizada' =>
                $jaEstavaAutorizada,
        ];
    }

    /**
     * Remove somente a autorização ATUAL.
     *
     * Participações históricas não são alteradas.
     *
     * @return array<string, mixed>
     */
    public function removerAutorizacaoFuncao(
        int $tipoId,
        int $funcaoId
    ): array {
        if ($this->repository->buscarPorId($tipoId) === null) {
            throw new TipoProgramacaoNaoEncontradoException($tipoId);
        }

        $funcao = $this->repository
            ->buscarFuncaoPorId($funcaoId);

        if ($funcao === null) {
            throw new FuncaoNaoEncontradaException($funcaoId);
        }

        $estavaAutorizada =
            $this->repository->funcaoEstaAutorizada(
                $tipoId,
                $funcaoId
            );

        if ($estavaAutorizada) {
            $this->repository->removerAutorizacaoFuncao(
                $tipoId,
                $funcaoId
            );
        }

        return [
            'tipo_programacao' =>
                $this->buscarPorId($tipoId),
            'funcao' =>
                $this->formatarFuncaoBasica($funcao),
            'funcao_estava_autorizada' =>
                $estavaAutorizada,
        ];
    }

    /**
     * Lista os candidatos normais para uma escala desse tipo.
     *
     * Se o tipo estiver inativo, ele permanece consultável para
     * histórico, mas não é usado para gerar novas candidaturas.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarCandidatos(int $tipoId): array
    {
        $tipo = $this->repository->buscarPorId($tipoId);

        if ($tipo === null) {
            throw new TipoProgramacaoNaoEncontradoException($tipoId);
        }

        if (!(bool) $tipo['ativo']) {
            throw new DadosInvalidosException([
                'tipo_programacao' =>
                    'Um tipo de programação inativo não pode gerar candidatos para novas escalas.',
            ]);
        }

        $candidatos =
            $this->repository
                ->listarCandidatosElegiveis($tipoId);

        return array_map(
            fn (array $candidato): array =>
                $this->formatarCandidato($candidato),
            $candidatos
        );
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
                'O nome do tipo de programação é obrigatório.';
        } elseif (mb_strlen($nome) > 120) {
            $erros['nome'] =
                'O nome deve possuir no máximo 120 caracteres.';
        }

        $descricao =
            $this->textoOpcional(
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
     * @param array<string, mixed> $tipo
     * @return array<string, mixed>
     */
    private function formatarTipo(array $tipo): array
    {
        return [
            'id' =>
                (int) $tipo['id'],
            'nome' =>
                $tipo['nome'],
            'descricao' =>
                $tipo['descricao'],
            'ativo' =>
                (bool) $tipo['ativo'],
            'desativado_em' =>
                $tipo['desativado_em'],
            'total_funcoes_autorizadas' =>
                (int) $tipo['total_funcoes_autorizadas'],
            'total_programacoes' =>
                (int) $tipo['total_programacoes'],
            'criado_em' =>
                $tipo['criado_em'],
            'atualizado_em' =>
                $tipo['atualizado_em'],
        ];
    }

    /**
     * @param array<string, mixed> $funcao
     * @return array<string, mixed>
     */
    private function formatarFuncao(array $funcao): array
    {
        $resultado =
            $this->formatarFuncaoBasica($funcao);

        $resultado['autorizado_em'] =
            $funcao['autorizado_em'];

        return $resultado;
    }

    /**
     * @param array<string, mixed> $funcao
     * @return array<string, mixed>
     */
    private function formatarFuncaoBasica(
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
                    (bool) $funcao['departamento_ativo'],
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
            'departamento' =>
                $departamento,
        ];
    }

    /**
     * @param array<string, mixed> $candidato
     * @return array<string, mixed>
     */
    private function formatarCandidato(
        array $candidato
    ): array {
        $departamento = null;

        if ($candidato['departamento_id'] !== null) {
            $departamento = [
                'id' =>
                    (int) $candidato['departamento_id'],
                'nome' =>
                    $candidato['departamento_nome'],
            ];
        }

        return [
            'usuario' => [
                'id' =>
                    (int) $candidato['usuario_id'],
                'nome' =>
                    $candidato['usuario_nome'],
                'email' =>
                    $candidato['usuario_email'],
            ],
            'funcao' => [
                'id' =>
                    (int) $candidato['funcao_id'],
                'nome' =>
                    $candidato['funcao_nome'],
                'departamento' =>
                    $departamento,
            ],
        ];
    }
}
