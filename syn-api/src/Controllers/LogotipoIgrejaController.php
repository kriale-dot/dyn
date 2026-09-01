<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Services\LogotipoIgrejaService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Controller HTTP do logotipo da igreja.
 */
final class LogotipoIgrejaController
{
    public function __construct(
        private LogotipoIgrejaService $service
    ) {
    }

    public function salvar(
        Request $request,
        Response $response
    ): Response {
        $arquivos =
            $request->getUploadedFiles();

        $logotipo =
            $arquivos['logotipo']
            ?? null;

        if (
            !$logotipo
            instanceof UploadedFileInterface
        ) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' =>
                        'Envie a imagem no campo multipart/form-data chamado "logotipo".',
                ],
                400
            );
        }

        try {
            $resultado =
                $this->service
                    ->salvar(
                        $logotipo
                    );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Logotipo da igreja atualizado com sucesso.',
                    'dados' =>
                        $resultado,
                ],
                200
            );
        } catch (DadosInvalidosException $e) {
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
    }

    public function remover(
        Request $request,
        Response $response
    ): Response {
        try {
            $resultado =
                $this->service
                    ->remover();

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Logotipo da igreja removido com sucesso.',
                    'dados' =>
                        $resultado,
                ],
                200
            );
        } catch (DadosInvalidosException $e) {
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
            ->withStatus(
                $statusCode
            );
    }
}
