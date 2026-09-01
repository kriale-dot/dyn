<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\UsuarioNaoEncontradoException;
use App\Services\PermissaoOrganizadorService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class PermissaoOrganizadorController
{
    public function __construct(
        private PermissaoOrganizadorService $service
    ) {
    }

    public function minhasPermissoes(
        Request $request,
        Response $response
    ): Response {
        $auth = $request->getAttribute('auth');

        return $this->json(
            $response,
            [
                'status' => 'ok',
                'dados' => $this->service->minhasPermissoes(
                    is_array($auth) ? $auth : []
                ),
            ],
            200
        );
    }

    public function listar(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'dados' => $this->service->listar(
                        (int) $args['usuarioId']
                    ),
                ],
                200
            );
        } catch (UsuarioNaoEncontradoException $e) {
            return $this->json(
                $response,
                ['status' => 'erro', 'mensagem' => $e->getMessage()],
                404
            );
        } catch (DadosInvalidosException $e) {
            return $this->validacao($response, $e);
        }
    }

    public function conceder(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $resultado = $this->service->conceder(
                (int) $args['usuarioId'],
                (int) $args['tipoId']
            );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        $resultado['permissao_criada']
                            ? 'Permissão concedida com sucesso.'
                            : 'O Organizador já possuía esta permissão.',
                    'dados' => $resultado['dados'],
                ],
                $resultado['permissao_criada'] ? 201 : 200
            );
        } catch (UsuarioNaoEncontradoException $e) {
            return $this->json(
                $response,
                ['status' => 'erro', 'mensagem' => $e->getMessage()],
                404
            );
        } catch (DadosInvalidosException $e) {
            return $this->validacao($response, $e);
        }
    }

    public function revogar(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $resultado = $this->service->revogar(
                (int) $args['usuarioId'],
                (int) $args['tipoId']
            );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        $resultado['permissao_removida']
                            ? 'Permissão revogada com sucesso.'
                            : 'A permissão não estava atribuída.',
                    'dados' => $resultado['dados'],
                ],
                200
            );
        } catch (UsuarioNaoEncontradoException $e) {
            return $this->json(
                $response,
                ['status' => 'erro', 'mensagem' => $e->getMessage()],
                404
            );
        } catch (DadosInvalidosException $e) {
            return $this->validacao($response, $e);
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
                'mensagem' => $e->getMessage(),
                'erros' => $e->getErros(),
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
            ->withStatus($statusCode);
    }
}
