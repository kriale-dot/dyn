<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class SerieProgramacaoNaoEncontradaException extends RuntimeException
{
    public function __construct(int $serieId)
    {
        parent::__construct("Série de programação com ID {$serieId} não encontrada.");
    }
}
