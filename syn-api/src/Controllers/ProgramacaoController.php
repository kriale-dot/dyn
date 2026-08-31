<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ConflitoLocalException;
use App\Exceptions\DadosInvalidosException;
use App\Exceptions\ProgramacaoNaoEncontradaException;
use App\Services\ProgramacaoService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller HTTP do módulo de programações.
 */
final class ProgramacaoController
{
    public function __construct(
        private ProgramacaoService $service
    ) {
    }

    public function listar(
        Request $request,
        Response $response
    ): Response {
        $programacoes =
            $this->service->listarTodas();

        return $this->json(
            $response,
            [
                'status' => 'ok',
                'total' =>
                    count($programacoes),
                'dados' =>
                    $programacoes,
            ],
            200
        );
    }

    /**
     * @param array<string, string> $args
     */
    public function buscarPorId(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $programacao =
                $this->service
                    ->buscarPorId(
                        (int) $args['id']
                    );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'dados' =>
                        $programacao,
                ],
                200
            );
        } catch (ProgramacaoNaoEncontradaException $e) {
            return $this->erroNaoEncontrado(
                $response,
                $e
            );
        }
    }

    public function criar(
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
                        'Envie os dados da programação em formato JSON.',
                ],
                400
            );
        }

        try {
            $resultado =
                $this->service
                    ->criar($dados);

            $resposta = [
                'status' => 'ok',
                'mensagem' =>
                    'Programação criada com sucesso.',
                'dados' => $resultado,
            ];

            if (
                $resultado[
                    'conflito_confirmado'
                ]
            ) {
                $resposta['alerta'] =
                    'A programação foi criada após confirmação explícita de conflito de local.';
            }

            return $this->json(
                $response,
                $resposta,
                201
            );
        } catch (ConflitoLocalException $e) {
            return $this->erroConflito(
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
     * @param array<string, string> $args
     */
    public function atualizar(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $dados =
            $request->getParsedBody();

        if (!is_array($dados)) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' =>
                        'Envie os dados da programação em formato JSON.',
                ],
                400
            );
        }

        try {
            $resultado =
                $this->service->atualizar(
                    (int) $args['id'],
                    $dados
                );

            $resposta = [
                'status' => 'ok',
                'mensagem' =>
                    'Programação atualizada com sucesso.',
                'dados' => $resultado,
            ];

            if (
                $resultado[
                    'conflito_confirmado'
                ]
            ) {
                $resposta['alerta'] =
                    'A alteração foi salva após confirmação explícita de conflito de local.';
            }

            return $this->json(
                $response,
                $resposta,
                200
            );
        } catch (ProgramacaoNaoEncontradaException $e) {
            return $this->erroNaoEncontrado(
                $response,
                $e
            );
        } catch (ConflitoLocalException $e) {
            return $this->erroConflito(
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
     * @param array<string, string> $args
     */
    public function cancelar(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $dados =
            $request->getParsedBody();

        if (!is_array($dados)) {
            $dados = [];
        }

        try {
            $resultado =
                $this->service->cancelar(
                    (int) $args['id'],
                    $dados
                );

            $mensagem =
                $resultado[
                    'ja_estava_cancelada'
                ]
                    ? 'A programação já estava cancelada.'
                    : 'Programação cancelada com sucesso.';

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        $mensagem,
                    'dados' =>
                        $resultado,
                ],
                200
            );
        } catch (ProgramacaoNaoEncontradaException $e) {
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
     * PATCH /programacoes/{id}/realizar
     *
     * @param array<string, string> $args
     */
    public function realizar(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $resultado =
                $this->service
                    ->realizar(
                        (int) $args['id']
                    );

            $mensagem =
                $resultado[
                    'ja_estava_realizada'
                ]
                    ? 'A programação já estava realizada.'
                    : 'Programação marcada como realizada com sucesso.';

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        $mensagem,
                    'dados' =>
                        $resultado,
                ],
                200
            );
        } catch (ProgramacaoNaoEncontradaException $e) {
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
                'mensagem' =>
                    $e->getMessage(),
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
                'mensagem' =>
                    $e->getMessage(),
                'erros' =>
                    $e->getErros(),
            ],
            422
        );
    }

    private function erroConflito(
        Response $response,
        ConflitoLocalException $e
    ): Response {
        return $this->json(
            $response,
            [
                'status' =>
                    'conflito',
                'mensagem' =>
                    $e->getMessage(),
                'conflitos' =>
                    $e->getConflitos(),
                'como_confirmar' =>
                    'Repita a requisição acrescentando "confirmar_conflito": true.',
            ],
            409
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

        $response->getBody()->write(
            $json
        );

        return $response
            ->withHeader(
                'Content-Type',
                'application/json; charset=utf-8'
            )
            ->withStatus($statusCode);
    }
}
