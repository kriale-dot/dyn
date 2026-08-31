<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Lançada quando uma função solicitada não existe.
 */
final class FuncaoNaoEncontradaException extends RuntimeException
{
    public function __construct(int $funcaoId)
    {
        parent::__construct(
            "Função com ID {$funcaoId} não encontrada."
        );
    }
}
