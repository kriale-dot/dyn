<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Services\NotificacaoService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller da Central de Notificações.
 */
final class NotificacaoController
{
    public function __construct(
        private NotificacaoService $service
    ) {
    }

    public function index(
        Request $request,
        Response $response
    ): Response {
        $query =
            $request->getQueryParams();

        $somenteNaoLidas =
            filter_var(
                $query[
                    'somente_nao_lidas'
                ] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

        $limite =
            isset($query['limite'])
                ? (int) $query['limite']
                : null;

        try {
            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'dados' =>
                        $this->service
                            ->listar(
                                $this->usuarioAutenticadoId(
                                    $request
                                ),
                                $somenteNaoLidas,
                                $limite
                            ),
                ],
                200
            );
        } catch (
            DadosInvalidosException $e
        ) {
            return $this->erroValidacao(
                $response,
                $e
            );
        }
    }

    public function resumo(
        Request $request,
        Response $response
    ): Response {
        return $this->json(
            $response,
            [
                'status' => 'ok',
                'dados' =>
                    $this->service
                        ->resumo(
                            $this->usuarioAutenticadoId(
                                $request
                            )
                        ),
            ],
            200
        );
    }

    public function marcarComoLida(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Notificação marcada como lida.',
                    'dados' =>
                        $this->service
                            ->marcarComoLida(
                                $this->usuarioAutenticadoId(
                                    $request
                                ),
                                (int) (
                                    $args['id']
                                    ?? 0
                                )
                            ),
                ],
                200
            );
        } catch (
            DadosInvalidosException $e
        ) {
            return $this->erroValidacao(
                $response,
                $e
            );
        }
    }

    public function marcarTodasComoLidas(
        Request $request,
        Response $response
    ): Response {
        return $this->json(
            $response,
            [
                'status' => 'ok',
                'mensagem' =>
                    'Notificações marcadas como lidas.',
                'dados' =>
                    $this->service
                        ->marcarTodasComoLidas(
                            $this->usuarioAutenticadoId(
                                $request
                            )
                        ),
            ],
            200
        );
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
