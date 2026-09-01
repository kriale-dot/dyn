<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository dedicado ao logotipo institucional da igreja.
 *
 * Cada instalação do SYN possui apenas uma igreja.
 * A tabela "igreja" usa o registro singleton de ID 1.
 */
final class LogotipoIgrejaRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarIgreja(): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                id,
                nome,
                logotipo
             FROM igreja
             WHERE id = 1
             LIMIT 1'
        );

        $stmt->execute();

        $igreja = $stmt->fetch();

        return $igreja === false
            ? null
            : $igreja;
    }

    /**
     * Atualiza somente a referência do logotipo.
     */
    public function atualizarLogotipo(
        ?string $logotipo
    ): void {
        $stmt = $this->pdo->prepare(
            'UPDATE igreja
             SET logotipo = :logotipo
             WHERE id = 1'
        );

        $stmt->execute([
            ':logotipo' => $logotipo,
        ]);
    }
}
