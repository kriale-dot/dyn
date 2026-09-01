<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\NecessidadeEspecificaNaoEncontradaException;
use App\Exceptions\UsuarioNaoEncontradoException;
use App\Repositories\NecessidadeEspecificaRepository;

/**
 * Service dos dados restritos de necessidade específica.
 *
 * A autorização HTTP é feita nas rotas.
 *
 * Nesta etapa adotamos a política mais restritiva:
 * somente ADMINISTRADOR.
 *
 * Quando o modelo de "Organizador autorizado" estiver
 * formalizado, este módulo poderá liberar apenas os
 * Organizadores que possuírem a autorização correspondente.
 */
final class NecessidadeEspecificaService
{
    public function __construct(
        private NecessidadeEspecificaRepository $repository
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarTodos(): array
    {
        return array_map(
            fn (array $registro): array =>
                $this->formatar($registro),
            $this->repository
                ->listarTodos()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function buscarPorUsuario(
        int $usuarioId
    ): array {
        if (
            $this->repository
                ->buscarUsuarioPorId(
                    $usuarioId
                )
            === null
        ) {
            throw new UsuarioNaoEncontradoException(
                $usuarioId
            );
        }

        $registro =
            $this->repository
                ->buscarPorUsuarioId(
                    $usuarioId
                );

        if ($registro === null) {
            throw new NecessidadeEspecificaNaoEncontradaException(
                $usuarioId
            );
        }

        return $this->formatar(
            $registro
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function salvar(
        int $usuarioId,
        string $observacao
    ): array {
        if (
            $this->repository
                ->buscarUsuarioPorId(
                    $usuarioId
                )
            === null
        ) {
            throw new UsuarioNaoEncontradoException(
                $usuarioId
            );
        }

        $observacao =
            trim($observacao);

        if ($observacao === '') {
            throw new DadosInvalidosException([
                'observacao' =>
                    'Descreva a necessidade específica de forma objetiva e respeitosa.',
            ]);
        }

        if (
            mb_strlen($observacao)
            > 2000
        ) {
            throw new DadosInvalidosException([
                'observacao' =>
                    'A observação deve possuir no máximo 2000 caracteres.',
            ]);
        }

        $this->repository->salvar(
            $usuarioId,
            $observacao
        );

        return $this->buscarPorUsuario(
            $usuarioId
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function desativar(
        int $usuarioId
    ): array {
        $registro =
            $this->repository
                ->buscarPorUsuarioId(
                    $usuarioId
                );

        if ($registro === null) {
            throw new NecessidadeEspecificaNaoEncontradaException(
                $usuarioId
            );
        }

        $jaEstavaInativo =
            !(bool) $registro['ativo'];

        if (!$jaEstavaInativo) {
            $this->repository
                ->desativar(
                    $usuarioId
                );
        }

        return [
            'necessidade' =>
                $this->buscarPorUsuario(
                    $usuarioId
                ),
            'ja_estava_inativa' =>
                $jaEstavaInativo,
        ];
    }

    /**
     * @param array<string, mixed> $registro
     * @return array<string, mixed>
     */
    private function formatar(
        array $registro
    ): array {
        return [
            'id' =>
                (int) $registro['id'],
            'usuario' => [
                'id' =>
                    (int) $registro[
                        'usuario_id'
                    ],
                'nome' =>
                    $registro[
                        'usuario_nome'
                    ],
                'status' =>
                    $registro[
                        'usuario_status'
                    ],
            ],
            'observacao' =>
                $registro['observacao'],
            'ativo' =>
                (bool) $registro['ativo'],
            'criado_em' =>
                $registro['criado_em'],
            'atualizado_em' =>
                $registro[
                    'atualizado_em'
                ],
        ];
    }
}
