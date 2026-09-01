<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Services\RecuperacaoSenhaService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Endpoints públicos de recuperação de senha.
 */
final class RecuperacaoSenhaController
{
    public function __construct(
        private RecuperacaoSenhaService $service
    ) {
    }

    public function solicitar(
        Request $request,
        Response $response
    ): Response {
        $dados =
            $request->getParsedBody();

        if (!is_array($dados)) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' =>
                        'Envie os dados em formato JSON.',
                ],
                400
            );
        }

        try {
            $resultado =
                $this->service
                    ->solicitar(
                        $dados
                    );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        $resultado[
                            'mensagem_publica'
                        ],
                    'desenvolvimento' =>
                        $resultado[
                            'desenvolvimento'
                        ] ?? null,
                ],
                200
            );
        } catch (DadosInvalidosException $e) {
            return $this->validacao(
                $response,
                $e
            );
        }
    }

    public function redefinir(
        Request $request,
        Response $response
    ): Response {
        $dados =
            $request->getParsedBody();

        if (!is_array($dados)) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' =>
                        'Envie os dados em formato JSON.',
                ],
                400
            );
        }

        try {
            $resultado =
                $this->service
                    ->redefinir(
                        $dados
                    );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Senha redefinida com sucesso.',
                    'dados' =>
                        $resultado,
                ],
                200
            );
        } catch (DadosInvalidosException $e) {
            return $this->validacao(
                $response,
                $e
            );
        }
    }

    private function validacao(
        Response $response,
        DadosInvalidosException $e
    ): Response {
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
