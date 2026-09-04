<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\HistoricoSegurancaRepository;

/**
 * Service de leitura da atividade de segurança da conta.
 */
final class HistoricoSegurancaService
{
    public function __construct(
        private HistoricoSegurancaRepository $repository
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function listar(
        int $usuarioId,
        int $limite = 20
    ): array {
        $limite =
            max(
                1,
                min(
                    50,
                    $limite
                )
            );

        $eventos =
            $this->repository
                ->listarDoUsuario(
                    $usuarioId,
                    $limite
                );

        return [
            'total_retornado' =>
                count($eventos),
            'limite' =>
                $limite,
            'eventos' =>
                array_map(
                    static function (
                        array $evento
                    ): array {
                        return [
                            'id' =>
                                (int)
                                $evento['id'],
                            'tipo' =>
                                (string)
                                $evento['tipo'],
                            'titulo' =>
                                (string)
                                $evento['titulo'],
                            'detalhe' =>
                                $evento['detalhe']
                                !== null
                                    ? (string)
                                    $evento['detalhe']
                                    : null,
                            'criado_em' =>
                                (string)
                                $evento['criado_em'],
                        ];
                    },
                    $eventos
                ),
        ];
    }
}
