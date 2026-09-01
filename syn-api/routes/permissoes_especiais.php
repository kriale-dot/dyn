<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\PermissaoEspecialController;
use App\Middlewares\PermissaoEspecialMiddleware;
use App\Repositories\PermissaoEspecialRepository;
use App\Services\PermissaoEspecialService;

$pdoPermissaoEspecial = Database::conectar();

$permissaoEspecialRepository =
    new PermissaoEspecialRepository($pdoPermissaoEspecial);

$permissaoEspecialService =
    new PermissaoEspecialService($permissaoEspecialRepository);

$permissaoEspecialController =
    new PermissaoEspecialController($permissaoEspecialService);

$necessidadesEspecificasMiddleware =
    new PermissaoEspecialMiddleware(
        $permissaoEspecialService,
        $app->getResponseFactory(),
        'NECESSIDADES_ESPECIFICAS_GERENCIAR'
    );

$app->get(
    '/permissoes-especiais',
    [$permissaoEspecialController, 'listarCatalogo']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->get(
    '/usuarios/{usuarioId:[0-9]+}/permissoes-especiais',
    [$permissaoEspecialController, 'listarDoUsuario']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->post(
    '/usuarios/{usuarioId:[0-9]+}/permissoes-especiais/{permissaoId:[0-9]+}',
    [$permissaoEspecialController, 'conceder']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);

$app->delete(
    '/usuarios/{usuarioId:[0-9]+}/permissoes-especiais/{permissaoId:[0-9]+}',
    [$permissaoEspecialController, 'revogar']
)
    ->add($adminMiddleware)
    ->add($authMiddleware);
