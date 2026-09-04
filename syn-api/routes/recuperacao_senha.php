<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\RecuperacaoSenhaController;
use App\Repositories\RecuperacaoSenhaRepository;
use App\Services\RecuperacaoSenhaService;
use App\Services\EmailService;
use App\Services\RateLimitService;
use App\Middlewares\RateLimitMiddleware;

$pdoRecuperacaoSenha =
    Database::conectar();

$recuperacaoSenhaRepository =
    new RecuperacaoSenhaRepository(
        $pdoRecuperacaoSenha
    );

$emailService =
    new EmailService();

$recuperacaoSenhaService =
    new RecuperacaoSenhaService(
        $recuperacaoSenhaRepository,
        $emailService
    );

$recuperacaoSenhaController =
    new RecuperacaoSenhaController(
        $recuperacaoSenhaService
    );

/**
 * ============================================================
 * RATE LIMIT DA RECUPERAÇÃO
 * ============================================================
 *
 * Solicitar e-mail:
 * - 20 / 30 min por IP;
 * - 5 / 30 min por IP + e-mail.
 *
 * Redefinir:
 * - 30 / 15 min por IP.
 *
 * Isso reduz disparos de e-mail em massa e tentativas automatizadas
 * de adivinhação de token.
 */
$recuperacaoRateLimitService =
    new RateLimitService(
        $pdoRecuperacaoSenha
    );

$esqueciSenhaRateLimitMiddleware =
    new RateLimitMiddleware(
        $recuperacaoRateLimitService,
        $app->getResponseFactory(),
        'RECUPERACAO_SOLICITAR',
        20,
        30 * 60,
        'email',
        5
    );

$redefinirSenhaRateLimitMiddleware =
    new RateLimitMiddleware(
        $recuperacaoRateLimitService,
        $app->getResponseFactory(),
        'RECUPERACAO_REDEFINIR',
        30,
        15 * 60
    );

/**
 * Rotas públicas.
 *
 * O usuário ainda não consegue autenticar porque justamente
 * esqueceu a senha.
 */
$app->post(
    '/auth/esqueci-senha',
    [
        $recuperacaoSenhaController,
        'solicitar',
    ]
)
    ->add(
        $esqueciSenhaRateLimitMiddleware
    );

$app->post(
    '/auth/redefinir-senha',
    [
        $recuperacaoSenhaController,
        'redefinir',
    ]
)
    ->add(
        $redefinirSenhaRateLimitMiddleware
    );
