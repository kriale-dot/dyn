<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\UsuarioController;
use App\Repositories\UsuarioRepository;
use App\Services\UsuarioService;

$pdo = Database::conectar();

$usuarioRepository = new UsuarioRepository($pdo);
$usuarioService = new UsuarioService($usuarioRepository);
$usuarioController = new UsuarioController($usuarioService);

/**
 * Minha Semana do usuário autenticado.
 *
 * Não existe mais necessidade de o frontend escolher um ID.
 */
$app->get(
    '/minha-semana',
    [$usuarioController, 'minhaSemanaAutenticada']
)->add($authMiddleware);

/**
 * Administrador e Organizador consultam pessoas.
 */
$app->get(
    '/usuarios',
    [$usuarioController, 'listar']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

$app->get(
    '/usuarios/{id:[0-9]+}',
    [$usuarioController, 'buscarPorId']
)
    ->add($adminOrganizadorMiddleware)
    ->add($authMiddleware);

/**
 * Gestão de cadastro e papel é administrativa.
 */
$app->post(
    '/usuarios',
    [$usuarioController, 'criar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->put(
    '/usuarios/{id:[0-9]+}',
    [$usuarioController, 'atualizar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->patch(
    '/usuarios/{id:[0-9]+}/desativar',
    [$usuarioController, 'desativar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);
