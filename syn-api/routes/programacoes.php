<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\ProgramacaoController;
use App\Repositories\ProgramacaoRepository;
use App\Services\ProgramacaoService;

$pdo = Database::conectar();
$repository = new ProgramacaoRepository($pdo);
$service = new ProgramacaoService($repository);
$controller = new ProgramacaoController($service);

$app->get(
    '/programacoes',
    [$controller, 'listar']
)->add($authMiddleware);

$app->get(
    '/programacoes/{id:[0-9]+}',
    [$controller, 'buscarPorId']
)->add($authMiddleware);

$app->post(
    '/programacoes',
    [$controller, 'criar']
)
    ->add($escopoTipoBodyMiddleware)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->put(
    '/programacoes/{id:[0-9]+}',
    [$controller, 'atualizar']
)
    ->add($escopoTipoBodyMiddleware)
    ->add($escopoProgramacaoMiddleware)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->patch(
    '/programacoes/{id:[0-9]+}/cancelar',
    [$controller, 'cancelar']
)
    ->add($escopoProgramacaoMiddleware)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->patch(
    '/programacoes/{id:[0-9]+}/realizar',
    [$controller, 'realizar']
)
    ->add($escopoProgramacaoMiddleware)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);
