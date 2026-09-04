<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\AuthController;
use App\Controllers\SegurancaContaController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\PapelMiddleware;
use App\Middlewares\RateLimitMiddleware;
use App\Repositories\AuthRepository;
use App\Repositories\SegurancaContaRepository;
use App\Services\AuthService;
use App\Services\SegurancaContaService;
use App\Services\EmailService;
use App\Services\RateLimitService;

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

$segurancaContaRepository =
    new SegurancaContaRepository(
        $authPdo
    );

$segurancaContaService =
    new SegurancaContaService(
        $segurancaContaRepository,
        new EmailService()
    );

$segurancaContaController =
    new SegurancaContaController(
        $segurancaContaService
    );

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
 * ============================================================
 * RATE LIMIT DO LOGIN
 * ============================================================
 *
 * Por IP:
 * 60 requisições / 15 minutos.
 *
 * Por IP + e-mail:
 * 10 requisições / 15 minutos.
 *
 * O limite é intencionalmente aplicado antes de saber se a conta
 * existe, evitando usar o rate limit para enumerar usuários.
 */
$authRateLimitService =
    new RateLimitService(
        $authPdo
    );

$loginRateLimitMiddleware =
    new RateLimitMiddleware(
        $authRateLimitService,
        $responseFactory,
        'AUTH_LOGIN',
        60,
        15 * 60,
        'email',
        10
    );


/**
 * Alteração autenticada de senha.
 *
 * A senha atual precisa ser informada, mas ainda limitamos tentativas
 * para reduzir ataques quando uma sessão é obtida indevidamente.
 */
$alterarSenhaRateLimitMiddleware =
    new RateLimitMiddleware(
        $authRateLimitService,
        $responseFactory,
        'AUTH_ALTERAR_SENHA',
        10,
        15 * 60
    );

/**
 * Login é público.
 */
$app->post(
    '/auth/login',
    [$authController, 'login']
)
    ->add(
        $loginRateLimitMiddleware
    );

/**
 * Altera a senha do próprio usuário.
 *
 * AuthMiddleware executa primeiro. Depois o rate limit protege
 * tentativas repetidas de senha atual.
 */
$app->post(
    '/auth/alterar-senha',
    [
        $segurancaContaController,
        'alterarSenha',
    ]
)
    ->add(
        $alterarSenhaRateLimitMiddleware
    )
    ->add(
        $authMiddleware
    );

/**
 * Encerra todas as sessões emitidas para o usuário.
 *
 * A própria sessão atual também é invalidada depois desta resposta.
 */
$app->post(
    '/auth/encerrar-todas-sessoes',
    [
        $authController,
        'encerrarTodasSessoes',
    ]
)
    ->add(
        $authMiddleware
    );

/**
 * /auth/me exige Bearer token.
 */
$app->get(
    '/auth/me',
    [$authController, 'me']
)->add($authMiddleware);
