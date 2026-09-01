<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository das necessidades específicas.
 *
 * A tabela é separada justamente para facilitar o controle
 * de acesso a essas informações.
 */
final class NecessidadeEspecificaRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarUsuarioPorId(
        int $usuarioId
    ): ?array {
        $stmt = $this->pdo->prepare(
            'SELECT id, nome, status
             FROM usuarios
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            ':id' => $usuarioId,
        ]);

        $usuario = $stmt->fetch();

        return $usuario === false
            ? null
            : $usuario;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarPorUsuarioId(
        int $usuarioId
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                ne.id,
                ne.usuario_id,
                ne.observacao,
                ne.ativo,
                ne.criado_em,
                ne.atualizado_em,

                u.nome AS usuario_nome,
                u.status AS usuario_status

            FROM necessidades_especificas ne

            INNER JOIN usuarios u
                ON u.id = ne.usuario_id

            WHERE ne.usuario_id = :usuario_id

            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuarioId,
        ]);

        $registro = $stmt->fetch();

        return $registro === false
            ? null
            : $registro;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarTodos(): array
    {
        $sql = <<<'SQL'
            SELECT
                ne.id,
                ne.usuario_id,
                ne.observacao,
                ne.ativo,
                ne.criado_em,
                ne.atualizado_em,

                u.nome AS usuario_nome,
                u.status AS usuario_status

            FROM necessidades_especificas ne

            INNER JOIN usuarios u
                ON u.id = ne.usuario_id

            ORDER BY
                ne.ativo DESC,
                u.nome ASC,
                ne.id ASC
        SQL;

        return $this->pdo
            ->query($sql)
            ->fetchAll();
    }

    /**
     * Cria ou reativa/atualiza o registro de um usuário.
     */
    public function salvar(
        int $usuarioId,
        string $observacao
    ): void {
        $sql = <<<'SQL'
            INSERT INTO necessidades_especificas (
                usuario_id,
                observacao,
                ativo
            )
            VALUES (
                :usuario_id,
                :observacao,
                1
            )
            ON DUPLICATE KEY UPDATE
                observacao = VALUES(observacao),
                ativo = 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':observacao' => $observacao,
        ]);
    }

    /**
     * Desativa sem apagar o conteúdo do banco.
     */
    public function desativar(
        int $usuarioId
    ): bool {
        $stmt = $this->pdo->prepare(
            'UPDATE necessidades_especificas
             SET ativo = 0
             WHERE usuario_id = :usuario_id
               AND ativo = 1'
        );

        $stmt->execute([
            ':usuario_id' => $usuarioId,
        ]);

        return $stmt->rowCount() > 0;
    }
}
