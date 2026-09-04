<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AutenticacaoException;
use App\Services\AlteracaoEmailService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller da alteração segura do e-mail de login.
 */
final class AlteracaoEmailController
{
    public function __construct(
        private AlteracaoEmailService $service
    ) {
    }

    public function solicitar(
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
                    'status' => 'erro',
                    'mensagem' =>
                        'Usuário não autenticado.',
                ],
                401
            );
        }

        $dados =
            $request
                ->getParsedBody();

        if (!is_array($dados)) {
            $dados = [];
        }

        try {
            return $this->json(
                $response,
                [
                    'status' =>
                        'ok',
                    'dados' =>
                        $this->service
                            ->solicitar(
                                (int)
                                ($auth['id'] ?? 0),
                                $dados
                            ),
                ],
                200
            );
        } catch (
            AutenticacaoException $e
        ) {
            return $this->json(
                $response,
                [
                    'status' =>
                        'erro',
                    'mensagem' =>
                        $e->getMessage(),
                ],
                422
            );
        }
    }

    public function confirmar(
        Request $request,
        Response $response
    ): Response {
        $dados =
            $request
                ->getParsedBody();

        if (!is_array($dados)) {
            $dados = [];
        }

        try {
            return $this->json(
                $response,
                [
                    'status' =>
                        'ok',
                    'dados' =>
                        $this->service
                            ->confirmar(
                                $dados
                            ),
                ],
                200
            );
        } catch (
            AutenticacaoException $e
        ) {
            return $this->json(
                $response,
                [
                    'status' =>
                        'erro',
                    'mensagem' =>
                        $e->getMessage(),
                ],
                422
            );
        }
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
