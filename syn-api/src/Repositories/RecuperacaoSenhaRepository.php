<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Throwable;

/**
 * Repository da recuperação de senha.
 *
 * Importante:
 * o banco recebe somente o HASH do token.
 */
final class RecuperacaoSenhaRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarUsuarioAtivoPorEmail(
        string $email
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                id,
                nome,
                email,
                status
            FROM usuarios
            WHERE LOWER(email) = LOWER(:email)
              AND status = 'ATIVO'
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
     * Invalida solicitações antigas ainda abertas e cria uma nova.
     */
    public function criarSolicitacao(
        int $usuarioId,
        string $tokenHash,
        string $expiraEm
    ): void {
        $this->pdo->beginTransaction();

        try {
            $stmtAntigos = $this->pdo->prepare(
                'UPDATE recuperacoes_senha
                 SET usado_em = NOW()
                 WHERE usuario_id = :usuario_id
                   AND usado_em IS NULL'
            );

            $stmtAntigos->execute([
                ':usuario_id' => $usuarioId,
            ]);

            $stmt = $this->pdo->prepare(
                'INSERT INTO recuperacoes_senha (
                    usuario_id,
                    token_hash,
                    expira_em
                 )
                 VALUES (
                    :usuario_id,
                    :token_hash,
                    :expira_em
                 )'
            );

            $stmt->execute([
                ':usuario_id' => $usuarioId,
                ':token_hash' => $tokenHash,
                ':expira_em' => $expiraEm,
            ]);

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarTokenValido(
        string $tokenHash
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                r.id,
                r.usuario_id,
                r.token_hash,
                r.expira_em,
                r.usado_em,

                u.nome AS usuario_nome,
                u.email AS usuario_email,
                u.status AS usuario_status

            FROM recuperacoes_senha r

            INNER JOIN usuarios u
                ON u.id = r.usuario_id

            WHERE r.token_hash = :token_hash
              AND r.usado_em IS NULL
              AND r.expira_em >= NOW()
              AND u.status = 'ATIVO'

            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':token_hash' => $tokenHash,
        ]);

        $registro = $stmt->fetch();

        return $registro === false
            ? null
            : $registro;
    }

    /**
     * Troca a senha e consome o token na mesma transação.
     */
    public function redefinirSenha(
        int $recuperacaoId,
        int $usuarioId,
        string $senhaHash
    ): void {
        $this->pdo->beginTransaction();

        try {
            $stmtUsuario = $this->pdo->prepare(
                'UPDATE usuarios
                 SET senha_hash = :senha_hash
                 WHERE id = :usuario_id
                   AND status = \'ATIVO\''
            );

            $stmtUsuario->execute([
                ':senha_hash' => $senhaHash,
                ':usuario_id' => $usuarioId,
            ]);

            if ($stmtUsuario->rowCount() !== 1) {
                throw new \RuntimeException(
                    'Não foi possível atualizar a senha do usuário.'
                );
            }

            /**
             * Consome todas as solicitações abertas do usuário.
             * Assim um token anterior não continua válido.
             */
            $stmtTokens = $this->pdo->prepare(
                'UPDATE recuperacoes_senha
                 SET usado_em = NOW()
                 WHERE usuario_id = :usuario_id
                   AND usado_em IS NULL'
            );

            $stmtTokens->execute([
                ':usuario_id' => $usuarioId,
            ]);

            $this->pdo->commit();
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }
}
