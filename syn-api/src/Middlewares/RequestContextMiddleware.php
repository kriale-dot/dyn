<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Logging\AppLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * Contexto técnico de cada requisição.
 *
 * Gera um X-Request-ID e mede o tempo de resposta.
 *
 * O mesmo ID aparece:
 * - no cabeçalho da resposta;
 * - no log técnico;
 * - na resposta JSON de erro inesperado.
 *
 * Isso facilita descobrir qual erro corresponde a uma ação do usuário.
 */
final class RequestContextMiddleware
{
    public function __construct(
        private AppLogger $logger
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $requestId =
            $this->requestId(
                $request
            );

        $inicio =
            hrtime(
                true
            );

        $request =
            $request->withAttribute(
                'request_id',
                $requestId
            );

        try {
            $response =
                $handler->handle(
                    $request
                );

            $duracaoMs =
                $this->duracaoMs(
                    $inicio
                );

            $status =
                $response
                    ->getStatusCode();

            $this->registrarResposta(
                $request,
                $requestId,
                $status,
                $duracaoMs
            );

            return $response
                ->withHeader(
                    'X-Request-ID',
                    $requestId
                )
                ->withHeader(
                    'X-Response-Time-Ms',
                    number_format(
                        $duracaoMs,
                        2,
                        '.',
                        ''
                    )
                );
        } catch (Throwable $exception) {
            /*
             * Normalmente o ErrorMiddleware interno tratará a exceção.
             * Este catch é apenas uma última proteção para falhas que
             * escapem do pipeline.
             */
            $this->logger
                ->error(
                    'http_exception_unhandled',
                    array_merge(
                        $this->contextoRequest(
                            $request,
                            $requestId
                        ),
                        $this->logger
                            ->contextoExcecao(
                                $exception
                            )
                    )
                );

            throw $exception;
        }
    }

    private function requestId(
        ServerRequestInterface $request
    ): string {
        $recebido =
            trim(
                $request
                    ->getHeaderLine(
                        'X-Request-ID'
                    )
            );

        /*
         * Aceita um ID vindo de proxy/monitoramento apenas se for
         * curto e possuir caracteres seguros.
         */
        if (
            $recebido !== ''
            && preg_match(
                '/^[A-Za-z0-9._:-]{8,100}$/',
                $recebido
            )
        ) {
            return $recebido;
        }

        return bin2hex(
            random_bytes(16)
        );
    }

    private function duracaoMs(
        int $inicio
    ): float {
        return (
            hrtime(
                true
            )
            - $inicio
        ) / 1_000_000;
    }

    private function registrarResposta(
        ServerRequestInterface $request,
        string $requestId,
        int $status,
        float $duracaoMs
    ): void {
        $logarTodas =
            filter_var(
                $_ENV[
                    'LOG_HTTP_REQUESTS'
                ]
                ?? getenv(
                    'LOG_HTTP_REQUESTS'
                )
                ?: false,
                FILTER_VALIDATE_BOOL
            );

        if (
            !$logarTodas
            && $status < 400
        ) {
            return;
        }

        $contexto =
            $this->contextoRequest(
                $request,
                $requestId
            );

        $contexto[
            'http_status'
        ] = $status;

        $contexto[
            'duracao_ms'
        ] = round(
            $duracaoMs,
            2
        );

        if ($status >= 500) {
            $this->logger
                ->error(
                    'http_response',
                    $contexto
                );

            return;
        }

        if ($status >= 400) {
            $this->logger
                ->warning(
                    'http_response',
                    $contexto
                );

            return;
        }

        $this->logger
            ->info(
                'http_response',
                $contexto
            );
    }

    /**
     * @return array<string, mixed>
     */
    private function contextoRequest(
        ServerRequestInterface $request,
        string $requestId
    ): array {
        return [
            'request_id' =>
                $requestId,
            'metodo' =>
                strtoupper(
                    $request->getMethod()
                ),
            'caminho' =>
                $request
                    ->getUri()
                    ->getPath(),
            'ip' =>
                $request
                    ->getServerParams()[
                        'REMOTE_ADDR'
                    ]
                ?? null,
            'user_agent' =>
                mb_substr(
                    $request
                        ->getHeaderLine(
                            'User-Agent'
                        ),
                    0,
                    500
                ),
        ];
    }
}
