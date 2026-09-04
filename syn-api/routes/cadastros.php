<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\CadastroController;
use App\Repositories\CadastroRepository;
use App\Services\CadastroService;
use App\Services\EmailService;
use App\Services\RateLimitService;
use App\Middlewares\RateLimitMiddleware;

$pdoCadastro =
    Database::conectar();

$cadastroRepository =
    new CadastroRepository(
        $pdoCadastro
    );

$cadastroEmailService =
    new EmailService();

$cadastroService =
    new CadastroService(
        $cadastroRepository,
        $cadastroEmailService
    );

$cadastroController =
    new CadastroController(
        $cadastroService
    );

/**
 * ============================================================
 * RATE LIMIT DO CADASTRO PÚBLICO
 * ============================================================
 *
 * - 20 solicitações / hora por IP;
 * - 5 solicitações / hora por IP + e-mail.
 */
$cadastroRateLimitService =
    new RateLimitService(
        $pdoCadastro
    );

$cadastroPublicoRateLimitMiddleware =
    new RateLimitMiddleware(
        $cadastroRateLimitService,
        $app->getResponseFactory(),
        'CADASTRO_PUBLICO',
        20,
        60 * 60,
        'email',
        5
    );

$confirmacaoEmailRateLimitMiddleware =
    new RateLimitMiddleware(
        $cadastroRateLimitService,
        $app->getResponseFactory(),
        'CADASTRO_CONFIRMAR_EMAIL',
        30,
        60 * 60
    );

$reenviarConfirmacaoRateLimitMiddleware =
    new RateLimitMiddleware(
        $cadastroRateLimitService,
        $app->getResponseFactory(),
        'CADASTRO_REENVIAR_EMAIL',
        20,
        60 * 60,
        'email',
        5
    );

/**
 * ============================================================
 * CADASTRO PÚBLICO
 * ============================================================
 *
 * NÃO utiliza AuthMiddleware.
 *
 * A rota cria somente uma SOLICITAÇÃO.
 * Ela não cria usuário nem permite login.
 */
$app->post(
    '/publico/cadastros',
    [
        $cadastroController,
        'solicitar',
    ]
)
    ->add(
        $cadastroPublicoRateLimitMiddleware
    );


/**
 * Confirma o endereço por um token de uso único.
 */
$app->post(
    '/publico/cadastros/confirmar-email',
    [
        $cadastroController,
        'confirmarEmail',
    ]
)
    ->add(
        $confirmacaoEmailRateLimitMiddleware
    );

/**
 * Reenvio do link.
 *
 * A resposta é genérica para não informar publicamente se existe uma
 * solicitação para o endereço.
 */
$app->post(
    '/publico/cadastros/reenviar-confirmacao',
    [
        $cadastroController,
        'reenviarConfirmacao',
    ]
)
    ->add(
        $reenviarConfirmacaoRateLimitMiddleware
    );

/**
 * ============================================================
 * FILA DE APROVAÇÃO
 * ============================================================
 *
 * Todas exigem login.
 *
 * A autorização fina é verificada pelo CadastroService:
 *
 * - ADMINISTRADOR: sempre pode;
 * - ORGANIZADOR: somente com CADASTROS_APROVAR;
 * - MEMBRO: não pode.
 */
$app->get(
    '/gestao/cadastros',
    [
        $cadastroController,
        'listar',
    ]
)
    ->add($authMiddleware);

$app->get(
    '/gestao/cadastros/{id:[0-9]+}',
    [
        $cadastroController,
        'obter',
    ]
)
    ->add($authMiddleware);

$app->patch(
    '/gestao/cadastros/{id:[0-9]+}/aprovar',
    [
        $cadastroController,
        'aprovar',
    ]
)
    ->add($authMiddleware);

$app->patch(
    '/gestao/cadastros/{id:[0-9]+}/rejeitar',
    [
        $cadastroController,
        'rejeitar',
    ]
)
    ->add($authMiddleware);
