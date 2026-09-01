<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Lançada quando não existe registro de necessidade específica
 * para o usuário informado.
 */
final class NecessidadeEspecificaNaoEncontradaException extends RuntimeException
{
    public function __construct(int $usuarioId)
    {
        parent::__construct(
            "Necessidade específica do usuário com ID {$usuarioId} não encontrada."
        );
    }
}
