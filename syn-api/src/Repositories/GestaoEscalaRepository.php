<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository da tela administrativa de escala.
 *
 * Responsabilidades:
 * - localizar a programação;
 * - identificar papel do usuário autenticado;
 * - verificar escopo do Organizador;
 * - listar funções permitidas;
 * - listar escala existente;
 * - listar candidatos elegíveis.
 */
final class GestaoEscalaRepository
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
        $stmt->execute([':id' => $programacaoId]);
        $programacao = $stmt->fetch();

        return $programacao === false ? null : $programacao;
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
                p.codigo AS papel_codigo,
                p.nome AS papel_nome
            FROM usuarios u
            INNER JOIN papeis p
                ON p.id = u.papel_id
            WHERE u.id = :id
            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $usuarioId]);
        $usuario = $stmt->fetch();

        return $usuario === false ? null : $usuario;
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

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':tipo_id' => $tipoProgramacaoId,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarFuncoesPermitidas(
        int $tipoProgramacaoId
    ): array {
        $sql = <<<'SQL'
            SELECT
                f.id,
                f.nome,
                f.descricao,
                f.departamento_id,
                d.nome AS departamento_nome
            FROM funcoes_tipos_programacao ftp
            INNER JOIN funcoes f
                ON f.id = ftp.funcao_id
            LEFT JOIN departamentos d
                ON d.id = f.departamento_id
            WHERE ftp.tipo_programacao_id = :tipo_id
              AND f.ativo = 1
              AND (d.id IS NULL OR d.ativo = 1)
            ORDER BY
                d.nome ASC,
                f.nome ASC,
                f.id ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tipo_id' => $tipoProgramacaoId]);

        return $stmt->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarEscala(
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
        $stmt->execute([':programacao_id' => $programacaoId]);

        return $stmt->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarCandidatos(
        int $tipoProgramacaoId
    ): array {
        $sql = <<<'SQL'
            SELECT
                u.id AS usuario_id,
                u.nome AS usuario_nome,
                u.email AS usuario_email,
                u.foto AS usuario_foto,
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
            INNER JOIN usuarios_funcoes uf
                ON uf.funcao_id = f.id
            INNER JOIN usuarios u
                ON u.id = uf.usuario_id
               AND u.status = 'ATIVO'
            WHERE ftp.tipo_programacao_id = :tipo_id
              AND (d.id IS NULL OR d.ativo = 1)
            ORDER BY
                d.nome ASC,
                f.nome ASC,
                u.nome ASC,
                u.id ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tipo_id' => $tipoProgramacaoId]);

        return $stmt->fetchAll();
    }
}
