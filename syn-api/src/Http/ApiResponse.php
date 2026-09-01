<?php

declare(strict_types=1);

namespace App\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * Contrato de resposta JSON recomendado para novos Controllers.
 *
 * A API já utiliza majoritariamente o formato:
 *
 * sucesso:
 * {
 *   "status": "ok",
 *   "dados": ...
 * }
 *
 * erro:
 * {
 *   "status": "erro",
 *   "mensagem": "...",
 *   "erros": ...
 * }
 *
 * Esta classe centraliza esse padrão para os próximos módulos
 * sem obrigar uma refatoração arriscada de todos os Controllers
 * que já estão funcionando.
 */
final class ApiResponse
{
    /**
     * @param mixed $dados
     */
    public static function sucesso(
        ResponseInterface $response,
        mixed $dados = null,
        ?string $mensagem = null,
        int $statusCode = 200
    ): ResponseInterface {
        $payload = [
            'status' => 'ok',
        ];

        if ($mensagem !== null) {
            $payload['mensagem'] =
                $mensagem;
        }

        if ($dados !== null) {
            $payload['dados'] =
                $dados;
        }

        return self::json(
            $response,
            $payload,
            $statusCode
        );
    }

    /**
     * @param array<string, mixed>|null $erros
     */
    public static function erro(
        ResponseInterface $response,
        string $mensagem,
        int $statusCode,
        ?array $erros = null
    ): ResponseInterface {
        $payload = [
            'status' =>
                'erro',
            'mensagem' =>
                $mensagem,
        ];

        if (
            $erros !== null
            && $erros !== []
        ) {
            $payload['erros'] =
                $erros;
        }

        return self::json(
            $response,
            $payload,
            $statusCode
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function json(
        ResponseInterface $response,
        array $payload,
        int $statusCode
    ): ResponseInterface {
        $response
            ->getBody()
            ->write(
                json_encode(
                    $payload,
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
