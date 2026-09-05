<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\ApiResponse;
use App\Services\DiagnosticoService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Endpoints públicos para monitoramento técnico da API.
 *
 * Nenhum deles retorna credenciais, tokens ou detalhes internos
 * de exceções.
 */
final class DiagnosticoController
{
    public function __construct(
        private DiagnosticoService $service
    ) {
    }

    /**
     * Liveness:
     * confirma apenas que o processo PHP/Slim está respondendo.
     */
    public function health(
        Request $request,
        Response $response
    ): Response {
        return ApiResponse::sucesso(
            $response,
            [
                'servico' =>
                    'SYN API',
                'vivo' =>
                    true,
                'timestamp_utc' =>
                    gmdate(
                        DATE_ATOM
                    ),
            ]
        )->withHeader(
            'Cache-Control',
            'no-store'
        );
    }

    /**
     * Readiness:
     * confirma dependências necessárias para receber tráfego.
     */
    public function ready(
        Request $request,
        Response $response
    ): Response {
        $resultado =
            $this->service
                ->verificarProntidao();

        if (
            $resultado[
                'pronto'
            ]
        ) {
            return ApiResponse::sucesso(
                $response,
                $resultado
            )->withHeader(
                'Cache-Control',
                'no-store'
            );
        }

        return ApiResponse::erro(
            $response,
            'A API está ativa, mas ainda não está pronta para receber tráfego.',
            503,
            [
                'diagnostico' =>
                    $resultado,
            ]
        )->withHeader(
            'Cache-Control',
            'no-store'
        );
    }
}
