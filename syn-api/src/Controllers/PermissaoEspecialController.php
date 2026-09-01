<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\UsuarioNaoEncontradoException;
use App\Services\PermissaoEspecialService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class PermissaoEspecialController
{
    public function __construct(
        private PermissaoEspecialService $service
    ) {
    }

    public function listarCatalogo(
        Request $request,
        Response $response
    ): Response {
        $dados = $this->service->listarCatalogo();

        return $this->json(
            $response,
            [
                'status' => 'ok',
                'total' => count($dados),
                'dados' => $dados,
            ],
            200
        );
    }

    public function listarDoUsuario(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'dados' => $this->service->listarDoUsuario(
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
                (int) $args['permissaoId']
            );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        $resultado['permissao_criada']
                            ? 'Permissão especial concedida com sucesso.'
                            : 'O usuário já possuía esta permissão.',
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
    }

    public function revogar(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $resultado = $this->service->revogar(
                (int) $args['usuarioId'],
                (int) $args['permissaoId']
            );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        $resultado['permissao_removida']
                            ? 'Permissão especial revogada com sucesso.'
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
