<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository dedicado à foto do perfil.
 *
 * O arquivo físico é responsabilidade do Service.
 * Esta classe cuida somente da informação persistida em usuarios.foto.
 */
final class FotoPerfilRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarUsuarioPorId(int $usuarioId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                id,
                nome,
                email,
                foto,
                status
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
     * Atualiza a referência pública da foto.
     */
    public function atualizarFoto(
        int $usuarioId,
        ?string $foto
    ): void {
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios
             SET foto = :foto
             WHERE id = :id'
        );

        $stmt->execute([
            ':foto' => $foto,
            ':id' => $usuarioId,
        ]);
    }
}
