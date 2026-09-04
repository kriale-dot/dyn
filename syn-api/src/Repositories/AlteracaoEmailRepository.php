<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Throwable;

/**
 * Repository do fluxo de alteração do e-mail de login.
 */
final class AlteracaoEmailRepository
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

    public function emailExiste(
        string $email,
        int $ignorarUsuarioId
    ): bool {
        $stmt =
            $this->pdo->prepare(
                'SELECT 1
                 FROM usuarios
                 WHERE LOWER(email) =
                    LOWER(:email)
                   AND id <> :usuario_id
                 LIMIT 1'
            );

        $stmt->execute([
            ':email' =>
                $email,
            ':usuario_id' =>
                $ignorarUsuarioId,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Cancela solicitações pendentes anteriores.
     */
    public function cancelarPendentes(
        int $usuarioId
    ): void {
        $stmt =
            $this->pdo->prepare(
                'UPDATE alteracoes_email
                 SET
                    status = "CANCELADO",
                    cancelado_em = NOW(),
                    token_hash = NULL
                 WHERE usuario_id =
                    :usuario_id
                   AND status =
                    "PENDENTE"'
            );

        $stmt->execute([
            ':usuario_id' =>
                $usuarioId,
        ]);
    }

    public function criar(
        int $usuarioId,
        string $emailAnterior,
        string $novoEmail,
        string $tokenHash,
        string $expiraEm
    ): int {
        $stmt =
            $this->pdo->prepare(
                'INSERT INTO alteracoes_email (
                    usuario_id,
                    email_anterior,
                    novo_email,
                    token_hash,
                    status,
                    expira_em,
                    solicitado_em
                 )
                 VALUES (
                    :usuario_id,
                    :email_anterior,
                    :novo_email,
                    :token_hash,
                    "PENDENTE",
                    :expira_em,
                    NOW()
                 )'
            );

        $stmt->execute([
            ':usuario_id' =>
                $usuarioId,
            ':email_anterior' =>
                $emailAnterior,
            ':novo_email' =>
                $novoEmail,
            ':token_hash' =>
                $tokenHash,
            ':expira_em' =>
                $expiraEm,
        ]);

        return (int)
            $this->pdo
                ->lastInsertId();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarPorTokenHash(
        string $tokenHash
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                ae.*,
                u.nome AS usuario_nome,
                u.email AS usuario_email_atual,
                u.status AS usuario_status

            FROM alteracoes_email ae

            INNER JOIN usuarios u
                ON u.id = ae.usuario_id

            WHERE ae.token_hash =
                :token_hash

            LIMIT 1
        SQL;

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':token_hash' =>
                $tokenHash,
        ]);

        $row =
            $stmt->fetch();

        return $row === false
            ? null
            : $row;
    }

    /**
     * Confirma a alteração dentro de uma transação e revoga todos os JWT.
     *
     * @return array<string, mixed>
     */
    public function confirmar(
        int $alteracaoId,
        string $tokenHash
    ): array {
        $this->pdo
            ->beginTransaction();

        try {
            $stmt =
                $this->pdo
                    ->prepare(
                        'SELECT
                            ae.*,
                            u.nome AS usuario_nome,
                            u.email AS usuario_email_atual,
                            u.status AS usuario_status
                         FROM alteracoes_email ae
                         INNER JOIN usuarios u
                            ON u.id = ae.usuario_id
                         WHERE ae.id = :id
                         FOR UPDATE'
                    );

            $stmt->execute([
                ':id' =>
                    $alteracaoId,
            ]);

            $alteracao =
                $stmt->fetch();

            if ($alteracao === false) {
                $this->pdo
                    ->rollBack();

                return [
                    'resultado' =>
                        'NAO_ENCONTRADO',
                ];
            }

            if (
                $alteracao['status']
                !== 'PENDENTE'
                || !hash_equals(
                    (string)
                    ($alteracao['token_hash']
                        ?? ''),
                    $tokenHash
                )
            ) {
                $this->pdo
                    ->rollBack();

                return [
                    'resultado' =>
                        'TOKEN_INVALIDO',
                ];
            }

            if (
                $alteracao[
                    'usuario_status'
                ]
                !== 'ATIVO'
            ) {
                $this->pdo
                    ->rollBack();

                return [
                    'resultado' =>
                        'USUARIO_INATIVO',
                ];
            }

            /**
             * Se o e-mail atual mudou por outro caminho depois da
             * solicitação, não aplicamos uma troca em cima de estado
             * inesperado.
             */
            if (
                mb_strtolower(
                    (string)
                    $alteracao[
                        'usuario_email_atual'
                    ]
                )
                !== mb_strtolower(
                    (string)
                    $alteracao[
                        'email_anterior'
                    ]
                )
            ) {
                $this->pdo
                    ->rollBack();

                return [
                    'resultado' =>
                        'EMAIL_ATUAL_MUDOU',
                ];
            }

            $stmt =
                $this->pdo
                    ->prepare(
                        'SELECT 1
                         FROM usuarios
                         WHERE LOWER(email) =
                            LOWER(:novo_email)
                           AND id <> :usuario_id
                         LIMIT 1'
                    );

            $stmt->execute([
                ':novo_email' =>
                    $alteracao[
                        'novo_email'
                    ],
                ':usuario_id' =>
                    (int)
                    $alteracao[
                        'usuario_id'
                    ],
            ]);

            if (
                $stmt->fetchColumn()
                !== false
            ) {
                $this->pdo
                    ->rollBack();

                return [
                    'resultado' =>
                        'EMAIL_EM_USO',
                ];
            }

            $stmt =
                $this->pdo
                    ->prepare(
                        'UPDATE usuarios
                         SET
                            email = :novo_email,
                            sessao_versao =
                                sessao_versao + 1
                         WHERE id =
                            :usuario_id
                           AND status =
                            "ATIVO"'
                    );

            $stmt->execute([
                ':novo_email' =>
                    $alteracao[
                        'novo_email'
                    ],
                ':usuario_id' =>
                    (int)
                    $alteracao[
                        'usuario_id'
                    ],
            ]);

            if (
                $stmt->rowCount() !== 1
            ) {
                $this->pdo
                    ->rollBack();

                return [
                    'resultado' =>
                        'FALHA_USUARIO',
                ];
            }

            $stmt =
                $this->pdo
                    ->prepare(
                        'UPDATE alteracoes_email
                         SET
                            status =
                                "CONFIRMADO",
                            confirmado_em =
                                NOW(),
                            token_hash =
                                NULL
                         WHERE id = :id'
                    );

            $stmt->execute([
                ':id' =>
                    $alteracaoId,
            ]);

            /**
             * Qualquer outro pedido pendente desse usuário deixa de ser
             * válido depois que o e-mail principal mudou.
             */
            $stmt =
                $this->pdo
                    ->prepare(
                        'UPDATE alteracoes_email
                         SET
                            status =
                                "CANCELADO",
                            cancelado_em =
                                NOW(),
                            token_hash =
                                NULL
                         WHERE usuario_id =
                            :usuario_id
                           AND status =
                            "PENDENTE"
                           AND id <> :id'
                    );

            $stmt->execute([
                ':usuario_id' =>
                    (int)
                    $alteracao[
                        'usuario_id'
                    ],
                ':id' =>
                    $alteracaoId,
            ]);

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
                        "EMAIL_ALTERADO",
                        "E-mail de acesso alterado",
                        "O endereço usado para login e recuperação de senha foi alterado.",
                        NOW()
                     )'
                );

            $stmt->execute([
                ':usuario_id' =>
                    (int)
                    $alteracao[
                        'usuario_id'
                    ],
            ]);

            $this->pdo
                ->commit();

            return [
                'resultado' =>
                    'CONFIRMADO',
                'usuario_id' =>
                    (int)
                    $alteracao[
                        'usuario_id'
                    ],
                'nome' =>
                    (string)
                    $alteracao[
                        'usuario_nome'
                    ],
                'email_anterior' =>
                    (string)
                    $alteracao[
                        'email_anterior'
                    ],
                'novo_email' =>
                    (string)
                    $alteracao[
                        'novo_email'
                    ],
            ];
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

    public function marcarExpirado(
        int $id
    ): void {
        $stmt =
            $this->pdo->prepare(
                'UPDATE alteracoes_email
                 SET
                    status = "EXPIRADO",
                    token_hash = NULL
                 WHERE id = :id
                   AND status = "PENDENTE"'
            );

        $stmt->execute([
            ':id' => $id,
        ]);
    }
}
