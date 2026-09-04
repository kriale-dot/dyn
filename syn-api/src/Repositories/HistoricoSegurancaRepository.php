<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Consulta do histórico de segurança do próprio usuário.
 */
final class HistoricoSegurancaRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarDoUsuario(
        int $usuarioId,
        int $limite
    ): array {
        $sql = "
            SELECT
                id,
                tipo,
                titulo,
                detalhe,
                criado_em

            FROM eventos_seguranca_conta

            WHERE usuario_id =
                :usuario_id

            ORDER BY
                criado_em DESC,
                id DESC

            LIMIT {$limite}
        ";

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':usuario_id' =>
                $usuarioId,
        ]);

        return $stmt->fetchAll();
    }
}
