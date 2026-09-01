<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\NotificacaoController;
use App\Repositories\NotificacaoRepository;
use App\Services\NotificacaoService;

$pdoNotificacoes =
    Database::conectar();

$notificacaoRepository =
    new NotificacaoRepository(
        $pdoNotificacoes
    );

$notificacaoService =
    new NotificacaoService(
        $notificacaoRepository
    );

$notificacaoController =
    new NotificacaoController(
        $notificacaoService
    );

/**
 * Central de notificações do próprio usuário.
 */
$app->get(
    '/notificacoes',
    [
        $notificacaoController,
        'index',
    ]
)->add($authMiddleware);

$app->get(
    '/notificacoes/resumo',
    [
        $notificacaoController,
        'resumo',
    ]
)->add($authMiddleware);

$app->patch(
    '/notificacoes/{id:[0-9]+}/lida',
    [
        $notificacaoController,
        'marcarComoLida',
    ]
)->add($authMiddleware);

$app->patch(
    '/notificacoes/marcar-todas-lidas',
    [
        $notificacaoController,
        'marcarTodasComoLidas',
    ]
)->add($authMiddleware);
