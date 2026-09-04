<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\EscalasSemanaController;
use App\Repositories\EscalasSemanaRepository;
use App\Services\EscalasSemanaService;

$pdoEscalasSemana =
    Database::conectar();

$escalasSemanaRepository =
    new EscalasSemanaRepository(
        $pdoEscalasSemana
    );

$escalasSemanaService =
    new EscalasSemanaService(
        $escalasSemanaRepository
    );

$escalasSemanaController =
    new EscalasSemanaController(
        $escalasSemanaService
    );

/**
 * GET /gestao/escalas-semana
 *
 * Opcional:
 * ?data_referencia=2026-09-02
 *
 * ADMINISTRADOR:
 * vê todos os tipos.
 *
 * ORGANIZADOR:
 * vê somente tipos atribuídos ao seu escopo.
 *
 * MEMBRO:
 * 403.
 */
$app->get(
    '/gestao/escalas-semana',
    [
        $escalasSemanaController,
        'index',
    ]
)->add($authMiddleware);
