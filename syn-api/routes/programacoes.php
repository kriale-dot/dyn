<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\ProgramacaoController;
use App\Repositories\ProgramacaoRepository;
use App\Services\ProgramacaoService;

$pdo = Database::conectar();

$repository =
    new ProgramacaoRepository($pdo);

$service =
    new ProgramacaoService($repository);

$controller =
    new ProgramacaoController($service);

/**
 * Programação geral pode ser consultada por qualquer usuário
 * autenticado nesta versão.
 */
$app->get(
    '/programacoes',
    [$controller, 'listar']
)->add($authMiddleware);

$app->get(
    '/programacoes/{id:[0-9]+}',
    [$controller, 'buscarPorId']
)->add($authMiddleware);

/**
 * Administrador e Organizador administram programações.
 *
 * O documento prevê escopo granular para Organizador.
 * Como esse modelo ainda não foi detalhado, aqui implementamos
 * apenas a barreira inicial de papel.
 */
$app->post(
    '/programacoes',
    [$controller, 'criar']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->put(
    '/programacoes/{id:[0-9]+}',
    [$controller, 'atualizar']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->patch(
    '/programacoes/{id:[0-9]+}/cancelar',
    [$controller, 'cancelar']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->patch(
    '/programacoes/{id:[0-9]+}/realizar',
    [$controller, 'realizar']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);
