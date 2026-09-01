<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\UsuarioNaoEncontradoException;
use App\Services\PerfilService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller HTTP de perfil e aniversariantes.
 */
final class PerfilController
{
    public function __construct(
        private PerfilService $service
    ) {
    }

    public function meuPerfil(
        Request $request,
        Response $response
    ): Response {
        $usuarioId =
            $this->usuarioAutenticadoId(
                $request
            );

        try {
            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'dados' =>
                        $this->service
                            ->meuPerfil(
                                $usuarioId
                            ),
                ],
                200
            );
        } catch (UsuarioNaoEncontradoException $e) {
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
    }

    public function atualizarMeuPerfil(
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
                        'Envie os dados do perfil em formato JSON.',
                ],
                400
            );
        }

        try {
            $perfil =
                $this->service
                    ->atualizarMeuPerfil(
                        $this->usuarioAutenticadoId(
                            $request
                        ),
                        $dados
                    );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Perfil atualizado com sucesso.',
                    'dados' =>
                        $perfil,
                ],
                200
            );
        } catch (UsuarioNaoEncontradoException $e) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' =>
                        $e->getMessage(),
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

    public function aniversariantesHoje(
        Request $request,
        Response $response
    ): Response {
        return $this->executarAniversariantes(
            $request,
            $response,
            'hoje'
        );
    }

    public function aniversariantesSemana(
        Request $request,
        Response $response
    ): Response {
        return $this->executarAniversariantes(
            $request,
            $response,
            'semana'
        );
    }

    private function executarAniversariantes(
        Request $request,
        Response $response,
        string $modo
    ): Response {
        $query =
            $request->getQueryParams();

        $dataReferencia =
            isset(
                $query[
                    'data_referencia'
                ]
            )
                ? (string) $query[
                    'data_referencia'
                ]
                : null;

        try {
            $resultado =
                $modo === 'hoje'
                    ? $this->service
                        ->aniversariantesHoje(
                            $dataReferencia
                        )
                    : $this->service
                        ->aniversariantesSemana(
                            $dataReferencia
                        );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'total' =>
                        count(
                            $resultado[
                                'aniversariantes'
                            ]
                        ),
                    'dados' =>
                        $resultado,
                ],
                200
            );
        } catch (DadosInvalidosException $e) {
            return $this->erroValidacao(
                $response,
                $e
            );
        }
    }

    private function usuarioAutenticadoId(
        Request $request
    ): int {
        $auth =
            $request->getAttribute(
                'auth'
            );

        return is_array($auth)
            ? (int) (
                $auth['id'] ?? 0
            )
            : 0;
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
