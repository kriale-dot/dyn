<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository exclusivo da autenticação.
 *
 * Importante:
 * senha_hash é consultada apenas internamente.
 * Ela nunca deve aparecer em respostas JSON.
 */
final class AuthRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Busca um usuário pelo e-mail para validar login.
     *
     * @return array<string, mixed>|null
     */
    public function buscarPorEmail(
        string $email
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                u.id,
                u.nome,
                u.email,
                u.foto,
                u.status,
                u.senha_hash,
                u.ultimo_login_em,
                u.sessao_versao,

                p.id AS papel_id,
                p.codigo AS papel_codigo,
                p.nome AS papel_nome

            FROM usuarios u

            INNER JOIN papeis p
                ON p.id = u.papel_id

            WHERE u.email = :email

            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':email' => $email,
        ]);

        $usuario = $stmt->fetch();

        return $usuario === false
            ? null
            : $usuario;
    }

    /**
     * Busca o usuário atual a cada requisição autenticada.
     *
     * Essa decisão é importante:
     * mesmo que um token ainda não tenha expirado, um usuário
     * desativado deixa de acessar a API imediatamente.
     *
     * @return array<string, mixed>|null
     */
    public function buscarPorId(
        int $usuarioId
    ): ?array {
        $sql = <<<'SQL'
            SELECT
                u.id,
                u.nome,
                u.email,
                u.foto,
                u.status,
                u.ultimo_login_em,
                u.sessao_versao,

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
     * Invalida todos os JWT já emitidos para o usuário.
     *
     * Nenhum token precisa ser armazenado no banco. Basta aumentar a
     * versão atual da sessão.
     */
    public function incrementarVersaoSessao(
        int $usuarioId
    ): bool {
        $this->pdo
            ->beginTransaction();

        try {
            $stmt =
                $this->pdo->prepare(
                    'UPDATE usuarios
                     SET sessao_versao =
                         sessao_versao + 1
                     WHERE id = :id'
                );

            $stmt->execute([
                ':id' =>
                    $usuarioId,
            ]);

            if (
                $stmt->rowCount()
                !== 1
            ) {
                $this->pdo
                    ->rollBack();

                return false;
            }

            $this->registrarEventoSeguranca(
                $usuarioId,
                'SESSOES_ENCERRADAS',
                'Todas as sessões foram encerradas',
                'Os tokens anteriores da conta foram invalidados.'
            );

            $this->pdo
                ->commit();

            return true;
        } catch (\Throwable $e) {
            if (
                $this->pdo
                    ->inTransaction()
            ) {
                $this->pdo
                    ->rollBack();
            }

            throw $e;
        }
    }


    /**
     * Registra o momento do último login bem-sucedido.
     */
    public function registrarUltimoLogin(
        int $usuarioId
    ): void {
        $this->pdo
            ->beginTransaction();

        try {
            $stmt =
                $this->pdo->prepare(
                    'UPDATE usuarios
                     SET ultimo_login_em = NOW()
                     WHERE id = :id'
                );

            $stmt->execute([
                ':id' =>
                    $usuarioId,
            ]);

            $this->registrarEventoSeguranca(
                $usuarioId,
                'LOGIN_SUCESSO',
                'Login realizado',
                'A conta foi acessada com e-mail e senha válidos.'
            );

            $this->pdo
                ->commit();
        } catch (\Throwable $e) {
            if (
                $this->pdo
                    ->inTransaction()
            ) {
                $this->pdo
                    ->rollBack();
            }

            throw $e;
        }
    }

    private function registrarEventoSeguranca(
        int $usuarioId,
        string $tipo,
        string $titulo,
        ?string $detalhe
    ): void {
        $stmt =
            $this->pdo->prepare(
                'INSERT INTO eventos_seguranca_conta (
                    usuario_id,
                    tipo,
                    titulo,
                    detalhe,
                    criado_em
                 )
                 VALUES (
                    :usuario_id,
                    :tipo,
                    :titulo,
                    :detalhe,
                    NOW()
                 )'
            );

        $stmt->execute([
            ':usuario_id' =>
                $usuarioId,
            ':tipo' =>
                $tipo,
            ':titulo' =>
                $titulo,
            ':detalhe' =>
                $detalhe,
        ]);
    }
}
