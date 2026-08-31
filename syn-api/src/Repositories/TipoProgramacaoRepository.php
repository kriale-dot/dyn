<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository do módulo Tipos de Programação.
 *
 * Responsável pelo SQL de:
 * - tipos_programacao;
 * - funcoes_tipos_programacao;
 * - consulta de candidatos elegíveis.
 */
final class TipoProgramacaoRepository
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
                tp.id,
                tp.nome,
                tp.descricao,
                tp.ativo,
                tp.desativado_em,
                tp.criado_em,
                tp.atualizado_em,

                (
                    SELECT COUNT(*)
                    FROM funcoes_tipos_programacao ftp
                    WHERE ftp.tipo_programacao_id = tp.id
                ) AS total_funcoes_autorizadas,

                (
                    SELECT COUNT(*)
                    FROM programacoes pr
                    WHERE pr.tipo_programacao_id = tp.id
                ) AS total_programacoes

            FROM tipos_programacao tp

            ORDER BY
                tp.ativo DESC,
                tp.nome ASC,
                tp.id ASC
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
                tp.id,
                tp.nome,
                tp.descricao,
                tp.ativo,
                tp.desativado_em,
                tp.criado_em,
                tp.atualizado_em,

                (
                    SELECT COUNT(*)
                    FROM funcoes_tipos_programacao ftp
                    WHERE ftp.tipo_programacao_id = tp.id
                ) AS total_funcoes_autorizadas,

                (
                    SELECT COUNT(*)
                    FROM programacoes pr
                    WHERE pr.tipo_programacao_id = tp.id
                ) AS total_programacoes

            FROM tipos_programacao tp

            WHERE tp.id = :id

            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id,
        ]);

        $tipo = $stmt->fetch();

        return $tipo === false ? null : $tipo;
    }

    public function nomeExiste(
        string $nome,
        ?int $ignorarId = null
    ): bool {
        $sql = <<<'SQL'
            SELECT 1
            FROM tipos_programacao
            WHERE LOWER(nome) = LOWER(:nome)
        SQL;

        $parametros = [
            ':nome' => $nome,
        ];

        if ($ignorarId !== null) {
            $sql .= ' AND id <> :ignorar_id';
            $parametros[':ignorar_id'] = $ignorarId;
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
            INSERT INTO tipos_programacao (
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
     * @param array<string, mixed> $dados
     */
    public function atualizar(
        int $id,
        array $dados
    ): bool {
        $sql = <<<'SQL'
            UPDATE tipos_programacao
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

    public function desativar(int $id): bool
    {
        $sql = <<<'SQL'
            UPDATE tipos_programacao
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
     * Busca uma função com seu departamento.
     *
     * @return array<string, mixed>|null
     */
    public function buscarFuncaoPorId(
        int $funcaoId
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                f.id,
                f.nome,
                f.descricao,
                f.ativo,
                d.id AS departamento_id,
                d.nome AS departamento_nome,
                d.ativo AS departamento_ativo
            FROM funcoes f
            LEFT JOIN departamentos d
                ON d.id = f.departamento_id
            WHERE f.id = :id
            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $funcaoId,
        ]);

        $funcao = $stmt->fetch();

        return $funcao === false ? null : $funcao;
    }

    /**
     * Lista funções autorizadas para o tipo.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarFuncoesAutorizadas(
        int $tipoId
    ): array {
        $sql = <<<'SQL'
            SELECT
                f.id,
                f.nome,
                f.descricao,
                f.ativo,
                ftp.criado_em AS autorizado_em,

                d.id AS departamento_id,
                d.nome AS departamento_nome,
                d.ativo AS departamento_ativo

            FROM funcoes_tipos_programacao ftp

            INNER JOIN funcoes f
                ON f.id = ftp.funcao_id

            LEFT JOIN departamentos d
                ON d.id = f.departamento_id

            WHERE ftp.tipo_programacao_id = :tipo_id

            ORDER BY
                f.ativo DESC,
                f.nome ASC,
                f.id ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':tipo_id' => $tipoId,
        ]);

        return $stmt->fetchAll();
    }

    public function funcaoEstaAutorizada(
        int $tipoId,
        int $funcaoId
    ): bool {
        $sql = <<<'SQL'
            SELECT 1
            FROM funcoes_tipos_programacao
            WHERE tipo_programacao_id = :tipo_id
              AND funcao_id = :funcao_id
            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':tipo_id' => $tipoId,
            ':funcao_id' => $funcaoId,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function autorizarFuncao(
        int $tipoId,
        int $funcaoId
    ): bool {
        $sql = <<<'SQL'
            INSERT INTO funcoes_tipos_programacao (
                funcao_id,
                tipo_programacao_id
            )
            VALUES (
                :funcao_id,
                :tipo_id
            )
        SQL;

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':funcao_id' => $funcaoId,
            ':tipo_id' => $tipoId,
        ]);
    }

    public function removerAutorizacaoFuncao(
        int $tipoId,
        int $funcaoId
    ): bool {
        $sql = <<<'SQL'
            DELETE FROM funcoes_tipos_programacao
            WHERE tipo_programacao_id = :tipo_id
              AND funcao_id = :funcao_id
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':tipo_id' => $tipoId,
            ':funcao_id' => $funcaoId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Relação central de elegibilidade.
     *
     * O resultado representa pares USUÁRIO + FUNÇÃO que podem
     * normalmente ser usados para uma nova escala desse tipo.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarCandidatosElegiveis(
        int $tipoId
    ): array {
        $sql = <<<'SQL'
            SELECT
                u.id AS usuario_id,
                u.nome AS usuario_nome,
                u.email AS usuario_email,

                f.id AS funcao_id,
                f.nome AS funcao_nome,

                d.id AS departamento_id,
                d.nome AS departamento_nome

            FROM funcoes_tipos_programacao ftp

            INNER JOIN funcoes f
                ON f.id = ftp.funcao_id

            INNER JOIN usuarios_funcoes uf
                ON uf.funcao_id = f.id

            INNER JOIN usuarios u
                ON u.id = uf.usuario_id

            LEFT JOIN departamentos d
                ON d.id = f.departamento_id

            WHERE ftp.tipo_programacao_id = :tipo_id
              AND u.status = 'ATIVO'
              AND f.ativo = 1

            ORDER BY
                f.nome ASC,
                u.nome ASC,
                u.id ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':tipo_id' => $tipoId,
        ]);

        return $stmt->fetchAll();
    }
}
