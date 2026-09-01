<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\SerieProgramacaoController;
use App\Repositories\SerieProgramacaoRepository;
use App\Services\SerieProgramacaoService;

$pdo = Database::conectar();
$repository = new SerieProgramacaoRepository($pdo);
$service = new SerieProgramacaoService($repository);
$controller = new SerieProgramacaoController($service);

$app->get(
    '/series-programacao',
    [$controller, 'listar']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->get(
    '/series-programacao/{id:[0-9]+}',
    [$controller, 'buscarPorId']
)
    ->add($escopoSerieMiddleware)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->post(
    '/series-programacao',
    [$controller, 'criar']
)
    ->add($escopoTipoBodyMiddleware)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->patch(
    '/series-programacao/{id:[0-9]+}/desativar',
    [$controller, 'desativar']
)
    ->add($escopoSerieMiddleware)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);
