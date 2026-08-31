<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AutenticacaoException;
use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller HTTP da autenticação.
 */
final class AuthController
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    /**
     * POST /auth/login
     */
    public function login(
        Request $request,
        Response $response
    ): Response {
        $dados = $request->getParsedBody();

        if (!is_array($dados)) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' =>
                        'Envie e-mail e senha em formato JSON.',
                ],
                400
            );
        }

        try {
            $resultado =
                $this->authService->login(
                    (string) (
                        $dados['email']
                        ?? ''
                    ),
                    (string) (
                        $dados['senha']
                        ?? ''
                    )
                );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Login realizado com sucesso.',
                    'dados' => $resultado,
                ],
                200
            );
        } catch (AutenticacaoException $e) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' =>
                        $e->getMessage(),
                ],
                401
            );
        }
    }

    /**
     * GET /auth/me
     *
     * AuthMiddleware já validou o token e adicionou
     * o usuário atual ao Request.
     */
    public function me(
        Request $request,
        Response $response
    ): Response {
        $auth =
            $request->getAttribute('auth');

        return $this->json(
            $response,
            [
                'status' => 'ok',
                'dados' => $auth,
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
        $json = json_encode(
            $dados,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_THROW_ON_ERROR
        );

        $response->getBody()->write($json);

        return $response
            ->withHeader(
                'Content-Type',
                'application/json; charset=utf-8'
            )
            ->withStatus($statusCode);
    }
}
