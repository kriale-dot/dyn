<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository das notificações internas.
 *
 * A Etapa 33 mantém os avisos derivados da Etapa 32 e acrescenta
 * limpeza de avisos de proximidade que ficaram obsoletos após
 * alteração/cancelamento da programação.
 *
 * As notificações PROGRAMACAO_ALTERADA e PROGRAMACAO_CANCELADA
 * são inseridas pelo trigger da migration 08.
 *
 * A Etapa 88 acrescenta CADASTRO_PENDENTE para Administradores e
 * Organizadores que possuem CADASTROS_APROVAR.
 */
final class NotificacaoRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function sincronizarEscalasPendentes(
        int $usuarioId
    ): void {
        $sql = <<<'SQL'
            INSERT IGNORE INTO notificacoes (
                usuario_id,
                tipo,
                titulo,
                mensagem,
                url_acao,
                origem_tipo,
                origem_id,
                expira_em
            )
            SELECT
                p.usuario_id,
                'ESCALA_PENDENTE',
                'Confirme sua participação',

                CONCAT(
                    'Você foi escalado como ',
                    p.funcao_nome_historico,
                    ' em ',
                    pr.titulo,
                    '.'
                ),

                CONCAT(
                    '/programacoes/',
                    pr.id
                ),

                'PARTICIPACAO',
                p.id,
                pr.fim_em

            FROM participacoes p

            INNER JOIN programacoes pr
                ON pr.id = p.programacao_id

            WHERE p.usuario_id = :usuario_id
              AND p.status = 'ESCALADO'
              AND pr.status = 'AGENDADA'
              AND pr.fim_em >= NOW()
        SQL;

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':usuario_id' =>
                $usuarioId,
        ]);
    }

    public function sincronizarProximosCompromissos(
        int $usuarioId
    ): void {
        $sql = <<<'SQL'
            INSERT IGNORE INTO notificacoes (
                usuario_id,
                tipo,
                titulo,
                mensagem,
                url_acao,
                origem_tipo,
                origem_id,
                expira_em
            )
            SELECT
                p.usuario_id,
                'PROXIMO_COMPROMISSO',
                'Seu compromisso está próximo',

                CONCAT(
                    pr.titulo,
                    ' começa em ',
                    DATE_FORMAT(
                        pr.inicio_em,
                        '%d/%m/%Y às %H:%i'
                    ),
                    '.'
                ),

                CONCAT(
                    '/programacoes/',
                    pr.id
                ),

                'PARTICIPACAO',
                p.id,
                pr.fim_em

            FROM participacoes p

            INNER JOIN programacoes pr
                ON pr.id = p.programacao_id

            WHERE p.usuario_id = :usuario_id
              AND p.status = 'CONFIRMADO'
              AND pr.status = 'AGENDADA'
              AND pr.inicio_em >= NOW()
              AND pr.inicio_em
                    <= DATE_ADD(
                        NOW(),
                        INTERVAL 48 HOUR
                    )
        SQL;

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':usuario_id' =>
                $usuarioId,
        ]);
    }

    public function encerrarEscalasPendentesResolvidas(
        int $usuarioId
    ): void {
        $sql = <<<'SQL'
            UPDATE notificacoes n

            INNER JOIN participacoes p
                ON n.origem_tipo = 'PARTICIPACAO'
               AND n.origem_id = p.id

            INNER JOIN programacoes pr
                ON pr.id = p.programacao_id

            SET n.lida_em =
                COALESCE(
                    n.lida_em,
                    NOW()
                )

            WHERE n.usuario_id = :usuario_id
              AND n.tipo = 'ESCALA_PENDENTE'

              AND (
                    p.status <> 'ESCALADO'
                    OR pr.status <> 'AGENDADA'
                    OR pr.fim_em < NOW()
              )
        SQL;

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':usuario_id' =>
                $usuarioId,
        ]);
    }

    /**
     * Fecha avisos de "compromisso próximo" que perderam sentido
     * porque a escala/programação mudou.
     */
    public function encerrarProximosCompromissosObsoletos(
        int $usuarioId
    ): void {
        $sql = <<<'SQL'
            UPDATE notificacoes n

            INNER JOIN participacoes p
                ON n.origem_tipo = 'PARTICIPACAO'
               AND n.origem_id = p.id

            INNER JOIN programacoes pr
                ON pr.id = p.programacao_id

            SET n.lida_em =
                COALESCE(
                    n.lida_em,
                    NOW()
                )

            WHERE n.usuario_id = :usuario_id
              AND n.tipo = 'PROXIMO_COMPROMISSO'
              AND n.lida_em IS NULL

              AND (
                    p.status <> 'CONFIRMADO'
                    OR pr.status <> 'AGENDADA'
                    OR pr.fim_em < NOW()
                    OR pr.inicio_em
                        > DATE_ADD(
                            NOW(),
                            INTERVAL 48 HOUR
                        )
              )
        SQL;

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':usuario_id' =>
                $usuarioId,
        ]);
    }

    /**
     * Cria uma notificação para cada cadastro que:
     *
     * - já confirmou o e-mail;
     * - está com status PENDENTE;
     * - ainda precisa de decisão administrativa.
     *
     * O destinatário recebe a notificação somente se for:
     *
     * - ADMINISTRADOR; ou
     * - ORGANIZADOR com CADASTROS_APROVAR.
     *
     * INSERT IGNORE aproveita a chave única da tabela `notificacoes`,
     * evitando duplicar o mesmo aviso em cada sincronização do sino.
     */
    public function sincronizarCadastrosPendentes(
        int $usuarioId
    ): void {
        $sql = <<<'SQL'
            INSERT IGNORE INTO notificacoes (
                usuario_id,
                tipo,
                titulo,
                mensagem,
                url_acao,
                origem_tipo,
                origem_id,
                expira_em
            )

            SELECT
                u.id,

                'CADASTRO_PENDENTE',

                'Novo cadastro aguardando aprovação',

                CONCAT(
                    sc.nome,
                    ' confirmou o e-mail e aguarda análise.'
                ),

                '/gestao/cadastros',

                'SOLICITACAO_CADASTRO',

                sc.id,

                NULL

            FROM usuarios u

            INNER JOIN papeis p
                ON p.id = u.papel_id

            CROSS JOIN solicitacoes_cadastro sc

            WHERE u.id = :usuario_id

              AND u.status = 'ATIVO'

              AND sc.status = 'PENDENTE'

              AND sc.email_confirmado_em IS NOT NULL

              AND (
                    p.codigo = 'ADMINISTRADOR'

                    OR (
                        p.codigo = 'ORGANIZADOR'

                        AND EXISTS (
                            SELECT 1

                            FROM usuarios_permissoes_especiais upe

                            INNER JOIN permissoes_especiais pe
                                ON pe.id = upe.permissao_id

                            WHERE upe.usuario_id = u.id
                              AND pe.codigo =
                                  'CADASTROS_APROVAR'
                              AND pe.ativo = 1
                        )
                    )
              )
        SQL;

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':usuario_id' =>
                $usuarioId,
        ]);
    }

    /**
     * Se o cadastro já foi aprovado ou rejeitado, o aviso antigo deixa
     * de ser uma pendência.
     *
     * Isso também cobre uma decisão tomada por OUTRO aprovador.
     */
    public function encerrarCadastrosResolvidos(
        int $usuarioId
    ): void {
        $sql = <<<'SQL'
            UPDATE notificacoes n

            INNER JOIN solicitacoes_cadastro sc
                ON n.origem_tipo =
                    'SOLICITACAO_CADASTRO'
               AND n.origem_id = sc.id

            SET n.lida_em =
                COALESCE(
                    n.lida_em,
                    NOW()
                )

            WHERE n.usuario_id = :usuario_id

              AND n.tipo =
                  'CADASTRO_PENDENTE'

              AND n.lida_em IS NULL

              AND sc.status <>
                  'PENDENTE'
        SQL;

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':usuario_id' =>
                $usuarioId,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listar(
        int $usuarioId,
        bool $somenteNaoLidas,
        int $limite
    ): array {
        $filtroLida =
            $somenteNaoLidas
                ? 'AND lida_em IS NULL'
                : '';

        $sql = "
            SELECT
                id,
                tipo,
                titulo,
                mensagem,
                url_acao,
                origem_tipo,
                origem_id,
                lida_em,
                expira_em,
                criada_em

            FROM notificacoes

            WHERE usuario_id = :usuario_id

              AND (
                    expira_em IS NULL
                    OR expira_em >= NOW()
              )

              {$filtroLida}

            ORDER BY
                CASE
                    WHEN lida_em IS NULL
                    THEN 0
                    ELSE 1
                END ASC,
                criada_em DESC,
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

    public function contarNaoLidas(
        int $usuarioId
    ): int {
        $sql = <<<'SQL'
            SELECT COUNT(*)

            FROM notificacoes

            WHERE usuario_id = :usuario_id
              AND lida_em IS NULL
              AND (
                    expira_em IS NULL
                    OR expira_em >= NOW()
              )
        SQL;

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':usuario_id' =>
                $usuarioId,
        ]);

        return (int)
            $stmt->fetchColumn();
    }

    public function marcarComoLida(
        int $usuarioId,
        int $notificacaoId
    ): bool {
        $sql = <<<'SQL'
            UPDATE notificacoes

            SET lida_em =
                COALESCE(
                    lida_em,
                    NOW()
                )

            WHERE id = :id
              AND usuario_id = :usuario_id
        SQL;

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':id' =>
                $notificacaoId,
            ':usuario_id' =>
                $usuarioId,
        ]);

        return $stmt->rowCount()
            > 0;
    }

    public function marcarTodasComoLidas(
        int $usuarioId
    ): int {
        $sql = <<<'SQL'
            UPDATE notificacoes

            SET lida_em = NOW()

            WHERE usuario_id = :usuario_id
              AND lida_em IS NULL
              AND (
                    expira_em IS NULL
                    OR expira_em >= NOW()
              )
        SQL;

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':usuario_id' =>
                $usuarioId,
        ]);

        return $stmt->rowCount();
    }
}
