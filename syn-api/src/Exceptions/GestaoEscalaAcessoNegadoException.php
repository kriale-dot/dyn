<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Exceção específica para tentativa de administrar uma escala
 * sem autorização.
 */
final class GestaoEscalaAcessoNegadoException extends \RuntimeException
{
}
