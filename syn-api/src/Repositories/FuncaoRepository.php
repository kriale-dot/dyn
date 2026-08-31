<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository do módulo de funções.
 *
 * Esta camada conhece:
 * - SQL;
 * - tabelas;
 * - relacionamentos;
 * - PDO.
 *
 * Ela não conhece Request/Response e não decide códigos HTTP.
 */
final class FuncaoRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarTodas(): array
    {
        $sql = <<<'SQL'
            SELECT
                f.id,
                f.nome,
                f.descricao,
                f.ativo,
                f.desativado_em,
                f.criado_em,
                f.atualizado_em,

                d.id AS departamento_id,
                d.nome AS departamento_nome,
                d.ativo AS departamento_ativo

            FROM funcoes f

            LEFT JOIN departamentos d
                ON d.id = f.departamento_id

            ORDER BY
                f.ativo DESC,
                f.nome ASC,
                f.id ASC
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
                f.id,
                f.nome,
                f.descricao,
                f.ativo,
                f.desativado_em,
                f.criado_em,
                f.atualizado_em,

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
            ':id' => $id,
        ]);

        $funcao = $stmt->fetch();

        return $funcao === false ? null : $funcao;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarDepartamentoPorId(int $id): ?array
    {
        $sql = <<<'SQL'
            SELECT
                id,
                nome,
                ativo
            FROM departamentos
            WHERE id = :id
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
     * Verifica duplicidade de nome dentro do mesmo departamento.
     *
     * O operador <=> do MariaDB permite comparar NULL com NULL.
     */
    public function nomeExisteNoDepartamento(
        string $nome,
        ?int $departamentoId,
        ?int $ignorarFuncaoId = null
    ): bool {
        $sql = <<<'SQL'
            SELECT 1
            FROM funcoes
            WHERE LOWER(nome) = LOWER(:nome)
              AND departamento_id <=> :departamento_id
        SQL;

        if ($ignorarFuncaoId !== null) {
            $sql .= ' AND id <> :ignorar_id';
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);

        $parametros = [
            ':nome' => $nome,
            ':departamento_id' => $departamentoId,
        ];

        if ($ignorarFuncaoId !== null) {
            $parametros[':ignorar_id'] = $ignorarFuncaoId;
        }

        $stmt->execute($parametros);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * @param array<string, mixed> $dados
     */
    public function criar(array $dados): int
    {
        $sql = <<<'SQL'
            INSERT INTO funcoes (
                departamento_id,
                nome,
                descricao,
                ativo
            )
            VALUES (
                :departamento_id,
                :nome,
                :descricao,
                1
            )
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':departamento_id' => $dados['departamento_id'],
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
            UPDATE funcoes
            SET
                departamento_id = :departamento_id,
                nome = :nome,
                descricao = :descricao
            WHERE id = :id
        SQL;

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':departamento_id' => $dados['departamento_id'],
            ':nome' => $dados['nome'],
            ':descricao' => $dados['descricao'],
            ':id' => $id,
        ]);
    }

    /**
     * Desativa a função sem excluir fisicamente.
     */
    public function desativar(int $id): bool
    {
        $sql = <<<'SQL'
            UPDATE funcoes
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
     * Quantos usuários ainda possuem esta função na relação atual.
     */
    public function contarUsuariosComFuncao(int $funcaoId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM usuarios_funcoes WHERE funcao_id = :funcao_id'
        );

        $stmt->execute([
            ':funcao_id' => $funcaoId,
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarUsuarioPorId(int $usuarioId): ?array
    {
        $sql = <<<'SQL'
            SELECT
                id,
                nome,
                status
            FROM usuarios
            WHERE id = :id
            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $usuarioId,
        ]);

        $usuario = $stmt->fetch();

        return $usuario === false ? null : $usuario;
    }

    public function usuarioPossuiFuncao(
        int $usuarioId,
        int $funcaoId
    ): bool {
        $sql = <<<'SQL'
            SELECT 1
            FROM usuarios_funcoes
            WHERE usuario_id = :usuario_id
              AND funcao_id = :funcao_id
            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':funcao_id' => $funcaoId,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Cria a relação atual usuário x função.
     */
    public function atribuirAoUsuario(
        int $usuarioId,
        int $funcaoId
    ): bool {
        $sql = <<<'SQL'
            INSERT INTO usuarios_funcoes (
                usuario_id,
                funcao_id
            )
            VALUES (
                :usuario_id,
                :funcao_id
            )
        SQL;

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':funcao_id' => $funcaoId,
        ]);
    }

    /**
     * Remove APENAS a habilitação atual do usuário.
     *
     * Não toca em participacoes, que representam fatos históricos.
     */
    public function removerDoUsuario(
        int $usuarioId,
        int $funcaoId
    ): bool {
        $sql = <<<'SQL'
            DELETE FROM usuarios_funcoes
            WHERE usuario_id = :usuario_id
              AND funcao_id = :funcao_id
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':funcao_id' => $funcaoId,
        ]);

        return $stmt->rowCount() > 0;
    }
}
