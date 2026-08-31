<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\FuncaoNaoEncontradaException;
use App\Exceptions\TipoProgramacaoNaoEncontradoException;
use App\Services\TipoProgramacaoService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller HTTP de Tipos de Programação.
 */
final class TipoProgramacaoController
{
    public function __construct(
        private TipoProgramacaoService $service
    ) {
    }

    /**
     * GET /tipos-programacao
     */
    public function listar(
        Request $request,
        Response $response
    ): Response {
        $tipos = $this->service->listarTodos();

        return $this->json(
            $response,
            [
                'status' => 'ok',
                'total' => count($tipos),
                'dados' => $tipos,
            ],
            200
        );
    }

    /**
     * GET /tipos-programacao/{id}
     *
     * @param array<string, string> $args
     */
    public function buscarPorId(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'dados' =>
                        $this->service
                            ->buscarPorId(
                                (int) $args['id']
                            ),
                ],
                200
            );
        } catch (TipoProgramacaoNaoEncontradoException $e) {
            return $this->erroNaoEncontrado(
                $response,
                $e
            );
        }
    }

    /**
     * POST /tipos-programacao
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
                        'Envie os dados do tipo de programação em formato JSON.',
                ],
                400
            );
        }

        try {
            $tipo = $this->service->criar($dados);

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Tipo de programação cadastrado com sucesso.',
                    'dados' => $tipo,
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
     * PUT /tipos-programacao/{id}
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
                        'Envie os dados do tipo de programação em formato JSON.',
                ],
                400
            );
        }

        try {
            $tipo = $this->service->atualizar(
                (int) $args['id'],
                $dados
            );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Tipo de programação atualizado com sucesso.',
                    'dados' => $tipo,
                ],
                200
            );
        } catch (TipoProgramacaoNaoEncontradoException $e) {
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
     * PATCH /tipos-programacao/{id}/desativar
     *
     * @param array<string, string> $args
     */
    public function desativar(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $resultado = $this->service
                ->desativar((int) $args['id']);

            $mensagem =
                $resultado['ja_estava_inativo']
                    ? 'O tipo de programação já estava inativo.'
                    : 'Tipo de programação desativado com sucesso.';

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' => $mensagem,
                    'dados' => $resultado,
                ],
                200
            );
        } catch (TipoProgramacaoNaoEncontradoException $e) {
            return $this->erroNaoEncontrado(
                $response,
                $e
            );
        }
    }

    /**
     * POST /tipos-programacao/{tipoId}/funcoes/{funcaoId}
     *
     * @param array<string, string> $args
     */
    public function autorizarFuncao(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $resultado =
                $this->service->autorizarFuncao(
                    (int) $args['tipoId'],
                    (int) $args['funcaoId']
                );

            $statusCode =
                $resultado['ja_estava_autorizada']
                    ? 200
                    : 201;

            $mensagem =
                $resultado['ja_estava_autorizada']
                    ? 'A função já estava autorizada para este tipo de programação.'
                    : 'Função autorizada para o tipo de programação com sucesso.';

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' => $mensagem,
                    'dados' => $resultado,
                ],
                $statusCode
            );
        } catch (TipoProgramacaoNaoEncontradoException|FuncaoNaoEncontradaException $e) {
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
     * DELETE /tipos-programacao/{tipoId}/funcoes/{funcaoId}
     *
     * @param array<string, string> $args
     */
    public function removerAutorizacaoFuncao(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $resultado =
                $this->service
                    ->removerAutorizacaoFuncao(
                        (int) $args['tipoId'],
                        (int) $args['funcaoId']
                    );

            $mensagem =
                $resultado['funcao_estava_autorizada']
                    ? 'Autorização removida com sucesso.'
                    : 'A função já não estava autorizada para este tipo de programação.';

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' => $mensagem,
                    'dados' => $resultado,
                ],
                200
            );
        } catch (TipoProgramacaoNaoEncontradoException|FuncaoNaoEncontradaException $e) {
            return $this->erroNaoEncontrado(
                $response,
                $e
            );
        }
    }

    /**
     * GET /tipos-programacao/{id}/candidatos
     *
     * @param array<string, string> $args
     */
    public function listarCandidatos(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $candidatos =
                $this->service
                    ->listarCandidatos(
                        (int) $args['id']
                    );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'total' => count($candidatos),
                    'dados' => $candidatos,
                ],
                200
            );
        } catch (TipoProgramacaoNaoEncontradoException $e) {
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
