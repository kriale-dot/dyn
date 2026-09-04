<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\HistoricoSegurancaService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Endpoint do histórico de segurança do próprio usuário.
 */
final class HistoricoSegurancaController
{
    public function __construct(
        private HistoricoSegurancaService $service
    ) {
    }

    public function listar(
        Request $request,
        Response $response
    ): Response {
        $auth =
            $request
                ->getAttribute(
                    'auth'
                );

        if (!is_array($auth)) {
            return $this->json(
                $response,
                [
                    'status' =>
                        'erro',
                    'mensagem' =>
                        'Usuário não autenticado.',
                ],
                401
            );
        }

        $query =
            $request
                ->getQueryParams();

        $limite =
            isset(
                $query['limite']
            )
                ? (int)
                $query['limite']
                : 20;

        return $this->json(
            $response,
            [
                'status' =>
                    'ok',
                'dados' =>
                    $this->service
                        ->listar(
                            (int)
                            ($auth['id'] ?? 0),
                            $limite
                        ),
            ],
            200
        );
    }

    /**
     * @param array<string, mixed> $dados
     */
    private function json(
        Response $response,
        array $dados,
        int $statusCode
    ): Response {
        $response
            ->getBody()
            ->write(
                json_encode(
                    $dados,
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
            ->withStatus(
                $statusCode
            );
    }
}
