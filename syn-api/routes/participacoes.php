<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\ParticipacaoController;
use App\Repositories\ParticipacaoRepository;
use App\Services\ParticipacaoService;

$pdo = Database::conectar();

$repository =
    new ParticipacaoRepository($pdo);

$service =
    new ParticipacaoService($repository);

$controller =
    new ParticipacaoController($service);

/**
 * Gestão e acompanhamento das escalas:
 * Administrador ou Organizador.
 */
$app->get(
    '/programacoes/{id:[0-9]+}/participacoes',
    [$controller, 'listarPorProgramacao']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->get(
    '/programacoes/{id:[0-9]+}/candidatos',
    [$controller, 'listarCandidatos']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->post(
    '/programacoes/{id:[0-9]+}/participacoes',
    [$controller, 'criar']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->get(
    '/participacoes/{id:[0-9]+}',
    [$controller, 'buscarPorId']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

/**
 * Respostas do membro.
 *
 * Além do AuthMiddleware, o Service confere se a participação
 * realmente pertence ao usuário autenticado.
 */
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

/**
 * Cancelamento administrativo da escala.
 */
$app->patch(
    '/participacoes/{id:[0-9]+}/cancelar',
    [$controller, 'cancelar']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);
