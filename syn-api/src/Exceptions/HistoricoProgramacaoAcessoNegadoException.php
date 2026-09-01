<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Usada quando um usuário tenta consultar o histórico administrativo
 * de uma programação sem possuir permissão.
 */
final class HistoricoProgramacaoAcessoNegadoException
    extends \RuntimeException
{
}
