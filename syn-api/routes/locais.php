<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\LocalController;
use App\Repositories\LocalRepository;
use App\Services\LocalService;

$pdo = Database::conectar();

$repository = new LocalRepository($pdo);
$service = new LocalService($repository);
$controller = new LocalController($service);

/**
 * Organizador consulta locais.
 */
$app->get(
    '/locais',
    [$controller, 'listar']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->get(
    '/locais/{id:[0-9]+}',
    [$controller, 'buscarPorId']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

/**
 * CRUD estrutural: Administrador.
 */
$app->post(
    '/locais',
    [$controller, 'criar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->put(
    '/locais/{id:[0-9]+}',
    [$controller, 'atualizar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->patch(
    '/locais/{id:[0-9]+}/desativar',
    [$controller, 'desativar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);
