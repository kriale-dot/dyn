<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Exceção de regra de negócio do fluxo de cadastro público.
 *
 * O status HTTP acompanha a regra:
 *
 * 403 -> usuário autenticado não é aprovador;
 * 404 -> solicitação não encontrada;
 * 409 -> conflito de estado/e-mail;
 * 422 -> dados inválidos.
 */
final class CadastroException extends RuntimeException
{
    public function __construct(
        string $message,
        private int $statusCode = 409
    ) {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
