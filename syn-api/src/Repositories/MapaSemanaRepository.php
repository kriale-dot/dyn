<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Consulta a programação semanal e acrescenta, quando existir,
 * a participação do usuário autenticado.
 */
final class MapaSemanaRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarSemana(
        int $usuarioId,
        string $inicio,
        string $fimExclusivo
    ): array {
        $sql = <<<'SQL'
            SELECT
                pr.id AS programacao_id,
                pr.titulo,
                pr.descricao,
                pr.inicio_em,
                pr.fim_em,
                pr.status AS programacao_status,
                pr.permite_resposta,
                pr.tipo_programacao_nome_historico,
                pr.local_nome_historico,
                pr.organizador_nome_historico,

                p.id AS participacao_id,
                p.status AS participacao_status,
                p.funcao_nome_historico,
                p.observacao AS participacao_observacao

            FROM programacoes pr

            LEFT JOIN participacoes p
                ON p.programacao_id = pr.id
               AND p.usuario_id = :usuario_id
               AND p.status IN ('ESCALADO', 'CONFIRMADO')

            WHERE pr.status IN ('AGENDADA', 'REALIZADA')
              AND pr.inicio_em >= :inicio
              AND pr.inicio_em < :fim

            ORDER BY pr.inicio_em, pr.id, p.id
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':inicio' => $inicio,
            ':fim' => $fimExclusivo,
        ]);

        return $stmt->fetchAll();
    }
}
