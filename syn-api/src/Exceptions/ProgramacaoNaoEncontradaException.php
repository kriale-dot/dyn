<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Lançada quando uma programação solicitada não existe.
 */
final class ProgramacaoNaoEncontradaException extends RuntimeException
{
    public function __construct(int $programacaoId)
    {
        parent::__construct(
            "Programação com ID {$programacaoId} não encontrada."
        );
    }
}
