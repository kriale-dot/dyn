<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository de participações / escalas.
 *
 * A tabela participacoes representa o fato histórico:
 *
 * pessoa + programação + função efetivamente exercida.
 *
 * Por isso esta camada nunca faz DELETE físico em participacoes.
 */
final class ParticipacaoRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarProgramacaoPorId(
        int $programacaoId
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                id,
                tipo_programacao_id,
                titulo,
                inicio_em,
                fim_em,
                status,
                permite_resposta,
                tipo_programacao_nome_historico,
                local_nome_historico,
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
     * @return array<string, mixed>|null
     */
    public function buscarUsuarioPorId(
        int $usuarioId
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                id,
                nome,
                email,
                status
            FROM usuarios
            WHERE id = :id
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
     * @return array<string, mixed>|null
     */
    public function buscarFuncaoPorId(
        int $funcaoId
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                f.id,
                f.nome,
                f.descricao,
                f.ativo,

                d.id AS departamento_id,
                d.nome AS departamento_nome,
                d.ativo AS departamento_ativo

            FROM funcoes f

            LEFT JOIN departamentos d
                ON d.id = f.departamento_id

            WHERE f.id = :id

            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $funcaoId,
        ]);

        $funcao = $stmt->fetch();

        return $funcao === false
            ? null
            : $funcao;
    }

    /**
     * Verifica a habilitação ATUAL do usuário.
     */
    public function usuarioPossuiFuncao(
        int $usuarioId,
        int $funcaoId
    ): bool {
        $sql = <<<'SQL'
            SELECT 1
            FROM usuarios_funcoes
            WHERE usuario_id = :usuario_id
              AND funcao_id = :funcao_id
            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':funcao_id' => $funcaoId,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Verifica se a função é autorizada para o tipo da programação.
     */
    public function funcaoAutorizadaParaTipo(
        int $funcaoId,
        int $tipoProgramacaoId
    ): bool {
        $sql = <<<'SQL'
            SELECT 1
            FROM funcoes_tipos_programacao
            WHERE funcao_id = :funcao_id
              AND tipo_programacao_id = :tipo_programacao_id
            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':funcao_id' => $funcaoId,
            ':tipo_programacao_id' =>
                $tipoProgramacaoId,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Lista candidatos normais da programação concreta.
     *
     * Cada linha representa um par:
     * usuário + função compatível.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarCandidatosElegiveis(
        int $programacaoId
    ): array {
        $sql = <<<'SQL'
            SELECT DISTINCT
                u.id AS usuario_id,
                u.nome AS usuario_nome,
                u.email AS usuario_email,

                f.id AS funcao_id,
                f.nome AS funcao_nome,

                d.id AS departamento_id,
                d.nome AS departamento_nome

            FROM programacoes p

            INNER JOIN funcoes_tipos_programacao ftp
                ON ftp.tipo_programacao_id =
                   p.tipo_programacao_id

            INNER JOIN funcoes f
                ON f.id = ftp.funcao_id

            INNER JOIN usuarios_funcoes uf
                ON uf.funcao_id = f.id

            INNER JOIN usuarios u
                ON u.id = uf.usuario_id

            LEFT JOIN departamentos d
                ON d.id = f.departamento_id

            WHERE p.id = :programacao_id
              AND u.status = 'ATIVO'
              AND f.ativo = 1

            ORDER BY
                f.nome ASC,
                u.nome ASC,
                u.id ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':programacao_id' => $programacaoId,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Busca participação pela chave natural definida no schema.
     *
     * @return array<string, mixed>|null
     */
    public function buscarPorProgramacaoUsuarioFuncao(
        int $programacaoId,
        int $usuarioId,
        int $funcaoId
    ): ?array {
        $sql = <<<'SQL'
            SELECT id
            FROM participacoes
            WHERE programacao_id = :programacao_id
              AND usuario_id = :usuario_id
              AND funcao_id = :funcao_id
            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':programacao_id' => $programacaoId,
            ':usuario_id' => $usuarioId,
            ':funcao_id' => $funcaoId,
        ]);

        $id = $stmt->fetchColumn();

        if ($id === false) {
            return null;
        }

        return $this->buscarPorId((int) $id);
    }

    /**
     * Cria uma escala com snapshots históricos.
     *
     * @param array<string, mixed> $dados
     */
    public function criar(array $dados): int
    {
        $sql = <<<'SQL'
            INSERT INTO participacoes (
                programacao_id,
                usuario_id,
                funcao_id,
                status,

                usuario_nome_historico,
                funcao_nome_historico,
                departamento_nome_historico,

                observacao
            )
            VALUES (
                :programacao_id,
                :usuario_id,
                :funcao_id,
                'ESCALADO',

                :usuario_nome_historico,
                :funcao_nome_historico,
                :departamento_nome_historico,

                :observacao
            )
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':programacao_id' =>
                $dados['programacao_id'],
            ':usuario_id' =>
                $dados['usuario_id'],
            ':funcao_id' =>
                $dados['funcao_id'],

            ':usuario_nome_historico' =>
                $dados['usuario_nome_historico'],
            ':funcao_nome_historico' =>
                $dados['funcao_nome_historico'],
            ':departamento_nome_historico' =>
                $dados['departamento_nome_historico'],

            ':observacao' =>
                $dados['observacao'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarPorProgramacao(
        int $programacaoId
    ): array {
        $sql = <<<'SQL'
            SELECT
                pa.id,
                pa.programacao_id,
                pa.usuario_id,
                pa.funcao_id,
                pa.status,

                pa.usuario_nome_historico,
                pa.funcao_nome_historico,
                pa.departamento_nome_historico,

                pa.observacao,
                pa.respondido_em,
                pa.cancelado_em,
                pa.criado_em,
                pa.atualizado_em,

                p.titulo AS programacao_titulo,
                p.inicio_em,
                p.fim_em,
                p.status AS programacao_status,
                p.permite_resposta,
                p.local_nome_historico,

                u.nome AS usuario_nome_atual,
                u.status AS usuario_status_atual,

                f.nome AS funcao_nome_atual,
                f.ativo AS funcao_ativa_atual

            FROM participacoes pa

            INNER JOIN programacoes p
                ON p.id = pa.programacao_id

            INNER JOIN usuarios u
                ON u.id = pa.usuario_id

            INNER JOIN funcoes f
                ON f.id = pa.funcao_id

            WHERE pa.programacao_id = :programacao_id

            ORDER BY
                pa.funcao_nome_historico ASC,
                pa.usuario_nome_historico ASC,
                pa.id ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':programacao_id' => $programacaoId,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarPorId(
        int $participacaoId
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                pa.id,
                pa.programacao_id,
                pa.usuario_id,
                pa.funcao_id,
                pa.status,

                pa.usuario_nome_historico,
                pa.funcao_nome_historico,
                pa.departamento_nome_historico,

                pa.observacao,
                pa.respondido_em,
                pa.cancelado_em,
                pa.criado_em,
                pa.atualizado_em,

                p.titulo AS programacao_titulo,
                p.inicio_em,
                p.fim_em,
                p.status AS programacao_status,
                p.permite_resposta,
                p.local_nome_historico,

                u.nome AS usuario_nome_atual,
                u.status AS usuario_status_atual,

                f.nome AS funcao_nome_atual,
                f.ativo AS funcao_ativa_atual

            FROM participacoes pa

            INNER JOIN programacoes p
                ON p.id = pa.programacao_id

            INNER JOIN usuarios u
                ON u.id = pa.usuario_id

            INNER JOIN funcoes f
                ON f.id = pa.funcao_id

            WHERE pa.id = :id

            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $participacaoId,
        ]);

        $participacao = $stmt->fetch();

        return $participacao === false
            ? null
            : $participacao;
    }

    /**
     * Registra a resposta do participante.
     *
     * status deve ser:
     * CONFIRMADO, INDISPONIVEL ou RECUSADO.
     */
    public function responder(
        int $participacaoId,
        string $status,
        ?string $observacao
    ): bool {
        $sql = <<<'SQL'
            UPDATE participacoes
            SET
                status = :status,
                respondido_em = NOW(),
                observacao =
                    COALESCE(:observacao, observacao),
                cancelado_em = NULL
            WHERE id = :id
              AND status <> 'CANCELADO'
        SQL;

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':status' => $status,
            ':observacao' => $observacao,
            ':id' => $participacaoId,
        ]);
    }

    /**
     * Cancela a escala sem excluir o registro.
     */
    public function cancelar(
        int $participacaoId,
        ?string $observacao
    ): bool {
        $sql = <<<'SQL'
            UPDATE participacoes
            SET
                status = 'CANCELADO',
                cancelado_em = NOW(),
                observacao =
                    COALESCE(:observacao, observacao)
            WHERE id = :id
              AND status <> 'CANCELADO'
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':observacao' => $observacao,
            ':id' => $participacaoId,
        ]);

        return $stmt->rowCount() > 0;
    }
}
