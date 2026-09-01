<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository do Dashboard inicial.
 *
 * Esta camada concentra apenas consultas SQL.
 * Ela não conhece HTTP e não decide regras de apresentação.
 */
final class DashboardRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarUsuarioPorId(
        int $usuarioId
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                u.id,
                u.nome,
                u.foto,
                u.status,
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
     * Compromissos pessoais da semana.
     *
     * Só entram participações que ainda representam
     * compromisso real do usuário:
     * ESCALADO ou CONFIRMADO.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarCompromissosDaSemana(
        int $usuarioId,
        string $inicio,
        string $fimExclusivo
    ): array {
        $sql = <<<'SQL'
            SELECT
                p.id AS participacao_id,
                p.status AS participacao_status,
                p.funcao_nome_historico,
                p.observacao AS participacao_observacao,

                pr.id AS programacao_id,
                pr.titulo,
                pr.descricao,
                pr.inicio_em,
                pr.fim_em,
                pr.status AS programacao_status,
                pr.permite_resposta,
                pr.tipo_programacao_nome_historico,
                pr.local_nome_historico,
                pr.organizador_nome_historico

            FROM participacoes p

            INNER JOIN programacoes pr
                ON pr.id = p.programacao_id

            WHERE p.usuario_id = :usuario_id

              AND p.status IN (
                  'ESCALADO',
                  'CONFIRMADO'
              )

              AND pr.status IN (
                  'AGENDADA',
                  'REALIZADA'
              )

              AND pr.inicio_em >= :inicio
              AND pr.inicio_em < :fim

            ORDER BY
                pr.inicio_em ASC,
                pr.id ASC,
                p.id ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':inicio' => $inicio,
            ':fim' => $fimExclusivo,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Programação geral da semana.
     *
     * A tela inicial pode mostrar o que acontece na igreja,
     * independentemente de o usuário estar escalado.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarProgramacoesDaSemana(
        string $inicio,
        string $fimExclusivo
    ): array {
        $sql = <<<'SQL'
            SELECT
                id,
                titulo,
                descricao,
                inicio_em,
                fim_em,
                status,
                permite_resposta,
                tipo_programacao_nome_historico,
                local_nome_historico,
                organizador_nome_historico
            FROM programacoes
            WHERE status IN (
                'AGENDADA',
                'REALIZADA'
            )
              AND inicio_em >= :inicio
              AND inicio_em < :fim
            ORDER BY
                inicio_em ASC,
                id ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':inicio' => $inicio,
            ':fim' => $fimExclusivo,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Busca aniversariantes ativos pelos pares MM-DD.
     *
     * Não retorna idade nem ano de nascimento ao Dashboard.
     *
     * @param array<int, string> $diasMes
     * @return array<int, array<string, mixed>>
     */
    public function listarAniversariantesPorDiasMes(
        array $diasMes
    ): array {
        if ($diasMes === []) {
            return [];
        }

        $marcadores = implode(
            ', ',
            array_fill(
                0,
                count($diasMes),
                '?'
            )
        );

        $sql = "
            SELECT
                id,
                nome,
                foto,
                data_nascimento
            FROM usuarios
            WHERE status = 'ATIVO'
              AND data_nascimento IS NOT NULL
              AND DATE_FORMAT(
                    data_nascimento,
                    '%m-%d'
                  ) IN ({$marcadores})
            ORDER BY
                DATE_FORMAT(
                    data_nascimento,
                    '%m-%d'
                ) ASC,
                nome ASC,
                id ASC
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute(
            array_values($diasMes)
        );

        return $stmt->fetchAll();
    }
}
