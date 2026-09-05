<?php

declare(strict_types=1);

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware CORS do SYN.
 *
 * Produção:
 * - aceita somente as origens explicitamente configuradas em
 *   CORS_ALLOWED_ORIGINS.
 *
 * Desenvolvimento:
 * - além das origens configuradas, aceita o frontend aberto em
 *   localhost ou em endereços IPv4 privados da rede local:
 *
 *     10.x.x.x
 *     172.16.x.x até 172.31.x.x
 *     192.168.x.x
 *
 * Isso permite testar o SYN em celular/tablet na mesma rede sem
 * precisar alterar o CORS cada vez que o IP do computador mudar.
 *
 * Como o SYN usa Bearer Token e não cookie de sessão, não ativamos
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
            rtrim(
                trim(
                    $request->getHeaderLine(
                        'Origin'
                    )
                ),
                '/'
            );

        /**
         * Postman, curl e navegação direta até a API normalmente
         * não enviam Origin.
         */
        if ($origem === '') {
            return $handler->handle(
                $request
            );
        }

        $origemPermitida =
            $this->origemPermitida(
                $origem
            );

        /**
         * Preflight OPTIONS de origem não autorizada.
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
             * Sem Access-Control-Allow-Origin o navegador bloqueia
             * a resposta. É justamente isso que aparecia no celular
             * como "Failed to fetch".
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
     * Decide se uma origem pode acessar a API.
     */
    private function origemPermitida(
        string $origem
    ): bool {
        if (
            in_array(
                $origem,
                $this->origensPermitidas,
                true
            )
        ) {
            return true;
        }

        /**
         * Em produção não existe liberação automática da LAN.
         */
        if (!$this->ambienteDesenvolvimento()) {
            return false;
        }

        return $this->origemRedeLocal(
            $origem
        );
    }

    /**
     * Verifica APP_ENV.
     */
    private function ambienteDesenvolvimento(): bool
    {
        $ambiente =
            strtolower(
                trim(
                    (string) (
                        $_ENV['APP_ENV']
                        ?? getenv('APP_ENV')
                        ?: 'production'
                    )
                )
            );

        return $ambiente
            === 'development';
    }

    /**
     * Aceita somente origens HTTP locais durante development.
     *
     * Exemplos aceitos:
     * http://localhost:5173
     * http://127.0.0.1:5173
     * http://192.168.15.8:5173
     * http://10.0.0.10:5173
     * http://172.20.0.5:5173
     */
    private function origemRedeLocal(
        string $origem
    ): bool {
        $partes =
            parse_url(
                $origem
            );

        if (!is_array($partes)) {
            return false;
        }

        $scheme =
            strtolower(
                (string) (
                    $partes['scheme']
                    ?? ''
                )
            );

        $host =
            strtolower(
                trim(
                    (string) (
                        $partes['host']
                        ?? ''
                    )
                )
            );

        if ($scheme !== 'http') {
            return false;
        }

        if (
            $host === 'localhost'
            || $host === '127.0.0.1'
        ) {
            return true;
        }

        if (
            filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV4
            ) === false
        ) {
            return false;
        }

        return $this->ipv4Privado(
            $host
        );
    }

    /**
     * Faixas privadas RFC1918.
     */
    private function ipv4Privado(
        string $ip
    ): bool {
        $octetos =
            array_map(
                'intval',
                explode(
                    '.',
                    $ip
                )
            );

        if (count($octetos) !== 4) {
            return false;
        }

        [$a, $b] = $octetos;

        if ($a === 10) {
            return true;
        }

        if (
            $a === 172
            && $b >= 16
            && $b <= 31
        ) {
            return true;
        }

        if (
            $a === 192
            && $b === 168
        ) {
            return true;
        }

        return false;
    }

    /**
     * Lê CORS_ALLOWED_ORIGINS.
     *
     * Exemplo:
     *
     * CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
     *
     * Em development, IPs privados da LAN também serão aceitos
     * automaticamente por origemRedeLocal().
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
