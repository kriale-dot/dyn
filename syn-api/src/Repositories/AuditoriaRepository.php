<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Persistência e consulta da auditoria.
 *
 * Por segurança, esta classe nunca recebe senha, JWT nem corpo
 * completo de requisição para armazenamento.
 */
final class AuditoriaRepository
{
    public function __construct(
        private PDO $pdo
    ) {
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

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $usuarioId,
        ]);

        $item = $stmt->fetch();

        return $item === false
            ? null
            : $item;
    }

    /**
     * @param array<string, mixed> $dados
     */
    public function registrar(
        array $dados
    ): void {
        $sql = <<<'SQL'
            INSERT INTO auditoria_operacoes (
                request_id,

                usuario_id,
                usuario_nome_historico,
                papel_codigo_historico,

                metodo,
                caminho,

                recurso,
                entidade_id,

                http_status,
                sucesso,

                mensagem_resultado,

                ip,
                user_agent
            )
            VALUES (
                :request_id,

                :usuario_id,
                :usuario_nome,
                :papel_codigo,

                :metodo,
                :caminho,

                :recurso,
                :entidade_id,

                :http_status,
                :sucesso,

                :mensagem_resultado,

                :ip,
                :user_agent
            )
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':request_id' =>
                $dados['request_id'],

            ':usuario_id' =>
                $dados['usuario_id'],
            ':usuario_nome' =>
                $dados['usuario_nome_historico'],
            ':papel_codigo' =>
                $dados['papel_codigo_historico'],

            ':metodo' =>
                $dados['metodo'],
            ':caminho' =>
                $dados['caminho'],

            ':recurso' =>
                $dados['recurso'],
            ':entidade_id' =>
                $dados['entidade_id'],

            ':http_status' =>
                $dados['http_status'],
            ':sucesso' =>
                $dados['sucesso'],

            ':mensagem_resultado' =>
                $dados['mensagem_resultado'],

            ':ip' =>
                $dados['ip'],
            ':user_agent' =>
                $dados['user_agent'],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listar(
        int $limite,
        int $offset,
        ?int $usuarioId,
        ?string $metodo,
        ?string $recurso,
        ?bool $somenteErros
    ): array {
        $where = [];
        $params = [];

        if ($usuarioId !== null) {
            $where[] =
                'usuario_id = :usuario_id';

            $params[':usuario_id'] =
                $usuarioId;
        }

        if ($metodo !== null) {
            $where[] =
                'metodo = :metodo';

            $params[':metodo'] =
                $metodo;
        }

        if ($recurso !== null) {
            $where[] =
                'recurso = :recurso';

            $params[':recurso'] =
                $recurso;
        }

        if ($somenteErros === true) {
            $where[] =
                'sucesso = 0';
        }

        $clausulaWhere =
            $where === []
                ? ''
                : 'WHERE '
                    . implode(
                        ' AND ',
                        $where
                    );

        $sql = "
            SELECT
                id,
                request_id,

                usuario_id,
                usuario_nome_historico,
                papel_codigo_historico,

                metodo,
                caminho,

                recurso,
                entidade_id,

                http_status,
                sucesso,

                mensagem_resultado,

                ip,
                user_agent,

                criado_em

            FROM auditoria_operacoes

            {$clausulaWhere}

            ORDER BY
                criado_em DESC,
                id DESC

            LIMIT {$limite}
            OFFSET {$offset}
        ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarPorId(
        int $id
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                id,
                request_id,

                usuario_id,
                usuario_nome_historico,
                papel_codigo_historico,

                metodo,
                caminho,

                recurso,
                entidade_id,

                http_status,
                sucesso,

                mensagem_resultado,

                ip,
                user_agent,

                criado_em

            FROM auditoria_operacoes

            WHERE id = :id
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
