<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\UsuarioNaoEncontradoException;
use App\Services\UsuarioService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller HTTP do módulo de usuários.
 */
final class UsuarioController
{
    public function __construct(
        private UsuarioService $usuarioService
    ) {
    }

    public function listar(
        Request $request,
        Response $response
    ): Response {
        $usuarios =
            $this->usuarioService->listarTodos();

        return $this->json(
            $response,
            [
                'status' => 'ok',
                'total' => count($usuarios),
                'dados' => $usuarios,
            ],
            200
        );
    }

    /**
     * GET /minha-semana
     *
     * O ID vem do usuário autenticado, e não da URL.
     */
    public function minhaSemanaAutenticada(
        Request $request,
        Response $response
    ): Response {
        $auth =
            $request->getAttribute('auth');

        $usuarioId =
            is_array($auth)
                ? (int) ($auth['id'] ?? 0)
                : 0;

        $query =
            $request->getQueryParams();

        $dataReferencia =
            isset($query['data_referencia'])
                ? (string) $query['data_referencia']
                : null;

        try {
            $resultado =
                $this->usuarioService
                    ->minhaSemana(
                        $usuarioId,
                        $dataReferencia
                    );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'dados' => $resultado,
                ],
                200
            );
        } catch (UsuarioNaoEncontradoException $e) {
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
     * @param array<string, string> $args
     */
    public function buscarPorId(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $usuario =
                $this->usuarioService
                    ->buscarPorId(
                        (int) $args['id']
                    );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'dados' => $usuario,
                ],
                200
            );
        } catch (UsuarioNaoEncontradoException $e) {
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
                        'Envie os dados do usuário em formato JSON.',
                ],
                400
            );
        }

        try {
            $usuario =
                $this->usuarioService->criar($dados);

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Usuário cadastrado com sucesso.',
                    'dados' => $usuario,
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
                        'Envie os dados do usuário em formato JSON.',
                ],
                400
            );
        }

        try {
            $usuario =
                $this->usuarioService->atualizar(
                    (int) $args['id'],
                    $dados
                );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Usuário atualizado com sucesso.',
                    'dados' => $usuario,
                ],
                200
            );
        } catch (UsuarioNaoEncontradoException $e) {
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
     * @param array<string, string> $args
     */
    public function desativar(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $resultado =
                $this->usuarioService->desativar(
                    (int) $args['id']
                );

            $mensagem =
                $resultado['ja_estava_inativo']
                    ? 'O usuário já estava inativo.'
                    : 'Usuário desativado com sucesso.';

            $resposta = [
                'status' => 'ok',
                'mensagem' => $mensagem,
                'dados' => $resultado,
            ];

            if (
                $resultado[
                    'possui_escalas_futuras'
                ]
            ) {
                $resposta['alerta'] =
                    'O usuário possui escalas futuras que precisam ser canceladas ou substituídas.';
            }

            return $this->json(
                $response,
                $resposta,
                200
            );
        } catch (UsuarioNaoEncontradoException $e) {
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
            ->withStatus($statusCode);
    }
}
