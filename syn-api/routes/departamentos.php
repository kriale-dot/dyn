<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\DepartamentoController;
use App\Repositories\DepartamentoRepository;
use App\Services\DepartamentoService;

$pdo = Database::conectar();

$repository =
    new DepartamentoRepository($pdo);

$service =
    new DepartamentoService($repository);

$controller =
    new DepartamentoController($service);

$app->get(
    '/departamentos',
    [$controller, 'listar']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->get(
    '/departamentos/{id:[0-9]+}',
    [$controller, 'buscarPorId']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->post(
    '/departamentos',
    [$controller, 'criar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->put(
    '/departamentos/{id:[0-9]+}',
    [$controller, 'atualizar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->patch(
    '/departamentos/{id:[0-9]+}/desativar',
    [$controller, 'desativar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);
