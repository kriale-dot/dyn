<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\NecessidadeEspecificaController;
use App\Repositories\NecessidadeEspecificaRepository;
use App\Services\NecessidadeEspecificaService;

$pdo = Database::conectar();

$repository = new NecessidadeEspecificaRepository($pdo);
$service = new NecessidadeEspecificaService($repository);
$controller = new NecessidadeEspecificaController($service);

/**
 * ADMINISTRADOR:
 * acesso total por papel.
 *
 * ORGANIZADOR:
 * exige NECESSIDADES_ESPECIFICAS_GERENCIAR.
 *
 * MEMBRO:
 * bloqueado.
 */
$app->get(
    '/necessidades-especificas',
    [$controller, 'listar']
)
    ->add($necessidadesEspecificasMiddleware)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->get(
    '/usuarios/{usuarioId:[0-9]+}/necessidade-especifica',
    [$controller, 'buscarPorUsuario']
)
    ->add($necessidadesEspecificasMiddleware)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->put(
    '/usuarios/{usuarioId:[0-9]+}/necessidade-especifica',
    [$controller, 'salvar']
)
    ->add($necessidadesEspecificasMiddleware)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->patch(
    '/usuarios/{usuarioId:[0-9]+}/necessidade-especifica/desativar',
    [$controller, 'desativar']
)
    ->add($necessidadesEspecificasMiddleware)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);
