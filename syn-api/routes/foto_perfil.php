<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\FotoPerfilController;
use App\Repositories\FotoPerfilRepository;
use App\Services\FotoPerfilService;

$pdoFotoPerfil =
    Database::conectar();

$fotoPerfilRepository =
    new FotoPerfilRepository(
        $pdoFotoPerfil
    );

/**
 * routes/ está na raiz do projeto.
 *
 * dirname(__DIR__) = raiz do syn-api
 */
$diretorioFotosPerfil =
    dirname(__DIR__)
    . DIRECTORY_SEPARATOR
    . 'public'
    . DIRECTORY_SEPARATOR
    . 'uploads'
    . DIRECTORY_SEPARATOR
    . 'perfis';

$fotoPerfilService =
    new FotoPerfilService(
        $fotoPerfilRepository,
        $diretorioFotosPerfil
    );

$fotoPerfilController =
    new FotoPerfilController(
        $fotoPerfilService
    );

/**
 * O usuário só altera a PRÓPRIA foto.
 * O ID é obtido do usuário autenticado.
 */
$app->post(
    '/meu-perfil/foto',
    [$fotoPerfilController, 'salvar']
)->add($authMiddleware);

$app->delete(
    '/meu-perfil/foto',
    [$fotoPerfilController, 'remover']
)->add($authMiddleware);
