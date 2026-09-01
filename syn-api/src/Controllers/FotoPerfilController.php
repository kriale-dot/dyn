<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\UsuarioNaoEncontradoException;
use App\Services\FotoPerfilService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Controller do upload da foto do próprio perfil.
 */
final class FotoPerfilController
{
    public function __construct(
        private FotoPerfilService $service
    ) {
    }

    public function salvar(
        Request $request,
        Response $response
    ): Response {
        $arquivos =
            $request->getUploadedFiles();

        $foto =
            $arquivos['foto']
            ?? null;

        if (
            !$foto
            instanceof UploadedFileInterface
        ) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' =>
                        'Envie a imagem no campo multipart/form-data chamado "foto".',
                ],
                400
            );
        }

        try {
            $resultado =
                $this->service
                    ->salvar(
                        $this->usuarioAutenticadoId(
                            $request
                        ),
                        $foto
                    );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Foto de perfil atualizada com sucesso.',
                    'dados' =>
                        $resultado,
                ],
                200
            );
        } catch (
            UsuarioNaoEncontradoException
            | DadosInvalidosException $e
        ) {
            return $this->erroConhecido(
                $response,
                $e
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
                    ->remover(
                        $this->usuarioAutenticadoId(
                            $request
                        )
                    );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        'Foto de perfil removida com sucesso.',
                    'dados' =>
                        $resultado,
                ],
                200
            );
        } catch (
            UsuarioNaoEncontradoException
            | DadosInvalidosException $e
        ) {
            return $this->erroConhecido(
                $response,
                $e
            );
        }
    }

    private function usuarioAutenticadoId(
        Request $request
    ): int {
        $auth =
            $request->getAttribute(
                'auth'
            );

        return is_array($auth)
            ? (int) (
                $auth['id'] ?? 0
            )
            : 0;
    }

    private function erroConhecido(
        Response $response,
        \Throwable $e
    ): Response {
        if (
            $e instanceof
            UsuarioNaoEncontradoException
        ) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' =>
                        $e->getMessage(),
                ],
                404
            );
        }

        /** @var DadosInvalidosException $e */
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
