<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ConflitoPessoaException;
use App\Exceptions\DadosInvalidosException;
use App\Exceptions\FuncaoNaoEncontradaException;
use App\Exceptions\ParticipacaoNaoEncontradaException;
use App\Exceptions\ProgramacaoNaoEncontradaException;
use App\Exceptions\UsuarioNaoEncontradoException;
use App\Repositories\ParticipacaoRepository;

/**
 * Service de participações / escalas.
 *
 * Regras principais:
 *
 * 1. a programação precisa existir;
 * 2. usuário inativo não recebe nova escala;
 * 3. função inativa não recebe nova escala;
 * 4. o usuário precisa possuir atualmente a função;
 * 5. a função precisa ser autorizada para o tipo da programação;
 * 6. a participação grava snapshots históricos;
 * 7. respostas alteram status, mas não apagam o registro;
 * 8. a mesma pessoa em programações sobrepostas gera ALERTA.
 */
final class ParticipacaoService
{
    public function __construct(
        private ParticipacaoRepository $repository
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function listarPorProgramacao(
        int $programacaoId
    ): array {
        $programacao =
            $this->obterProgramacao($programacaoId);

        $participacoes =
            $this->repository
                ->listarPorProgramacao($programacaoId);

        $formatadas = array_map(
            fn (array $participacao): array =>
                $this->formatarParticipacao($participacao),
            $participacoes
        );

        return [
            'programacao' =>
                $this->formatarProgramacaoResumida(
                    $programacao
                ),
            'resumo' =>
                $this->montarResumoStatus($participacoes),
            'participacoes' =>
                $formatadas,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function listarCandidatos(
        int $programacaoId
    ): array {
        $programacao =
            $this->obterProgramacao($programacaoId);

        if (
            in_array(
                $programacao['status'],
                ['CANCELADA', 'REALIZADA'],
                true
            )
        ) {
            throw new DadosInvalidosException([
                'programacao' =>
                    'Uma programação cancelada ou realizada não recebe novas escalas.',
            ]);
        }

        $candidatos =
            $this->repository
                ->listarCandidatosElegiveis(
                    $programacaoId
                );

        return [
            'programacao' =>
                $this->formatarProgramacaoResumida(
                    $programacao
                ),
            'total' =>
                count($candidatos),
            'candidatos' =>
                array_map(
                    fn (array $candidato): array =>
                        $this->formatarCandidato(
                            $candidato
                        ),
                    $candidatos
                ),
        ];
    }

    /**
     * Cria uma nova escala.
     *
     * NOVO nesta etapa:
     *
     * se a pessoa já estiver comprometida em outra programação
     * que se sobreponha, lançamos ConflitoPessoaException.
     *
     * O cliente recebe HTTP 409 e pode repetir o POST com:
     *
     * "confirmar_conflito_pessoa": true
     *
     * A regra é de alerta, não de bloqueio absoluto.
     *
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function criar(
        int $programacaoId,
        array $dados
    ): array {
        $programacao =
            $this->obterProgramacao($programacaoId);

        if (
            in_array(
                $programacao['status'],
                ['CANCELADA', 'REALIZADA'],
                true
            )
        ) {
            throw new DadosInvalidosException([
                'programacao' =>
                    'Não é possível criar nova escala em uma programação cancelada ou realizada.',
            ]);
        }

        $erros = [];

        $usuarioId =
            $this->validarId(
                $dados['usuario_id'] ?? null,
                'usuario_id',
                $erros
            );

        $funcaoId =
            $this->validarId(
                $dados['funcao_id'] ?? null,
                'funcao_id',
                $erros
            );

        $observacao =
            $this->textoOpcional(
                $dados['observacao'] ?? null
            );

        if (
            $observacao !== null
            && mb_strlen($observacao) > 500
        ) {
            $erros['observacao'] =
                'A observação deve possuir no máximo 500 caracteres.';
        }

        if ($erros !== []) {
            throw new DadosInvalidosException($erros);
        }

        $usuario =
            $this->repository
                ->buscarUsuarioPorId($usuarioId);

        if ($usuario === null) {
            throw new UsuarioNaoEncontradoException(
                $usuarioId
            );
        }

        $funcao =
            $this->repository
                ->buscarFuncaoPorId($funcaoId);

        if ($funcao === null) {
            throw new FuncaoNaoEncontradaException(
                $funcaoId
            );
        }

        if ($usuario['status'] !== 'ATIVO') {
            throw new DadosInvalidosException([
                'usuario_id' =>
                    'Usuários inativos não podem receber novas escalas.',
            ]);
        }

        if (!(bool) $funcao['ativo']) {
            throw new DadosInvalidosException([
                'funcao_id' =>
                    'Funções inativas não podem ser usadas em novas escalas.',
            ]);
        }

        if (
            !$this->repository->usuarioPossuiFuncao(
                $usuarioId,
                $funcaoId
            )
        ) {
            throw new DadosInvalidosException([
                'funcao_id' =>
                    'O usuário não possui atualmente a função informada.',
            ]);
        }

        if (
            !$this->repository->funcaoAutorizadaParaTipo(
                $funcaoId,
                (int) $programacao['tipo_programacao_id']
            )
        ) {
            throw new DadosInvalidosException([
                'funcao_id' =>
                    'A função não está autorizada para o tipo desta programação.',
            ]);
        }

        /**
         * Se exatamente a mesma combinação já existe, não devemos
         * criar outra nem disparar conflito contra ela mesma.
         */
        $existente =
            $this->repository
                ->buscarPorProgramacaoUsuarioFuncao(
                    $programacaoId,
                    $usuarioId,
                    $funcaoId
                );

        if ($existente !== null) {
            return [
                'participacao' =>
                    $this->formatarParticipacao(
                        $existente
                    ),
                'ja_existia' => true,
                'conflito_pessoa_confirmado' => false,
                'conflitos_detectados' => [],
            ];
        }

        /**
         * DETECÇÃO DO CONFLITO DE PESSOA.
         */
        $conflitos =
            $this->repository
                ->buscarConflitosDePessoa(
                    $usuarioId,
                    $programacaoId,
                    $programacao['inicio_em'],
                    $programacao['fim_em']
                );

        $conflitosFormatados =
            $this->formatarConflitosPessoa(
                $conflitos
            );

        $confirmarConflito =
            $this->normalizarBooleano(
                $dados[
                    'confirmar_conflito_pessoa'
                ] ?? false,
                'confirmar_conflito_pessoa'
            );

        if (
            $conflitosFormatados !== []
            && !$confirmarConflito
        ) {
            throw new ConflitoPessoaException(
                $conflitosFormatados
            );
        }

        $participacaoId =
            $this->repository->criar([
                'programacao_id' =>
                    $programacaoId,
                'usuario_id' =>
                    $usuarioId,
                'funcao_id' =>
                    $funcaoId,

                'usuario_nome_historico' =>
                    $usuario['nome'],
                'funcao_nome_historico' =>
                    $funcao['nome'],
                'departamento_nome_historico' =>
                    $funcao['departamento_nome'],

                'observacao' =>
                    $observacao,
            ]);

        return [
            'participacao' =>
                $this->buscarPorId(
                    $participacaoId
                ),
            'ja_existia' => false,
            'conflito_pessoa_confirmado' =>
                $conflitosFormatados !== []
                && $confirmarConflito,
            'conflitos_detectados' =>
                $conflitosFormatados,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buscarPorId(
        int $participacaoId
    ): array {
        $participacao =
            $this->repository
                ->buscarPorId($participacaoId);

        if ($participacao === null) {
            throw new ParticipacaoNaoEncontradaException(
                $participacaoId
            );
        }

        return $this->formatarParticipacao(
            $participacao
        );
    }

    /**
     * Respostas do usuário autenticado.
     *
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function confirmar(
        int $participacaoId,
        int $usuarioAutenticadoId,
        array $dados = []
    ): array {
        return $this->responder(
            $participacaoId,
            $usuarioAutenticadoId,
            'CONFIRMADO',
            $dados
        );
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function indisponivel(
        int $participacaoId,
        int $usuarioAutenticadoId,
        array $dados = []
    ): array {
        return $this->responder(
            $participacaoId,
            $usuarioAutenticadoId,
            'INDISPONIVEL',
            $dados
        );
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function recusar(
        int $participacaoId,
        int $usuarioAutenticadoId,
        array $dados = []
    ): array {
        return $this->responder(
            $participacaoId,
            $usuarioAutenticadoId,
            'RECUSADO',
            $dados
        );
    }

    /**
     * Cancelamento administrativo da escala.
     *
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function cancelar(
        int $participacaoId,
        array $dados = []
    ): array {
        $participacao =
            $this->obterParticipacaoBruta(
                $participacaoId
            );

        if (
            $participacao['programacao_status']
            === 'REALIZADA'
        ) {
            throw new DadosInvalidosException([
                'participacao' =>
                    'Uma escala de programação já realizada não pode ser cancelada por esta rota.',
            ]);
        }

        $observacao =
            $this->validarObservacaoResposta(
                $dados
            );

        $jaEstavaCancelada =
            $participacao['status']
            === 'CANCELADO';

        if (!$jaEstavaCancelada) {
            $this->repository->cancelar(
                $participacaoId,
                $observacao
            );
        }

        return [
            'participacao' =>
                $this->buscarPorId(
                    $participacaoId
                ),
            'ja_estava_cancelada' =>
                $jaEstavaCancelada,
        ];
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    private function responder(
        int $participacaoId,
        int $usuarioAutenticadoId,
        string $novoStatus,
        array $dados
    ): array {
        $participacao =
            $this->obterParticipacaoBruta(
                $participacaoId
            );

        if (
            (int) $participacao['usuario_id']
            !== $usuarioAutenticadoId
        ) {
            throw new DadosInvalidosException([
                'participacao' =>
                    'Você não pode responder à participação de outro usuário.',
            ]);
        }

        if ($participacao['status'] === 'CANCELADO') {
            throw new DadosInvalidosException([
                'participacao' =>
                    'Uma participação cancelada não pode receber resposta.',
            ]);
        }

        if (
            in_array(
                $participacao['programacao_status'],
                ['CANCELADA', 'REALIZADA'],
                true
            )
        ) {
            throw new DadosInvalidosException([
                'programacao' =>
                    'Esta programação não aceita mais respostas.',
            ]);
        }

        if (!(bool) $participacao['permite_resposta']) {
            throw new DadosInvalidosException([
                'participacao' =>
                    'A programação está configurada para não receber respostas dos participantes.',
            ]);
        }

        $observacao =
            $this->validarObservacaoResposta(
                $dados
            );

        $this->repository->responder(
            $participacaoId,
            $novoStatus,
            $observacao
        );

        return $this->buscarPorId(
            $participacaoId
        );
    }

    /**
     * @param array<string, mixed> $dados
     */
    private function validarObservacaoResposta(
        array $dados
    ): ?string {
        $observacao =
            $this->textoOpcional(
                $dados['observacao'] ?? null
            );

        if (
            $observacao !== null
            && mb_strlen($observacao) > 500
        ) {
            throw new DadosInvalidosException([
                'observacao' =>
                    'A observação deve possuir no máximo 500 caracteres.',
            ]);
        }

        return $observacao;
    }

    /**
     * @return array<string, mixed>
     */
    private function obterProgramacao(
        int $programacaoId
    ): array {
        $programacao =
            $this->repository
                ->buscarProgramacaoPorId(
                    $programacaoId
                );

        if ($programacao === null) {
            throw new ProgramacaoNaoEncontradaException(
                $programacaoId
            );
        }

        return $programacao;
    }

    /**
     * @return array<string, mixed>
     */
    private function obterParticipacaoBruta(
        int $participacaoId
    ): array {
        $participacao =
            $this->repository
                ->buscarPorId(
                    $participacaoId
                );

        if ($participacao === null) {
            throw new ParticipacaoNaoEncontradaException(
                $participacaoId
            );
        }

        return $participacao;
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
     * Normaliza o booleano de confirmação explícita.
     */
    private function normalizarBooleano(
        mixed $valor,
        string $campo
    ): bool {
        if (is_bool($valor)) {
            return $valor;
        }

        if ($valor === 1 || $valor === '1') {
            return true;
        }

        if ($valor === 0 || $valor === '0') {
            return false;
        }

        if (is_string($valor)) {
            $texto = mb_strtolower(
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
     * @param array<int, array<string, mixed>> $participacoes
     * @return array<string, int>
     */
    private function montarResumoStatus(
        array $participacoes
    ): array {
        $resumo = [
            'total' => count($participacoes),
            'escalados' => 0,
            'confirmados' => 0,
            'indisponiveis' => 0,
            'recusados' => 0,
            'cancelados' => 0,
        ];

        foreach ($participacoes as $participacao) {
            switch ($participacao['status']) {
                case 'ESCALADO':
                    $resumo['escalados']++;
                    break;

                case 'CONFIRMADO':
                    $resumo['confirmados']++;
                    break;

                case 'INDISPONIVEL':
                    $resumo['indisponiveis']++;
                    break;

                case 'RECUSADO':
                    $resumo['recusados']++;
                    break;

                case 'CANCELADO':
                    $resumo['cancelados']++;
                    break;
            }
        }

        return $resumo;
    }

    /**
     * @param array<string, mixed> $programacao
     * @return array<string, mixed>
     */
    private function formatarProgramacaoResumida(
        array $programacao
    ): array {
        return [
            'id' => (int) $programacao['id'],
            'titulo' => $programacao['titulo'],
            'inicio_em' => $programacao['inicio_em'],
            'fim_em' => $programacao['fim_em'],
            'status' => $programacao['status'],
            'permite_resposta' =>
                (bool) $programacao['permite_resposta'],
            'tipo_programacao' =>
                $programacao[
                    'tipo_programacao_nome_historico'
                ],
            'local' =>
                $programacao[
                    'local_nome_historico'
                ],
            'organizador' =>
                $programacao[
                    'organizador_nome_historico'
                ],
        ];
    }

    /**
     * @param array<string, mixed> $candidato
     * @return array<string, mixed>
     */
    private function formatarCandidato(
        array $candidato
    ): array {
        $departamento = null;

        if ($candidato['departamento_id'] !== null) {
            $departamento = [
                'id' =>
                    (int) $candidato['departamento_id'],
                'nome' =>
                    $candidato['departamento_nome'],
            ];
        }

        return [
            'usuario' => [
                'id' =>
                    (int) $candidato['usuario_id'],
                'nome' =>
                    $candidato['usuario_nome'],
                'email' =>
                    $candidato['usuario_email'],
            ],
            'funcao' => [
                'id' =>
                    (int) $candidato['funcao_id'],
                'nome' =>
                    $candidato['funcao_nome'],
                'departamento' =>
                    $departamento,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $participacao
     * @return array<string, mixed>
     */
    private function formatarParticipacao(
        array $participacao
    ): array {
        return [
            'id' =>
                (int) $participacao['id'],
            'status' =>
                $participacao['status'],
            'observacao' =>
                $participacao['observacao'],
            'respondido_em' =>
                $participacao['respondido_em'],
            'cancelado_em' =>
                $participacao['cancelado_em'],

            'historico' => [
                'usuario_nome' =>
                    $participacao[
                        'usuario_nome_historico'
                    ],
                'funcao_nome' =>
                    $participacao[
                        'funcao_nome_historico'
                    ],
                'departamento_nome' =>
                    $participacao[
                        'departamento_nome_historico'
                    ],
            ],

            'usuario' => [
                'id' =>
                    (int) $participacao['usuario_id'],
                'nome_atual' =>
                    $participacao['usuario_nome_atual'],
                'status_atual' =>
                    $participacao['usuario_status_atual'],
            ],

            'funcao' => [
                'id' =>
                    (int) $participacao['funcao_id'],
                'nome_atual' =>
                    $participacao['funcao_nome_atual'],
                'ativa_atual' =>
                    (bool) $participacao[
                        'funcao_ativa_atual'
                    ],
            ],

            'programacao' => [
                'id' =>
                    (int) $participacao[
                        'programacao_id'
                    ],
                'titulo' =>
                    $participacao[
                        'programacao_titulo'
                    ],
                'inicio_em' =>
                    $participacao['inicio_em'],
                'fim_em' =>
                    $participacao['fim_em'],
                'status' =>
                    $participacao[
                        'programacao_status'
                    ],
                'local' =>
                    $participacao[
                        'local_nome_historico'
                    ],
                'permite_resposta' =>
                    (bool) $participacao[
                        'permite_resposta'
                    ],
            ],

            'criado_em' =>
                $participacao['criado_em'],
            'atualizado_em' =>
                $participacao['atualizado_em'],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $conflitos
     * @return array<int, array<string, mixed>>
     */
    private function formatarConflitosPessoa(
        array $conflitos
    ): array {
        return array_map(
            static fn (
                array $conflito
            ): array => [
                'participacao_id' =>
                    (int) $conflito[
                        'participacao_id'
                    ],
                'participacao_status' =>
                    $conflito[
                        'participacao_status'
                    ],
                'funcao' =>
                    $conflito[
                        'funcao_nome_historico'
                    ],
                'programacao' => [
                    'id' =>
                        (int) $conflito[
                            'programacao_id'
                        ],
                    'titulo' =>
                        $conflito['titulo'],
                    'inicio_em' =>
                        $conflito['inicio_em'],
                    'fim_em' =>
                        $conflito['fim_em'],
                    'status' =>
                        $conflito[
                            'programacao_status'
                        ],
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
            ],
            $conflitos
        );
    }
}
