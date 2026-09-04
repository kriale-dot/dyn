<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Repositories\PublicoRepository;
use DateTimeImmutable;

/**
 * Regras da área pública do SYN.
 *
 * Não existe usuário autenticado nesta camada.
 * A regra fundamental é:
 *
 *   só pode sair daqui informação explicitamente publicável.
 */
final class PublicoService
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
        private PublicoRepository $repository
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function igreja(): ?array
    {
        $item =
            $this->repository
                ->buscarIgreja();

        if ($item === null) {
            return null;
        }

        return [
            'nome' =>
                $item['nome'],

            'logotipo' =>
                $item['logotipo'],

            'endereco' => [
                'cep' =>
                    $item['cep'],
                'logradouro' =>
                    $item['logradouro'],
                'numero' =>
                    $item['numero'],
                'complemento' =>
                    $item['complemento'],
                'bairro' =>
                    $item['bairro'],
                'cidade' =>
                    $item['cidade'],
                'estado' =>
                    $item['estado'],
            ],

            'contatos' => [
                'telefone' =>
                    $item['telefone'],
                'email' =>
                    $item['email'],
                'site' =>
                    $item['site'],
            ],
        ];
    }

    /**
     * Mapa público de segunda-feira a domingo.
     *
     * @return array<string, mixed>
     */
    public function mapaSemana(
        ?string $dataReferencia
    ): array {
        $referencia =
            $this->resolverData(
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
                ->modify('+7 days');

        $linhas =
            $this->repository
                ->listarProgramacoes(
                    $inicioSemana
                        ->format(
                            'Y-m-d H:i:s'
                        ),
                    $fimExclusivo
                        ->format(
                            'Y-m-d H:i:s'
                        )
                );

        $porDia = [];

        foreach ($linhas as $linha) {
            $data =
                substr(
                    (string)
                    $linha['inicio_em'],
                    0,
                    10
                );

            $porDia[$data][] =
                $this->formatarProgramacao(
                    $linha
                );
        }

        $dias = [];
        $cursor = $inicioSemana;

        for ($i = 0; $i < 7; $i++) {
            $data =
                $cursor->format(
                    'Y-m-d'
                );

            $itens =
                $porDia[$data]
                ?? [];

            $dias[] = [
                'data' => $data,
                'dia_semana' =>
                    self::NOMES_DIAS[
                        (int)
                        $cursor->format('N')
                    ],
                'tem_programacao' =>
                    $itens !== [],
                'programacoes' =>
                    $itens,
            ];

            $cursor =
                $cursor
                    ->modify('+1 day');
        }

        $canceladas =
            count(
                array_filter(
                    $linhas,
                    static fn (
                        array $item
                    ): bool =>
                        $item['status']
                        === 'CANCELADA'
                )
            );

        return [
            'semana' => [
                'data_referencia' =>
                    $referencia
                        ->format('Y-m-d'),
                'inicio' =>
                    $inicioSemana
                        ->format('Y-m-d'),
                'fim' =>
                    $inicioSemana
                        ->modify('+6 days')
                        ->format('Y-m-d'),
                'numero_iso' =>
                    (int)
                    $inicioSemana
                        ->format('W'),
                'ano_iso' =>
                    (int)
                    $inicioSemana
                        ->format('o'),
            ],

            'resumo' => [
                'programacoes' =>
                    count($linhas),
                'canceladas' =>
                    $canceladas,
            ],

            'dias' =>
                $dias,
        ];
    }

    /**
     * Lista pública para páginas como "Próximas programações".
     *
     * Sem filtros, usamos hoje até 90 dias.
     * O intervalo máximo aceito é 180 dias.
     *
     * @return array<string, mixed>
     */
    public function programacoes(
        ?string $dataInicial,
        ?string $dataFinal
    ): array {
        $inicio =
            $dataInicial === null
                || trim($dataInicial) === ''
            ? new DateTimeImmutable(
                'today'
            )
            : $this->resolverData(
                $dataInicial
            );

        $fim =
            $dataFinal === null
                || trim($dataFinal) === ''
            ? $inicio
                ->modify('+90 days')
            : $this->resolverData(
                $dataFinal
            );

        if ($fim < $inicio) {
            throw new DadosInvalidosException([
                'data_final' =>
                    'A data final deve ser igual ou posterior à data inicial.',
            ]);
        }

        $dias =
            (int)
            $inicio
                ->diff($fim)
                ->format('%a');

        if ($dias > 180) {
            throw new DadosInvalidosException([
                'periodo' =>
                    'A consulta pública pode abranger no máximo 180 dias.',
            ]);
        }

        $fimExclusivo =
            $fim
                ->modify('+1 day')
                ->setTime(
                    0,
                    0,
                    0
                );

        $linhas =
            $this->repository
                ->listarProgramacoes(
                    $inicio
                        ->setTime(
                            0,
                            0,
                            0
                        )
                        ->format(
                            'Y-m-d H:i:s'
                        ),
                    $fimExclusivo
                        ->format(
                            'Y-m-d H:i:s'
                        )
                );

        return [
            'periodo' => [
                'inicio' =>
                    $inicio
                        ->format('Y-m-d'),
                'fim' =>
                    $fim
                        ->format('Y-m-d'),
            ],

            'total' =>
                count($linhas),

            'programacoes' =>
                array_map(
                    fn (
                        array $item
                    ): array =>
                        $this
                            ->formatarProgramacao(
                                $item
                            ),
                    $linhas
                ),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function programacao(
        int $id
    ): ?array {
        if ($id < 1) {
            throw new DadosInvalidosException([
                'id' =>
                    'Informe um ID de programação válido.',
            ]);
        }

        $item =
            $this->repository
                ->buscarProgramacaoPorId(
                    $id
                );

        return $item === null
            ? null
            : $this
                ->formatarProgramacao(
                    $item
                );
    }

    /**
     * Projeção pública.
     *
     * Observe que propositalmente NÃO existem aqui:
     * - organizador;
     * - participações;
     * - nomes de membros;
     * - funções;
     * - observações internas;
     * - histórico administrativo.
     *
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function formatarProgramacao(
        array $item
    ): array {
        return [
            'id' =>
                (int)
                $item['id'],

            'quando' => [
                'inicio_em' =>
                    $item['inicio_em'],
                'fim_em' =>
                    $item['fim_em'],
            ],

            'onde' => [
                'local' =>
                    $item[
                        'local_nome_historico'
                    ],
            ],

            'o_que' => [
                'titulo' =>
                    $item['titulo'],
                'tipo' =>
                    $item[
                        'tipo_programacao_nome_historico'
                    ],
                'descricao' =>
                    $item[
                        'descricao_publica'
                    ],
            ],

            'status' =>
                $item['status'],
        ];
    }

    private function resolverData(
        ?string $valor
    ): DateTimeImmutable {
        if (
            $valor === null
            || trim($valor) === ''
        ) {
            return new DateTimeImmutable(
                'today'
            );
        }

        $valor = trim($valor);

        $data =
            DateTimeImmutable
                ::createFromFormat(
                    '!Y-m-d',
                    $valor
                );

        if (
            $data === false
            || $data->format('Y-m-d')
                !== $valor
        ) {
            throw new DadosInvalidosException([
                'data' =>
                    'Use uma data válida no formato YYYY-MM-DD.',
            ]);
        }

        return $data;
    }
}
