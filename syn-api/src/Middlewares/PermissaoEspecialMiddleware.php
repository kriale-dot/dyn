<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Exceptions\PermissaoEspecialException;
use App\Services\PermissaoEspecialService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class PermissaoEspecialMiddleware implements MiddlewareInterface
{
    public function __construct(
        private PermissaoEspecialService $service,
        private ResponseFactoryInterface $responseFactory,
        private string $codigo
    ) {
    }

    public function process(
        Request $request,
        RequestHandlerInterface $handler
    ): Response {
        $auth = $request->getAttribute('auth');

        if (!is_array($auth)) {
            return $this->erro(401, 'Usuário não autenticado.');
        }

        try {
            $this->service->exigirCodigo($auth, $this->codigo);
        } catch (PermissaoEspecialException $e) {
            return $this->erro(403, $e->getMessage());
        }

        return $handler->handle($request);
    }

    private function erro(int $statusCode, string $mensagem): Response
    {
        $response = $this->responseFactory->createResponse($statusCode);

        $response->getBody()->write(
            json_encode(
                ['status' => 'erro', 'mensagem' => $mensagem],
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
