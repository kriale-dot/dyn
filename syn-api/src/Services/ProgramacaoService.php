<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ConflitoLocalException;
use App\Exceptions\DadosInvalidosException;
use App\Exceptions\ProgramacaoNaoEncontradaException;
use App\Repositories\ProgramacaoRepository;
use DateTimeImmutable;

/**
 * Service do módulo de programações.
 */
final class ProgramacaoService
{
    public function __construct(
        private ProgramacaoRepository $repository
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarTodas(): array
    {
        $programacoes =
            $this->repository->listarTodas();

        return array_map(
            fn (array $programacao): array =>
                $this->formatarProgramacao(
                    $programacao
                ),
            $programacoes
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function buscarPorId(int $id): array
    {
        $programacao =
            $this->repository->buscarPorId($id);

        if ($programacao === null) {
            throw new ProgramacaoNaoEncontradaException(
                $id
            );
        }

        return $this->formatarProgramacao(
            $programacao
        );
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function criar(array $dados): array
    {
        $dadosValidados =
            $this->validarDadosProgramacao(
                $dados
            );

        $dadosComReferencias =
            $this->resolverReferencias(
                $dadosValidados
            );

        $conflitos =
            $this->repository
                ->buscarConflitosDeLocal(
                    $dadosComReferencias[
                        'local_id'
                    ],
                    $dadosComReferencias[
                        'inicio_em'
                    ],
                    $dadosComReferencias[
                        'fim_em'
                    ]
                );

        $confirmarConflito =
            $this->normalizarBooleano(
                $dados[
                    'confirmar_conflito'
                ] ?? false,
                'confirmar_conflito'
            );

        if (
            $conflitos !== []
            && !$confirmarConflito
        ) {
            throw new ConflitoLocalException(
                $this->formatarConflitos(
                    $conflitos
                )
            );
        }

        $id =
            $this->repository->criar(
                $dadosComReferencias
            );

        return [
            'programacao' =>
                $this->buscarPorId($id),
            'conflito_confirmado' =>
                $conflitos !== []
                && $confirmarConflito,
            'conflitos_detectados' =>
                $this->formatarConflitos(
                    $conflitos
                ),
        ];
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function atualizar(
        int $id,
        array $dados
    ): array {
        $programacaoAtual =
            $this->repository->buscarPorId($id);

        if ($programacaoAtual === null) {
            throw new ProgramacaoNaoEncontradaException(
                $id
            );
        }

        if (
            in_array(
                $programacaoAtual['status'],
                ['CANCELADA', 'REALIZADA'],
                true
            )
        ) {
            throw new DadosInvalidosException([
                'programacao' =>
                    'Programações canceladas ou realizadas não podem ser editadas por esta rota.',
            ]);
        }

        $dadosValidados =
            $this->validarDadosProgramacao(
                $dados
            );

        $dadosComReferencias =
            $this->resolverReferencias(
                $dadosValidados
            );

        $conflitos =
            $this->repository
                ->buscarConflitosDeLocal(
                    $dadosComReferencias[
                        'local_id'
                    ],
                    $dadosComReferencias[
                        'inicio_em'
                    ],
                    $dadosComReferencias[
                        'fim_em'
                    ],
                    $id
                );

        $confirmarConflito =
            $this->normalizarBooleano(
                $dados[
                    'confirmar_conflito'
                ] ?? false,
                'confirmar_conflito'
            );

        if (
            $conflitos !== []
            && !$confirmarConflito
        ) {
            throw new ConflitoLocalException(
                $this->formatarConflitos(
                    $conflitos
                )
            );
        }

        $this->repository->atualizar(
            $id,
            $dadosComReferencias
        );

        return [
            'programacao' =>
                $this->buscarPorId($id),
            'conflito_confirmado' =>
                $conflitos !== []
                && $confirmarConflito,
            'conflitos_detectados' =>
                $this->formatarConflitos(
                    $conflitos
                ),
        ];
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function cancelar(
        int $id,
        array $dados
    ): array {
        $programacao =
            $this->repository->buscarPorId($id);

        if ($programacao === null) {
            throw new ProgramacaoNaoEncontradaException(
                $id
            );
        }

        $motivo =
            $this->textoOpcional(
                $dados['motivo'] ?? null
            );

        if (
            $motivo !== null
            && mb_strlen($motivo) > 500
        ) {
            throw new DadosInvalidosException([
                'motivo' =>
                    'O motivo do cancelamento deve possuir no máximo 500 caracteres.',
            ]);
        }

        if (
            $programacao['status']
            === 'REALIZADA'
        ) {
            throw new DadosInvalidosException([
                'programacao' =>
                    'Uma programação realizada não pode ser cancelada.',
            ]);
        }

        $jaEstavaCancelada =
            $programacao['status']
            === 'CANCELADA';

        if (!$jaEstavaCancelada) {
            $this->repository->cancelar(
                $id,
                $motivo
            );
        }

        return [
            'programacao' =>
                $this->buscarPorId($id),
            'ja_estava_cancelada' =>
                $jaEstavaCancelada,
        ];
    }

    /**
     * Marca a programação como REALIZADA.
     *
     * Regras desta etapa:
     * - AGENDADA -> REALIZADA;
     * - REALIZADA -> operação idempotente;
     * - CANCELADA -> bloqueada;
     * - RASCUNHO -> bloqueado.
     *
     * As participações não são alteradas automaticamente.
     *
     * @return array<string, mixed>
     */
    public function realizar(int $id): array
    {
        $programacao =
            $this->repository->buscarPorId($id);

        if ($programacao === null) {
            throw new ProgramacaoNaoEncontradaException(
                $id
            );
        }

        if (
            $programacao['status']
            === 'CANCELADA'
        ) {
            throw new DadosInvalidosException([
                'programacao' =>
                    'Uma programação cancelada não pode ser marcada como realizada.',
            ]);
        }

        if (
            $programacao['status']
            === 'RASCUNHO'
        ) {
            throw new DadosInvalidosException([
                'programacao' =>
                    'Uma programação em rascunho deve ser agendada antes de ser realizada.',
            ]);
        }

        $jaEstavaRealizada =
            $programacao['status']
            === 'REALIZADA';

        if (!$jaEstavaRealizada) {
            $this->repository->realizar($id);
        }

        return [
            'programacao' =>
                $this->buscarPorId($id),
            'ja_estava_realizada' =>
                $jaEstavaRealizada,
        ];
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    private function validarDadosProgramacao(
        array $dados
    ): array {
        $erros = [];

        $titulo = trim(
            (string) ($dados['titulo'] ?? '')
        );

        if ($titulo === '') {
            $erros['titulo'] =
                'O título da programação é obrigatório.';
        } elseif (mb_strlen($titulo) > 180) {
            $erros['titulo'] =
                'O título deve possuir no máximo 180 caracteres.';
        }

        $tipoId =
            $this->validarId(
                $dados[
                    'tipo_programacao_id'
                ] ?? null,
                'tipo_programacao_id',
                $erros
            );

        $localId =
            $this->validarId(
                $dados['local_id'] ?? null,
                'local_id',
                $erros
            );

        $organizadorId =
            $this->validarId(
                $dados[
                    'organizador_id'
                ] ?? null,
                'organizador_id',
                $erros
            );

        $inicioEm =
            $this->normalizarDataHora(
                $dados['inicio_em'] ?? null,
                'inicio_em',
                $erros
            );

        $fimEm =
            $this->normalizarDataHora(
                $dados['fim_em'] ?? null,
                'fim_em',
                $erros
            );

        if (
            $inicioEm !== null
            && $fimEm !== null
            && $fimEm <= $inicioEm
        ) {
            $erros['fim_em'] =
                'O horário final deve ser posterior ao horário inicial.';
        }

        $permiteResposta = true;

        try {
            $permiteResposta =
                $this->normalizarBooleano(
                    $dados[
                        'permite_resposta'
                    ] ?? true,
                    'permite_resposta'
                );
        } catch (DadosInvalidosException $e) {
            $erros = array_merge(
                $erros,
                $e->getErros()
            );
        }

        if ($erros !== []) {
            throw new DadosInvalidosException(
                $erros
            );
        }

        return [
            'titulo' =>
                $titulo,
            'descricao' =>
                $this->textoOpcional(
                    $dados[
                        'descricao'
                    ] ?? null
                ),
            'tipo_programacao_id' =>
                $tipoId,
            'local_id' =>
                $localId,
            'organizador_id' =>
                $organizadorId,
            'inicio_em' =>
                $inicioEm,
            'fim_em' =>
                $fimEm,
            'permite_resposta' =>
                $permiteResposta,
        ];
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    private function resolverReferencias(
        array $dados
    ): array {
        $erros = [];

        $tipo =
            $this->repository
                ->buscarTipoProgramacaoPorId(
                    $dados[
                        'tipo_programacao_id'
                    ]
                );

        if ($tipo === null) {
            $erros[
                'tipo_programacao_id'
            ] =
                'O tipo de programação informado não existe.';
        } elseif (!(bool) $tipo['ativo']) {
            $erros[
                'tipo_programacao_id'
            ] =
                'O tipo de programação informado está inativo.';
        }

        $local =
            $this->repository
                ->buscarLocalPorId(
                    $dados['local_id']
                );

        if ($local === null) {
            $erros['local_id'] =
                'O local informado não existe.';
        } elseif (!(bool) $local['ativo']) {
            $erros['local_id'] =
                'O local informado está inativo.';
        }

        $organizador =
            $this->repository
                ->buscarOrganizadorPorId(
                    $dados[
                        'organizador_id'
                    ]
                );

        if ($organizador === null) {
            $erros[
                'organizador_id'
            ] =
                'O responsável/organizador informado não existe.';
        } elseif (
            $organizador['status']
            !== 'ATIVO'
        ) {
            $erros[
                'organizador_id'
            ] =
                'O responsável/organizador informado está inativo.';
        }

        if ($erros !== []) {
            throw new DadosInvalidosException(
                $erros
            );
        }

        return array_merge(
            $dados,
            [
                'tipo_programacao_nome_historico' =>
                    $tipo['nome'],
                'local_nome_historico' =>
                    $local['nome'],
                'organizador_nome_historico' =>
                    $organizador['nome'],
            ]
        );
    }

    /**
     * @param array<string, string> $erros
     */
    private function validarId(
        mixed $valor,
        string $campo,
        array &$erros
    ): ?int {
        $id = filter_var(
            $valor,
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1,
                ],
            ]
        );

        if ($id === false) {
            $erros[$campo] =
                'Informe um ID válido.';
            return null;
        }

        return (int) $id;
    }

    /**
     * @param array<string, string> $erros
     */
    private function normalizarDataHora(
        mixed $valor,
        string $campo,
        array &$erros
    ): ?string {
        if (!is_string($valor)) {
            $erros[$campo] =
                'Informe data e hora válidas.';
            return null;
        }

        $valor = trim($valor);

        $formatos = [
            'Y-m-d H:i:s',
            'Y-m-d\TH:i:s',
            'Y-m-d H:i',
            'Y-m-d\TH:i',
        ];

        foreach ($formatos as $formato) {
            $data =
                DateTimeImmutable::createFromFormat(
                    '!' . $formato,
                    $valor
                );

            if (
                $data !== false
                && $data->format($formato)
                    === $valor
            ) {
                return $data->format(
                    'Y-m-d H:i:s'
                );
            }
        }

        $erros[$campo] =
            'Use o formato YYYY-MM-DD HH:MM:SS, por exemplo 2026-09-06 10:00:00.';

        return null;
    }

    private function normalizarBooleano(
        mixed $valor,
        string $campo
    ): bool {
        if (is_bool($valor)) {
            return $valor;
        }

        if (
            $valor === 1
            || $valor === '1'
        ) {
            return true;
        }

        if (
            $valor === 0
            || $valor === '0'
        ) {
            return false;
        }

        if (is_string($valor)) {
            $texto =
                mb_strtolower(
                    trim($valor)
                );

            if ($texto === 'true') {
                return true;
            }

            if ($texto === 'false') {
                return false;
            }
        }

        throw new DadosInvalidosException([
            $campo =>
                'Informe true ou false.',
        ]);
    }

    private function textoOpcional(
        mixed $valor
    ): ?string {
        if ($valor === null) {
            return null;
        }

        $texto = trim((string) $valor);

        return $texto === ''
            ? null
            : $texto;
    }

    /**
     * @param array<string, mixed> $programacao
     * @return array<string, mixed>
     */
    private function formatarProgramacao(
        array $programacao
    ): array {
        return [
            'id' =>
                (int) $programacao['id'],
            'serie_id' =>
                $programacao['serie_id']
                    === null
                    ? null
                    : (int) $programacao[
                        'serie_id'
                    ],
            'titulo' =>
                $programacao['titulo'],
            'descricao' =>
                $programacao['descricao'],
            'inicio_em' =>
                $programacao['inicio_em'],
            'fim_em' =>
                $programacao['fim_em'],
            'status' =>
                $programacao['status'],
            'permite_resposta' =>
                (bool) $programacao[
                    'permite_resposta'
                ],

            'tipo_programacao' => [
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
                    (int) $programacao[
                        'local_id'
                    ],
                'nome_historico' =>
                    $programacao[
                        'local_nome_historico'
                    ],
            ],

            'organizador' => [
                'id' =>
                    (int) $programacao[
                        'organizador_id'
                    ],
                'nome_historico' =>
                    $programacao[
                        'organizador_nome_historico'
                    ],
            ],

            'total_participacoes' =>
                (int) $programacao[
                    'total_participacoes'
                ],

            'cancelada_em' =>
                $programacao[
                    'cancelada_em'
                ],
            'motivo_cancelamento' =>
                $programacao[
                    'motivo_cancelamento'
                ],
            'realizado_em' =>
                $programacao[
                    'realizado_em'
                ],
            'criado_em' =>
                $programacao['criado_em'],
            'atualizado_em' =>
                $programacao[
                    'atualizado_em'
                ],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $conflitos
     * @return array<int, array<string, mixed>>
     */
    private function formatarConflitos(
        array $conflitos
    ): array {
        return array_map(
            static fn (
                array $conflito
            ): array => [
                'id' =>
                    (int) $conflito['id'],
                'titulo' =>
                    $conflito['titulo'],
                'inicio_em' =>
                    $conflito['inicio_em'],
                'fim_em' =>
                    $conflito['fim_em'],
                'status' =>
                    $conflito['status'],
                'local' =>
                    $conflito[
                        'local_nome_historico'
                    ],
                'tipo_programacao' =>
                    $conflito[
                        'tipo_programacao_nome_historico'
                    ],
                'organizador' =>
                    $conflito[
                        'organizador_nome_historico'
                    ],
            ],
            $conflitos
        );
    }
}
