<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\DashboardController;
use App\Repositories\DashboardRepository;
use App\Services\DashboardService;

$pdoDashboard =
    Database::conectar();

$dashboardRepository =
    new DashboardRepository(
        $pdoDashboard
    );

$dashboardService =
    new DashboardService(
        $dashboardRepository
    );

$dashboardController =
    new DashboardController(
        $dashboardService
    );

/**
 * Tela inicial do usuário autenticado.
 *
 * Exemplo:
 *
 * GET /dashboard
 * GET /dashboard?data_referencia=2026-09-06
 */
$app->get(
    '/dashboard',
    [
        $dashboardController,
        'index',
    ]
)->add($authMiddleware);
