<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Repositories\NotificacaoRepository;

/**
 * Service da Central de Notificações.
 */
final class NotificacaoService
{
    private const LIMITE_PADRAO =
        50;

    private const LIMITE_MAXIMO =
        100;

    public function __construct(
        private NotificacaoRepository $repository
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function listar(
        int $usuarioId,
        bool $somenteNaoLidas,
        ?int $limite
    ): array {
        $this->sincronizar(
            $usuarioId
        );

        $limiteFinal =
            $this->normalizarLimite(
                $limite
            );

        $itens =
            $this->repository
                ->listar(
                    $usuarioId,
                    $somenteNaoLidas,
                    $limiteFinal
                );

        return [
            'nao_lidas' =>
                $this->repository
                    ->contarNaoLidas(
                        $usuarioId
                    ),

            'somente_nao_lidas' =>
                $somenteNaoLidas,

            'limite' =>
                $limiteFinal,

            'notificacoes' =>
                array_map(
                    static fn (
                        array $item
                    ): array => [
                        'id' =>
                            (int) $item['id'],
                        'tipo' =>
                            $item['tipo'],
                        'titulo' =>
                            $item['titulo'],
                        'mensagem' =>
                            $item['mensagem'],
                        'url_acao' =>
                            $item['url_acao'],
                        'lida' =>
                            $item['lida_em']
                                !== null,
                        'lida_em' =>
                            $item['lida_em'],
                        'criada_em' =>
                            $item['criada_em'],
                        'expira_em' =>
                            $item['expira_em'],
                    ],
                    $itens
                ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function resumo(
        int $usuarioId
    ): array {
        $this->sincronizar(
            $usuarioId
        );

        return [
            'nao_lidas' =>
                $this->repository
                    ->contarNaoLidas(
                        $usuarioId
                    ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function marcarComoLida(
        int $usuarioId,
        int $notificacaoId
    ): array {
        if ($notificacaoId <= 0) {
            throw new DadosInvalidosException([
                'notificacao_id' =>
                    'O ID da notificação deve ser maior que zero.',
            ]);
        }

        $alterou =
            $this->repository
                ->marcarComoLida(
                    $usuarioId,
                    $notificacaoId
                );

        if (!$alterou) {
            throw new DadosInvalidosException([
                'notificacao' =>
                    'Notificação não encontrada para o usuário autenticado.',
            ]);
        }

        return [
            'id' =>
                $notificacaoId,
            'lida' =>
                true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function marcarTodasComoLidas(
        int $usuarioId
    ): array {
        $quantidade =
            $this->repository
                ->marcarTodasComoLidas(
                    $usuarioId
                );

        return [
            'quantidade_atualizada' =>
                $quantidade,
            'nao_lidas' =>
                0,
        ];
    }

    private function sincronizar(
        int $usuarioId
    ): void {
        $this->repository
            ->sincronizarEscalasPendentes(
                $usuarioId
            );

        $this->repository
            ->sincronizarProximosCompromissos(
                $usuarioId
            );

        $this->repository
            ->encerrarEscalasPendentesResolvidas(
                $usuarioId
            );

        $this->repository
            ->encerrarProximosCompromissosObsoletos(
                $usuarioId
            );
    }

    private function normalizarLimite(
        ?int $limite
    ): int {
        if ($limite === null) {
            return self::LIMITE_PADRAO;
        }

        if ($limite < 1) {
            throw new DadosInvalidosException([
                'limite' =>
                    'O limite deve ser maior que zero.',
            ]);
        }

        return min(
            $limite,
            self::LIMITE_MAXIMO
        );
    }
}
