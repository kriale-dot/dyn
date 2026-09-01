<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Throwable;

final class SerieProgramacaoRepository
{
    public function __construct(private PDO $pdo) {}

    public function listarTodas(): array
    {
        $sql = <<<'SQL'
            SELECT
                s.id,
                s.tipo_programacao_id,
                s.local_id,
                s.organizador_id,
                s.titulo,
                s.descricao,
                s.inicio_base,
                s.fim_base,
                s.regra_recorrencia,
                s.data_limite,
                s.ativa,
                s.criado_em,
                s.atualizado_em,
                (SELECT COUNT(*) FROM programacoes p WHERE p.serie_id = s.id) AS total_ocorrencias,
                (SELECT COUNT(*) FROM programacoes p
                    WHERE p.serie_id = s.id
                      AND p.inicio_em > NOW()
                      AND p.status <> 'CANCELADA') AS total_ocorrencias_futuras
            FROM series_programacao s
            ORDER BY s.ativa DESC, s.inicio_base ASC, s.id ASC
        SQL;

        return $this->pdo->query($sql)->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = <<<'SQL'
            SELECT
                s.id,
                s.tipo_programacao_id,
                s.local_id,
                s.organizador_id,
                s.titulo,
                s.descricao,
                s.inicio_base,
                s.fim_base,
                s.regra_recorrencia,
                s.data_limite,
                s.ativa,
                s.criado_em,
                s.atualizado_em,
                (SELECT COUNT(*) FROM programacoes p WHERE p.serie_id = s.id) AS total_ocorrencias,
                (SELECT COUNT(*) FROM programacoes p
                    WHERE p.serie_id = s.id
                      AND p.inicio_em > NOW()
                      AND p.status <> 'CANCELADA') AS total_ocorrencias_futuras
            FROM series_programacao s
            WHERE s.id = :id
            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $serie = $stmt->fetch();
        return $serie === false ? null : $serie;
    }

    public function listarOcorrencias(int $serieId): array
    {
        $sql = <<<'SQL'
            SELECT
                p.id,
                p.serie_id,
                p.titulo,
                p.descricao,
                p.inicio_em,
                p.fim_em,
                p.status,
                p.permite_resposta,
                p.tipo_programacao_id,
                p.local_id,
                p.organizador_id,
                p.tipo_programacao_nome_historico,
                p.local_nome_historico,
                p.organizador_nome_historico,
                p.cancelada_em,
                p.motivo_cancelamento,
                p.realizado_em
            FROM programacoes p
            WHERE p.serie_id = :serie_id
            ORDER BY p.inicio_em ASC, p.id ASC
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':serie_id' => $serieId]);
        return $stmt->fetchAll();
    }

    public function buscarTipoProgramacaoPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, nome, ativo FROM tipos_programacao WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch();
        return $r === false ? null : $r;
    }

    public function buscarLocalPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, nome, ativo FROM locais WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch();
        return $r === false ? null : $r;
    }

    public function buscarOrganizadorPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, nome, status FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch();
        return $r === false ? null : $r;
    }

    public function buscarConflitosDeLocal(int $localId, string $inicioEm, string $fimEm): array
    {
        $sql = <<<'SQL'
            SELECT
                id,
                serie_id,
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
            ORDER BY inicio_em ASC, id ASC
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':local_id' => $localId,
            ':inicio_em' => $inicioEm,
            ':fim_em' => $fimEm,
        ]);
        return $stmt->fetchAll();
    }

    public function criarComOcorrencias(array $serie, array $ocorrencias): int
    {
        $this->pdo->beginTransaction();
        try {
            $stmtSerie = $this->pdo->prepare(<<<'SQL'
                INSERT INTO series_programacao (
                    tipo_programacao_id, local_id, organizador_id,
                    titulo, descricao, inicio_base, fim_base,
                    regra_recorrencia, data_limite, ativa
                ) VALUES (
                    :tipo_programacao_id, :local_id, :organizador_id,
                    :titulo, :descricao, :inicio_base, :fim_base,
                    :regra_recorrencia, :data_limite, 1
                )
            SQL);
            $stmtSerie->execute([
                ':tipo_programacao_id' => $serie['tipo_programacao_id'],
                ':local_id' => $serie['local_id'],
                ':organizador_id' => $serie['organizador_id'],
                ':titulo' => $serie['titulo'],
                ':descricao' => $serie['descricao'],
                ':inicio_base' => $serie['inicio_base'],
                ':fim_base' => $serie['fim_base'],
                ':regra_recorrencia' => $serie['regra_recorrencia'],
                ':data_limite' => $serie['data_limite'],
            ]);
            $serieId = (int) $this->pdo->lastInsertId();

            $stmtOc = $this->pdo->prepare(<<<'SQL'
                INSERT INTO programacoes (
                    serie_id, tipo_programacao_id, local_id, organizador_id,
                    titulo, descricao, inicio_em, fim_em, status, permite_resposta,
                    tipo_programacao_nome_historico,
                    local_nome_historico,
                    organizador_nome_historico
                ) VALUES (
                    :serie_id, :tipo_programacao_id, :local_id, :organizador_id,
                    :titulo, :descricao, :inicio_em, :fim_em, 'AGENDADA', :permite_resposta,
                    :tipo_programacao_nome_historico,
                    :local_nome_historico,
                    :organizador_nome_historico
                )
            SQL);

            foreach ($ocorrencias as $oc) {
                $stmtOc->execute([
                    ':serie_id' => $serieId,
                    ':tipo_programacao_id' => $serie['tipo_programacao_id'],
                    ':local_id' => $serie['local_id'],
                    ':organizador_id' => $serie['organizador_id'],
                    ':titulo' => $serie['titulo'],
                    ':descricao' => $serie['descricao'],
                    ':inicio_em' => $oc['inicio_em'],
                    ':fim_em' => $oc['fim_em'],
                    ':permite_resposta' => $serie['permite_resposta'] ? 1 : 0,
                    ':tipo_programacao_nome_historico' => $serie['tipo_programacao_nome_historico'],
                    ':local_nome_historico' => $serie['local_nome_historico'],
                    ':organizador_nome_historico' => $serie['organizador_nome_historico'],
                ]);
            }

            $this->pdo->commit();
            return $serieId;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    public function desativar(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE series_programacao SET ativa = 0 WHERE id = :id AND ativa = 1');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
