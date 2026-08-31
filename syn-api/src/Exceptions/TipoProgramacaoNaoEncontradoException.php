<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Lançada quando um tipo de programação solicitado não existe.
 */
final class TipoProgramacaoNaoEncontradoException extends RuntimeException
{
    public function __construct(int $tipoProgramacaoId)
    {
        parent::__construct(
            "Tipo de programação com ID {$tipoProgramacaoId} não encontrado."
        );
    }
}
