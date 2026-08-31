<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Services\IgrejaService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller HTTP do cadastro institucional da igreja.
 *
 * Responsabilidades:
 * - receber Request;
 * - extrair os dados HTTP necessários;
 * - chamar o Service;
 * - transformar o resultado em Response JSON.
 *
 * O Controller NÃO executa SQL.
 */
final class IgrejaController
{
    public function __construct(
        private IgrejaService $igrejaService
    ) {
    }

    /**
     * GET /igreja
     */
    public function buscar(
        Request $request,
        Response $response
    ): Response {
        $igreja = $this->igrejaService->buscar();

        if ($igreja === null) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' => 'Cadastro da igreja não encontrado.',
                ],
                404
            );
        }

        return $this->json(
            $response,
            [
                'status' => 'ok',
                'dados' => $igreja,
            ],
            200
        );
    }

    /**
     * PUT /igreja
     *
     * Recebe JSON, entrega os dados ao Service e devolve
     * o cadastro atualizado.
     */
    public function atualizar(
        Request $request,
        Response $response
    ): Response {
        $dados = $request->getParsedBody();

        /**
         * Se o cliente não enviou um objeto JSON válido,
         * não temos dados que possam ser processados.
         */
        if (!is_array($dados)) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' => 'Envie os dados da igreja em formato JSON.',
                ],
                400
            );
        }

        try {
            $igreja = $this->igrejaService->atualizar($dados);

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' => 'Cadastro da igreja atualizado com sucesso.',
                    'dados' => $igreja,
                ],
                200
            );
        } catch (DadosInvalidosException $e) {
            /**
             * 422 significa:
             * a requisição foi entendida, porém os dados
             * não satisfazem as regras de validação.
             */
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' => $e->getMessage(),
                    'erros' => $e->getErros(),
                ],
                422
            );
        }
    }

    /**
     * Cria respostas JSON padronizadas.
     *
     * @param array<string, mixed> $dados
     */
    private function json(
        Response $response,
        array $dados,
        int $statusCode
    ): Response {
        $json = json_encode(
            $dados,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_THROW_ON_ERROR
        );

        $response->getBody()->write($json);

        return $response
            ->withHeader(
                'Content-Type',
                'application/json; charset=utf-8'
            )
            ->withStatus($statusCode);
    }
}
