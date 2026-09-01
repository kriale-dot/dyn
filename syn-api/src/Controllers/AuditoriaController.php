<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuditoriaAcessoNegadoException;
use App\Exceptions\DadosInvalidosException;
use App\Services\AuditoriaService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller de consulta da auditoria.
 */
final class AuditoriaController
{
    public function __construct(
        private AuditoriaService $service
    ) {
    }

    public function index(
        Request $request,
        Response $response
    ): Response {
        $query =
            $request->getQueryParams();

        try {
            $dados =
                $this->service
                    ->listar(
                        $this->usuarioAutenticadoId(
                            $request
                        ),

                        isset($query['pagina'])
                            ? (int) $query['pagina']
                            : null,

                        isset($query['limite'])
                            ? (int) $query['limite']
                            : null,

                        isset($query['usuario_id'])
                            ? (int) $query['usuario_id']
                            : null,

                        isset($query['metodo'])
                            ? (string) $query['metodo']
                            : null,

                        isset($query['recurso'])
                            ? (string) $query['recurso']
                            : null,

                        isset($query['somente_erros'])
                            ? filter_var(
                                $query['somente_erros'],
                                FILTER_VALIDATE_BOOLEAN
                            )
                            : null
                    );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'dados' => $dados,
                ],
                200
            );
        } catch (
            AuditoriaAcessoNegadoException $e
        ) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' =>
                        $e->getMessage(),
                ],
                403
            );
        } catch (
            DadosInvalidosException $e
        ) {
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

    public function show(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $dados =
                $this->service
                    ->buscar(
                        $this->usuarioAutenticadoId(
                            $request
                        ),
                        (int) (
                            $args['id']
                            ?? 0
                        )
                    );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'dados' => $dados,
                ],
                200
            );
        } catch (
            AuditoriaAcessoNegadoException $e
        ) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' =>
                        $e->getMessage(),
                ],
                403
            );
        } catch (
            DadosInvalidosException $e
        ) {
            $erros =
                $e->getErros();

            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' =>
                        $e->getMessage(),
                    'erros' =>
                        $erros,
                ],
                isset($erros['auditoria'])
                    ? 404
                    : 422
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
                $auth['id']
                ?? 0
            )
            : 0;
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
