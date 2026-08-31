<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository responsável pelo acesso aos dados dos usuários.
 */
final class UsuarioRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarTodos(): array
    {
        $sql = <<<'SQL'
            SELECT
                u.id,
                u.nome,
                u.data_nascimento,
                u.telefone,
                u.email,
                u.foto,
                u.status,
                u.ultimo_login_em,
                u.desativado_em,
                u.criado_em,
                u.atualizado_em,
                p.id AS papel_id,
                p.codigo AS papel_codigo,
                p.nome AS papel_nome
            FROM usuarios u
            INNER JOIN papeis p
                ON p.id = u.papel_id
            ORDER BY u.nome ASC, u.id ASC
        SQL;

        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarPorId(int $id): ?array
    {
        $sql = <<<'SQL'
            SELECT
                u.id,
                u.nome,
                u.data_nascimento,
                u.telefone,
                u.email,
                u.foto,
                u.status,
                u.ultimo_login_em,
                u.desativado_em,
                u.criado_em,
                u.atualizado_em,
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
            ':id' => $id,
        ]);

        $usuario = $stmt->fetch();

        return $usuario === false ? null : $usuario;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarFuncoesPorUsuarioId(int $usuarioId): array
    {
        $sql = <<<'SQL'
            SELECT
                f.id,
                f.nome,
                f.descricao,
                f.ativo,
                uf.atribuido_em,
                d.id AS departamento_id,
                d.nome AS departamento_nome,
                d.ativo AS departamento_ativo
            FROM usuarios_funcoes uf
            INNER JOIN funcoes f
                ON f.id = uf.funcao_id
            LEFT JOIN departamentos d
                ON d.id = f.departamento_id
            WHERE uf.usuario_id = :usuario_id
            ORDER BY f.nome ASC, f.id ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuarioId,
        ]);

        return $stmt->fetchAll();
    }

    public function emailExiste(string $email): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM usuarios WHERE email = :email LIMIT 1'
        );

        $stmt->execute([
            ':email' => $email,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function emailExisteParaOutroUsuario(
        string $email,
        int $usuarioId
    ): bool {
        $sql = <<<'SQL'
            SELECT 1
            FROM usuarios
            WHERE email = :email
              AND id <> :usuario_id
            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':email' => $email,
            ':usuario_id' => $usuarioId,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function papelExiste(int $papelId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM papeis WHERE id = :id LIMIT 1'
        );

        $stmt->execute([
            ':id' => $papelId,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * @param array<string, mixed> $dados
     */
    public function criar(array $dados): int
    {
        $sql = <<<'SQL'
            INSERT INTO usuarios (
                nome,
                data_nascimento,
                telefone,
                email,
                senha_hash,
                foto,
                papel_id,
                status
            )
            VALUES (
                :nome,
                :data_nascimento,
                :telefone,
                :email,
                :senha_hash,
                :foto,
                :papel_id,
                'ATIVO'
            )
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':nome' => $dados['nome'],
            ':data_nascimento' => $dados['data_nascimento'],
            ':telefone' => $dados['telefone'],
            ':email' => $dados['email'],
            ':senha_hash' => $dados['senha_hash'],
            ':foto' => $dados['foto'],
            ':papel_id' => $dados['papel_id'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param array<string, mixed> $dados
     */
    public function atualizar(
        int $id,
        array $dados
    ): bool {
        $sql = <<<'SQL'
            UPDATE usuarios
            SET
                nome = :nome,
                data_nascimento = :data_nascimento,
                telefone = :telefone,
                email = :email,
                foto = :foto,
                papel_id = :papel_id
            WHERE id = :id
        SQL;

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':nome' => $dados['nome'],
            ':data_nascimento' => $dados['data_nascimento'],
            ':telefone' => $dados['telefone'],
            ':email' => $dados['email'],
            ':foto' => $dados['foto'],
            ':papel_id' => $dados['papel_id'],
            ':id' => $id,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarParticipacoesFuturasAtivas(
        int $usuarioId
    ): array {
        $sql = <<<'SQL'
            SELECT
                pa.id AS participacao_id,
                pa.status AS participacao_status,
                pa.funcao_nome_historico,
                pr.id AS programacao_id,
                pr.titulo,
                pr.inicio_em,
                pr.fim_em,
                pr.local_nome_historico
            FROM participacoes pa
            INNER JOIN programacoes pr
                ON pr.id = pa.programacao_id
            WHERE pa.usuario_id = :usuario_id
              AND pr.inicio_em > NOW()
              AND pr.status <> 'CANCELADA'
              AND pa.status IN ('ESCALADO', 'CONFIRMADO')
            ORDER BY pr.inicio_em ASC, pa.id ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuarioId,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Faz exclusão lógica do usuário.
     */
    public function desativar(int $id): bool
    {
        $sql = <<<'SQL'
            UPDATE usuarios
            SET
                status = 'INATIVO',
                desativado_em = NOW()
            WHERE id = :id
              AND status <> 'INATIVO'
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Consulta base da tela "Minha Semana".
     *
     * Mostramos compromissos ativos do usuário:
     * - participação ESCALADO ou CONFIRMADO;
     * - programação AGENDADA ou REALIZADA;
     * - dentro do intervalo [inicioSemana, fimSemana).
     *
     * INDISPONIVEL, RECUSADO e CANCELADO permanecem no histórico,
     * mas não são tratados como compromissos pessoais ativos.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarCompromissosDaSemana(
        int $usuarioId,
        string $inicioSemana,
        string $fimSemana
    ): array {
        $sql = <<<'SQL'
            SELECT
                pa.id AS participacao_id,
                pa.status AS participacao_status,

                pa.usuario_nome_historico,
                pa.funcao_nome_historico,
                pa.departamento_nome_historico,

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

            FROM participacoes pa

            INNER JOIN programacoes pr
                ON pr.id = pa.programacao_id

            WHERE pa.usuario_id = :usuario_id
              AND pa.status IN ('ESCALADO', 'CONFIRMADO')
              AND pr.status IN ('AGENDADA', 'REALIZADA')
              AND pr.inicio_em >= :inicio_semana
              AND pr.inicio_em < :fim_semana

            ORDER BY
                pr.inicio_em ASC,
                pr.id ASC,
                pa.funcao_nome_historico ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':inicio_semana' => $inicioSemana,
            ':fim_semana' => $fimSemana,
        ]);

        return $stmt->fetchAll();
    }
}
