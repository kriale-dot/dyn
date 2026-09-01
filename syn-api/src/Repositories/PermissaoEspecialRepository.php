<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class PermissaoEspecialRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listarPermissoes(): array
    {
        return $this->pdo->query(
            'SELECT id, codigo, nome, descricao, ativo, criado_em, atualizado_em
             FROM permissoes_especiais
             ORDER BY ativo DESC, nome ASC, id ASC'
        )->fetchAll();
    }

    public function buscarPermissaoPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, codigo, nome, descricao, ativo
             FROM permissoes_especiais
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function buscarUsuarioComPapel(int $usuarioId): ?array
    {
        $sql = <<<'SQL'
            SELECT
                u.id, u.nome, u.email, u.status,
                p.codigo AS papel_codigo,
                p.nome AS papel_nome
            FROM usuarios u
            INNER JOIN papeis p ON p.id = u.papel_id
            WHERE u.id = :id
            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $usuarioId]);

        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function listarDoUsuario(int $usuarioId): array
    {
        $sql = <<<'SQL'
            SELECT
                pe.id, pe.codigo, pe.nome, pe.descricao,
                pe.ativo, upe.concedido_em
            FROM usuarios_permissoes_especiais upe
            INNER JOIN permissoes_especiais pe
                ON pe.id = upe.permissao_id
            WHERE upe.usuario_id = :usuario_id
            ORDER BY pe.ativo DESC, pe.nome ASC, pe.id ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);

        return $stmt->fetchAll();
    }

    public function usuarioPossuiCodigo(
        int $usuarioId,
        string $codigo
    ): bool {
        $sql = <<<'SQL'
            SELECT 1
            FROM usuarios_permissoes_especiais upe
            INNER JOIN permissoes_especiais pe
                ON pe.id = upe.permissao_id
            WHERE upe.usuario_id = :usuario_id
              AND pe.codigo = :codigo
              AND pe.ativo = 1
            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':codigo' => $codigo,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function conceder(int $usuarioId, int $permissaoId): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT IGNORE INTO usuarios_permissoes_especiais
             (usuario_id, permissao_id)
             VALUES (:usuario_id, :permissao_id)'
        );
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':permissao_id' => $permissaoId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function revogar(int $usuarioId, int $permissaoId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM usuarios_permissoes_especiais
             WHERE usuario_id = :usuario_id
               AND permissao_id = :permissao_id'
        );
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':permissao_id' => $permissaoId,
        ]);

        return $stmt->rowCount() > 0;
    }
}
