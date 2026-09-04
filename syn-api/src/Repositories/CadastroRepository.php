<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Throwable;

/**
 * Repository do fluxo de cadastro público.
 *
 * A solicitação pública fica separada da tabela `usuarios`.
 * Ela só é convertida em usuário dentro da transação de aprovação.
 */
final class CadastroRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function usuarioExistePorEmail(
        string $email
    ): bool {
        $stmt =
            $this->pdo->prepare(
                'SELECT 1
                 FROM usuarios
                 WHERE LOWER(email) = LOWER(:email)
                 LIMIT 1'
            );

        $stmt->execute([
            ':email' => $email,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarPorEmail(
        string $email
    ): ?array {
        $stmt =
            $this->pdo->prepare(
                'SELECT *
                 FROM solicitacoes_cadastro
                 WHERE LOWER(email) = LOWER(:email)
                 LIMIT 1'
            );

        $stmt->execute([
            ':email' => $email,
        ]);

        $row = $stmt->fetch();

        return $row === false
            ? null
            : $row;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarPorId(
        int $id
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                sc.id,
                sc.nome,
                sc.data_nascimento,
                sc.telefone,
                sc.email,
                sc.status,
                sc.tentativas,
                sc.motivo_rejeicao,
                sc.solicitado_em,
                sc.analisado_em,
                sc.criado_em,
                sc.atualizado_em,

                sc.analisado_por_usuario_id,
                analisador.nome AS analisado_por_nome,

                sc.usuario_criado_id

            FROM solicitacoes_cadastro sc

            LEFT JOIN usuarios analisador
                ON analisador.id =
                    sc.analisado_por_usuario_id

            WHERE sc.id = :id

            LIMIT 1
        SQL;

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':id' => $id,
        ]);

        $row =
            $stmt->fetch();

        return $row === false
            ? null
            : $row;
    }

    /**
     * @param array<string, mixed> $dados
     */
    public function criar(
        array $dados
    ): int {
        $sql = <<<'SQL'
            INSERT INTO solicitacoes_cadastro (
                nome,
                data_nascimento,
                telefone,
                email,
                senha_hash,
                email_confirmacao_token_hash,
                email_confirmacao_expira_em,
                email_confirmado_em,
                status,
                tentativas,
                solicitado_em
            )
            VALUES (
                :nome,
                :data_nascimento,
                :telefone,
                :email,
                :senha_hash,
                :email_confirmacao_token_hash,
                :email_confirmacao_expira_em,
                NULL,
                'AGUARDANDO_EMAIL',
                1,
                NOW()
            )
        SQL;

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':nome' =>
                $dados['nome'],
            ':data_nascimento' =>
                $dados['data_nascimento'],
            ':telefone' =>
                $dados['telefone'],
            ':email' =>
                $dados['email'],
            ':senha_hash' =>
                $dados['senha_hash'],
            ':email_confirmacao_token_hash' =>
                $dados[
                    'email_confirmacao_token_hash'
                ],
            ':email_confirmacao_expira_em' =>
                $dados[
                    'email_confirmacao_expira_em'
                ],
        ]);

        return (int)
            $this->pdo
                ->lastInsertId();
    }

    /**
     * Uma pessoa rejeitada pode corrigir os dados e solicitar novamente.
     *
     * Mantemos o mesmo registro e incrementamos `tentativas`.
     *
     * @param array<string, mixed> $dados
     */
    public function reabrir(
        int $id,
        array $dados
    ): void {
        $sql = <<<'SQL'
            UPDATE solicitacoes_cadastro

            SET
                nome = :nome,
                data_nascimento = :data_nascimento,
                telefone = :telefone,
                email = :email,
                senha_hash = :senha_hash,

                email_confirmacao_token_hash =
                    :email_confirmacao_token_hash,

                email_confirmacao_expira_em =
                    :email_confirmacao_expira_em,

                email_confirmado_em = NULL,

                status = 'AGUARDANDO_EMAIL',

                tentativas = tentativas + 1,

                motivo_rejeicao = NULL,

                analisado_por_usuario_id = NULL,
                usuario_criado_id = NULL,

                solicitado_em = NOW(),
                analisado_em = NULL

            WHERE id = :id
              AND status IN (
                    'REJEITADO',
                    'EXPIRADO'
              )
        SQL;

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':id' => $id,
            ':nome' =>
                $dados['nome'],
            ':data_nascimento' =>
                $dados['data_nascimento'],
            ':telefone' =>
                $dados['telefone'],
            ':email' =>
                $dados['email'],
            ':senha_hash' =>
                $dados['senha_hash'],
            ':email_confirmacao_token_hash' =>
                $dados[
                    'email_confirmacao_token_hash'
                ],
            ':email_confirmacao_expira_em' =>
                $dados[
                    'email_confirmacao_expira_em'
                ],
        ]);
    }

    /**
     * Marca como EXPIRADO todo cadastro cujo e-mail não foi confirmado
     * dentro do prazo.
     *
     * O token é removido. O restante da solicitação permanece como
     * histórico e poderá ser reaberto se a pessoa se cadastrar novamente.
     */
    public function expirarConfirmacoesVencidas(): int
    {
        $stmt =
            $this->pdo->prepare(
                'UPDATE solicitacoes_cadastro
                 SET
                    status = "EXPIRADO",
                    email_confirmacao_token_hash = NULL
                 WHERE status = "AGUARDANDO_EMAIL"
                   AND email_confirmacao_expira_em IS NOT NULL
                   AND email_confirmacao_expira_em < NOW()'
            );

        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * Expira uma solicitação específica.
     */
    public function expirarPorId(
        int $id
    ): bool {
        $stmt =
            $this->pdo->prepare(
                'UPDATE solicitacoes_cadastro
                 SET
                    status = "EXPIRADO",
                    email_confirmacao_token_hash = NULL
                 WHERE id = :id
                   AND status = "AGUARDANDO_EMAIL"'
            );

        $stmt->execute([
            ':id' =>
                $id,
        ]);

        return $stmt->rowCount() === 1;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listar(
        string $status
    ): array {
        $sql = <<<'SQL'
            SELECT
                sc.id,
                sc.nome,
                sc.data_nascimento,
                sc.telefone,
                sc.email,
                sc.status,
                sc.tentativas,
                sc.motivo_rejeicao,
                sc.solicitado_em,
                sc.email_confirmado_em,
                sc.email_confirmacao_expira_em,
                sc.analisado_em,
                sc.usuario_criado_id,

                analisador.nome AS analisado_por_nome,

                TIMESTAMPDIFF(
                    DAY,
                    sc.solicitado_em,
                    NOW()
                ) AS dias_aguardando

            FROM solicitacoes_cadastro sc

            LEFT JOIN usuarios analisador
                ON analisador.id =
                    sc.analisado_por_usuario_id

            WHERE sc.status = :status

            ORDER BY
                sc.solicitado_em ASC,
                sc.id ASC
        SQL;

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':status' => $status,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarPorTokenConfirmacaoHash(
        string $tokenHash
    ): ?array {
        $stmt =
            $this->pdo->prepare(
                'SELECT *
                 FROM solicitacoes_cadastro
                 WHERE email_confirmacao_token_hash =
                    :token_hash
                 LIMIT 1'
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
     * Confirma o e-mail e libera a solicitação para a fila de aprovação.
     */
    public function confirmarEmail(
        int $id,
        string $tokenHash
    ): bool {
        $stmt =
            $this->pdo->prepare(
                'UPDATE solicitacoes_cadastro
                 SET
                    status = "PENDENTE",
                    email_confirmado_em = NOW(),
                    email_confirmacao_token_hash = NULL,
                    email_confirmacao_expira_em = NULL
                 WHERE id = :id
                   AND status = "AGUARDANDO_EMAIL"
                   AND email_confirmacao_token_hash =
                        :token_hash'
            );

        $stmt->execute([
            ':id' =>
                $id,
            ':token_hash' =>
                $tokenHash,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Gera uma nova janela de confirmação para solicitação que ainda
     * aguarda validação do e-mail.
     */
    public function atualizarTokenConfirmacao(
        int $id,
        string $tokenHash,
        string $expiraEm
    ): bool {
        $stmt =
            $this->pdo->prepare(
                'UPDATE solicitacoes_cadastro
                 SET
                    email_confirmacao_token_hash =
                        :token_hash,
                    email_confirmacao_expira_em =
                        :expira_em
                 WHERE id = :id
                   AND status =
                        "AGUARDANDO_EMAIL"'
            );

        $stmt->execute([
            ':id' =>
                $id,
            ':token_hash' =>
                $tokenHash,
            ':expira_em' =>
                $expiraEm,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function usuarioPossuiPermissaoEspecial(
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

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':usuario_id' =>
                $usuarioId,
            ':codigo' =>
                $codigo,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Converte uma solicitação PENDENTE em usuário MEMBRO.
     *
     * A operação inteira é transacional:
     *
     * - bloqueia a solicitação;
     * - confirma que ainda está pendente;
     * - confirma que o e-mail não virou usuário por outro caminho;
     * - cria o usuário;
     * - marca a solicitação como aprovada;
     * - remove o hash de senha da tabela de solicitações.
     *
     * @return array<string, mixed>
     */
    public function aprovar(
        int $solicitacaoId,
        int $aprovadorId
    ): array {
        $this->pdo->beginTransaction();

        try {
            $stmt =
                $this->pdo->prepare(
                    'SELECT *
                     FROM solicitacoes_cadastro
                     WHERE id = :id
                     FOR UPDATE'
                );

            $stmt->execute([
                ':id' =>
                    $solicitacaoId,
            ]);

            $solicitacao =
                $stmt->fetch();

            if ($solicitacao === false) {
                $this->pdo->rollBack();

                return [
                    'resultado' =>
                        'NAO_ENCONTRADO',
                ];
            }

            if (
                $solicitacao['status']
                !== 'PENDENTE'
                || empty(
                    $solicitacao[
                        'email_confirmado_em'
                    ]
                )
            ) {
                $this->pdo->rollBack();

                return [
                    'resultado' =>
                        'NAO_PENDENTE',
                ];
            }

            $stmt =
                $this->pdo->prepare(
                    'SELECT id
                     FROM usuarios
                     WHERE LOWER(email) =
                        LOWER(:email)
                     LIMIT 1'
                );

            $stmt->execute([
                ':email' =>
                    $solicitacao['email'],
            ]);

            if (
                $stmt->fetchColumn()
                !== false
            ) {
                $this->pdo->rollBack();

                return [
                    'resultado' =>
                        'EMAIL_EXISTE',
                ];
            }

            $papelId =
                $this->pdo
                    ->query(
                        "SELECT id
                         FROM papeis
                         WHERE codigo = 'MEMBRO'
                         LIMIT 1"
                    )
                    ->fetchColumn();

            if ($papelId === false) {
                $this->pdo->rollBack();

                return [
                    'resultado' =>
                        'PAPEL_MEMBRO_NAO_ENCONTRADO',
                ];
            }

            if (
                empty(
                    $solicitacao[
                        'senha_hash'
                    ]
                )
            ) {
                $this->pdo->rollBack();

                return [
                    'resultado' =>
                        'SEM_SENHA',
                ];
            }

            $stmt =
                $this->pdo->prepare(
                    'INSERT INTO usuarios (
                        papel_id,
                        nome,
                        data_nascimento,
                        telefone,
                        email,
                        senha_hash,
                        status
                     )
                     VALUES (
                        :papel_id,
                        :nome,
                        :data_nascimento,
                        :telefone,
                        :email,
                        :senha_hash,
                        "ATIVO"
                     )'
                );

            $stmt->execute([
                ':papel_id' =>
                    (int) $papelId,
                ':nome' =>
                    $solicitacao['nome'],
                ':data_nascimento' =>
                    $solicitacao[
                        'data_nascimento'
                    ],
                ':telefone' =>
                    $solicitacao['telefone'],
                ':email' =>
                    $solicitacao['email'],
                ':senha_hash' =>
                    $solicitacao[
                        'senha_hash'
                    ],
            ]);

            $usuarioId =
                (int)
                $this->pdo
                    ->lastInsertId();

            $stmt =
                $this->pdo->prepare(
                    'UPDATE solicitacoes_cadastro

                     SET
                        status = "APROVADO",
                        analisado_por_usuario_id =
                            :aprovador_id,
                        usuario_criado_id =
                            :usuario_id,
                        analisado_em = NOW(),
                        motivo_rejeicao = NULL,
                        senha_hash = NULL

                     WHERE id = :id'
                );

            $stmt->execute([
                ':aprovador_id' =>
                    $aprovadorId,
                ':usuario_id' =>
                    $usuarioId,
                ':id' =>
                    $solicitacaoId,
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
                        "CONTA_CRIADA",
                        "Conta criada",
                        "Seu cadastro foi aprovado e a conta foi ativada como Membro.",
                        NOW()
                     )'
                );

            $stmt->execute([
                ':usuario_id' =>
                    $usuarioId,
            ]);

            $this->pdo->commit();

            return [
                'resultado' =>
                    'APROVADO',
                'usuario_id' =>
                    $usuarioId,
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

    /**
     * Rejeita e remove o hash da senha da tabela de solicitações.
     */
    public function rejeitar(
        int $solicitacaoId,
        int $aprovadorId,
        ?string $motivo
    ): string {
        $stmt =
            $this->pdo->prepare(
                'UPDATE solicitacoes_cadastro

                 SET
                    status = "REJEITADO",
                    motivo_rejeicao = :motivo,
                    analisado_por_usuario_id =
                        :aprovador_id,
                    analisado_em = NOW(),
                    senha_hash = NULL

                 WHERE id = :id
                   AND status = "PENDENTE"'
            );

        $stmt->execute([
            ':motivo' =>
                $motivo,
            ':aprovador_id' =>
                $aprovadorId,
            ':id' =>
                $solicitacaoId,
        ]);

        if (
            $stmt->rowCount() > 0
        ) {
            return 'REJEITADO';
        }

        $existe =
            $this->buscarPorId(
                $solicitacaoId
            );

        return $existe === null
            ? 'NAO_ENCONTRADO'
            : 'NAO_PENDENTE';
    }
}
