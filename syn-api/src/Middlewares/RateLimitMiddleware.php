<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Exceptions\RateLimitException;
use App\Services\RateLimitService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware de limitação de requisições.
 *
 * Existem dois níveis opcionais:
 *
 * 1. limite por IP;
 * 2. limite por IP + identificador do body (por exemplo, e-mail).
 *
 * Não confiamos automaticamente em X-Forwarded-For. Nesta etapa a
 * origem é REMOTE_ADDR. Quando o SYN ficar atrás de proxy reverso,
 * configuraremos proxies confiáveis explicitamente.
 */
final class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(
        private RateLimitService $service,
        private ResponseFactoryInterface $responseFactory,
        private string $acao,
        private int $limitePorIp,
        private int $janelaSegundos,
        private ?string $campoIdentidade = null,
        private ?int $limitePorIdentidade = null
    ) {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            $ip =
                $this->obterIp(
                    $request
                );

            /**
             * Protege contra "spray" de muitas contas diferentes.
             */
            $this->service
                ->consumir(
                    $this->acao
                    . ':IP',
                    'ip:' . $ip,
                    $this->limitePorIp,
                    $this->janelaSegundos
                );

            /**
             * Também limita uma mesma identidade quando configurada.
             *
             * O e-mail não é salvo puro: RateLimitService persiste
             * somente SHA-256 desta chave.
             */
            if (
                $this->campoIdentidade !== null
                && $this->limitePorIdentidade !== null
            ) {
                $dados =
                    $request
                        ->getParsedBody();

                $identidade =
                    is_array($dados)
                        ? trim(
                            mb_strtolower(
                                (string)
                                (
                                    $dados[
                                        $this
                                            ->campoIdentidade
                                    ]
                                    ?? ''
                                )
                            )
                        )
                        : '';

                if ($identidade !== '') {
                    $this->service
                        ->consumir(
                            $this->acao
                            . ':IDENTIDADE',
                            'ip:'
                            . $ip
                            . '|'
                            . $this->campoIdentidade
                            . ':'
                            . $identidade,
                            $this->limitePorIdentidade,
                            $this->janelaSegundos
                        );
                }
            }

            return $handler
                ->handle(
                    $request
                );
        } catch (RateLimitException $e) {
            $response =
                $this->responseFactory
                    ->createResponse(
                        429
                    );

            $payload = [
                'status' =>
                    'erro',
                'mensagem' =>
                    $e->getMessage(),
                'retry_after_segundos' =>
                    $e->getRetryAfterSeconds(),
            ];

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
                    'Retry-After',
                    (string)
                    $e->getRetryAfterSeconds()
                )
                ->withHeader(
                    'X-RateLimit-Limit',
                    (string)
                    $e->getLimit()
                );
        }
    }

    private function obterIp(
        ServerRequestInterface $request
    ): string {
        $server =
            $request
                ->getServerParams();

        $ip =
            trim(
                (string)
                (
                    $server[
                        'REMOTE_ADDR'
                    ]
                    ?? 'desconhecido'
                )
            );

        return $ip !== ''
            ? $ip
            : 'desconhecido';
    }
}
