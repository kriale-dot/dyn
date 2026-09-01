<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Exceptions\PermissaoOrganizadorException;
use App\Services\PermissaoOrganizadorService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

final class EscopoOrganizadorMiddleware implements MiddlewareInterface
{
    public function __construct(
        private PermissaoOrganizadorService $service,
        private ResponseFactoryInterface $responseFactory,
        private string $origem,
        private string $argumentoRota = 'id'
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

        $papel = (string) ($auth['papel']['codigo'] ?? '');

        if ($papel === 'ADMINISTRADOR') {
            return $handler->handle($request);
        }

        if ($papel !== 'ORGANIZADOR') {
            return $this->erro(
                403,
                'Você não possui permissão para executar esta operação.'
            );
        }

        $tipoId = $this->resolverTipoId($request);

        // Corpo/entidade inválida será tratada pelo Controller/Service.
        if ($tipoId === null) {
            return $handler->handle($request);
        }

        try {
            $this->service->exigirTipo($auth, $tipoId);
        } catch (PermissaoOrganizadorException $e) {
            return $this->erro(403, $e->getMessage());
        }

        return $handler->handle($request);
    }

    private function resolverTipoId(Request $request): ?int
    {
        if ($this->origem === 'body') {
            $dados = $request->getParsedBody();

            if (!is_array($dados)) {
                return null;
            }

            return $this->inteiroPositivo(
                $dados['tipo_programacao_id'] ?? null
            );
        }

        $route = RouteContext::fromRequest($request)->getRoute();

        if ($route === null) {
            return null;
        }

        $id = $this->inteiroPositivo(
            $route->getArgument($this->argumentoRota)
        );

        if ($id === null) {
            return null;
        }

        return match ($this->origem) {
            'tipo_rota' => $id,
            'programacao' => $this->service->tipoIdDaProgramacao($id),
            'participacao' => $this->service->tipoIdDaParticipacao($id),
            'serie' => $this->service->tipoIdDaSerie($id),
            default => null,
        };
    }

    private function inteiroPositivo(mixed $valor): ?int
    {
        $id = filter_var(
            $valor,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        return $id === false ? null : (int) $id;
    }

    private function erro(int $statusCode, string $mensagem): Response
    {
        $response = $this->responseFactory->createResponse($statusCode);

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
