<?php

declare(strict_types=1);

namespace App\Middlewares;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware de autorização por PAPEL.
 *
 * Exemplo:
 *
 * new PapelMiddleware(
 *     ['ADMINISTRADOR', 'ORGANIZADOR'],
 *     $responseFactory
 * )
 *
 * Atenção:
 * nesta etapa fazemos a autorização de base por papel.
 * O documento prevê permissões mais granulares para Organizador,
 * porém o modelo detalhado desse escopo ainda não foi definido.
 */
final class PapelMiddleware implements MiddlewareInterface
{
    /**
     * @param array<int, string> $papeisPermitidos
     */
    public function __construct(
        private array $papeisPermitidos,
        private ResponseFactoryInterface $responseFactory
    ) {
    }

    public function process(
        Request $request,
        RequestHandlerInterface $handler
    ): Response {
        $auth =
            $request->getAttribute('auth');

        $papel =
            is_array($auth)
                ? (
                    $auth['papel']['codigo']
                    ?? null
                )
                : null;

        if (
            !is_string($papel)
            || !in_array(
                $papel,
                $this->papeisPermitidos,
                true
            )
        ) {
            return $this->proibido();
        }

        return $handler->handle($request);
    }

    private function proibido(): Response
    {
        $response =
            $this->responseFactory
                ->createResponse(403);

        $response->getBody()->write(
            json_encode(
                [
                    'status' => 'erro',
                    'mensagem' =>
                        'Você não possui permissão para executar esta operação.',
                ],
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
            )
        );

        return $response->withHeader(
            'Content-Type',
            'application/json; charset=utf-8'
        );
    }
}
