<?php

declare(strict_types=1);

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware CORS do SYN.
 *
 * Permite que o frontend React, executando em uma origem diferente
 * da API, faça requisições HTTP com segurança.
 *
 * Exemplo em desenvolvimento:
 *
 * Frontend:
 * http://localhost:5173
 *
 * API:
 * http://localhost:8282
 *
 * Como usamos Bearer Token e não cookies de sessão, não habilitamos
 * Access-Control-Allow-Credentials nesta versão.
 */
final class CorsMiddleware
{
    /**
     * @param array<int, string> $origensPermitidas
     */
    public function __construct(
        private array $origensPermitidas
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $origem =
            trim(
                $request->getHeaderLine(
                    'Origin'
                )
            );

        /**
         * Postman, curl e chamadas servidor-servidor normalmente
         * não enviam Origin. Elas continuam funcionando normalmente.
         */
        if ($origem === '') {
            return $handler->handle(
                $request
            );
        }

        $origemPermitida =
            in_array(
                $origem,
                $this->origensPermitidas,
                true
            );

        /**
         * Em uma requisição preflight (OPTIONS), uma origem não
         * autorizada recebe 403 antes de chegar às rotas de negócio.
         */
        if (
            strtoupper(
                $request->getMethod()
            ) === 'OPTIONS'
            && !$origemPermitida
        ) {
            $response =
                new \Slim\Psr7\Response(
                    403
                );

            $response
                ->getBody()
                ->write(
                    json_encode(
                        [
                            'status' => 'erro',
                            'mensagem' =>
                                'Origem não autorizada pelo CORS.',
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
                    'Vary',
                    'Origin'
                );
        }

        $response =
            $handler->handle(
                $request
            );

        if (!$origemPermitida) {
            /**
             * Para requisições normais, omitimos o cabeçalho
             * Access-Control-Allow-Origin. O navegador bloqueará
             * o acesso à resposta.
             */
            return $response
                ->withHeader(
                    'Vary',
                    'Origin'
                );
        }

        return $response
            ->withHeader(
                'Access-Control-Allow-Origin',
                $origem
            )
            ->withHeader(
                'Access-Control-Allow-Methods',
                'GET, POST, PUT, PATCH, DELETE, OPTIONS'
            )
            ->withHeader(
                'Access-Control-Allow-Headers',
                'Authorization, Content-Type, Accept'
            )
            ->withHeader(
                'Access-Control-Max-Age',
                '600'
            )
            ->withHeader(
                'Vary',
                'Origin'
            );
    }

    /**
     * Lê CORS_ALLOWED_ORIGINS.
     *
     * Formato:
     *
     * CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
     *
     * @return array<int, string>
     */
    public static function origensDoAmbiente(): array
    {
        $valor =
            (string) (
                $_ENV[
                    'CORS_ALLOWED_ORIGINS'
                ]
                ?? getenv(
                    'CORS_ALLOWED_ORIGINS'
                )
                ?: ''
            );

        if (trim($valor) === '') {
            /**
             * Defaults seguros apenas para desenvolvimento local.
             */
            return [
                'http://localhost:5173',
                'http://127.0.0.1:5173',
            ];
        }

        $origens =
            array_map(
                static fn (
                    string $item
                ): string =>
                    rtrim(
                        trim($item),
                        '/'
                    ),
                explode(
                    ',',
                    $valor
                )
            );

        return array_values(
            array_filter(
                array_unique(
                    $origens
                ),
                static fn (
                    string $item
                ): bool =>
                    $item !== ''
            )
        );
    }
}
