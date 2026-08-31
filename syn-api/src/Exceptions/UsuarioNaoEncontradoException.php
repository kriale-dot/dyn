<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Exceção de domínio lançada quando um usuário solicitado
 * não existe no banco de dados.
 *
 * A exceção NÃO conhece HTTP.
 * O Controller é quem transforma este caso em resposta 404.
 */
final class UsuarioNaoEncontradoException extends RuntimeException
{
    public function __construct(
        int $usuarioId
    ) {
        parent::__construct(
            "Usuário com ID {$usuarioId} não encontrado."
        );
    }
}
