<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Http\ApiErrorHandler;
use App\Logging\AppLogger;
use App\Middlewares\RequestContextMiddleware;
use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

/**
 * ============================================================
 * ENTRADA DA API SYN
 * ============================================================
 */

$dotenv =
    Dotenv::createImmutable(
        dirname(__DIR__)
    );

$dotenv->safeLoad();

$app =
    AppFactory::create();

/**
 * Permite JSON em Request::getParsedBody().
 */
$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();

/**
 * ============================================================
 * ROTAS TÉCNICAS BÁSICAS
 * ============================================================
 */

/**
 * Resposta simples da raiz.
 *
 * O health check completo da Etapa 108 está em:
 *
 * GET /health
 * GET /health/ready
 */
$app->get(
    '/',
    function (
        Request $request,
        Response $response
    ): Response {
        $response
            ->getBody()
            ->write(
                json_encode(
                    [
                        'sistema' =>
                            'SYN',
                        'status' =>
                            'ok',
                    ],
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                    | JSON_THROW_ON_ERROR
                )
            );

        return $response
            ->withHeader(
                'Content-Type',
                'application/json; charset=utf-8'
            )
            ->withHeader(
                'Cache-Control',
                'no-store'
            );
    }
);

/**
 * Rota diagnóstica antiga somente em development.
 *
 * Em produção use /health/ready.
 */
if (
    (
        $_ENV[
            'APP_ENV'
        ]
        ?? 'production'
    )
    === 'development'
) {
    $app->get(
        '/teste-banco',
        function (
            Request $request,
            Response $response
        ): Response {
            $pdo =
                Database::conectar();

            $resultado =
                $pdo
                    ->query(
                        'SELECT DATABASE() AS banco'
                    )
                    ->fetch();

            $response
                ->getBody()
                ->write(
                    json_encode(
                        [
                            'status' =>
                                'ok',
                            'banco' =>
                                $resultado[
                                    'banco'
                                ]
                                ?? null,
                        ],
                        JSON_UNESCAPED_UNICODE
                        | JSON_UNESCAPED_SLASHES
                        | JSON_THROW_ON_ERROR
                    )
                );

            return $response
                ->withHeader(
                    'Content-Type',
                    'application/json; charset=utf-8'
                )
                ->withHeader(
                    'Cache-Control',
                    'no-store'
                );
        }
    );
}

/**
 * Registra todas as rotas e os middlewares globais já existentes:
 * CORS, auditoria, autenticação por rota, etc.
 */
require __DIR__
    . '/../routes/routes.php';

/**
 * ============================================================
 * OBSERVABILIDADE / ERROS
 * ============================================================
 *
 * IMPORTANTE SOBRE A ORDEM:
 *
 * Slim executa middlewares na ordem inversa em que são adicionados.
 *
 * As rotas acima adicionam middlewares globais como CORS e Auditoria.
 * Por isso o ErrorMiddleware é adicionado DEPOIS das rotas:
 * ele fica por fora deles e pode capturar também falhas desses middlewares.
 */

$appDebug =
    filter_var(
        $_ENV[
            'APP_DEBUG'
        ]
        ?? false,
        FILTER_VALIDATE_BOOL
    );

$logger =
    new AppLogger(
        dirname(
            __DIR__
        )
        . '/storage/logs/syn.log'
    );

$errorMiddleware =
    $app
        ->addErrorMiddleware(
            displayErrorDetails:
                $appDebug,
            logErrors:
                false,
            logErrorDetails:
                false
        );

$errorMiddleware
    ->setDefaultErrorHandler(
        new ApiErrorHandler(
            $logger,
            $appDebug
        )
    );

/**
 * Este é adicionado por último para ser o middleware mais externo.
 *
 * Ele cria o request_id ANTES da requisição entrar no ErrorMiddleware.
 * Assim, até um erro 500 pode ser correlacionado com o log.
 */
$app->add(
    new RequestContextMiddleware(
        $logger
    )
);

$app->run();
