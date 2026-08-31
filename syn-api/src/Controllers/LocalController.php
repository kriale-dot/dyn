<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\LocalNaoEncontradoException;
use App\Services\LocalService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller HTTP do módulo de locais.
 */
final class LocalController
{
    public function __construct(
        private LocalService $localService
    ) {
    }

    /**
     * GET /locais
     */
    public function listar(
        Request $request,
        Response $response
    ): Response {
        $locais = $this->localService->listarTodos();

        return $this->json(
            $response,
            [
                'status' => 'ok',
                'total' => count($locais),
                'dados' => $locais,
            ],
            200
        );
    }

    /**
     * GET /locais/{id}
     *
     * @param array<string, string> $args
     */
    public function buscarPorId(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $local =
                $this->localService
                    ->buscarPorId((int) $args['id']);

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'dados' => $local,
                ],
                200
            );
        } catch (LocalNaoEncontradoException $e) {
            return $this->erroNaoEncontrado(
                $response,
                $e
            );
        }
    }

    /**
     * POST /locais
     */
    public function criar(
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
                        'Envie os dados do local em formato JSON.',
                ],
                400
            );
        }

        try {
            $local = $this->localService->criar($dados);

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Local cadastrado com sucesso.',
                    'dados' => $local,
                ],
                201
            );
        } catch (DadosInvalidosException $e) {
            return $this->erroValidacao(
                $response,
                $e
            );
        }
    }

    /**
     * PUT /locais/{id}
     *
     * @param array<string, string> $args
     */
    public function atualizar(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $dados = $request->getParsedBody();

        if (!is_array($dados)) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' =>
                        'Envie os dados do local em formato JSON.',
                ],
                400
            );
        }

        try {
            $local = $this->localService->atualizar(
                (int) $args['id'],
                $dados
            );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Local atualizado com sucesso.',
                    'dados' => $local,
                ],
                200
            );
        } catch (LocalNaoEncontradoException $e) {
            return $this->erroNaoEncontrado(
                $response,
                $e
            );
        } catch (DadosInvalidosException $e) {
            return $this->erroValidacao(
                $response,
                $e
            );
        }
    }

    /**
     * PATCH /locais/{id}/desativar
     *
     * @param array<string, string> $args
     */
    public function desativar(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $resultado =
                $this->localService
                    ->desativar((int) $args['id']);

            $mensagem =
                $resultado['ja_estava_inativo']
                    ? 'O local já estava inativo.'
                    : 'Local desativado com sucesso.';

            $resposta = [
                'status' => 'ok',
                'mensagem' => $mensagem,
                'dados' => $resultado,
            ];

            if (
                $resultado['possui_programacoes_futuras']
            ) {
                $resposta['alerta'] =
                    'O local possui programações futuras que precisam ser revisadas. Elas não foram canceladas automaticamente.';
            }

            return $this->json(
                $response,
                $resposta,
                200
            );
        } catch (LocalNaoEncontradoException $e) {
            return $this->erroNaoEncontrado(
                $response,
                $e
            );
        }
    }

    private function erroNaoEncontrado(
        Response $response,
        \Throwable $e
    ): Response {
        return $this->json(
            $response,
            [
                'status' => 'erro',
                'mensagem' => $e->getMessage(),
            ],
            404
        );
    }

    private function erroValidacao(
        Response $response,
        DadosInvalidosException $e
    ): Response {
        return $this->json(
            $response,
            [
                'status' => 'erro',
                'mensagem' => $e->getMessage(),
                'erros' => $e->getErros(),
            ],
            422
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
