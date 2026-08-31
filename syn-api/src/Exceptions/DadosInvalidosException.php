<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Exceção lançada quando os dados enviados pelo cliente
 * não atendem às regras de validação da aplicação.
 *
 * O Service pode lançar esta exceção sem conhecer HTTP.
 * O Controller decide depois que ela será convertida em 422.
 */
final class DadosInvalidosException extends RuntimeException
{
    /**
     * @param array<string, string> $erros
     */
    public function __construct(
        private array $erros,
        string $message = 'Os dados informados são inválidos.'
    ) {
        parent::__construct($message);
    }

    /**
     * @return array<string, string>
     */
    public function getErros(): array
    {
        return $this->erros;
    }
}
