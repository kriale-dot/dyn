<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\PublicoController;
use App\Repositories\PublicoRepository;
use App\Services\PublicoService;

/**
 * ============================================================
 * ÁREA PÚBLICA DO SYN
 * ============================================================
 *
 * Estas rotas NÃO recebem $authMiddleware.
 *
 * Isso é intencional: qualquer visitante pode consultar somente
 * a projeção pública das programações.
 */
$pdoPublico =
    Database::conectar();

$publicoRepository =
    new PublicoRepository(
        $pdoPublico
    );

$publicoService =
    new PublicoService(
        $publicoRepository
    );

$publicoController =
    new PublicoController(
        $publicoService
    );

$app->get(
    '/publico/igreja',
    [
        $publicoController,
        'igreja',
    ]
);

$app->get(
    '/publico/mapa-semana',
    [
        $publicoController,
        'mapaSemana',
    ]
);

$app->get(
    '/publico/programacoes',
    [
        $publicoController,
        'programacoes',
    ]
);

$app->get(
    '/publico/programacoes/{id:[0-9]+}',
    [
        $publicoController,
        'programacao',
    ]
);
