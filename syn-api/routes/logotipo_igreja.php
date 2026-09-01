<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\LogotipoIgrejaController;
use App\Repositories\LogotipoIgrejaRepository;
use App\Services\LogotipoIgrejaService;

$pdoLogotipoIgreja =
    Database::conectar();

$logotipoIgrejaRepository =
    new LogotipoIgrejaRepository(
        $pdoLogotipoIgreja
    );

$diretorioLogotipoIgreja =
    dirname(__DIR__)
    . DIRECTORY_SEPARATOR
    . 'public'
    . DIRECTORY_SEPARATOR
    . 'uploads'
    . DIRECTORY_SEPARATOR
    . 'igreja';

$logotipoIgrejaService =
    new LogotipoIgrejaService(
        $logotipoIgrejaRepository,
        $diretorioLogotipoIgreja
    );

$logotipoIgrejaController =
    new LogotipoIgrejaController(
        $logotipoIgrejaService
    );

/**
 * Somente o Administrador altera a identidade institucional.
 */
$app->post(
    '/igreja/logotipo',
    [
        $logotipoIgrejaController,
        'salvar',
    ]
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->delete(
    '/igreja/logotipo',
    [
        $logotipoIgrejaController,
        'remover',
    ]
)
    ->add($adminMiddleware)
    ->add($authMiddleware);
