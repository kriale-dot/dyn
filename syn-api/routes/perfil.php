<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\PerfilController;
use App\Controllers\HistoricoSegurancaController;
use App\Controllers\AlteracaoEmailController;
use App\Repositories\PerfilRepository;
use App\Repositories\HistoricoSegurancaRepository;
use App\Repositories\AlteracaoEmailRepository;
use App\Services\PerfilService;
use App\Services\HistoricoSegurancaService;
use App\Services\AlteracaoEmailService;
use App\Services\EmailService;
use App\Services\RateLimitService;
use App\Middlewares\RateLimitMiddleware;

$pdo = Database::conectar();

$repository =
    new PerfilRepository($pdo);

$service =
    new PerfilService($repository);

$controller =
    new PerfilController($service);

$historicoSegurancaRepository =
    new HistoricoSegurancaRepository(
        $pdo
    );

$historicoSegurancaService =
    new HistoricoSegurancaService(
        $historicoSegurancaRepository
    );

$historicoSegurancaController =
    new HistoricoSegurancaController(
        $historicoSegurancaService
    );

$alteracaoEmailRepository =
    new AlteracaoEmailRepository(
        $pdo
    );

$alteracaoEmailService =
    new AlteracaoEmailService(
        $alteracaoEmailRepository,
        new EmailService()
    );

$alteracaoEmailController =
    new AlteracaoEmailController(
        $alteracaoEmailService
    );

$alteracaoEmailRateLimitService =
    new RateLimitService(
        $pdo
    );

$solicitarAlteracaoEmailRateLimit =
    new RateLimitMiddleware(
        $alteracaoEmailRateLimitService,
        $app->getResponseFactory(),
        'ALTERACAO_EMAIL_SOLICITAR',
        8,
        60 * 60
    );

$confirmarAlteracaoEmailRateLimit =
    new RateLimitMiddleware(
        $alteracaoEmailRateLimitService,
        $app->getResponseFactory(),
        'ALTERACAO_EMAIL_CONFIRMAR',
        30,
        60 * 60
    );

/**
 * Perfil do próprio usuário autenticado.
 */
$app->get(
    '/meu-perfil',
    [$controller, 'meuPerfil']
)->add($authMiddleware);


/**
 * Histórico do próprio usuário.
 *
 * Nunca expõe senha, token ou histórico de outros usuários.
 */
$app->get(
    '/meu-perfil/atividade-seguranca',
    [
        $historicoSegurancaController,
        'listar',
    ]
)
    ->add(
        $authMiddleware
    );

$app->put(
    '/meu-perfil',
    [$controller, 'atualizarMeuPerfil']
)->add($authMiddleware);


/**
 * Solicita a troca do e-mail da própria conta.
 *
 * Requer senha atual e um JWT válido.
 */
$app->post(
    '/meu-perfil/alterar-email',
    [
        $alteracaoEmailController,
        'solicitar',
    ]
)
    ->add(
        $solicitarAlteracaoEmailRateLimit
    )
    ->add(
        $authMiddleware
    );

/**
 * Confirmação pública feita pelo link enviado ao novo endereço.
 *
 * O token é de uso único e o novo e-mail ainda é verificado novamente
 * contra duplicidade no momento da confirmação.
 */
$app->post(
    '/publico/conta/confirmar-email',
    [
        $alteracaoEmailController,
        'confirmar',
    ]
)
    ->add(
        $confirmarAlteracaoEmailRateLimit
    );

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
