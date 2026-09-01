<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Repositories\AuditoriaRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Auditoria transversal da API.
 *
 * É um middleware global, porém grava somente requisições de escrita:
 *
 * POST, PUT, PATCH e DELETE.
 *
 * Não armazenamos:
 * - senha;
 * - token JWT;
 * - corpo completo;
 * - query string.
 *
 * O middleware decodifica o JWT apenas para identificar o autor.
 * A autorização real continua sendo responsabilidade dos middlewares
 * normais da aplicação.
 */
final class AuditoriaMiddleware
{
    private const METODOS_AUDITADOS = [
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
    ];

    /**
     * Rotas públicas/sensíveis que não devem ter seus dados
     * tratados como operação administrativa de usuário.
     */
    private const CAMINHOS_IGNORADOS = [
        '/auth/login',
        '/auth/esqueci-senha',
        '/auth/redefinir-senha',
    ];

    public function __construct(
        private AuditoriaRepository $repository,
        private string $jwtSecret
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $metodo =
            strtoupper(
                $request->getMethod()
            );

        $caminho =
            $request->getUri()
                ->getPath();

        if (
            !in_array(
                $metodo,
                self::METODOS_AUDITADOS,
                true
            )
            || in_array(
                $caminho,
                self::CAMINHOS_IGNORADOS,
                true
            )
        ) {
            return $handler->handle(
                $request
            );
        }

        $requestId =
            bin2hex(
                random_bytes(16)
            );

        $usuarioId =
            $this->extrairUsuarioIdDoJwt(
                $request
            );

        $usuario =
            $usuarioId !== null
                ? $this->repository
                    ->buscarUsuario(
                        $usuarioId
                    )
                : null;

        /**
         * Primeiro executamos a operação.
         *
         * Assim conseguimos registrar também o status HTTP final.
         */
        try {
            $response =
                $handler->handle(
                    $request
                );

            $this->registrarComSeguranca(
                $request,
                $response,
                $requestId,
                $usuario
            );

            return $response;
        } catch (\Throwable $e) {
            /**
             * Se a aplicação lançar exceção antes de produzir Response,
             * registramos HTTP 500 como resultado técnico.
             *
             * Depois a exceção continua seu fluxo normal.
             */
            $this->registrarFalhaExcepcional(
                $request,
                $requestId,
                $usuario
            );

            throw $e;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function extrairUsuarioIdDoJwt(
        ServerRequestInterface $request
    ): ?int {
        $cabecalho =
            trim(
                $request->getHeaderLine(
                    'Authorization'
                )
            );

        if (
            !preg_match(
                '/^Bearer\s+(.+)$/i',
                $cabecalho,
                $matches
            )
        ) {
            return null;
        }

        $token =
            trim(
                $matches[1]
            );

        if (
            $token === ''
            || $this->jwtSecret === ''
        ) {
            return null;
        }

        try {
            $payload =
                JWT::decode(
                    $token,
                    new Key(
                        $this->jwtSecret,
                        'HS256'
                    )
                );

            $id =
                isset($payload->sub)
                    ? (int) $payload->sub
                    : 0;

            return $id > 0
                ? $id
                : null;
        } catch (\Throwable) {
            /**
             * Token inválido será tratado pelo AuthMiddleware.
             * Auditoria não deve substituir autenticação.
             */
            return null;
        }
    }

    /**
     * @param array<string, mixed>|null $usuario
     */
    private function registrarComSeguranca(
        ServerRequestInterface $request,
        ResponseInterface $response,
        string $requestId,
        ?array $usuario
    ): void {
        try {
            $status =
                $response->getStatusCode();

            $mensagem =
                $this->extrairMensagemResposta(
                    $response
                );

            [
                $recurso,
                $entidadeId,
            ] =
                $this->inferirAlvo(
                    $request,
                    $response
                );

            $this->repository
                ->registrar([
                    'request_id' =>
                        $requestId,

                    'usuario_id' =>
                        $usuario !== null
                            ? (int) $usuario['id']
                            : null,

                    'usuario_nome_historico' =>
                        $usuario['nome']
                        ?? null,

                    'papel_codigo_historico' =>
                        $usuario['papel_codigo']
                        ?? null,

                    'metodo' =>
                        strtoupper(
                            $request->getMethod()
                        ),

                    'caminho' =>
                        mb_substr(
                            $request
                                ->getUri()
                                ->getPath(),
                            0,
                            255
                        ),

                    'recurso' =>
                        $recurso,

                    'entidade_id' =>
                        $entidadeId,

                    'http_status' =>
                        $status,

                    'sucesso' =>
                        $status >= 200
                        && $status < 400
                            ? 1
                            : 0,

                    'mensagem_resultado' =>
                        $mensagem,

                    'ip' =>
                        $this->obterIp(
                            $request
                        ),

                    'user_agent' =>
                        $this->obterUserAgent(
                            $request
                        ),
                ]);
        } catch (\Throwable) {
            /**
             * Falha na auditoria NÃO pode derrubar a operação principal.
             *
             * Em produção, essa exceção pode futuramente ser enviada
             * ao logger técnico da aplicação.
             */
        }
    }

    /**
     * @param array<string, mixed>|null $usuario
     */
    private function registrarFalhaExcepcional(
        ServerRequestInterface $request,
        string $requestId,
        ?array $usuario
    ): void {
        try {
            [
                $recurso,
                $entidadeId,
            ] =
                $this->inferirAlvo(
                    $request,
                    null
                );

            $this->repository
                ->registrar([
                    'request_id' =>
                        $requestId,

                    'usuario_id' =>
                        $usuario !== null
                            ? (int) $usuario['id']
                            : null,

                    'usuario_nome_historico' =>
                        $usuario['nome']
                        ?? null,

                    'papel_codigo_historico' =>
                        $usuario['papel_codigo']
                        ?? null,

                    'metodo' =>
                        strtoupper(
                            $request->getMethod()
                        ),

                    'caminho' =>
                        mb_substr(
                            $request
                                ->getUri()
                                ->getPath(),
                            0,
                            255
                        ),

                    'recurso' =>
                        $recurso,

                    'entidade_id' =>
                        $entidadeId,

                    'http_status' =>
                        500,

                    'sucesso' =>
                        0,

                    'mensagem_resultado' =>
                        'A operação terminou com uma exceção não tratada.',

                    'ip' =>
                        $this->obterIp(
                            $request
                        ),

                    'user_agent' =>
                        $this->obterUserAgent(
                            $request
                        ),
                ]);
        } catch (\Throwable) {
            // Auditoria nunca substitui o tratamento do erro original.
        }
    }

    private function extrairMensagemResposta(
        ResponseInterface $response
    ): ?string {
        try {
            $conteudo =
                (string) $response
                    ->getBody();

            if ($conteudo === '') {
                return null;
            }

            $json =
                json_decode(
                    $conteudo,
                    true
                );

            if (
                !is_array($json)
                || !isset($json['mensagem'])
                || !is_string(
                    $json['mensagem']
                )
            ) {
                return null;
            }

            return mb_substr(
                $json['mensagem'],
                0,
                500
            );
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{0: ?string, 1: ?int}
     */
    private function inferirAlvo(
        ServerRequestInterface $request,
        ?ResponseInterface $response
    ): array {
        $segmentos =
            array_values(
                array_filter(
                    explode(
                        '/',
                        trim(
                            $request
                                ->getUri()
                                ->getPath(),
                            '/'
                        )
                    ),
                    static fn (
                        string $item
                    ): bool =>
                        $item !== ''
                )
            );

        $recurso =
            isset($segmentos[0])
                ? mb_substr(
                    $segmentos[0],
                    0,
                    80
                )
                : null;

        $entidadeId = null;

        foreach (
            array_slice(
                $segmentos,
                1
            )
            as $segmento
        ) {
            if (ctype_digit($segmento)) {
                $entidadeId =
                    (int) $segmento;
                break;
            }
        }

        /**
         * Em POST de criação, se a URL não possui ID tentamos
         * extrair dados.id da resposta JSON.
         */
        if (
            $entidadeId === null
            && $response !== null
            && strtoupper(
                $request->getMethod()
            ) === 'POST'
        ) {
            try {
                $json =
                    json_decode(
                        (string) $response
                            ->getBody(),
                        true
                    );

                $id =
                    $json['dados']['id']
                    ?? null;

                if (
                    is_int($id)
                    || (
                        is_string($id)
                        && ctype_digit($id)
                    )
                ) {
                    $entidadeId =
                        (int) $id;
                }
            } catch (\Throwable) {
                // Sem ID é aceitável.
            }
        }

        return [
            $recurso,
            $entidadeId,
        ];
    }

    private function obterIp(
        ServerRequestInterface $request
    ): ?string {
        $params =
            $request->getServerParams();

        $ip =
            isset(
                $params[
                    'REMOTE_ADDR'
                ]
            )
                ? trim(
                    (string) $params[
                        'REMOTE_ADDR'
                    ]
                )
                : '';

        if ($ip === '') {
            return null;
        }

        return mb_substr(
            $ip,
            0,
            45
        );
    }

    private function obterUserAgent(
        ServerRequestInterface $request
    ): ?string {
        $valor =
            trim(
                $request->getHeaderLine(
                    'User-Agent'
                )
            );

        if ($valor === '') {
            return null;
        }

        return mb_substr(
            $valor,
            0,
            500
        );
    }
}
