<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\FuncaoController;
use App\Repositories\FuncaoRepository;
use App\Services\FuncaoService;

$pdo = Database::conectar();

$funcaoRepository = new FuncaoRepository($pdo);
$funcaoService = new FuncaoService($funcaoRepository);
$funcaoController = new FuncaoController($funcaoService);

/**
 * Consulta: Administrador e Organizador.
 */
$app->get(
    '/funcoes',
    [$funcaoController, 'listar']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->get(
    '/funcoes/{id:[0-9]+}',
    [$funcaoController, 'buscarPorId']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

/**
 * Cadastro/edição/desativação: Administrador.
 */
$app->post(
    '/funcoes',
    [$funcaoController, 'criar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->put(
    '/funcoes/{id:[0-9]+}',
    [$funcaoController, 'atualizar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->patch(
    '/funcoes/{id:[0-9]+}/desativar',
    [$funcaoController, 'desativar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

/**
 * Atribuição das funções ATUAIS do usuário:
 * responsabilidade administrativa nesta versão.
 */
$app->post(
    '/usuarios/{usuarioId:[0-9]+}/funcoes/{funcaoId:[0-9]+}',
    [$funcaoController, 'atribuirAoUsuario']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->delete(
    '/usuarios/{usuarioId:[0-9]+}/funcoes/{funcaoId:[0-9]+}',
    [$funcaoController, 'removerDoUsuario']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);
