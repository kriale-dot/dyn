<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class PermissaoOrganizadorRepository
{
    public function __construct(private PDO $pdo)
    {
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

        $usuario = $stmt->fetch();
        return $usuario === false ? null : $usuario;
    }

    public function buscarTipo(int $tipoId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nome, descricao, ativo
             FROM tipos_programacao
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $tipoId]);

        $tipo = $stmt->fetch();
        return $tipo === false ? null : $tipo;
    }

    public function listarTiposPermitidos(int $usuarioId): array
    {
        $sql = <<<'SQL'
            SELECT
                tp.id,
                tp.nome,
                tp.descricao,
                tp.ativo,
                otp.atribuido_em
            FROM organizadores_tipos_programacao otp
            INNER JOIN tipos_programacao tp
                ON tp.id = otp.tipo_programacao_id
            WHERE otp.usuario_id = :usuario_id
            ORDER BY tp.ativo DESC, tp.nome ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);

        return $stmt->fetchAll();
    }

    public function possuiPermissao(int $usuarioId, int $tipoId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1
             FROM organizadores_tipos_programacao
             WHERE usuario_id = :usuario_id
               AND tipo_programacao_id = :tipo_id
             LIMIT 1'
        );

        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':tipo_id' => $tipoId,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function conceder(int $usuarioId, int $tipoId): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT IGNORE INTO organizadores_tipos_programacao
             (usuario_id, tipo_programacao_id)
             VALUES (:usuario_id, :tipo_id)'
        );

        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':tipo_id' => $tipoId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function revogar(int $usuarioId, int $tipoId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM organizadores_tipos_programacao
             WHERE usuario_id = :usuario_id
               AND tipo_programacao_id = :tipo_id'
        );

        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':tipo_id' => $tipoId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function tipoIdDaProgramacao(int $programacaoId): ?int
    {
        $stmt = $this->pdo->prepare(
            'SELECT tipo_programacao_id
             FROM programacoes
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $programacaoId]);

        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    public function tipoIdDaParticipacao(int $participacaoId): ?int
    {
        $sql = <<<'SQL'
            SELECT p.tipo_programacao_id
            FROM participacoes pa
            INNER JOIN programacoes p ON p.id = pa.programacao_id
            WHERE pa.id = :id
            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $participacaoId]);

        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    public function tipoIdDaSerie(int $serieId): ?int
    {
        $stmt = $this->pdo->prepare(
            'SELECT tipo_programacao_id
             FROM series_programacao
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $serieId]);

        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }
}
