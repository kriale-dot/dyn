<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository da tela de detalhe de uma programação.
 *
 * A consulta usa os nomes históricos já gravados em programacoes
 * e participacoes. Assim, alterações futuras em usuário, função,
 * departamento, local ou tipo não reescrevem o passado.
 */
final class DetalheProgramacaoRepository
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
                serie_id,
                titulo,
                descricao,
                inicio_em,
                fim_em,
                status,
                permite_resposta,
                tipo_programacao_id,
                tipo_programacao_nome_historico,
                local_id,
                local_nome_historico,
                organizador_id,
                organizador_nome_historico
            FROM programacoes
            WHERE id = :id
            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $programacaoId,
        ]);

        $programacao = $stmt->fetch();

        return $programacao === false
            ? null
            : $programacao;
    }

    /**
     * Escala completa da programação.
     *
     * Mantemos inclusive participações recusadas, indisponíveis
     * ou canceladas para que o detalhe represente corretamente
     * o estado da escala e preserve o histórico operacional.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarParticipacoes(
        int $programacaoId
    ): array {
        $sql = <<<'SQL'
            SELECT
                p.id,
                p.usuario_id,
                p.funcao_id,
                p.status,
                p.usuario_nome_historico,
                p.funcao_nome_historico,
                p.departamento_nome_historico,
                p.observacao,

                u.foto AS usuario_foto_atual,
                u.status AS usuario_status_atual

            FROM participacoes p

            LEFT JOIN usuarios u
                ON u.id = p.usuario_id

            WHERE p.programacao_id = :programacao_id

            ORDER BY
                p.departamento_nome_historico ASC,
                p.funcao_nome_historico ASC,
                p.usuario_nome_historico ASC,
                p.id ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':programacao_id' => $programacaoId,
        ]);

        return $stmt->fetchAll();
    }
}
