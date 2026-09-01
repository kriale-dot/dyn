<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\AppBootstrapController;
use App\Repositories\AppBootstrapRepository;
use App\Services\AppBootstrapService;

$pdoAppBootstrap =
    Database::conectar();

$appBootstrapRepository =
    new AppBootstrapRepository(
        $pdoAppBootstrap
    );

$appBootstrapService =
    new AppBootstrapService(
        $appBootstrapRepository
    );

$appBootstrapController =
    new AppBootstrapController(
        $appBootstrapService
    );

/**
 * Estado inicial da aplicação React após autenticação.
 *
 * GET /app-bootstrap
 */
$app->get(
    '/app-bootstrap',
    [
        $appBootstrapController,
        'index',
    ]
)->add($authMiddleware);
