<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\DetalheProgramacaoController;
use App\Repositories\DetalheProgramacaoRepository;
use App\Services\DetalheProgramacaoService;

$pdoDetalheProgramacao =
    Database::conectar();

$detalheProgramacaoRepository =
    new DetalheProgramacaoRepository(
        $pdoDetalheProgramacao
    );

$detalheProgramacaoService =
    new DetalheProgramacaoService(
        $detalheProgramacaoRepository
    );

$detalheProgramacaoController =
    new DetalheProgramacaoController(
        $detalheProgramacaoService
    );

/**
 * Detalhe completo para a futura tela React.
 *
 * Exemplo:
 *
 * GET /programacoes/10/detalhes
 */
$app->get(
    '/programacoes/{id:[0-9]+}/detalhes',
    [
        $detalheProgramacaoController,
        'show',
    ]
)->add($authMiddleware);
