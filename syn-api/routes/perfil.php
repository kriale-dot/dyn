<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\PerfilController;
use App\Repositories\PerfilRepository;
use App\Services\PerfilService;

$pdo = Database::conectar();

$repository =
    new PerfilRepository($pdo);

$service =
    new PerfilService($repository);

$controller =
    new PerfilController($service);

/**
 * Perfil do próprio usuário autenticado.
 */
$app->get(
    '/meu-perfil',
    [$controller, 'meuPerfil']
)->add($authMiddleware);

$app->put(
    '/meu-perfil',
    [$controller, 'atualizarMeuPerfil']
)->add($authMiddleware);

/**
 * Aniversariantes.
 *
 * Qualquer usuário autenticado pode consultar.
 * A resposta é propositalmente discreta:
 * não expõe ano de nascimento nem idade.
 */
$app->get(
    '/aniversariantes/hoje',
    [$controller, 'aniversariantesHoje']
)->add($authMiddleware);

$app->get(
    '/aniversariantes/semana',
    [$controller, 'aniversariantesSemana']
)->add($authMiddleware);
