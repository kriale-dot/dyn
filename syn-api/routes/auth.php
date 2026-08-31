<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\AuthController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\PapelMiddleware;
use App\Repositories\AuthRepository;
use App\Services\AuthService;

/**
 * ============================================================
 * AUTENTICAÇÃO E MIDDLEWARES COMPARTILHADOS
 * ============================================================
 *
 * Este arquivo é carregado PRIMEIRO por routes/routes.php.
 *
 * Além de registrar /auth/login e /auth/me, ele cria os
 * middlewares reutilizados pelos demais arquivos de rotas.
 */

$authPdo = Database::conectar();

$authRepository =
    new AuthRepository($authPdo);

$jwtSecret =
    (string) (
        $_ENV['JWT_SECRET']
        ?? ''
    );

$jwtTtlSeconds =
    (int) (
        $_ENV['JWT_TTL_SECONDS']
        ?? 3600
    );

$authService =
    new AuthService(
        $authRepository,
        $jwtSecret,
        $jwtTtlSeconds
    );

$authController =
    new AuthController($authService);

$responseFactory =
    $app->getResponseFactory();

/**
 * Middleware: exige login.
 */
$authMiddleware =
    new AuthMiddleware(
        $authService,
        $responseFactory
    );

/**
 * Middleware: somente Administrador.
 */
$adminMiddleware =
    new PapelMiddleware(
        ['ADMINISTRADOR'],
        $responseFactory
    );

/**
 * Middleware: Administrador ou Organizador.
 *
 * Nesta etapa ainda não existe a granularidade interna de
 * permissões do Organizador por área/atividade.
 */
$adminOrganizadorMiddleware =
    new PapelMiddleware(
        [
            'ADMINISTRADOR',
            'ORGANIZADOR',
        ],
        $responseFactory
    );

/**
 * Login é público.
 */
$app->post(
    '/auth/login',
    [$authController, 'login']
);

/**
 * /auth/me exige Bearer token.
 */
$app->get(
    '/auth/me',
    [$authController, 'me']
)->add($authMiddleware);
