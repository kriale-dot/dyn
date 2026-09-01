<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Representa conflito de uma mesma pessoa em programações
 * com horários sobrepostos.
 *
 * O Controller converte esta exceção em HTTP 409 Conflict.
 */
final class ConflitoPessoaException extends RuntimeException
{
    /**
     * @param array<int, array<string, mixed>> $conflitos
     */
    public function __construct(
        private array $conflitos
    ) {
        parent::__construct(
            'A pessoa já possui compromisso em outra programação com horário sobreposto.'
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
