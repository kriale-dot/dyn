<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\HistoricoProgramacaoAcessoNegadoException;
use App\Repositories\HistoricoProgramacaoRepository;

/**
 * Histórico administrativo de alterações importantes.
 *
 * Administrador:
 * acesso total.
 *
 * Organizador:
 * somente tipos atribuídos ao seu escopo.
 *
 * Membro:
 * não acessa o histórico administrativo.
 */
final class HistoricoProgramacaoService
{
    public function __construct(
        private HistoricoProgramacaoRepository $repository
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function listar(
        int $programacaoId,
        int $usuarioAutenticadoId
    ): array {
        if ($programacaoId <= 0) {
            throw new DadosInvalidosException([
                'programacao_id' =>
                    'O ID da programação deve ser maior que zero.',
            ]);
        }

        $programacao =
            $this->repository
                ->buscarProgramacao(
                    $programacaoId
                );

        if ($programacao === null) {
            throw new DadosInvalidosException([
                'programacao' =>
                    'Programação não encontrada.',
            ]);
        }

        $usuario =
            $this->repository
                ->buscarUsuario(
                    $usuarioAutenticadoId
                );

        if (
            $usuario === null
            || $usuario['status']
                !== 'ATIVO'
        ) {
            throw new HistoricoProgramacaoAcessoNegadoException(
                'Usuário autenticado inválido ou inativo.'
            );
        }

        $this->validarPermissao(
            $usuario,
            (int) $programacao[
                'tipo_programacao_id'
            ]
        );

        $eventos =
            $this->repository
                ->listarEventos(
                    $programacaoId
                );

        return [
            'programacao' => [
                'id' =>
                    (int) $programacao['id'],
                'titulo_atual' =>
                    $programacao['titulo'],
                'status_atual' =>
                    $programacao['status'],
                'tipo' => [
                    'id' =>
                        (int) $programacao[
                            'tipo_programacao_id'
                        ],
                    'nome' =>
                        $programacao[
                            'tipo_programacao_nome_historico'
                        ],
                ],
            ],

            'total_eventos' =>
                count($eventos),

            'eventos' =>
                array_map(
                    fn (array $item): array =>
                        $this->formatarEvento(
                            $item
                        ),
                    $eventos
                ),
        ];
    }

    /**
     * @param array<string, mixed> $usuario
     */
    private function validarPermissao(
        array $usuario,
        int $tipoProgramacaoId
    ): void {
        $papel =
            (string) $usuario[
                'papel_codigo'
            ];

        if ($papel === 'ADMINISTRADOR') {
            return;
        }

        if ($papel !== 'ORGANIZADOR') {
            throw new HistoricoProgramacaoAcessoNegadoException(
                'Somente Administrador ou Organizador pode consultar este histórico.'
            );
        }

        if (
            !$this->repository
                ->organizadorPodeAdministrarTipo(
                    (int) $usuario['id'],
                    $tipoProgramacaoId
                )
        ) {
            throw new HistoricoProgramacaoAcessoNegadoException(
                'O Organizador não possui permissão para este tipo de programação.'
            );
        }
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function formatarEvento(
        array $item
    ): array {
        $alteracoes = [];

        $this->adicionarAlteracao(
            $alteracoes,
            'titulo',
            $item['titulo_anterior'],
            $item['titulo_novo']
        );

        $this->adicionarAlteracao(
            $alteracoes,
            'descricao',
            $item['descricao_anterior'],
            $item['descricao_nova']
        );

        $this->adicionarAlteracao(
            $alteracoes,
            'inicio_em',
            $item['inicio_anterior'],
            $item['inicio_novo']
        );

        $this->adicionarAlteracao(
            $alteracoes,
            'fim_em',
            $item['fim_anterior'],
            $item['fim_novo']
        );

        $this->adicionarAlteracao(
            $alteracoes,
            'local',
            $item['local_anterior'],
            $item['local_novo']
        );

        $this->adicionarAlteracao(
            $alteracoes,
            'status',
            $item['status_anterior'],
            $item['status_novo']
        );

        return [
            'id' =>
                (int) $item['id'],
            'tipo' =>
                $item['tipo_evento'],
            'criada_em' =>
                $item['criada_em'],
            'alteracoes' =>
                $alteracoes,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $alteracoes
     */
    private function adicionarAlteracao(
        array &$alteracoes,
        string $campo,
        mixed $anterior,
        mixed $novo
    ): void {
        if ($anterior === $novo) {
            return;
        }

        $alteracoes[] = [
            'campo' => $campo,
            'anterior' => $anterior,
            'novo' => $novo,
        ];
    }
}
