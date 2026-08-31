<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository exclusivo da autenticação.
 *
 * Importante:
 * senha_hash é consultada apenas internamente.
 * Ela nunca deve aparecer em respostas JSON.
 */
final class AuthRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Busca um usuário pelo e-mail para validar login.
     *
     * @return array<string, mixed>|null
     */
    public function buscarPorEmail(
        string $email
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                u.id,
                u.nome,
                u.email,
                u.foto,
                u.status,
                u.senha_hash,
                u.ultimo_login_em,

                p.id AS papel_id,
                p.codigo AS papel_codigo,
                p.nome AS papel_nome

            FROM usuarios u

            INNER JOIN papeis p
                ON p.id = u.papel_id

            WHERE u.email = :email

            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':email' => $email,
        ]);

        $usuario = $stmt->fetch();

        return $usuario === false
            ? null
            : $usuario;
    }

    /**
     * Busca o usuário atual a cada requisição autenticada.
     *
     * Essa decisão é importante:
     * mesmo que um token ainda não tenha expirado, um usuário
     * desativado deixa de acessar a API imediatamente.
     *
     * @return array<string, mixed>|null
     */
    public function buscarPorId(
        int $usuarioId
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                u.id,
                u.nome,
                u.email,
                u.foto,
                u.status,
                u.ultimo_login_em,

                p.id AS papel_id,
                p.codigo AS papel_codigo,
                p.nome AS papel_nome

            FROM usuarios u

            INNER JOIN papeis p
                ON p.id = u.papel_id

            WHERE u.id = :id

            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $usuarioId,
        ]);

        $usuario = $stmt->fetch();

        return $usuario === false
            ? null
            : $usuario;
    }

    /**
     * Registra o momento do último login bem-sucedido.
     */
    public function registrarUltimoLogin(
        int $usuarioId
    ): void {
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios
             SET ultimo_login_em = NOW()
             WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $usuarioId,
        ]);
    }
}
