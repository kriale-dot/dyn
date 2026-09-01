<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\GestaoEscalaController;
use App\Repositories\GestaoEscalaRepository;
use App\Services\GestaoEscalaService;

$pdoGestaoEscala = Database::conectar();

$gestaoEscalaRepository = new GestaoEscalaRepository(
    $pdoGestaoEscala
);

$gestaoEscalaService = new GestaoEscalaService(
    $gestaoEscalaRepository
);

$gestaoEscalaController = new GestaoEscalaController(
    $gestaoEscalaService
);

/**
 * GET /programacoes/{id}/gestao-escala
 *
 * ADMINISTRADOR: acesso total.
 * ORGANIZADOR: apenas tipos atribuídos a ele.
 * MEMBRO: 403.
 */
$app->get(
    '/programacoes/{id:[0-9]+}/gestao-escala',
    [$gestaoEscalaController, 'show']
)->add($authMiddleware);
