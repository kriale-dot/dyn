<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\HistoricoProgramacaoController;
use App\Repositories\HistoricoProgramacaoRepository;
use App\Services\HistoricoProgramacaoService;

$pdoHistoricoProgramacao =
    Database::conectar();

$historicoProgramacaoRepository =
    new HistoricoProgramacaoRepository(
        $pdoHistoricoProgramacao
    );

$historicoProgramacaoService =
    new HistoricoProgramacaoService(
        $historicoProgramacaoRepository
    );

$historicoProgramacaoController =
    new HistoricoProgramacaoController(
        $historicoProgramacaoService
    );

/**
 * Histórico administrativo das alterações importantes.
 *
 * GET /programacoes/10/historico-alteracoes
 */
$app->get(
    '/programacoes/{id:[0-9]+}/historico-alteracoes',
    [
        $historicoProgramacaoController,
        'index',
    ]
)->add($authMiddleware);
