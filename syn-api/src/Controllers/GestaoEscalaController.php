<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\GestaoEscalaAcessoNegadoException;
use App\Services\GestaoEscalaService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller da tela administrativa de escala.
 */
final class GestaoEscalaController
{
    public function __construct(
        private GestaoEscalaService $service
    ) {
    }

    public function show(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $dados = $this->service->obter(
                (int) ($args['id'] ?? 0),
                $this->usuarioAutenticadoId($request)
            );

            return $this->json(
                $response,
                ['status' => 'ok', 'dados' => $dados],
                200
            );
        } catch (GestaoEscalaAcessoNegadoException $e) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' => $e->getMessage(),
                ],
                403
            );
        } catch (DadosInvalidosException $e) {
            $erros = $e->getErros();

            $statusCode = isset($erros['programacao'])
                && $erros['programacao'] === 'Programação não encontrada.'
                    ? 404
                    : 422;

            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' => $e->getMessage(),
                    'erros' => $erros,
                ],
                $statusCode
            );
        }
    }

    private function usuarioAutenticadoId(Request $request): int
    {
        $auth = $request->getAttribute('auth');

        return is_array($auth)
            ? (int) ($auth['id'] ?? 0)
            : 0;
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
