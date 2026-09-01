<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\RecuperacaoSenhaController;
use App\Repositories\RecuperacaoSenhaRepository;
use App\Services\RecuperacaoSenhaService;

$pdoRecuperacaoSenha =
    Database::conectar();

$recuperacaoSenhaRepository =
    new RecuperacaoSenhaRepository(
        $pdoRecuperacaoSenha
    );

$recuperacaoSenhaService =
    new RecuperacaoSenhaService(
        $recuperacaoSenhaRepository
    );

$recuperacaoSenhaController =
    new RecuperacaoSenhaController(
        $recuperacaoSenhaService
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
);

$app->post(
    '/auth/redefinir-senha',
    [
        $recuperacaoSenhaController,
        'redefinir',
    ]
);
