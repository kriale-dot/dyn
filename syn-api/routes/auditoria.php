<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\AuditoriaController;
use App\Middlewares\AuditoriaMiddleware;
use App\Repositories\AuditoriaRepository;
use App\Services\AuditoriaService;

$pdoAuditoria =
    Database::conectar();

$auditoriaRepository =
    new AuditoriaRepository(
        $pdoAuditoria
    );

$auditoriaService =
    new AuditoriaService(
        $auditoriaRepository
    );

$auditoriaController =
    new AuditoriaController(
        $auditoriaService
    );

/**
 * Consulta da auditoria.
 *
 * O Service permite somente ADMINISTRADOR.
 */
$app->get(
    '/auditoria',
    [
        $auditoriaController,
        'index',
    ]
)->add($authMiddleware);

$app->get(
    '/auditoria/{id:[0-9]+}',
    [
        $auditoriaController,
        'show',
    ]
)->add($authMiddleware);

/**
 * Middleware global de auditoria.
 *
 * Ele audita apenas:
 * POST, PUT, PATCH e DELETE.
 *
 * A leitura da própria auditoria (GET) não cria novo registro.
 */
$jwtSecretAuditoria =
    (string) (
        $_ENV['JWT_SECRET']
        ?? getenv('JWT_SECRET')
        ?: ''
    );

$auditoriaMiddleware =
    new AuditoriaMiddleware(
        $auditoriaRepository,
        $jwtSecretAuditoria
    );

$app->add(
    $auditoriaMiddleware
);
