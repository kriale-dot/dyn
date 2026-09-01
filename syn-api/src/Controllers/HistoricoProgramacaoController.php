<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\HistoricoProgramacaoAcessoNegadoException;
use App\Services\HistoricoProgramacaoService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller do histórico de alterações da programação.
 */
final class HistoricoProgramacaoController
{
    public function __construct(
        private HistoricoProgramacaoService $service
    ) {
    }

    public function index(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $dados =
                $this->service
                    ->listar(
                        (int) (
                            $args['id']
                            ?? 0
                        ),
                        $this->usuarioAutenticadoId(
                            $request
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
            HistoricoProgramacaoAcessoNegadoException $e
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

            $statusCode =
                isset(
                    $erros['programacao']
                )
                    ? 404
                    : 422;

            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' =>
                        $e->getMessage(),
                    'erros' =>
                        $erros,
                ],
                $statusCode
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
