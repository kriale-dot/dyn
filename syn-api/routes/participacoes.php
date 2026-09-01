<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\ParticipacaoController;
use App\Repositories\ParticipacaoRepository;
use App\Services\ParticipacaoService;

$pdo = Database::conectar();
$repository = new ParticipacaoRepository($pdo);
$service = new ParticipacaoService($repository);
$controller = new ParticipacaoController($service);

$app->get(
    '/programacoes/{id:[0-9]+}/participacoes',
    [$controller, 'listarPorProgramacao']
)
    ->add($escopoProgramacaoMiddleware)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->get(
    '/programacoes/{id:[0-9]+}/candidatos',
    [$controller, 'listarCandidatos']
)
    ->add($escopoProgramacaoMiddleware)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->post(
    '/programacoes/{id:[0-9]+}/participacoes',
    [$controller, 'criar']
)
    ->add($escopoProgramacaoMiddleware)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->get(
    '/participacoes/{id:[0-9]+}',
    [$controller, 'buscarPorId']
)
    ->add($escopoParticipacaoMiddleware)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->patch(
    '/participacoes/{id:[0-9]+}/confirmar',
    [$controller, 'confirmar']
)->add($authMiddleware);

$app->patch(
    '/participacoes/{id:[0-9]+}/indisponivel',
    [$controller, 'indisponivel']
)->add($authMiddleware);

$app->patch(
    '/participacoes/{id:[0-9]+}/recusar',
    [$controller, 'recusar']
)->add($authMiddleware);

$app->patch(
    '/participacoes/{id:[0-9]+}/cancelar',
    [$controller, 'cancelar']
)
    ->add($escopoParticipacaoMiddleware)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);
