<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository da visão consolidada das escalas de uma semana.
 *
 * Esta consulta é somente de leitura. As alterações da escala
 * continuam usando os endpoints de participações já existentes.
 */
final class EscalasSemanaRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarUsuarioAutenticado(
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

        $item =
            $stmt->fetch();

        return $item === false
            ? null
            : $item;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarProgramacoesSemana(
        string $inicio,
        string $fimExclusivo,
        int $usuarioId,
        string $papel
    ): array {
        $restricaoEscopo = '';

        if ($papel === 'ORGANIZADOR') {
            $restricaoEscopo = <<<'SQL'

                AND EXISTS (
                    SELECT 1
                    FROM organizadores_tipos_programacao otp
                    WHERE otp.usuario_id = :usuario_id
                      AND otp.tipo_programacao_id =
                          p.tipo_programacao_id
                )
            SQL;
        }

        $sql = "
            SELECT
                p.id,
                p.titulo,
                p.descricao,
                p.inicio_em,
                p.fim_em,
                p.status,
                p.permite_resposta,

                p.tipo_programacao_id,
                p.tipo_programacao_nome_historico,

                p.local_id,
                p.local_nome_historico,

                p.organizador_id,
                p.organizador_nome_historico

            FROM programacoes p

            WHERE p.inicio_em >= :inicio
              AND p.inicio_em < :fim
              {$restricaoEscopo}

            ORDER BY
                p.inicio_em ASC,
                p.id ASC
        ";

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $params = [
            ':inicio' => $inicio,
            ':fim' => $fimExclusivo,
        ];

        if ($papel === 'ORGANIZADOR') {
            $params[':usuario_id'] =
                $usuarioId;
        }

        $stmt->execute(
            $params
        );

        return $stmt->fetchAll();
    }

    /**
     * @param array<int, int> $programacaoIds
     * @return array<int, array<string, mixed>>
     */
    public function listarParticipacoes(
        array $programacaoIds
    ): array {
        if ($programacaoIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];

        foreach (
            array_values($programacaoIds)
            as $indice => $id
        ) {
            $chave =
                ':programacao_'
                . $indice;

            $placeholders[] =
                $chave;

            $params[$chave] =
                $id;
        }

        $lista =
            implode(
                ', ',
                $placeholders
            );

        $sql = "
            SELECT
                p.id,
                p.programacao_id,
                p.usuario_id,
                p.funcao_id,
                p.status,

                p.usuario_nome_historico,
                p.funcao_nome_historico,
                p.departamento_nome_historico,

                p.observacao

            FROM participacoes p

            WHERE p.programacao_id IN ({$lista})

            ORDER BY
                p.programacao_id ASC,
                p.departamento_nome_historico ASC,
                p.funcao_nome_historico ASC,
                p.usuario_nome_historico ASC,
                p.id ASC
        ";

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute(
            $params
        );

        return $stmt->fetchAll();
    }

    /**
     * Lista, de uma só vez, as funções atualmente habilitadas
     * para os tipos de programação presentes na semana.
     *
     * Atenção conceitual:
     * "habilitada" significa que a função PODE participar daquele
     * tipo de programação. Isso não significa que ela seja
     * obrigatória em toda ocorrência.
     *
     * @param array<int, int> $tipoIds
     * @return array<int, array<string, mixed>>
     */
    public function listarFuncoesHabilitadasPorTipos(
        array $tipoIds
    ): array {
        if ($tipoIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];

        foreach (
            array_values(
                array_unique(
                    $tipoIds
                )
            )
            as $indice => $id
        ) {
            $chave =
                ':tipo_'
                . $indice;

            $placeholders[] =
                $chave;

            $params[$chave] =
                $id;
        }

        $lista =
            implode(
                ', ',
                $placeholders
            );

        $sql = "
            SELECT
                ftp.tipo_programacao_id,

                f.id AS funcao_id,
                f.nome AS funcao_nome,

                d.id AS departamento_id,
                d.nome AS departamento_nome

            FROM funcoes_tipos_programacao ftp

            INNER JOIN funcoes f
                ON f.id = ftp.funcao_id
               AND f.ativo = 1

            LEFT JOIN departamentos d
                ON d.id = f.departamento_id

            WHERE ftp.tipo_programacao_id IN ({$lista})
              AND (
                    d.id IS NULL
                    OR d.ativo = 1
              )

            ORDER BY
                ftp.tipo_programacao_id ASC,
                d.nome ASC,
                f.nome ASC,
                f.id ASC
        ";

        $stmt =
            $this->pdo->prepare(
                $sql
            );

        $stmt->execute(
            $params
        );

        return $stmt->fetchAll();
    }

}
