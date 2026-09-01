<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository do histórico de alterações da programação.
 */
final class HistoricoProgramacaoRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarProgramacao(
        int $programacaoId
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                id,
                titulo,
                status,
                tipo_programacao_id,
                tipo_programacao_nome_historico
            FROM programacoes
            WHERE id = :id
            LIMIT 1
        SQL;

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':id' => $programacaoId,
        ]);

        $item = $stmt->fetch();

        return $item === false
            ? null
            : $item;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarUsuario(
        int $usuarioId
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                u.id,
                u.nome,
                u.status,
                p.codigo AS papel_codigo
            FROM usuarios u
            INNER JOIN papeis p
                ON p.id = u.papel_id
            WHERE u.id = :id
            LIMIT 1
        SQL;

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':id' => $usuarioId,
        ]);

        $item = $stmt->fetch();

        return $item === false
            ? null
            : $item;
    }

    public function organizadorPodeAdministrarTipo(
        int $usuarioId,
        int $tipoProgramacaoId
    ): bool {
        $sql = <<<'SQL'
            SELECT 1
            FROM organizadores_tipos_programacao
            WHERE usuario_id = :usuario_id
              AND tipo_programacao_id = :tipo_id
            LIMIT 1
        SQL;

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':tipo_id' => $tipoProgramacaoId,
        ]);

        return $stmt->fetchColumn()
            !== false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarEventos(
        int $programacaoId
    ): array {
        $sql = <<<'SQL'
            SELECT
                id,
                tipo_evento,

                titulo_anterior,
                titulo_novo,

                descricao_anterior,
                descricao_nova,

                inicio_anterior,
                inicio_novo,

                fim_anterior,
                fim_novo,

                local_anterior,
                local_novo,

                status_anterior,
                status_novo,

                criada_em

            FROM eventos_programacao

            WHERE programacao_id = :programacao_id

            ORDER BY
                criada_em DESC,
                id DESC
        SQL;

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute([
            ':programacao_id' =>
                $programacaoId,
        ]);

        return $stmt->fetchAll();
    }
}
