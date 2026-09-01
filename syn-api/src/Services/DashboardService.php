<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\UsuarioNaoEncontradoException;
use App\Repositories\DashboardRepository;
use DateTimeImmutable;

/**
 * Service do Dashboard.
 *
 * O objetivo é entregar em uma única chamada os dados mais úteis
 * para a futura tela inicial do SYN:
 *
 * - compromissos do próprio usuário;
 * - programação geral da semana;
 * - aniversariantes da semana;
 * - pequenos totais para indicadores visuais.
 *
 * Nenhuma necessidade específica/sensível é exposta aqui.
 */
final class DashboardService
{
    public function __construct(
        private DashboardRepository $repository
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function obterDashboard(
        int $usuarioId,
        ?string $dataReferencia
    ): array {
        $usuario =
            $this->repository
                ->buscarUsuarioPorId(
                    $usuarioId
                );

        if ($usuario === null) {
            throw new UsuarioNaoEncontradoException(
                $usuarioId
            );
        }

        $referencia =
            $this->resolverDataReferencia(
                $dataReferencia
            );

        $inicioSemana =
            $referencia
                ->modify('monday this week')
                ->setTime(0, 0, 0);

        $fimExclusivo =
            $inicioSemana
                ->modify('+7 days');

        $inicioSql =
            $inicioSemana
                ->format('Y-m-d H:i:s');

        $fimSql =
            $fimExclusivo
                ->format('Y-m-d H:i:s');

        $compromissos =
            $this->repository
                ->listarCompromissosDaSemana(
                    $usuarioId,
                    $inicioSql,
                    $fimSql
                );

        $programacoes =
            $this->repository
                ->listarProgramacoesDaSemana(
                    $inicioSql,
                    $fimSql
                );

        $diasDaSemana =
            $this->montarDiasDaSemana(
                $inicioSemana
            );

        $aniversariantes =
            $this->repository
                ->listarAniversariantesPorDiasMes(
                    array_keys(
                        $diasDaSemana
                    )
                );

        $aniversariantesFormatados =
            $this->formatarAniversariantes(
                $aniversariantes,
                $diasDaSemana
            );

        $compromissosFormatados =
            array_map(
                fn (array $item): array =>
                    $this->formatarCompromisso(
                        $item
                    ),
                $compromissos
            );

        $programacoesFormatadas =
            array_map(
                fn (array $item): array =>
                    $this->formatarProgramacao(
                        $item
                    ),
                $programacoes
            );

        $pendentes =
            count(
                array_filter(
                    $compromissos,
                    static fn (
                        array $item
                    ): bool =>
                        $item[
                            'participacao_status'
                        ] === 'ESCALADO'
                )
            );

        $confirmados =
            count(
                array_filter(
                    $compromissos,
                    static fn (
                        array $item
                    ): bool =>
                        $item[
                            'participacao_status'
                        ] === 'CONFIRMADO'
                )
            );

        return [
            'usuario' => [
                'id' =>
                    (int) $usuario['id'],
                'nome' =>
                    $usuario['nome'],
                'foto' =>
                    $usuario['foto'],
                'papel' => [
                    'id' =>
                        (int) $usuario[
                            'papel_id'
                        ],
                    'codigo' =>
                        $usuario[
                            'papel_codigo'
                        ],
                    'nome' =>
                        $usuario[
                            'papel_nome'
                        ],
                ],
            ],

            'semana' => [
                'data_referencia' =>
                    $referencia
                        ->format('Y-m-d'),
                'inicio' =>
                    $inicioSemana
                        ->format('Y-m-d'),
                'fim' =>
                    $fimExclusivo
                        ->modify('-1 day')
                        ->format('Y-m-d'),
            ],

            'resumo' => [
                'meus_compromissos' =>
                    count($compromissos),
                'pendentes_confirmacao' =>
                    $pendentes,
                'confirmados' =>
                    $confirmados,
                'programacoes_da_semana' =>
                    count($programacoes),
                'aniversariantes_da_semana' =>
                    count(
                        $aniversariantesFormatados
                    ),
            ],

            'meus_compromissos' =>
                $compromissosFormatados,

            'programacoes_da_semana' =>
                $programacoesFormatadas,

            'aniversariantes_da_semana' =>
                $aniversariantesFormatados,
        ];
    }

    private function resolverDataReferencia(
        ?string $dataReferencia
    ): DateTimeImmutable {
        if (
            $dataReferencia === null
            || trim($dataReferencia) === ''
        ) {
            return new DateTimeImmutable(
                'today'
            );
        }

        $valor =
            trim(
                $dataReferencia
            );

        $data =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $valor
            );

        if (
            $data === false
            || $data->format('Y-m-d')
                !== $valor
        ) {
            throw new DadosInvalidosException([
                'data_referencia' =>
                    'Use uma data válida no formato YYYY-MM-DD.',
            ]);
        }

        return $data;
    }

    /**
     * Cria um mapa:
     *
     * MM-DD => YYYY-MM-DD
     *
     * Isso também funciona quando a semana atravessa
     * dezembro/janeiro.
     *
     * @return array<string, string>
     */
    private function montarDiasDaSemana(
        DateTimeImmutable $inicioSemana
    ): array {
        $dias = [];

        for ($i = 0; $i < 7; $i++) {
            $data =
                $inicioSemana
                    ->modify(
                        "+{$i} days"
                    );

            $dias[
                $data->format('m-d')
            ] =
                $data->format('Y-m-d');
        }

        return $dias;
    }

    /**
     * @param array<int, array<string, mixed>> $usuarios
     * @param array<string, string> $diasDaSemana
     * @return array<int, array<string, mixed>>
     */
    private function formatarAniversariantes(
        array $usuarios,
        array $diasDaSemana
    ): array {
        $resultado = [];

        foreach ($usuarios as $usuario) {
            $nascimento =
                (string) (
                    $usuario[
                        'data_nascimento'
                    ] ?? ''
                );

            if ($nascimento === '') {
                continue;
            }

            $dataNascimento =
                DateTimeImmutable::createFromFormat(
                    '!Y-m-d',
                    $nascimento
                );

            if ($dataNascimento === false) {
                continue;
            }

            $chave =
                $dataNascimento
                    ->format('m-d');

            if (
                !isset(
                    $diasDaSemana[$chave]
                )
            ) {
                continue;
            }

            $data =
                $diasDaSemana[$chave];

            $resultado[] = [
                'usuario_id' =>
                    (int) $usuario['id'],
                'nome' =>
                    $usuario['nome'],
                'foto' =>
                    $usuario['foto'],
                'data' =>
                    $data,
                'dia' =>
                    (int) substr(
                        $data,
                        8,
                        2
                    ),
                'mes' =>
                    (int) substr(
                        $data,
                        5,
                        2
                    ),
            ];
        }

        usort(
            $resultado,
            static function (
                array $a,
                array $b
            ): int {
                $comparacao =
                    strcmp(
                        $a['data'],
                        $b['data']
                    );

                if ($comparacao !== 0) {
                    return $comparacao;
                }

                return strcmp(
                    $a['nome'],
                    $b['nome']
                );
            }
        );

        return $resultado;
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function formatarCompromisso(
        array $item
    ): array {
        return [
            'participacao_id' =>
                (int) $item[
                    'participacao_id'
                ],
            'participacao_status' =>
                $item[
                    'participacao_status'
                ],
            'pendente_confirmacao' =>
                $item[
                    'participacao_status'
                ] === 'ESCALADO',
            'funcao' =>
                $item[
                    'funcao_nome_historico'
                ],
            'observacao' =>
                $item[
                    'participacao_observacao'
                ],

            'programacao' => [
                'id' =>
                    (int) $item[
                        'programacao_id'
                    ],
                'titulo' =>
                    $item['titulo'],
                'descricao' =>
                    $item['descricao'],
                'inicio_em' =>
                    $item['inicio_em'],
                'fim_em' =>
                    $item['fim_em'],
                'status' =>
                    $item[
                        'programacao_status'
                    ],
                'permite_resposta' =>
                    (bool) $item[
                        'permite_resposta'
                    ],
                'tipo' =>
                    $item[
                        'tipo_programacao_nome_historico'
                    ],
                'local' =>
                    $item[
                        'local_nome_historico'
                    ],
                'organizador' =>
                    $item[
                        'organizador_nome_historico'
                    ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function formatarProgramacao(
        array $item
    ): array {
        return [
            'id' =>
                (int) $item['id'],
            'titulo' =>
                $item['titulo'],
            'descricao' =>
                $item['descricao'],
            'inicio_em' =>
                $item['inicio_em'],
            'fim_em' =>
                $item['fim_em'],
            'status' =>
                $item['status'],
            'tipo' =>
                $item[
                    'tipo_programacao_nome_historico'
                ],
            'local' =>
                $item[
                    'local_nome_historico'
                ],
            'organizador' =>
                $item[
                    'organizador_nome_historico'
                ],
        ];
    }
}
