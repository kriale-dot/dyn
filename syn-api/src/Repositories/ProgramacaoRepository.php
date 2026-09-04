<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository do módulo de programações.
 *
 * ETAPA 73:
 * também persiste a decisão explícita de publicação.
 *
 * A coluna `visibilidade` NÃO é inferida por tipo de programação:
 * cada ocorrência precisa estar conscientemente marcada como
 * INTERNA ou PUBLICA.
 */
final class ProgramacaoRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarTodas(): array
    {
        $sql = <<<'SQL'
            SELECT
                p.id,
                p.serie_id,
                p.tipo_programacao_id,
                p.local_id,
                p.organizador_id,
                p.titulo,
                p.descricao,
                p.descricao_publica,
                p.visibilidade,
                p.inicio_em,
                p.fim_em,
                p.status,
                p.permite_resposta,

                p.tipo_programacao_nome_historico,
                p.local_nome_historico,
                p.organizador_nome_historico,

                p.cancelada_em,
                p.motivo_cancelamento,
                p.realizado_em,
                p.criado_em,
                p.atualizado_em,

                (
                    SELECT COUNT(*)
                    FROM participacoes pa
                    WHERE pa.programacao_id = p.id
                ) AS total_participacoes

            FROM programacoes p

            ORDER BY
                p.inicio_em ASC,
                p.id ASC
        SQL;

        return $this->pdo
            ->query($sql)
            ->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarPorId(int $id): ?array
    {
        $sql = <<<'SQL'
            SELECT
                p.id,
                p.serie_id,
                p.tipo_programacao_id,
                p.local_id,
                p.organizador_id,
                p.titulo,
                p.descricao,
                p.descricao_publica,
                p.visibilidade,
                p.inicio_em,
                p.fim_em,
                p.status,
                p.permite_resposta,

                p.tipo_programacao_nome_historico,
                p.local_nome_historico,
                p.organizador_nome_historico,

                p.cancelada_em,
                p.motivo_cancelamento,
                p.realizado_em,
                p.criado_em,
                p.atualizado_em,

                (
                    SELECT COUNT(*)
                    FROM participacoes pa
                    WHERE pa.programacao_id = p.id
                ) AS total_participacoes

            FROM programacoes p

            WHERE p.id = :id

            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id,
        ]);

        $programacao = $stmt->fetch();

        return $programacao === false
            ? null
            : $programacao;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarTipoProgramacaoPorId(
        int $id
    ): ?array {
        $stmt = $this->pdo->prepare(
            'SELECT id, nome, ativo
             FROM tipos_programacao
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            ':id' => $id,
        ]);

        $tipo = $stmt->fetch();

        return $tipo === false
            ? null
            : $tipo;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarLocalPorId(
        int $id
    ): ?array {
        $stmt = $this->pdo->prepare(
            'SELECT id, nome, ativo
             FROM locais
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            ':id' => $id,
        ]);

        $local = $stmt->fetch();

        return $local === false
            ? null
            : $local;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarOrganizadorPorId(
        int $id
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                u.id,
                u.nome,
                u.status,
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
            ':id' => $id,
        ]);

        $usuario = $stmt->fetch();

        return $usuario === false
            ? null
            : $usuario;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buscarConflitosDeLocal(
        int $localId,
        string $inicioEm,
        string $fimEm,
        ?int $ignorarProgramacaoId = null
    ): array {
        $sql = <<<'SQL'
            SELECT
                id,
                titulo,
                inicio_em,
                fim_em,
                status,
                local_nome_historico,
                tipo_programacao_nome_historico,
                organizador_nome_historico
            FROM programacoes
            WHERE local_id = :local_id
              AND status <> 'CANCELADA'
              AND inicio_em < :fim_em
              AND fim_em > :inicio_em
        SQL;

        $parametros = [
            ':local_id' => $localId,
            ':fim_em' => $fimEm,
            ':inicio_em' => $inicioEm,
        ];

        if ($ignorarProgramacaoId !== null) {
            $sql .=
                ' AND id <> :ignorar_programacao_id';

            $parametros[
                ':ignorar_programacao_id'
            ] = $ignorarProgramacaoId;
        }

        $sql .=
            ' ORDER BY inicio_em ASC, id ASC';

        $stmt =
            $this->pdo->prepare($sql);

        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    /**
     * @param array<string, mixed> $dados
     */
    public function criar(array $dados): int
    {
        $sql = <<<'SQL'
            INSERT INTO programacoes (
                serie_id,
                tipo_programacao_id,
                local_id,
                organizador_id,
                titulo,
                descricao,
                descricao_publica,
                visibilidade,
                inicio_em,
                fim_em,
                status,
                permite_resposta,

                tipo_programacao_nome_historico,
                local_nome_historico,
                organizador_nome_historico
            )
            VALUES (
                NULL,
                :tipo_programacao_id,
                :local_id,
                :organizador_id,
                :titulo,
                :descricao,
                :descricao_publica,
                :visibilidade,
                :inicio_em,
                :fim_em,
                'AGENDADA',
                :permite_resposta,

                :tipo_programacao_nome_historico,
                :local_nome_historico,
                :organizador_nome_historico
            )
        SQL;

        $stmt =
            $this->pdo->prepare($sql);

        $stmt->execute([
            ':tipo_programacao_id' =>
                $dados[
                    'tipo_programacao_id'
                ],
            ':local_id' =>
                $dados['local_id'],
            ':organizador_id' =>
                $dados['organizador_id'],
            ':titulo' =>
                $dados['titulo'],
            ':descricao' =>
                $dados['descricao'],
            ':descricao_publica' =>
                $dados['descricao_publica'],
            ':visibilidade' =>
                $dados['visibilidade'],
            ':inicio_em' =>
                $dados['inicio_em'],
            ':fim_em' =>
                $dados['fim_em'],
            ':permite_resposta' =>
                $dados['permite_resposta']
                    ? 1
                    : 0,

            ':tipo_programacao_nome_historico' =>
                $dados[
                    'tipo_programacao_nome_historico'
                ],
            ':local_nome_historico' =>
                $dados[
                    'local_nome_historico'
                ],
            ':organizador_nome_historico' =>
                $dados[
                    'organizador_nome_historico'
                ],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param array<string, mixed> $dados
     */
    public function atualizar(
        int $id,
        array $dados
    ): bool {
        $sql = <<<'SQL'
            UPDATE programacoes
            SET
                tipo_programacao_id =
                    :tipo_programacao_id,
                local_id =
                    :local_id,
                organizador_id =
                    :organizador_id,
                titulo =
                    :titulo,
                descricao =
                    :descricao,
                descricao_publica =
                    :descricao_publica,
                visibilidade =
                    :visibilidade,
                inicio_em =
                    :inicio_em,
                fim_em =
                    :fim_em,
                permite_resposta =
                    :permite_resposta,

                tipo_programacao_nome_historico =
                    :tipo_programacao_nome_historico,
                local_nome_historico =
                    :local_nome_historico,
                organizador_nome_historico =
                    :organizador_nome_historico

            WHERE id = :id
        SQL;

        $stmt =
            $this->pdo->prepare($sql);

        return $stmt->execute([
            ':tipo_programacao_id' =>
                $dados[
                    'tipo_programacao_id'
                ],
            ':local_id' =>
                $dados['local_id'],
            ':organizador_id' =>
                $dados['organizador_id'],
            ':titulo' =>
                $dados['titulo'],
            ':descricao' =>
                $dados['descricao'],
            ':descricao_publica' =>
                $dados['descricao_publica'],
            ':visibilidade' =>
                $dados['visibilidade'],
            ':inicio_em' =>
                $dados['inicio_em'],
            ':fim_em' =>
                $dados['fim_em'],
            ':permite_resposta' =>
                $dados['permite_resposta']
                    ? 1
                    : 0,

            ':tipo_programacao_nome_historico' =>
                $dados[
                    'tipo_programacao_nome_historico'
                ],
            ':local_nome_historico' =>
                $dados[
                    'local_nome_historico'
                ],
            ':organizador_nome_historico' =>
                $dados[
                    'organizador_nome_historico'
                ],

            ':id' => $id,
        ]);
    }

    /**
     * Cancela sem excluir.
     */
    public function cancelar(
        int $id,
        ?string $motivo
    ): bool {
        $sql = <<<'SQL'
            UPDATE programacoes
            SET
                status = 'CANCELADA',
                cancelada_em = NOW(),
                motivo_cancelamento = :motivo
            WHERE id = :id
              AND status <> 'CANCELADA'
              AND status <> 'REALIZADA'
        SQL;

        $stmt =
            $this->pdo->prepare($sql);

        $stmt->execute([
            ':motivo' => $motivo,
            ':id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Marca a programação como realizada.
     *
     * Não altera nenhuma participação.
     * Os estados das participações permanecem como foram
     * efetivamente registrados.
     */
    public function realizar(int $id): bool
    {
        $sql = <<<'SQL'
            UPDATE programacoes
            SET
                status = 'REALIZADA',
                realizado_em = NOW()
            WHERE id = :id
              AND status = 'AGENDADA'
        SQL;

        $stmt =
            $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }
}
