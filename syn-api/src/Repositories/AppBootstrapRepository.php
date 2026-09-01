<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository da inicialização do frontend.
 *
 * Esta classe reúne os dados mínimos que o React precisa
 * logo após autenticar o usuário:
 *
 * - identidade do usuário;
 * - papel;
 * - igreja;
 * - escopos do Organizador;
 * - permissões especiais.
 */
final class AppBootstrapRepository
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
                u.email,
                u.telefone,
                u.foto,
                u.status,
                u.data_nascimento,
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
     * @return array<string, mixed>|null
     */
    public function buscarIgreja(): ?array
    {
        $sql = <<<'SQL'
            SELECT
                id,
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
            WHERE id = 1
            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();

        $igreja = $stmt->fetch();

        return $igreja === false
            ? null
            : $igreja;
    }

    /**
     * Tipos de programação que um Organizador pode administrar.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarTiposDoOrganizador(
        int $usuarioId
    ): array {
        $sql = <<<'SQL'
            SELECT
                tp.id,
                tp.nome,
                tp.descricao,
                tp.ativo
            FROM organizadores_tipos_programacao otp
            INNER JOIN tipos_programacao tp
                ON tp.id = otp.tipo_programacao_id
            WHERE otp.usuario_id = :usuario_id
            ORDER BY
                tp.nome ASC,
                tp.id ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuarioId,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Permissões especiais explicitamente concedidas ao usuário.
     *
     * O Administrador não precisa ter linhas nessa tabela para possuir
     * acesso administrativo total; o Service trata esse bypass.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarPermissoesEspeciais(
        int $usuarioId
    ): array {
        $sql = <<<'SQL'
            SELECT
                pe.id,
                pe.codigo,
                pe.nome,
                pe.descricao
            FROM usuarios_permissoes_especiais upe
            INNER JOIN permissoes_especiais pe
                ON pe.id = upe.permissao_id
            WHERE upe.usuario_id = :usuario_id
              AND pe.ativo = 1
            ORDER BY
                pe.nome ASC,
                pe.id ASC
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuarioId,
        ]);

        return $stmt->fetchAll();
    }
}
