<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Lançada quando uma participação/escala não existe.
 */
final class ParticipacaoNaoEncontradaException extends RuntimeException
{
    public function __construct(int $participacaoId)
    {
        parent::__construct(
            "Participação com ID {$participacaoId} não encontrada."
        );
    }
}
