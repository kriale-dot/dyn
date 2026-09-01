<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\PermissaoOrganizadorController;
use App\Middlewares\EscopoOrganizadorMiddleware;
use App\Repositories\PermissaoOrganizadorRepository;
use App\Services\PermissaoOrganizadorService;

$pdoPermissaoOrganizador = Database::conectar();

$permissaoOrganizadorRepository =
    new PermissaoOrganizadorRepository($pdoPermissaoOrganizador);

$permissaoOrganizadorService =
    new PermissaoOrganizadorService($permissaoOrganizadorRepository);

$permissaoOrganizadorController =
    new PermissaoOrganizadorController($permissaoOrganizadorService);

$responseFactoryPermissao = $app->getResponseFactory();

$escopoTipoBodyMiddleware =
    new EscopoOrganizadorMiddleware(
        $permissaoOrganizadorService,
        $responseFactoryPermissao,
        'body'
    );

$escopoTipoRotaMiddleware =
    new EscopoOrganizadorMiddleware(
        $permissaoOrganizadorService,
        $responseFactoryPermissao,
        'tipo_rota'
    );

$escopoProgramacaoMiddleware =
    new EscopoOrganizadorMiddleware(
        $permissaoOrganizadorService,
        $responseFactoryPermissao,
        'programacao'
    );

$escopoParticipacaoMiddleware =
    new EscopoOrganizadorMiddleware(
        $permissaoOrganizadorService,
        $responseFactoryPermissao,
        'participacao'
    );

$escopoSerieMiddleware =
    new EscopoOrganizadorMiddleware(
        $permissaoOrganizadorService,
        $responseFactoryPermissao,
        'serie'
    );

$app->get(
    '/minhas-permissoes',
    [$permissaoOrganizadorController, 'minhasPermissoes']
)->add($authMiddleware);

$app->get(
    '/organizadores/{usuarioId:[0-9]+}/tipos-programacao',
    [$permissaoOrganizadorController, 'listar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->post(
    '/organizadores/{usuarioId:[0-9]+}/tipos-programacao/{tipoId:[0-9]+}',
    [$permissaoOrganizadorController, 'conceder']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->delete(
    '/organizadores/{usuarioId:[0-9]+}/tipos-programacao/{tipoId:[0-9]+}',
    [$permissaoOrganizadorController, 'revogar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);
