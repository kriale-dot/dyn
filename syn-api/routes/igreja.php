<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\IgrejaController;
use App\Repositories\IgrejaRepository;
use App\Services\IgrejaService;

$pdo = Database::conectar();

$igrejaRepository = new IgrejaRepository($pdo);
$igrejaService = new IgrejaService($igrejaRepository);
$igrejaController = new IgrejaController($igrejaService);

/**
 * Qualquer usuário autenticado pode consultar
 * os dados institucionais.
 */
$app->get(
    '/igreja',
    [$igrejaController, 'buscar']
)->add($authMiddleware);

/**
 * Somente Administrador altera o cadastro institucional.
 *
 * Como Slim executa middlewares em ordem LIFO:
 * auth é adicionado por último para executar primeiro.
 */
$app->put(
    '/igreja',
    [$igrejaController, 'atualizar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);
