<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Exceção usada quando:
 *
 * - e-mail/senha estão incorretos;
 * - token não existe;
 * - token é inválido;
 * - token expirou;
 * - usuário do token não existe mais;
 * - usuário foi desativado.
 *
 * O Controller/Middleware transforma esta exceção em HTTP 401.
 */
final class AutenticacaoException extends RuntimeException
{
}
