<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Services\AppBootstrapService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller que entrega o estado inicial da aplicação.
 */
final class AppBootstrapController
{
    public function __construct(
        private AppBootstrapService $service
    ) {
    }

    public function index(
        Request $request,
        Response $response
    ): Response {
        try {
            $dados =
                $this->service
                    ->obter(
                        $this->usuarioAutenticadoId(
                            $request
                        )
                    );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'dados' => $dados,
                ],
                200
            );
        } catch (
            DadosInvalidosException $e
        ) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' =>
                        $e->getMessage(),
                    'erros' =>
                        $e->getErros(),
                ],
                422
            );
        }
    }

    private function usuarioAutenticadoId(
        Request $request
    ): int {
        $auth =
            $request->getAttribute(
                'auth'
            );

        return is_array($auth)
            ? (int) (
                $auth['id']
                ?? 0
            )
            : 0;
    }

    /**
     * @param array<string, mixed> $dados
     */
    private function json(
        Response $response,
        array $dados,
        int $statusCode
    ): Response {
        $response->getBody()->write(
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
