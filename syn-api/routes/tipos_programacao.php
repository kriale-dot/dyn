<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\TipoProgramacaoController;
use App\Repositories\TipoProgramacaoRepository;
use App\Services\TipoProgramacaoService;

$pdo = Database::conectar();
$repository = new TipoProgramacaoRepository($pdo);
$service = new TipoProgramacaoService($repository);
$controller = new TipoProgramacaoController($service);

$app->get(
    '/tipos-programacao',
    [$controller, 'listar']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->get(
    '/tipos-programacao/{id:[0-9]+}',
    [$controller, 'buscarPorId']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->post(
    '/tipos-programacao',
    [$controller, 'criar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->put(
    '/tipos-programacao/{id:[0-9]+}',
    [$controller, 'atualizar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->patch(
    '/tipos-programacao/{id:[0-9]+}/desativar',
    [$controller, 'desativar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->post(
    '/tipos-programacao/{tipoId:[0-9]+}/funcoes/{funcaoId:[0-9]+}',
    [$controller, 'autorizarFuncao']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->delete(
    '/tipos-programacao/{tipoId:[0-9]+}/funcoes/{funcaoId:[0-9]+}',
    [$controller, 'removerAutorizacaoFuncao']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->get(
    '/tipos-programacao/{id:[0-9]+}/candidatos',
    [$controller, 'listarCandidatos']
)
    ->add($escopoTipoRotaMiddleware)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);
