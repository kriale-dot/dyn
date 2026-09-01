<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository do perfil do próprio usuário e aniversariantes.
 *
 * Esta camada conhece SQL e PDO, mas não conhece HTTP.
 */
final class PerfilRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buscarUsuarioPorId(int $id): ?array
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
    public function listarFuncoesDoUsuario(
        int $usuarioId
    ): array {
        $sql = <<<'SQL'
            SELECT
                f.id,
                f.nome,
                f.ativo,
                d.id AS departamento_id,
                d.nome AS departamento_nome
            FROM usuarios_funcoes uf
            INNER JOIN funcoes f
                ON f.id = uf.funcao_id
            LEFT JOIN departamentos d
                ON d.id = f.departamento_id
            WHERE uf.usuario_id = :usuario_id
            ORDER BY
                f.nome ASC,
                f.id ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuarioId,
        ]);

        return $stmt->fetchAll();
    }

    public function emailExisteParaOutroUsuario(
        string $email,
        int $usuarioId
    ): bool {
        $sql = <<<'SQL'
            SELECT 1
            FROM usuarios
            WHERE LOWER(email) = LOWER(:email)
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

    /**
     * Atualiza somente campos que o próprio usuário pode manter.
     *
     * Papel e status NÃO são alterados por esta rota.
     *
     * @param array<string, mixed> $dados
     */
    public function atualizarPerfil(
        int $usuarioId,
        array $dados
    ): bool {
        $sql = <<<'SQL'
            UPDATE usuarios
            SET
                nome = :nome,
                data_nascimento = :data_nascimento,
                telefone = :telefone,
                email = :email,
                foto = :foto
            WHERE id = :id
        SQL;

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':nome' => $dados['nome'],
            ':data_nascimento' => $dados['data_nascimento'],
            ':telefone' => $dados['telefone'],
            ':email' => $dados['email'],
            ':foto' => $dados['foto'],
            ':id' => $usuarioId,
        ]);
    }

    /**
     * Busca aniversariantes ATIVOS pelos respectivos mês/dia.
     *
     * Recebe valores MM-DD para funcionar inclusive em semanas
     * que atravessam a virada do ano.
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
            array_fill(0, count($diasMes), '?')
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
              AND DATE_FORMAT(data_nascimento, '%m-%d')
                  IN ({$marcadores})
            ORDER BY
                DATE_FORMAT(data_nascimento, '%m-%d') ASC,
                nome ASC,
                id ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($diasMes));

        return $stmt->fetchAll();
    }
}
