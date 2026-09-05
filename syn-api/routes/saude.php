<?php

declare(strict_types=1);

use App\Controllers\DiagnosticoController;
use App\Services\DiagnosticoService;

/**
 * ============================================================
 * SAÚDE / DIAGNÓSTICO DA API
 * ============================================================
 *
 * Rotas públicas propositalmente simples para uso por:
 *
 * - navegador;
 * - monitoramento;
 * - balanceador/reverse proxy;
 * - scripts de deploy.
 *
 * Elas NÃO retornam segredos ou mensagens internas de exceções.
 */

$diagnosticoService =
    new DiagnosticoService();

$diagnosticoController =
    new DiagnosticoController(
        $diagnosticoService
    );

/**
 * Liveness:
 * confirma somente que o processo HTTP está respondendo.
 */
$app->get(
    '/health',
    [
        $diagnosticoController,
        'health',
    ]
);

/**
 * Readiness:
 * verifica banco, extensões, uploads e configuração de produção.
 */
$app->get(
    '/health/ready',
    [
        $diagnosticoController,
        'ready',
    ]
);
