<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository do módulo de locais.
 *
 * Schema atual de locais:
 *
 * - id
 * - nome
 * - descricao
 * - capacidade
 * - ativo
 * - desativado_em
 * - criado_em
 * - atualizado_em
 */
final class LocalRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarTodos(): array
    {
        $sql = <<<'SQL'
            SELECT
                l.id,
                l.nome,
                l.descricao,
                l.capacidade,
                l.ativo,
                l.desativado_em,
                l.criado_em,
                l.atualizado_em,

                (
                    SELECT COUNT(*)
                    FROM programacoes p
                    WHERE p.local_id = l.id
                ) AS total_programacoes,

                (
                    SELECT COUNT(*)
                    FROM programacoes p
                    WHERE p.local_id = l.id
                      AND p.inicio_em > NOW()
                      AND p.status <> 'CANCELADA'
                ) AS total_programacoes_futuras

            FROM locais l

            ORDER BY
                l.ativo DESC,
                l.nome ASC,
                l.id ASC
        SQL;

        return $this->pdo
            ->query($sql)
            ->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarPorId(int $id): ?array
    {
        $sql = <<<'SQL'
            SELECT
                l.id,
                l.nome,
                l.descricao,
                l.capacidade,
                l.ativo,
                l.desativado_em,
                l.criado_em,
                l.atualizado_em,

                (
                    SELECT COUNT(*)
                    FROM programacoes p
                    WHERE p.local_id = l.id
                ) AS total_programacoes,

                (
                    SELECT COUNT(*)
                    FROM programacoes p
                    WHERE p.local_id = l.id
                      AND p.inicio_em > NOW()
                      AND p.status <> 'CANCELADA'
                ) AS total_programacoes_futuras

            FROM locais l

            WHERE l.id = :id

            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id,
        ]);

        $local = $stmt->fetch();

        return $local === false ? null : $local;
    }

    /**
     * Verifica duplicidade do nome.
     */
    public function nomeExiste(
        string $nome,
        ?int $ignorarLocalId = null
    ): bool {
        $sql = <<<'SQL'
            SELECT 1
            FROM locais
            WHERE LOWER(nome) = LOWER(:nome)
        SQL;

        $parametros = [
            ':nome' => $nome,
        ];

        if ($ignorarLocalId !== null) {
            $sql .= ' AND id <> :ignorar_id';
            $parametros[':ignorar_id'] = $ignorarLocalId;
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * @param array<string, mixed> $dados
     */
    public function criar(array $dados): int
    {
        $sql = <<<'SQL'
            INSERT INTO locais (
                nome,
                descricao,
                capacidade,
                ativo
            )
            VALUES (
                :nome,
                :descricao,
                :capacidade,
                1
            )
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':nome' => $dados['nome'],
            ':descricao' => $dados['descricao'],
            ':capacidade' => $dados['capacidade'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Atualiza dados cadastrais atuais.
     *
     * O status ativo/inativo é tratado separadamente.
     *
     * @param array<string, mixed> $dados
     */
    public function atualizar(
        int $id,
        array $dados
    ): bool {
        $sql = <<<'SQL'
            UPDATE locais
            SET
                nome = :nome,
                descricao = :descricao,
                capacidade = :capacidade
            WHERE id = :id
        SQL;

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':nome' => $dados['nome'],
            ':descricao' => $dados['descricao'],
            ':capacidade' => $dados['capacidade'],
            ':id' => $id,
        ]);
    }

    /**
     * Faz exclusão lógica.
     */
    public function desativar(int $id): bool
    {
        $sql = <<<'SQL'
            UPDATE locais
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
     * Lista programações futuras ainda não canceladas.
     *
     * A desativação do local NÃO cancela essas programações
     * automaticamente. Elas são devolvidas como alerta.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarProgramacoesFuturas(
        int $localId
    ): array {
        $sql = <<<'SQL'
            SELECT
                p.id,
                p.titulo,
                p.inicio_em,
                p.fim_em,
                p.status,
                p.local_nome_historico,
                p.tipo_programacao_nome_historico,
                p.organizador_nome_historico
            FROM programacoes p
            WHERE p.local_id = :local_id
              AND p.inicio_em > NOW()
              AND p.status <> 'CANCELADA'
            ORDER BY
                p.inicio_em ASC,
                p.id ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':local_id' => $localId,
        ]);

        return $stmt->fetchAll();
    }
}
