<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Exceção gerada quando uma chave ultrapassa o limite
 * permitido dentro de uma janela de tempo.
 */
final class RateLimitException extends RuntimeException
{
    public function __construct(
        string $message,
        private int $retryAfterSeconds,
        private int $limit
    ) {
        parent::__construct($message);
    }

    public function getRetryAfterSeconds(): int
    {
        return $this->retryAfterSeconds;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
