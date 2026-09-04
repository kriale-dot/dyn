<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Throwable;

/**
 * Acesso a dados das operações de segurança da própria conta.
 */
final class SegurancaContaRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarUsuarioAtivo(
        int $usuarioId
    ): ?array {
        $stmt =
            $this->pdo->prepare(
                'SELECT
                    id,
                    nome,
                    email,
                    senha_hash,
                    status,
                    sessao_versao
                 FROM usuarios
                 WHERE id = :id
                   AND status = "ATIVO"
                 LIMIT 1'
            );

        $stmt->execute([
            ':id' =>
                $usuarioId,
        ]);

        $row =
            $stmt->fetch();

        return $row === false
            ? null
            : $row;
    }

    /**
     * Troca a senha e revoga todos os JWT existentes na mesma transação.
     */
    public function alterarSenhaERevogarSessoes(
        int $usuarioId,
        string $novoHash
    ): bool {
        $this->pdo
            ->beginTransaction();

        try {
            $stmt =
                $this->pdo
                    ->prepare(
                        'UPDATE usuarios
                         SET
                            senha_hash = :senha_hash,
                            sessao_versao =
                                sessao_versao + 1
                         WHERE id = :id
                           AND status = "ATIVO"'
                    );

            $stmt->execute([
                ':senha_hash' =>
                    $novoHash,
                ':id' =>
                    $usuarioId,
            ]);

            $alterou =
                $stmt->rowCount()
                === 1;

            if (!$alterou) {
                $this->pdo
                    ->rollBack();

                return false;
            }

            $stmt =
                $this->pdo->prepare(
                    'INSERT INTO eventos_seguranca_conta (
                        usuario_id,
                        tipo,
                        titulo,
                        detalhe,
                        criado_em
                     )
                     VALUES (
                        :usuario_id,
                        "SENHA_ALTERADA",
                        "Senha alterada",
                        "A senha foi alterada pelo usuário autenticado.",
                        NOW()
                     )'
                );

            $stmt->execute([
                ':usuario_id' =>
                    $usuarioId,
            ]);

            $this->pdo
                ->commit();

            return true;
        } catch (Throwable $e) {
            if (
                $this->pdo
                    ->inTransaction()
            ) {
                $this->pdo
                    ->rollBack();
            }

            throw $e;
        }
    }
}
