<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AutenticacaoException;
use App\Services\SegurancaContaService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller das operações de segurança do próprio usuário.
 */
final class SegurancaContaController
{
    public function __construct(
        private SegurancaContaService $service
    ) {
    }

    public function alterarSenha(
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

        $dados =
            $request
                ->getParsedBody();

        if (!is_array($dados)) {
            $dados = [];
        }

        try {
            $resultado =
                $this->service
                    ->alterarSenha(
                        (int)
                        ($auth['id'] ?? 0),
                        $dados
                    );

            return $this->json(
                $response,
                [
                    'status' =>
                        'ok',
                    'dados' =>
                        $resultado,
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
