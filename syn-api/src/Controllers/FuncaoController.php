<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\FuncaoNaoEncontradaException;
use App\Exceptions\UsuarioNaoEncontradoException;
use App\Services\FuncaoService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller HTTP do módulo de funções.
 */
final class FuncaoController
{
    public function __construct(
        private FuncaoService $funcaoService
    ) {
    }

    /**
     * GET /funcoes
     */
    public function listar(
        Request $request,
        Response $response
    ): Response {
        $funcoes = $this->funcaoService->listarTodas();

        return $this->json(
            $response,
            [
                'status' => 'ok',
                'total' => count($funcoes),
                'dados' => $funcoes,
            ],
            200
        );
    }

    /**
     * GET /funcoes/{id}
     *
     * @param array<string, string> $args
     */
    public function buscarPorId(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $funcao = $this->funcaoService
                ->buscarPorId((int) $args['id']);

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'dados' => $funcao,
                ],
                200
            );
        } catch (FuncaoNaoEncontradaException $e) {
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
     * POST /funcoes
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
                        'Envie os dados da função em formato JSON.',
                ],
                400
            );
        }

        try {
            $funcao = $this->funcaoService->criar($dados);

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Função cadastrada com sucesso.',
                    'dados' => $funcao,
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
     * PUT /funcoes/{id}
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
                        'Envie os dados da função em formato JSON.',
                ],
                400
            );
        }

        try {
            $funcao = $this->funcaoService->atualizar(
                (int) $args['id'],
                $dados
            );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Função atualizada com sucesso.',
                    'dados' => $funcao,
                ],
                200
            );
        } catch (FuncaoNaoEncontradaException $e) {
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
     * PATCH /funcoes/{id}/desativar
     *
     * @param array<string, string> $args
     */
    public function desativar(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $resultado = $this->funcaoService
                ->desativar((int) $args['id']);

            $mensagem = $resultado['ja_estava_inativa']
                ? 'A função já estava inativa.'
                : 'Função desativada com sucesso.';

            $resposta = [
                'status' => 'ok',
                'mensagem' => $mensagem,
                'dados' => $resultado,
            ];

            if (
                $resultado['usuarios_com_funcao_atual'] > 0
            ) {
                $resposta['alerta'] =
                    'Existem usuários que ainda possuem esta função em suas habilitações atuais. A função está inativa e não poderá ser usada em novas atribuições.';
            }

            return $this->json(
                $response,
                $resposta,
                200
            );
        } catch (FuncaoNaoEncontradaException $e) {
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
     * POST /usuarios/{usuarioId}/funcoes/{funcaoId}
     *
     * @param array<string, string> $args
     */
    public function atribuirAoUsuario(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $resultado = $this->funcaoService
                ->atribuirAoUsuario(
                    (int) $args['usuarioId'],
                    (int) $args['funcaoId']
                );

            $statusCode =
                $resultado['ja_possuia_funcao']
                    ? 200
                    : 201;

            $mensagem =
                $resultado['ja_possuia_funcao']
                    ? 'O usuário já possuía esta função.'
                    : 'Função atribuída ao usuário com sucesso.';

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' => $mensagem,
                    'dados' => $resultado,
                ],
                $statusCode
            );
        } catch (UsuarioNaoEncontradoException|FuncaoNaoEncontradaException $e) {
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
     * DELETE /usuarios/{usuarioId}/funcoes/{funcaoId}
     *
     * @param array<string, string> $args
     */
    public function removerDoUsuario(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $resultado = $this->funcaoService
                ->removerDoUsuario(
                    (int) $args['usuarioId'],
                    (int) $args['funcaoId']
                );

            $mensagem =
                $resultado['funcao_estava_atribuida']
                    ? 'Função removida do usuário com sucesso.'
                    : 'A função já não estava atribuída ao usuário.';

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' => $mensagem,
                    'dados' => $resultado,
                ],
                200
            );
        } catch (UsuarioNaoEncontradoException|FuncaoNaoEncontradaException $e) {
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
