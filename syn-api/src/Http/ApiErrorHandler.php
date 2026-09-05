<?php

declare(strict_types=1);

namespace App\Http;

use App\Logging\AppLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;
use Slim\Psr7\Response;
use Throwable;

/**
 * Tratador JSON central de exceções HTTP da API SYN.
 *
 * Em produção:
 * - não expõe stack trace;
 * - não expõe SQL;
 * - não expõe caminho interno de arquivos;
 * - retorna um request_id para suporte técnico.
 *
 * Em development:
 * - acrescenta detalhes úteis para depuração.
 */
final class ApiErrorHandler
{
    public function __construct(
        private AppLogger $logger,
        private bool $displayErrorDetails
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        $requestId =
            (string) (
                $request
                    ->getAttribute(
                        'request_id'
                    )
                ?: bin2hex(
                    random_bytes(16)
                )
            );

        $statusCode =
            $this->statusCode(
                $exception
            );

        $mensagem =
            $this->mensagemPublica(
                $exception,
                $statusCode
            );

        $contexto = [
            'request_id' =>
                $requestId,
            'metodo' =>
                strtoupper(
                    $request
                        ->getMethod()
                ),
            'caminho' =>
                $request
                    ->getUri()
                    ->getPath(),
            'http_status' =>
                $statusCode,
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

        $contexto =
            array_merge(
                $contexto,
                $this->logger
                    ->contextoExcecao(
                        $exception
                    )
            );

        if ($statusCode >= 500) {
            $this->logger
                ->error(
                    'api_exception',
                    $contexto
                );
        } else {
            $this->logger
                ->warning(
                    'api_http_exception',
                    $contexto
                );
        }

        $payload = [
            'status' =>
                'erro',
            'mensagem' =>
                $mensagem,
            'request_id' =>
                $requestId,
        ];

        /*
         * Somente em development.
         *
         * Mesmo aqui não incluímos Authorization nem corpo da requisição.
         */
        if (
            $this->displayErrorDetails
            || $displayErrorDetails
        ) {
            $payload[
                'debug'
            ] = [
                'tipo' =>
                    $exception::class,
                'mensagem' =>
                    $exception
                        ->getMessage(),
                'arquivo' =>
                    $exception
                        ->getFile(),
                'linha' =>
                    $exception
                        ->getLine(),
            ];
        }

        $response =
            new Response(
                $statusCode
            );

        $response
            ->getBody()
            ->write(
                json_encode(
                    $payload,
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
            )
            ->withHeader(
                'X-Request-ID',
                $requestId
            );
    }

    private function statusCode(
        Throwable $exception
    ): int {
        if (
            $exception
            instanceof HttpException
        ) {
            $codigo =
                $exception
                    ->getCode();

            if (
                is_int(
                    $codigo
                )
                && $codigo >= 400
                && $codigo <= 599
            ) {
                return $codigo;
            }
        }

        return 500;
    }

    private function mensagemPublica(
        Throwable $exception,
        int $statusCode
    ): string {
        return match (
            $statusCode
        ) {
            400 =>
                'Requisição inválida.',
            401 =>
                'Não autenticado.',
            403 =>
                'Acesso não autorizado.',
            404 =>
                'Rota não encontrada.',
            405 =>
                'Método HTTP não permitido.',
            409 =>
                'A operação possui um conflito.',
            422 =>
                'Os dados enviados não puderam ser processados.',
            429 =>
                'Muitas requisições. Tente novamente mais tarde.',
            default =>
                $statusCode >= 500
                    ? 'Ocorreu um erro interno no SYN.'
                    : (
                        trim(
                            $exception
                                ->getMessage()
                        ) !== ''
                            ? $exception
                                ->getMessage()
                            : 'Não foi possível concluir a solicitação.'
                    ),
        };
    }
}
