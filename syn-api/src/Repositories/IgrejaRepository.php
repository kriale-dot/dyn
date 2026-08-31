<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Repository responsável pelo acesso à tabela igreja.
 *
 * Esta camada:
 * - conhece PDO;
 * - conhece SQL;
 * - lê e altera dados no banco.
 *
 * Ela NÃO conhece Request, Response, Postman ou regras HTTP.
 */
final class IgrejaRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Busca o único cadastro institucional da instalação SYN.
     *
     * @return array<string, mixed>|null
     */
    public function buscar(): ?array
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
                site,
                criado_em,
                atualizado_em
            FROM igreja
            WHERE singleton = 1
            LIMIT 1
        SQL;

        $stmt = $this->pdo->query($sql);

        $igreja = $stmt->fetch();

        return $igreja === false ? null : $igreja;
    }

    /**
     * Atualiza o cadastro institucional existente.
     *
     * Como cada ambiente SYN representa uma única igreja,
     * atualizamos sempre o registro cujo singleton = 1.
     *
     * @param array<string, mixed> $dados
     */
    public function atualizar(array $dados): bool
    {
        $sql = <<<'SQL'
            UPDATE igreja
            SET
                nome = :nome,
                logotipo = :logotipo,
                cep = :cep,
                logradouro = :logradouro,
                numero = :numero,
                complemento = :complemento,
                bairro = :bairro,
                cidade = :cidade,
                estado = :estado,
                telefone = :telefone,
                email = :email,
                site = :site
            WHERE singleton = 1
        SQL;

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':nome' => $dados['nome'],
            ':logotipo' => $dados['logotipo'],
            ':cep' => $dados['cep'],
            ':logradouro' => $dados['logradouro'],
            ':numero' => $dados['numero'],
            ':complemento' => $dados['complemento'],
            ':bairro' => $dados['bairro'],
            ':cidade' => $dados['cidade'],
            ':estado' => $dados['estado'],
            ':telefone' => $dados['telefone'],
            ':email' => $dados['email'],
            ':site' => $dados['site'],
        ]);
    }
}
