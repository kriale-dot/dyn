<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Lançada quando um local solicitado não existe.
 */
final class LocalNaoEncontradoException extends RuntimeException
{
    public function __construct(int $localId)
    {
        parent::__construct(
            "Local com ID {$localId} não encontrado."
        );
    }
}
