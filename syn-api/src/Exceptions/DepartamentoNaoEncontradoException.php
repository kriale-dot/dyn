<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Lançada quando um departamento solicitado não existe.
 */
final class DepartamentoNaoEncontradoException extends RuntimeException
{
    public function __construct(int $departamentoId)
    {
        parent::__construct(
            "Departamento com ID {$departamentoId} não encontrado."
        );
    }
}
