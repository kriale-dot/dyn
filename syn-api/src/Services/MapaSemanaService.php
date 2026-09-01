<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Repositories\MapaSemanaRepository;
use DateTimeImmutable;

/**
 * Monta o "Mapa da Semana".
 *
 * Mapa aqui não significa geolocalização. É uma estrutura temporal
 * que responde: quando, onde, o que e qual é a função do usuário.
 */
final class MapaSemanaService
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
        private MapaSemanaRepository $repository
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function obter(
        int $usuarioId,
        ?string $dataReferencia
    ): array {
        $referencia = $this->resolverDataReferencia($dataReferencia);

        $inicioSemana = $referencia
            ->modify('monday this week')
            ->setTime(0, 0, 0);

        $fimExclusivo = $inicioSemana->modify('+7 days');

        $linhas = $this->repository->listarSemana(
            $usuarioId,
            $inicioSemana->format('Y-m-d H:i:s'),
            $fimExclusivo->format('Y-m-d H:i:s')
        );

        $programacoes = $this->agruparProgramacoes($linhas);

        $proximoId = $this->identificarProximoCompromisso(
            $programacoes,
            $referencia
        );

        $dias = $this->montarDias(
            $inicioSemana,
            $programacoes,
            $proximoId
        );

        $meusCompromissos = count(array_filter(
            $programacoes,
            static fn (array $item): bool =>
                $item['meu_compromisso'] === true
        ));

        $pendentes = 0;

        foreach ($programacoes as $programacao) {
            foreach ($programacao['minhas_participacoes'] as $participacao) {
                if ($participacao['status'] === 'ESCALADO') {
                    $pendentes++;
                }
            }
        }

        return [
            'semana' => [
                'data_referencia' => $referencia->format('Y-m-d'),
                'inicio' => $inicioSemana->format('Y-m-d'),
                'fim' => $fimExclusivo
                    ->modify('-1 day')
                    ->format('Y-m-d'),
            ],
            'resumo' => [
                'programacoes' => count($programacoes),
                'meus_compromissos' => $meusCompromissos,
                'participacoes_pendentes' => $pendentes,
                'proximo_compromisso_programacao_id' => $proximoId,
            ],
            'dias' => $dias,
        ];
    }

    private function resolverDataReferencia(
        ?string $valor
    ): DateTimeImmutable {
        if ($valor === null || trim($valor) === '') {
            return new DateTimeImmutable('now');
        }

        $valor = trim($valor);

        $data = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $valor
        );

        if (
            $data === false
            || $data->format('Y-m-d') !== $valor
        ) {
            throw new DadosInvalidosException([
                'data_referencia' =>
                    'Use uma data válida no formato YYYY-MM-DD.',
            ]);
        }

        return $data->setTime(12, 0, 0);
    }

    /**
     * @param array<int, array<string, mixed>> $linhas
     * @return array<int, array<string, mixed>>
     */
    private function agruparProgramacoes(array $linhas): array
    {
        $agrupadas = [];

        foreach ($linhas as $linha) {
            $id = (int) $linha['programacao_id'];

            if (!isset($agrupadas[$id])) {
                $agrupadas[$id] = [
                    'id' => $id,

                    'quando' => [
                        'inicio_em' => $linha['inicio_em'],
                        'fim_em' => $linha['fim_em'],
                    ],

                    'onde' => [
                        'local' => $linha['local_nome_historico'],
                    ],

                    'o_que' => [
                        'titulo' => $linha['titulo'],
                        'tipo' => $linha[
                            'tipo_programacao_nome_historico'
                        ],
                        'descricao' => $linha['descricao'],
                    ],

                    'status' => $linha['programacao_status'],
                    'organizador' => $linha[
                        'organizador_nome_historico'
                    ],
                    'permite_resposta' => (bool) $linha[
                        'permite_resposta'
                    ],

                    'meu_compromisso' => false,
                    'minhas_participacoes' => [],
                ];
            }

            if ($linha['participacao_id'] !== null) {
                $agrupadas[$id]['meu_compromisso'] = true;

                $agrupadas[$id]['minhas_participacoes'][] = [
                    'id' => (int) $linha['participacao_id'],
                    'funcao' => $linha['funcao_nome_historico'],
                    'status' => $linha['participacao_status'],
                    'pendente_confirmacao' =>
                        $linha['participacao_status'] === 'ESCALADO',
                    'observacao' => $linha['participacao_observacao'],
                ];
            }
        }

        return array_values($agrupadas);
    }

    /**
     * @param array<int, array<string, mixed>> $programacoes
     */
    private function identificarProximoCompromisso(
        array $programacoes,
        DateTimeImmutable $referencia
    ): ?int {
        $proximoId = null;
        $proximoInicio = null;

        foreach ($programacoes as $item) {
            if ($item['meu_compromisso'] !== true) {
                continue;
            }

            $inicio = new DateTimeImmutable(
                $item['quando']['inicio_em']
            );

            if ($inicio < $referencia) {
                continue;
            }

            if ($proximoInicio === null || $inicio < $proximoInicio) {
                $proximoInicio = $inicio;
                $proximoId = (int) $item['id'];
            }
        }

        return $proximoId;
    }

    /**
     * @param array<int, array<string, mixed>> $programacoes
     * @return array<int, array<string, mixed>>
     */
    private function montarDias(
        DateTimeImmutable $inicioSemana,
        array $programacoes,
        ?int $proximoId
    ): array {
        $dias = [];

        for ($i = 0; $i < 7; $i++) {
            $data = $inicioSemana->modify("+{$i} days");
            $chave = $data->format('Y-m-d');

            $dias[$chave] = [
                'data' => $chave,
                'dia_semana' => self::NOMES_DIAS[
                    (int) $data->format('N')
                ],
                'tem_programacao' => false,
                'tem_meu_compromisso' => false,
                'programacoes' => [],
            ];
        }

        foreach ($programacoes as $programacao) {
            $data = substr(
                (string) $programacao['quando']['inicio_em'],
                0,
                10
            );

            if (!isset($dias[$data])) {
                continue;
            }

            $programacao['destaque'] = [
                'pessoal' => $programacao['meu_compromisso'],
                'proximo_compromisso' =>
                    $proximoId !== null
                    && (int) $programacao['id'] === $proximoId,
            ];

            $dias[$data]['programacoes'][] = $programacao;
            $dias[$data]['tem_programacao'] = true;

            if ($programacao['meu_compromisso'] === true) {
                $dias[$data]['tem_meu_compromisso'] = true;
            }
        }

        return array_values($dias);
    }
}
