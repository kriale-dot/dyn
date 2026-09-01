<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\NecessidadeEspecificaNaoEncontradaException;
use App\Exceptions\UsuarioNaoEncontradoException;
use App\Services\NecessidadeEspecificaService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller HTTP dos dados restritos de necessidades específicas.
 */
final class NecessidadeEspecificaController
{
    public function __construct(
        private NecessidadeEspecificaService $service
    ) {
    }

    public function listar(
        Request $request,
        Response $response
    ): Response {
        $dados =
            $this->service->listarTodos();

        return $this->json(
            $response,
            [
                'status' => 'ok',
                'total' =>
                    count($dados),
                'dados' =>
                    $dados,
            ],
            200
        );
    }

    /**
     * @param array<string, string> $args
     */
    public function buscarPorUsuario(
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
                            ->buscarPorUsuario(
                                (int) $args[
                                    'usuarioId'
                                ]
                            ),
                ],
                200
            );
        } catch (UsuarioNaoEncontradoException|NecessidadeEspecificaNaoEncontradaException $e) {
            return $this->naoEncontrado(
                $response,
                $e
            );
        }
    }

    /**
     * @param array<string, string> $args
     */
    public function salvar(
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
                        'Envie a observação em formato JSON.',
                ],
                400
            );
        }

        try {
            $registro =
                $this->service->salvar(
                    (int) $args[
                        'usuarioId'
                    ],
                    (string) (
                        $dados['observacao']
                        ?? ''
                    )
                );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Necessidade específica registrada com sucesso.',
                    'dados' =>
                        $registro,
                ],
                200
            );
        } catch (UsuarioNaoEncontradoException $e) {
            return $this->naoEncontrado(
                $response,
                $e
            );
        } catch (DadosInvalidosException $e) {
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

    /**
     * @param array<string, string> $args
     */
    public function desativar(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $resultado =
                $this->service
                    ->desativar(
                        (int) $args[
                            'usuarioId'
                        ]
                    );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        $resultado[
                            'ja_estava_inativa'
                        ]
                            ? 'A necessidade específica já estava inativa.'
                            : 'Necessidade específica desativada com sucesso.',
                    'dados' =>
                        $resultado,
                ],
                200
            );
        } catch (NecessidadeEspecificaNaoEncontradaException $e) {
            return $this->naoEncontrado(
                $response,
                $e
            );
        }
    }

    private function naoEncontrado(
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
