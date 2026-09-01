<?php

declare(strict_types=1);

use App\Middlewares\CorsMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Responde ao preflight do navegador.
 *
 * O CorsMiddleware acrescentará os cabeçalhos apropriados.
 */
$responderOptions =
    static function (
        Request $request,
        Response $response
    ): Response {
        return $response
            ->withStatus(
                204
            );
    };

$app->options(
    '/',
    $responderOptions
);

$app->options(
    '/{routes:.+}',
    $responderOptions
);

/**
 * Middleware global CORS.
 *
 * Origens permitidas são lidas do .env.
 */
$corsMiddleware =
    new CorsMiddleware(
        CorsMiddleware::origensDoAmbiente()
    );

$app->add(
    $corsMiddleware
);
