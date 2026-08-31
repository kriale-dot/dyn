<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
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

$app = AppFactory::create();

/**
 * Permite JSON em Request::getParsedBody().
 */
$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();

/**
 * Em desenvolvimento mostramos detalhes.
 * Em produção APP_DEBUG deve ser false.
 */
$appDebug =
    filter_var(
        $_ENV['APP_DEBUG'] ?? false,
        FILTER_VALIDATE_BOOL
    );

$app->addErrorMiddleware(
    displayErrorDetails: $appDebug,
    logErrors: true,
    logErrorDetails: true
);

/**
 * Health check público.
 */
$app->get(
    '/',
    function (
        Request $request,
        Response $response
    ): Response {
        $response->getBody()->write(
            json_encode(
                [
                    'sistema' => 'SYN',
                    'status' => 'ok',
                ],
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
            )
        );

        return $response->withHeader(
            'Content-Type',
            'application/json; charset=utf-8'
        );
    }
);

/**
 * Rota diagnóstica somente em development.
 *
 * Não deve ficar exposta em produção.
 */
if (
    ($_ENV['APP_ENV'] ?? 'production')
    === 'development'
) {
    $app->get(
        '/teste-banco',
        function (
            Request $request,
            Response $response
        ): Response {
            $pdo = Database::conectar();

            $resultado =
                $pdo
                    ->query(
                        'SELECT DATABASE() AS banco'
                    )
                    ->fetch();

            $response->getBody()->write(
                json_encode(
                    [
                        'status' => 'ok',
                        'banco' =>
                            $resultado[
                                'banco'
                            ] ?? null,
                    ],
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                    | JSON_THROW_ON_ERROR
                )
            );

            return $response->withHeader(
                'Content-Type',
                'application/json; charset=utf-8'
            );
        }
    );
}

require __DIR__ . '/../routes/routes.php';

$app->run();
