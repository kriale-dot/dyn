<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Representa conflito de horário no mesmo local.
 *
 * Esta exceção é convertida pelo Controller em HTTP 409 Conflict.
 */
final class ConflitoLocalException extends RuntimeException
{
    /**
     * @param array<int, array<string, mixed>> $conflitos
     */
    public function __construct(
        private array $conflitos
    ) {
        parent::__construct(
            'Existe conflito de horário no local informado. Confirme explicitamente se deseja continuar.'
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getConflitos(): array
    {
        return $this->conflitos;
    }
}
