<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository do módulo de departamentos.
 *
 * Esta camada é responsável pelo acesso SQL à tabela departamentos.
 */
final class DepartamentoRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Lista todos os departamentos, ativos e inativos.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarTodos(): array
    {
        $sql = <<<'SQL'
            SELECT
                d.id,
                d.nome,
                d.descricao,
                d.ativo,
                d.desativado_em,
                d.criado_em,
                d.atualizado_em,

                (
                    SELECT COUNT(*)
                    FROM funcoes f
                    WHERE f.departamento_id = d.id
                ) AS total_funcoes,

                (
                    SELECT COUNT(*)
                    FROM funcoes f
                    WHERE f.departamento_id = d.id
                      AND f.ativo = 1
                ) AS total_funcoes_ativas

            FROM departamentos d

            ORDER BY
                d.ativo DESC,
                d.nome ASC,
                d.id ASC
        SQL;

        return $this->pdo
            ->query($sql)
            ->fetchAll();
    }

    /**
     * Busca um departamento pelo ID.
     *
     * @return array<string, mixed>|null
     */
    public function buscarPorId(int $id): ?array
    {
        $sql = <<<'SQL'
            SELECT
                d.id,
                d.nome,
                d.descricao,
                d.ativo,
                d.desativado_em,
                d.criado_em,
                d.atualizado_em,

                (
                    SELECT COUNT(*)
                    FROM funcoes f
                    WHERE f.departamento_id = d.id
                ) AS total_funcoes,

                (
                    SELECT COUNT(*)
                    FROM funcoes f
                    WHERE f.departamento_id = d.id
                      AND f.ativo = 1
                ) AS total_funcoes_ativas

            FROM departamentos d

            WHERE d.id = :id

            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id,
        ]);

        $departamento = $stmt->fetch();

        return $departamento === false
            ? null
            : $departamento;
    }

    /**
     * Verifica se já existe um departamento com o mesmo nome.
     *
     * Na edição podemos ignorar o próprio ID.
     */
    public function nomeExiste(
        string $nome,
        ?int $ignorarDepartamentoId = null
    ): bool {
        $sql = <<<'SQL'
            SELECT 1
            FROM departamentos
            WHERE LOWER(nome) = LOWER(:nome)
        SQL;

        $parametros = [
            ':nome' => $nome,
        ];

        if ($ignorarDepartamentoId !== null) {
            $sql .= ' AND id <> :ignorar_id';

            $parametros[':ignorar_id'] =
                $ignorarDepartamentoId;
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($parametros);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Cria um novo departamento.
     *
     * @param array<string, mixed> $dados
     */
    public function criar(array $dados): int
    {
        $sql = <<<'SQL'
            INSERT INTO departamentos (
                nome,
                descricao,
                ativo
            )
            VALUES (
                :nome,
                :descricao,
                1
            )
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':nome' => $dados['nome'],
            ':descricao' => $dados['descricao'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Atualiza os dados atuais de um departamento.
     *
     * O status ativo/inativo não é alterado neste método.
     *
     * @param array<string, mixed> $dados
     */
    public function atualizar(
        int $id,
        array $dados
    ): bool {
        $sql = <<<'SQL'
            UPDATE departamentos
            SET
                nome = :nome,
                descricao = :descricao
            WHERE id = :id
        SQL;

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':nome' => $dados['nome'],
            ':descricao' => $dados['descricao'],
            ':id' => $id,
        ]);
    }

    /**
     * Faz exclusão lógica.
     *
     * Não apaga o departamento e não modifica automaticamente
     * as funções relacionadas.
     */
    public function desativar(int $id): bool
    {
        $sql = <<<'SQL'
            UPDATE departamentos
            SET
                ativo = 0,
                desativado_em = NOW()
            WHERE id = :id
              AND ativo = 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Lista as funções ligadas ao departamento.
     *
     * Isto é usado para informar o impacto da desativação.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarFuncoesDoDepartamento(
        int $departamentoId
    ): array {
        $sql = <<<'SQL'
            SELECT
                id,
                nome,
                descricao,
                ativo,
                desativado_em
            FROM funcoes
            WHERE departamento_id = :departamento_id
            ORDER BY
                ativo DESC,
                nome ASC,
                id ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':departamento_id' => $departamentoId,
        ]);

        return $stmt->fetchAll();
    }
}
