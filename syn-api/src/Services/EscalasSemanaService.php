<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\GestaoEscalaAcessoNegadoException;
use App\Repositories\EscalasSemanaRepository;
use DateTimeImmutable;

/**
 * Monta a visão administrativa das escalas da semana.
 *
 * Administrador:
 * vê todas as programações.
 *
 * Organizador:
 * vê somente tipos de programação pertencentes ao seu escopo.
 *
 * Membro:
 * não acessa esta projeção administrativa.
 */
final class EscalasSemanaService
{
    private const NOMES_DIAS = [
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    public function __construct(
        private EscalasSemanaRepository $repository
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function obter(
        int $usuarioAutenticadoId,
        ?string $dataReferencia
    ): array {
        $usuario =
            $this->repository
                ->buscarUsuarioAutenticado(
                    $usuarioAutenticadoId
                );

        if (
            $usuario === null
            || $usuario['status'] !== 'ATIVO'
        ) {
            throw new GestaoEscalaAcessoNegadoException(
                'Usuário autenticado inválido ou inativo.'
            );
        }

        $papel =
            (string) $usuario[
                'papel_codigo'
            ];

        if (
            !in_array(
                $papel,
                [
                    'ADMINISTRADOR',
                    'ORGANIZADOR',
                ],
                true
            )
        ) {
            throw new GestaoEscalaAcessoNegadoException(
                'Somente Administrador ou Organizador pode consultar as escalas da semana.'
            );
        }

        $referencia =
            $this->resolverDataReferencia(
                $dataReferencia
            );

        $inicioSemana =
            $referencia
                ->modify(
                    'monday this week'
                )
                ->setTime(
                    0,
                    0,
                    0
                );

        $fimExclusivo =
            $inicioSemana
                ->modify(
                    '+7 days'
                );

        $programacoes =
            $this->repository
                ->listarProgramacoesSemana(
                    $inicioSemana
                        ->format(
                            'Y-m-d H:i:s'
                        ),
                    $fimExclusivo
                        ->format(
                            'Y-m-d H:i:s'
                        ),
                    (int) $usuario['id'],
                    $papel
                );

        $ids =
            array_map(
                static fn (
                    array $item
                ): int =>
                    (int) $item['id'],
                $programacoes
            );

        $participacoes =
            $this->repository
                ->listarParticipacoes(
                    $ids
                );

        $tipoIds =
            array_values(
                array_unique(
                    array_map(
                        static fn (
                            array $item
                        ): int =>
                            (int) $item[
                                'tipo_programacao_id'
                            ],
                        $programacoes
                    )
                )
            );

        $funcoesHabilitadas =
            $this->repository
                ->listarFuncoesHabilitadasPorTipos(
                    $tipoIds
                );

        $porProgramacao =
            $this->indexarParticipacoes(
                $participacoes
            );

        $funcoesPorTipo =
            $this->indexarFuncoesPorTipo(
                $funcoesHabilitadas
            );

        $programacoesFormatadas = [];

        foreach (
            $programacoes
            as $programacao
        ) {
            $programacaoId =
                (int) $programacao['id'];

            $escala =
                $porProgramacao[
                    $programacaoId
                ]
                ?? [];

            $programacoesFormatadas[] =
                $this->formatarProgramacao(
                    $programacao,
                    $escala,
                    $funcoesPorTipo[
                        (int) $programacao[
                            'tipo_programacao_id'
                        ]
                    ]
                    ?? []
                );
        }

        $dias =
            $this->montarDias(
                $inicioSemana,
                $programacoesFormatadas
            );

        return [
            'semana' => [
                'data_referencia' =>
                    $referencia
                        ->format(
                            'Y-m-d'
                        ),
                'inicio' =>
                    $inicioSemana
                        ->format(
                            'Y-m-d'
                        ),
                'fim' =>
                    $fimExclusivo
                        ->modify(
                            '-1 day'
                        )
                        ->format(
                            'Y-m-d'
                        ),
                'numero_iso' =>
                    (int) $inicioSemana
                        ->format('W'),
                'ano_iso' =>
                    (int) $inicioSemana
                        ->format('o'),
            ],

            'gestor' => [
                'usuario_id' =>
                    (int) $usuario['id'],
                'nome' =>
                    $usuario['nome'],
                'papel' =>
                    $papel,
            ],

            'resumo' =>
                $this->resumirSemana(
                    $programacoesFormatadas
                ),

            'dias' =>
                $dias,
        ];
    }

    private function resolverDataReferencia(
        ?string $valor
    ): DateTimeImmutable {
        if (
            $valor === null
            || trim($valor) === ''
        ) {
            return new DateTimeImmutable(
                'now'
            );
        }

        $valor =
            trim($valor);

        $data =
            DateTimeImmutable
                ::createFromFormat(
                    '!Y-m-d',
                    $valor
                );

        if (
            $data === false
            || $data->format(
                'Y-m-d'
            ) !== $valor
        ) {
            throw new DadosInvalidosException([
                'data_referencia' =>
                    'Use uma data válida no formato YYYY-MM-DD.',
            ]);
        }

        return $data->setTime(
            12,
            0,
            0
        );
    }

    /**
     * @param array<int, array<string, mixed>> $participacoes
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function indexarParticipacoes(
        array $participacoes
    ): array {
        $indice = [];

        foreach (
            $participacoes
            as $item
        ) {
            $programacaoId =
                (int) $item[
                    'programacao_id'
                ];

            if (
                !isset(
                    $indice[
                        $programacaoId
                    ]
                )
            ) {
                $indice[
                    $programacaoId
                ] = [];
            }

            $indice[
                $programacaoId
            ][] = $item;
        }

        return $indice;
    }

    /**
     * @param array<int, array<string, mixed>> $funcoes
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function indexarFuncoesPorTipo(
        array $funcoes
    ): array {
        $indice = [];

        foreach (
            $funcoes
            as $item
        ) {
            $tipoId =
                (int) $item[
                    'tipo_programacao_id'
                ];

            if (
                !isset(
                    $indice[$tipoId]
                )
            ) {
                $indice[$tipoId] = [];
            }

            $indice[$tipoId][] = [
                'id' =>
                    (int) $item[
                        'funcao_id'
                    ],

                'nome' =>
                    $item[
                        'funcao_nome'
                    ],

                'departamento' => [
                    'id' =>
                        $item[
                            'departamento_id'
                        ] !== null
                            ? (int) $item[
                                'departamento_id'
                            ]
                            : null,

                    'nome' =>
                        $item[
                            'departamento_nome'
                        ],
                ],
            ];
        }

        return $indice;
    }

    /**
     * @param array<string, mixed> $programacao
     * @param array<int, array<string, mixed>> $escala
     * @param array<int, array<string, mixed>> $funcoesHabilitadas
     * @return array<string, mixed>
     */
    private function formatarProgramacao(
        array $programacao,
        array $escala,
        array $funcoesHabilitadas
    ): array {
        $contagem = [
            'total' => 0,
            'ativas' => 0,
            'escalados' => 0,
            'confirmados' => 0,
            'indisponiveis' => 0,
            'recusados' => 0,
            'cancelados' => 0,
        ];

        $escalaFormatada = [];

        foreach (
            $escala
            as $participacao
        ) {
            $status =
                (string) $participacao[
                    'status'
                ];

            $contagem['total']++;

            if (
                in_array(
                    $status,
                    [
                        'ESCALADO',
                        'CONFIRMADO',
                    ],
                    true
                )
            ) {
                $contagem['ativas']++;
            }

            if ($status === 'ESCALADO') {
                $contagem['escalados']++;
            }

            if ($status === 'CONFIRMADO') {
                $contagem['confirmados']++;
            }

            if ($status === 'INDISPONIVEL') {
                $contagem['indisponiveis']++;
            }

            if ($status === 'RECUSADO') {
                $contagem['recusados']++;
            }

            if ($status === 'CANCELADO') {
                $contagem['cancelados']++;
            }

            $escalaFormatada[] = [
                'participacao_id' =>
                    (int) $participacao['id'],

                'usuario' => [
                    'id' =>
                        (int) $participacao[
                            'usuario_id'
                        ],
                    'nome_historico' =>
                        $participacao[
                            'usuario_nome_historico'
                        ],
                ],

                'funcao' => [
                    'id' =>
                        (int) $participacao[
                            'funcao_id'
                        ],
                    'nome_historico' =>
                        $participacao[
                            'funcao_nome_historico'
                        ],
                    'departamento_nome_historico' =>
                        $participacao[
                            'departamento_nome_historico'
                        ],
                ],

                'status' =>
                    $status,

                'observacao' =>
                    $participacao[
                        'observacao'
                    ],
            ];
        }

        $baseConfirmacao =
            $contagem['escalados']
            + $contagem['confirmados'];

        $percentual =
            $baseConfirmacao > 0
                ? (int) round(
                    (
                        $contagem[
                            'confirmados'
                        ]
                        / $baseConfirmacao
                    )
                    * 100
                )
                : 0;

        $funcoesComParticipanteAtivo = [];

        foreach (
            $escala
            as $participacao
        ) {
            if (
                !in_array(
                    (string) $participacao[
                        'status'
                    ],
                    [
                        'ESCALADO',
                        'CONFIRMADO',
                    ],
                    true
                )
            ) {
                continue;
            }

            $funcoesComParticipanteAtivo[
                (int) $participacao[
                    'funcao_id'
                ]
            ] = true;
        }

        $funcoesSemParticipante = [];
        $funcoesComParticipante = [];

        foreach (
            $funcoesHabilitadas
            as $funcao
        ) {
            $funcaoId =
                (int) $funcao['id'];

            if (
                isset(
                    $funcoesComParticipanteAtivo[
                        $funcaoId
                    ]
                )
            ) {
                $funcoesComParticipante[] =
                    $funcao;
            } else {
                $funcoesSemParticipante[] =
                    $funcao;
            }
        }

        $totalFuncoesHabilitadas =
            count(
                $funcoesHabilitadas
            );

        $totalFuncoesComParticipante =
            count(
                $funcoesComParticipante
            );

        $percentualCobertura =
            $totalFuncoesHabilitadas > 0
                ? (int) round(
                    (
                        $totalFuncoesComParticipante
                        / $totalFuncoesHabilitadas
                    )
                    * 100
                )
                : 0;

        return [
            'id' =>
                (int) $programacao['id'],
            'titulo' =>
                $programacao['titulo'],
            'descricao' =>
                $programacao['descricao'],
            'status' =>
                $programacao['status'],
            'permite_resposta' =>
                (bool) $programacao[
                    'permite_resposta'
                ],

            'quando' => [
                'inicio_em' =>
                    $programacao[
                        'inicio_em'
                    ],
                'fim_em' =>
                    $programacao[
                        'fim_em'
                    ],
            ],

            'tipo' => [
                'id' =>
                    (int) $programacao[
                        'tipo_programacao_id'
                    ],
                'nome_historico' =>
                    $programacao[
                        'tipo_programacao_nome_historico'
                    ],
            ],

            'local' => [
                'id' =>
                    $programacao[
                        'local_id'
                    ] !== null
                        ? (int) $programacao[
                            'local_id'
                        ]
                        : null,
                'nome_historico' =>
                    $programacao[
                        'local_nome_historico'
                    ],
            ],

            'organizador' => [
                'id' =>
                    $programacao[
                        'organizador_id'
                    ] !== null
                        ? (int) $programacao[
                            'organizador_id'
                        ]
                        : null,
                'nome_historico' =>
                    $programacao[
                        'organizador_nome_historico'
                    ],
            ],

            'situacao_escala' =>
                $this->situacaoEscala(
                    $contagem,
                    (string) $programacao[
                        'status'
                    ]
                ),

            /**
             * Cobertura das funções HABILITADAS para o tipo.
             *
             * Uma função sem participante aqui não é
             * automaticamente uma função obrigatória ausente.
             * O dado serve como apoio visual ao gestor.
             */
            'cobertura_funcoes' => [
                'habilitadas_total' =>
                    $totalFuncoesHabilitadas,

                'com_participante_ativo' =>
                    $totalFuncoesComParticipante,

                'sem_participante_ativo' =>
                    count(
                        $funcoesSemParticipante
                    ),

                'percentual_cobertura' =>
                    $percentualCobertura,

                'funcoes_com_participante' =>
                    $funcoesComParticipante,

                'funcoes_sem_participante' =>
                    $funcoesSemParticipante,
            ],

            'resumo_escala' => [
                ...$contagem,
                'percentual_confirmacao' =>
                    $percentual,
            ],

            'escala' =>
                $escalaFormatada,
        ];
    }

    /**
     * @param array<string, int> $contagem
     */
    private function situacaoEscala(
        array $contagem,
        string $statusProgramacao
    ): string {
        /**
         * Programação já realizada ou cancelada não deve gerar
         * alerta operacional de "sem escala" na semana atual.
         */
        if (
            $statusProgramacao
            !== 'AGENDADA'
        ) {
            return 'ENCERRADA';
        }

        if (
            $contagem['total'] === 0
        ) {
            return 'SEM_ESCALA';
        }

        if (
            $contagem['ativas'] === 0
        ) {
            return 'SEM_PARTICIPANTES_ATIVOS';
        }

        if (
            $contagem['escalados'] > 0
        ) {
            return 'PENDENTE_CONFIRMACAO';
        }

        return 'CONFIRMADA';
    }

    /**
     * @param array<int, array<string, mixed>> $programacoes
     * @return array<string, int>
     */
    private function resumirSemana(
        array $programacoes
    ): array {
        $resumo = [
            'programacoes' =>
                count($programacoes),
            'programacoes_sem_escala' =>
                0,
            'programacoes_pendentes' =>
                0,
            'programacoes_confirmadas' =>
                0,
            'programacoes_encerradas' =>
                0,

            'funcoes_habilitadas' =>
                0,
            'funcoes_com_participante' =>
                0,
            'funcoes_sem_participante' =>
                0,

            'participacoes_ativas' =>
                0,
            'pendentes_confirmacao' =>
                0,
            'confirmadas' =>
                0,
        ];

        foreach (
            $programacoes
            as $item
        ) {
            if (
                $item[
                    'situacao_escala'
                ] === 'SEM_ESCALA'
                || $item[
                    'situacao_escala'
                ] === 'SEM_PARTICIPANTES_ATIVOS'
            ) {
                $resumo[
                    'programacoes_sem_escala'
                ]++;
            }

            if (
                $item[
                    'situacao_escala'
                ] === 'PENDENTE_CONFIRMACAO'
            ) {
                $resumo[
                    'programacoes_pendentes'
                ]++;
            }

            if (
                $item[
                    'situacao_escala'
                ] === 'CONFIRMADA'
            ) {
                $resumo[
                    'programacoes_confirmadas'
                ]++;
            }

            if (
                $item[
                    'situacao_escala'
                ] === 'ENCERRADA'
            ) {
                $resumo[
                    'programacoes_encerradas'
                ]++;
            }

            /**
             * Somamos a cobertura por ocorrência.
             * Uma mesma função pode aparecer em várias
             * programações da semana, o que é intencional.
             */
            $resumo[
                'funcoes_habilitadas'
            ] +=
                (int) $item[
                    'cobertura_funcoes'
                ][
                    'habilitadas_total'
                ];

            $resumo[
                'funcoes_com_participante'
            ] +=
                (int) $item[
                    'cobertura_funcoes'
                ][
                    'com_participante_ativo'
                ];

            $resumo[
                'funcoes_sem_participante'
            ] +=
                (int) $item[
                    'cobertura_funcoes'
                ][
                    'sem_participante_ativo'
                ];

            $resumo[
                'participacoes_ativas'
            ] +=
                (int) $item[
                    'resumo_escala'
                ][
                    'ativas'
                ];

            $resumo[
                'pendentes_confirmacao'
            ] +=
                (int) $item[
                    'resumo_escala'
                ][
                    'escalados'
                ];

            $resumo[
                'confirmadas'
            ] +=
                (int) $item[
                    'resumo_escala'
                ][
                    'confirmados'
                ];
        }

        return $resumo;
    }

    /**
     * @param array<int, array<string, mixed>> $programacoes
     * @return array<int, array<string, mixed>>
     */
    private function montarDias(
        DateTimeImmutable $inicioSemana,
        array $programacoes
    ): array {
        $dias = [];

        for (
            $i = 0;
            $i < 7;
            $i++
        ) {
            $data =
                $inicioSemana
                    ->modify(
                        "+{$i} days"
                    );

            $chave =
                $data->format(
                    'Y-m-d'
                );

            $dias[$chave] = [
                'data' =>
                    $chave,
                'dia_semana' =>
                    self::NOMES_DIAS[
                        (int) $data
                            ->format('N')
                    ],
                'programacoes' =>
                    [],
            ];
        }

        foreach (
            $programacoes
            as $programacao
        ) {
            $data =
                substr(
                    (string) $programacao[
                        'quando'
                    ][
                        'inicio_em'
                    ],
                    0,
                    10
                );

            if (
                isset(
                    $dias[$data]
                )
            ) {
                $dias[$data][
                    'programacoes'
                ][] = $programacao;
            }
        }

        return array_values(
            $dias
        );
    }
}
