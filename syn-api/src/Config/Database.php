<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Responsável por criar e fornecer a conexão PDO com o MariaDB.
 *
 * Esta classe pertence à camada de configuração/infraestrutura.
 * Ela NÃO conhece regras de negócio do SYN.
 */
final class Database
{
    /**
     * Cria uma nova conexão PDO usando as variáveis do arquivo .env.
     */
    public static function conectar(): PDO
    {
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $nome = $_ENV['DB_NAME'] ?? 'syn';
        $usuario = $_ENV['DB_USER'] ?? 'root';
        $senha = $_ENV['DB_PASS'] ?? '';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$nome};charset={$charset}";

        try {
            return new PDO(
                $dsn,
                $usuario,
                $senha,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            throw new RuntimeException(
                'Não foi possível conectar ao banco de dados.',
                0,
                $e
            );
        }
    }
}
