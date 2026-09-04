<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\GestaoEscalaAcessoNegadoException;
use App\Services\EscalasSemanaService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller da visão consolidada das escalas da semana.
 */
final class EscalasSemanaController
{
    public function __construct(
        private EscalasSemanaService $service
    ) {
    }

    public function index(
        Request $request,
        Response $response
    ): Response {
        $query =
            $request
                ->getQueryParams();

        try {
            $dados =
                $this->service
                    ->obter(
                        $this->usuarioAutenticadoId(
                            $request
                        ),
                        isset(
                            $query[
                                'data_referencia'
                            ]
                        )
                            ? (string) $query[
                                'data_referencia'
                            ]
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
            GestaoEscalaAcessoNegadoException $e
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

    private function usuarioAutenticadoId(
        Request $request
    ): int {
        $auth =
            $request
                ->getAttribute(
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
        $response
            ->getBody()
            ->write(
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
