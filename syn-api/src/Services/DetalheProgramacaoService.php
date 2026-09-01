<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Repositories\DetalheProgramacaoRepository;

/**
 * Monta a resposta completa da tela de detalhe da programação.
 *
 * A tela React poderá usar uma única chamada para apresentar:
 *
 * - informações da atividade;
 * - quando e onde;
 * - organizador;
 * - escala completa;
 * - situação de cada participante;
 * - participação do usuário autenticado;
 * - ações pessoais permitidas.
 */
final class DetalheProgramacaoService
{
    private const STATUS_PARTICIPACAO = [
        'ESCALADO',
        'CONFIRMADO',
        'INDISPONIVEL',
        'RECUSADO',
        'CANCELADO',
    ];

    public function __construct(
        private DetalheProgramacaoRepository $repository
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function obter(
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

        $participacoes =
            $this->repository
                ->listarParticipacoes(
                    $programacaoId
                );

        $escala = [];
        $minhasParticipacoes = [];

        $contadores = array_fill_keys(
            self::STATUS_PARTICIPACAO,
            0
        );

        foreach ($participacoes as $item) {
            $status =
                (string) $item['status'];

            if (isset($contadores[$status])) {
                $contadores[$status]++;
            }

            $ehMinha =
                (int) $item['usuario_id']
                === $usuarioAutenticadoId;

            $participacaoFormatada = [
                'id' =>
                    (int) $item['id'],

                'usuario' => [
                    'id' =>
                        (int) $item['usuario_id'],
                    'nome' =>
                        $item[
                            'usuario_nome_historico'
                        ],
                    'foto_atual' =>
                        $item[
                            'usuario_foto_atual'
                        ],
                    'status_atual' =>
                        $item[
                            'usuario_status_atual'
                        ],
                ],

                'funcao' => [
                    'id' =>
                        (int) $item['funcao_id'],
                    'nome' =>
                        $item[
                            'funcao_nome_historico'
                        ],
                    'departamento' =>
                        $item[
                            'departamento_nome_historico'
                        ],
                ],

                'status' =>
                    $status,

                'observacao' =>
                    $item['observacao'],

                'eh_minha_participacao' =>
                    $ehMinha,
            ];

            $escala[] =
                $participacaoFormatada;

            if ($ehMinha) {
                $participacaoFormatada[
                    'acoes_disponiveis'
                ] =
                    $this->acoesPessoaisDisponiveis(
                        $status,
                        (bool) $programacao[
                            'permite_resposta'
                        ],
                        (string) $programacao[
                            'status'
                        ]
                    );

                $minhasParticipacoes[] =
                    $participacaoFormatada;
            }
        }

        return [
            'programacao' => [
                'id' =>
                    (int) $programacao['id'],
                'serie_id' =>
                    $programacao['serie_id'] !== null
                        ? (int) $programacao['serie_id']
                        : null,
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
                    'nome' =>
                        $programacao[
                            'tipo_programacao_nome_historico'
                        ],
                ],

                'local' => [
                    'id' =>
                        $programacao['local_id'] !== null
                            ? (int) $programacao[
                                'local_id'
                            ]
                            : null,
                    'nome' =>
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
                    'nome' =>
                        $programacao[
                            'organizador_nome_historico'
                        ],
                ],
            ],

            'resumo_escala' => [
                'total' =>
                    count($participacoes),
                'escalados' =>
                    $contadores['ESCALADO'],
                'confirmados' =>
                    $contadores['CONFIRMADO'],
                'indisponiveis' =>
                    $contadores['INDISPONIVEL'],
                'recusados' =>
                    $contadores['RECUSADO'],
                'cancelados' =>
                    $contadores['CANCELADO'],
            ],

            'minhas_participacoes' =>
                $minhasParticipacoes,

            'escala' =>
                $escala,
        ];
    }

    /**
     * Ações que o frontend pode oferecer ao próprio usuário.
     *
     * Não concede autorização por si só: os endpoints específicos
     * continuam validando autenticação e regras de negócio.
     *
     * @return array<int, string>
     */
    private function acoesPessoaisDisponiveis(
        string $statusParticipacao,
        bool $permiteResposta,
        string $statusProgramacao
    ): array {
        if (
            !$permiteResposta
            || $statusProgramacao !== 'AGENDADA'
        ) {
            return [];
        }

        return match ($statusParticipacao) {
            'ESCALADO' => [
                'CONFIRMAR',
                'INDISPONIVEL',
                'RECUSAR',
            ],

            'CONFIRMADO' => [
                'INDISPONIVEL',
                'RECUSAR',
            ],

            default => [],
        };
    }
}
