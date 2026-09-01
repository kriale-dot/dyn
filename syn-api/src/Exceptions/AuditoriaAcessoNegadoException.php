<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Somente Administradores podem consultar a auditoria da API.
 */
final class AuditoriaAcessoNegadoException
    extends \RuntimeException
{
}
