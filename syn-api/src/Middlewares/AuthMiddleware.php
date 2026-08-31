<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Exceptions\AutenticacaoException;
use App\Services\AuthService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware de autenticação Bearer.
 *
 * Se o token for válido, acrescenta no Request:
 *
 * $request->getAttribute('auth')
 *
 * com os dados atuais do usuário.
 */
final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AuthService $authService,
        private ResponseFactoryInterface $responseFactory
    ) {
    }

    public function process(
        Request $request,
        RequestHandlerInterface $handler
    ): Response {
        $authorization =
            $request->getHeaderLine(
                'Authorization'
            );

        if (
            !preg_match(
                '/^Bearer\s+(.+)$/i',
                trim($authorization),
                $matches
            )
        ) {
            return $this->naoAutenticado(
                'Token de autenticação não informado.'
            );
        }

        $token = trim($matches[1]);

        try {
            $usuario =
                $this->authService
                    ->autenticarToken($token);
        } catch (AutenticacaoException $e) {
            return $this->naoAutenticado(
                $e->getMessage()
            );
        }

        $request =
            $request->withAttribute(
                'auth',
                $usuario
            );

        return $handler->handle($request);
    }

    private function naoAutenticado(
        string $mensagem
    ): Response {
        $response =
            $this->responseFactory
                ->createResponse(401);

        $response->getBody()->write(
            json_encode(
                [
                    'status' => 'erro',
                    'mensagem' => $mensagem,
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
