<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\FuncaoNaoEncontradaException;
use App\Exceptions\ParticipacaoNaoEncontradaException;
use App\Exceptions\ProgramacaoNaoEncontradaException;
use App\Exceptions\UsuarioNaoEncontradoException;
use App\Services\ParticipacaoService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller HTTP de participações / escalas.
 */
final class ParticipacaoController
{
    public function __construct(
        private ParticipacaoService $service
    ) {
    }

    /**
     * @param array<string, string> $args
     */
    public function listarPorProgramacao(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $resultado =
                $this->service
                    ->listarPorProgramacao(
                        (int) $args['id']
                    );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'dados' => $resultado,
                ],
                200
            );
        } catch (ProgramacaoNaoEncontradaException $e) {
            return $this->erroNaoEncontrado(
                $response,
                $e
            );
        }
    }

    /**
     * @param array<string, string> $args
     */
    public function listarCandidatos(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            $resultado =
                $this->service
                    ->listarCandidatos(
                        (int) $args['id']
                    );

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'dados' => $resultado,
                ],
                200
            );
        } catch (ProgramacaoNaoEncontradaException $e) {
            return $this->erroNaoEncontrado(
                $response,
                $e
            );
        } catch (DadosInvalidosException $e) {
            return $this->erroValidacao(
                $response,
                $e
            );
        }
    }

    /**
     * @param array<string, string> $args
     */
    public function criar(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $dados = $request->getParsedBody();

        if (!is_array($dados)) {
            return $this->json(
                $response,
                [
                    'status' => 'erro',
                    'mensagem' =>
                        'Envie os dados da escala em formato JSON.',
                ],
                400
            );
        }

        try {
            $resultado =
                $this->service->criar(
                    (int) $args['id'],
                    $dados
                );

            $nova =
                !$resultado['ja_existia'];

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' =>
                        $nova
                            ? 'Pessoa escalada com sucesso.'
                            : 'Esta pessoa já estava registrada nesta função e programação.',
                    'dados' => $resultado,
                ],
                $nova ? 201 : 200
            );
        } catch (ProgramacaoNaoEncontradaException|UsuarioNaoEncontradoException|FuncaoNaoEncontradaException $e) {
            return $this->erroNaoEncontrado(
                $response,
                $e
            );
        } catch (DadosInvalidosException $e) {
            return $this->erroValidacao(
                $response,
                $e
            );
        }
    }

    /**
     * @param array<string, string> $args
     */
    public function buscarPorId(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {
            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'dados' =>
                        $this->service
                            ->buscarPorId(
                                (int) $args['id']
                            ),
                ],
                200
            );
        } catch (ParticipacaoNaoEncontradaException $e) {
            return $this->erroNaoEncontrado(
                $response,
                $e
            );
        }
    }

    /**
     * @param array<string, string> $args
     */
    public function confirmar(
        Request $request,
        Response $response,
        array $args
    ): Response {
        return $this->executarResposta(
            $request,
            $response,
            (int) $args['id'],
            'confirmar'
        );
    }

    /**
     * @param array<string, string> $args
     */
    public function indisponivel(
        Request $request,
        Response $response,
        array $args
    ): Response {
        return $this->executarResposta(
            $request,
            $response,
            (int) $args['id'],
            'indisponivel'
        );
    }

    /**
     * @param array<string, string> $args
     */
    public function recusar(
        Request $request,
        Response $response,
        array $args
    ): Response {
        return $this->executarResposta(
            $request,
            $response,
            (int) $args['id'],
            'recusar'
        );
    }

    /**
     * @param array<string, string> $args
     */
    public function cancelar(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $dados = $request->getParsedBody();

        if (!is_array($dados)) {
            $dados = [];
        }

        try {
            $resultado =
                $this->service->cancelar(
                    (int) $args['id'],
                    $dados
                );

            $mensagem =
                $resultado['ja_estava_cancelada']
                    ? 'A participação já estava cancelada.'
                    : 'Participação cancelada com sucesso.';

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' => $mensagem,
                    'dados' => $resultado,
                ],
                200
            );
        } catch (ParticipacaoNaoEncontradaException $e) {
            return $this->erroNaoEncontrado(
                $response,
                $e
            );
        } catch (DadosInvalidosException $e) {
            return $this->erroValidacao(
                $response,
                $e
            );
        }
    }

    private function executarResposta(
        Request $request,
        Response $response,
        int $participacaoId,
        string $acao
    ): Response {
        $dados = $request->getParsedBody();

        if (!is_array($dados)) {
            $dados = [];
        }

        $auth =
            $request->getAttribute('auth');

        $usuarioAutenticadoId =
            is_array($auth)
                ? (int) ($auth['id'] ?? 0)
                : 0;

        try {
            $participacao = match ($acao) {
                'confirmar' =>
                    $this->service->confirmar(
                        $participacaoId,
                        $usuarioAutenticadoId,
                        $dados
                    ),

                'indisponivel' =>
                    $this->service->indisponivel(
                        $participacaoId,
                        $usuarioAutenticadoId,
                        $dados
                    ),

                'recusar' =>
                    $this->service->recusar(
                        $participacaoId,
                        $usuarioAutenticadoId,
                        $dados
                    ),

                default =>
                    throw new \LogicException(
                        'Ação de resposta inválida.'
                    ),
            };

            $mensagem = match ($acao) {
                'confirmar' =>
                    'Participação confirmada com sucesso.',
                'indisponivel' =>
                    'Indisponibilidade registrada com sucesso.',
                'recusar' =>
                    'Recusa registrada com sucesso.',
                default =>
                    'Resposta registrada com sucesso.',
            };

            return $this->json(
                $response,
                [
                    'status' => 'ok',
                    'mensagem' => $mensagem,
                    'dados' => $participacao,
                ],
                200
            );
        } catch (ParticipacaoNaoEncontradaException $e) {
            return $this->erroNaoEncontrado(
                $response,
                $e
            );
        } catch (DadosInvalidosException $e) {
            return $this->erroValidacao(
                $response,
                $e
            );
        }
    }

    private function erroNaoEncontrado(
        Response $response,
        \Throwable $e
    ): Response {
        return $this->json(
            $response,
            [
                'status' => 'erro',
                'mensagem' => $e->getMessage(),
            ],
            404
        );
    }

    private function erroValidacao(
        Response $response,
        DadosInvalidosException $e
    ): Response {
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

    /**
     * @param array<string, mixed> $dados
     */
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
