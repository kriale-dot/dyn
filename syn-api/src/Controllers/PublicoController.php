<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Services\PublicoService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller das rotas que NÃO exigem login.
 */
final class PublicoController
{
    public function __construct(
        private PublicoService $service
    ) {
    }

    public function igreja(
        Request $request,
        Response $response
    ): Response {
        $dados =
            $this->service
                ->igreja();

        if ($dados === null) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' =>
                        'Cadastro público da igreja não encontrado.',
                ],
                404
            );
        }

        return $this->json(
            $response,
            [
                'status' => 'ok',
                'dados' => $dados,
            ],
            200
        );
    }

    public function mapaSemana(
        Request $request,
        Response $response
    ): Response {
        $query =
            $request
                ->getQueryParams();

        try {
            $dados =
                $this->service
                    ->mapaSemana(
                        isset(
                            $query[
                                'data_referencia'
                            ]
                        )
                            ? (string)
                            $query[
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
            DadosInvalidosException $e
        ) {
            return $this
                ->erroValidacao(
                    $response,
                    $e
                );
        }
    }

    public function programacoes(
        Request $request,
        Response $response
    ): Response {
        $query =
            $request
                ->getQueryParams();

        try {
            $dados =
                $this->service
                    ->programacoes(
                        isset(
                            $query[
                                'data_inicial'
                            ]
                        )
                            ? (string)
                            $query[
                                'data_inicial'
                            ]
                            : null,
                        isset(
                            $query[
                                'data_final'
                            ]
                        )
                            ? (string)
                            $query[
                                'data_final'
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
            DadosInvalidosException $e
        ) {
            return $this
                ->erroValidacao(
                    $response,
                    $e
                );
        }
    }

    public function programacao(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $id =
                filter_var(
                    $args['id'] ?? null,
                    FILTER_VALIDATE_INT,
                    [
                        'options' => [
                            'min_range' => 1,
                        ],
                    ]
                );

            if ($id === false) {
                throw new DadosInvalidosException([
                    'id' =>
                        'Informe um ID de programação válido.',
                ]);
            }

            $dados =
                $this->service
                    ->programacao(
                        (int) $id
                    );

            if ($dados === null) {
                /**
                 * A resposta é 404 tanto para ID inexistente quanto
                 * para programação INTERNA.
                 *
                 * Assim a API pública não confirma a existência
                 * de um recurso privado.
                 */
                return $this->json(
                    $response,
                    [
                        'status' => 'erro',
                        'mensagem' =>
                            'Programação pública não encontrada.',
                    ],
                    404
                );
            }

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'dados' => $dados,
                ],
                200
            );
        } catch (
            DadosInvalidosException $e
        ) {
            return $this
                ->erroValidacao(
                    $response,
                    $e
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
