<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository EXCLUSIVO da área pública.
 *
 * Regra de segurança:
 * esta classe seleciona apenas os campos que podem sair pela API
 * anônima. Não reutilizamos consultas internas e depois "escondemos"
 * dados no Controller.
 *
 * Dessa forma, informações como participantes, funções, observações
 * internas e organizadores nunca entram na projeção pública.
 */
final class PublicoRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Dados institucionais que podem ser mostrados publicamente.
     *
     * @return array<string, mixed>|null
     */
    public function buscarIgreja(): ?array
    {
        $sql = <<<'SQL'
            SELECT
                nome,
                logotipo,
                cep,
                logradouro,
                numero,
                complemento,
                bairro,
                cidade,
                estado,
                telefone,
                email,
                site
            FROM igreja
            WHERE singleton = 1
            LIMIT 1
        SQL;

        $stmt = $this->pdo->query($sql);
        $item = $stmt->fetch();

        return $item === false
            ? null
            : $item;
    }

    /**
     * Busca as programações publicáveis dentro de um intervalo.
     *
     * RASCUNHO nunca é público.
     *
     * CANCELADA continua aparecendo porque é importante que uma
     * pessoa que viu a divulgação saiba que o evento foi cancelado.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarProgramacoes(
        string $inicio,
        string $fimExclusivo
    ): array {
        $sql = <<<'SQL'
            SELECT
                id,
                titulo,
                descricao_publica,
                inicio_em,
                fim_em,
                status,
                tipo_programacao_nome_historico,
                local_nome_historico

            FROM programacoes

            WHERE visibilidade = 'PUBLICA'
              AND status IN (
                    'AGENDADA',
                    'REALIZADA',
                    'CANCELADA'
              )
              AND inicio_em >= :inicio
              AND inicio_em < :fim

            ORDER BY inicio_em, id
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':inicio' => $inicio,
            ':fim' => $fimExclusivo,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Retorna uma programação somente se ela realmente puder ser
     * consultada anonimamente.
     *
     * @return array<string, mixed>|null
     */
    public function buscarProgramacaoPorId(
        int $id
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                id,
                titulo,
                descricao_publica,
                inicio_em,
                fim_em,
                status,
                tipo_programacao_nome_historico,
                local_nome_historico

            FROM programacoes

            WHERE id = :id
              AND visibilidade = 'PUBLICA'
              AND status IN (
                    'AGENDADA',
                    'REALIZADA',
                    'CANCELADA'
              )

            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id,
        ]);

        $item = $stmt->fetch();

        return $item === false
            ? null
            : $item;
    }
}
