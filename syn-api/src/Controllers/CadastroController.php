<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\CadastroException;
use App\Services\CadastroService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller do cadastro público e da fila de aprovação.
 */
final class CadastroController
{
    public function __construct(
        private CadastroService $service
    ) {
    }

    public function solicitar(
        Request $request,
        Response $response
    ): Response {
        try {
            $dados =
                $request
                    ->getParsedBody();

            if (!is_array($dados)) {
                $dados = [];
            }

            return $this->json(
                $response,
                [
                    'status' =>
                        'ok',
                    'dados' =>
                        $this->service
                            ->solicitar(
                                $dados
                            ),
                ],
                201
            );
        } catch (
            CadastroException $e
        ) {
            return $this
                ->erro(
                    $response,
                    $e
                );
        }
    }

    public function confirmarEmail(
        Request $request,
        Response $response
    ): Response {
        try {
            $dados =
                $request
                    ->getParsedBody();

            if (!is_array($dados)) {
                $dados = [];
            }

            return $this->json(
                $response,
                [
                    'status' =>
                        'ok',
                    'dados' =>
                        $this->service
                            ->confirmarEmail(
                                $dados
                            ),
                ],
                200
            );
        } catch (
            CadastroException $e
        ) {
            return $this
                ->erro(
                    $response,
                    $e
                );
        }
    }

    public function reenviarConfirmacao(
        Request $request,
        Response $response
    ): Response {
        try {
            $dados =
                $request
                    ->getParsedBody();

            if (!is_array($dados)) {
                $dados = [];
            }

            return $this->json(
                $response,
                [
                    'status' =>
                        'ok',
                    'dados' =>
                        $this->service
                            ->reenviarConfirmacao(
                                $dados
                            ),
                ],
                200
            );
        } catch (
            CadastroException $e
        ) {
            return $this
                ->erro(
                    $response,
                    $e
                );
        }
    }

    public function listar(
        Request $request,
        Response $response
    ): Response {
        try {
            $auth =
                $this->auth(
                    $request
                );

            $query =
                $request
                    ->getQueryParams();

            $dados =
                $this->service
                    ->listar(
                        $auth,
                        isset(
                            $query['status']
                        )
                            ? (string)
                            $query['status']
                            : null
                    );

            return $this->json(
                $response,
                [
                    'status' =>
                        'ok',
                    'dados' =>
                        $dados,
                ],
                200
            );
        } catch (
            CadastroException $e
        ) {
            return $this
                ->erro(
                    $response,
                    $e
                );
        }
    }

    public function obter(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $auth =
                $this->auth(
                    $request
                );

            $id =
                $this->id(
                    $args
                );

            return $this->json(
                $response,
                [
                    'status' =>
                        'ok',
                    'dados' =>
                        $this->service
                            ->obter(
                                $auth,
                                $id
                            ),
                ],
                200
            );
        } catch (
            CadastroException $e
        ) {
            return $this
                ->erro(
                    $response,
                    $e
                );
        }
    }

    public function aprovar(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $auth =
                $this->auth(
                    $request
                );

            $id =
                $this->id(
                    $args
                );

            return $this->json(
                $response,
                [
                    'status' =>
                        'ok',
                    'dados' =>
                        $this->service
                            ->aprovar(
                                $auth,
                                $id
                            ),
                ],
                200
            );
        } catch (
            CadastroException $e
        ) {
            return $this
                ->erro(
                    $response,
                    $e
                );
        }
    }

    public function rejeitar(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $auth =
                $this->auth(
                    $request
                );

            $id =
                $this->id(
                    $args
                );

            $dados =
                $request
                    ->getParsedBody();

            if (!is_array($dados)) {
                $dados = [];
            }

            return $this->json(
                $response,
                [
                    'status' =>
                        'ok',
                    'dados' =>
                        $this->service
                            ->rejeitar(
                                $auth,
                                $id,
                                $dados
                            ),
                ],
                200
            );
        } catch (
            CadastroException $e
        ) {
            return $this
                ->erro(
                    $response,
                    $e
                );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function auth(
        Request $request
    ): array {
        $auth =
            $request
                ->getAttribute(
                    'auth'
                );

        if (!is_array($auth)) {
            throw new CadastroException(
                'Usuário não autenticado.',
                401
            );
        }

        return $auth;
    }

    /**
     * @param array<string, mixed> $args
     */
    private function id(
        array $args
    ): int {
        $id =
            filter_var(
                $args['id']
                ?? null,
                FILTER_VALIDATE_INT,
                [
                    'options' => [
                        'min_range' => 1,
                    ],
                ]
            );

        if ($id === false) {
            throw new CadastroException(
                'ID de cadastro inválido.',
                422
            );
        }

        return (int) $id;
    }

    private function erro(
        Response $response,
        CadastroException $e
    ): Response {
        return $this->json(
            $response,
            [
                'status' =>
                    'erro',
                'mensagem' =>
                    $e->getMessage(),
            ],
            $e->getStatusCode()
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
