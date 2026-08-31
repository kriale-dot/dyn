<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\DepartamentoNaoEncontradoException;
use App\Services\DepartamentoService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller HTTP do módulo de departamentos.
 */
final class DepartamentoController
{
    public function __construct(
        private DepartamentoService $departamentoService
    ) {
    }

    /**
     * GET /departamentos
     */
    public function listar(
        Request $request,
        Response $response
    ): Response {
        $departamentos =
            $this->departamentoService->listarTodos();

        return $this->json(
            $response,
            [
                'status' => 'ok',
                'total' => count($departamentos),
                'dados' => $departamentos,
            ],
            200
        );
    }

    /**
     * GET /departamentos/{id}
     *
     * @param array<string, string> $args
     */
    public function buscarPorId(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $departamento =
                $this->departamentoService
                    ->buscarPorId((int) $args['id']);

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'dados' => $departamento,
                ],
                200
            );
        } catch (DepartamentoNaoEncontradoException $e) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' => $e->getMessage(),
                ],
                404
            );
        }
    }

    /**
     * POST /departamentos
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
                        'Envie os dados do departamento em formato JSON.',
                ],
                400
            );
        }

        try {
            $departamento =
                $this->departamentoService->criar($dados);

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Departamento cadastrado com sucesso.',
                    'dados' => $departamento,
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
     * PUT /departamentos/{id}
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
                        'Envie os dados do departamento em formato JSON.',
                ],
                400
            );
        }

        try {
            $departamento =
                $this->departamentoService->atualizar(
                    (int) $args['id'],
                    $dados
                );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Departamento atualizado com sucesso.',
                    'dados' => $departamento,
                ],
                200
            );
        } catch (DepartamentoNaoEncontradoException $e) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' => $e->getMessage(),
                ],
                404
            );
        } catch (DadosInvalidosException $e) {
            return $this->erroValidacao(
                $response,
                $e
            );
        }
    }

    /**
     * PATCH /departamentos/{id}/desativar
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
                $this->departamentoService
                    ->desativar((int) $args['id']);

            $mensagem =
                $resultado['ja_estava_inativo']
                    ? 'O departamento já estava inativo.'
                    : 'Departamento desativado com sucesso.';

            $resposta = [
                'status' => 'ok',
                'mensagem' => $mensagem,
                'dados' => $resultado,
            ];

            if ($resultado['possui_funcoes_ativas']) {
                $resposta['alerta'] =
                    'O departamento possui funções ativas relacionadas. Elas não foram desativadas automaticamente.';
            }

            return $this->json(
                $response,
                $resposta,
                200
            );
        } catch (DepartamentoNaoEncontradoException $e) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' => $e->getMessage(),
                ],
                404
            );
        }
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
