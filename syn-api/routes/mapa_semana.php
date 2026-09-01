<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\MapaSemanaController;
use App\Repositories\MapaSemanaRepository;
use App\Services\MapaSemanaService;

$pdoMapaSemana = Database::conectar();

$mapaSemanaRepository =
    new MapaSemanaRepository($pdoMapaSemana);

$mapaSemanaService =
    new MapaSemanaService($mapaSemanaRepository);

$mapaSemanaController =
    new MapaSemanaController($mapaSemanaService);

/**
 * GET /mapa-semana
 * GET /mapa-semana?data_referencia=2026-09-06
 */
$app->get(
    '/mapa-semana',
    [$mapaSemanaController, 'index']
)->add($authMiddleware);
