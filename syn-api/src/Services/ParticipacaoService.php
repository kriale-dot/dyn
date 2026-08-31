<?php

declare(strict_types=1);

namespace App\Services;

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
 * 7. respostas alteram status, mas não apagam o registro.
 */
final class ParticipacaoService
{
    public function __construct(
        private ParticipacaoRepository $repository
    ) {
    }

    /**
     * Lista a escala e um resumo por status.
     *
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
     * Lista candidatos NORMAIS da programação.
     *
     * Regras de elegibilidade são baseadas no estado atual.
     *
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
         * A chave UNIQUE no banco é:
         *
         * programacao_id + usuario_id + funcao_id
         *
         * Se já existir, retornamos o registro sem duplicar.
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
            ];
        }

        $participacaoId =
            $this->repository->criar([
                'programacao_id' =>
                    $programacaoId,
                'usuario_id' =>
                    $usuarioId,
                'funcao_id' =>
                    $funcaoId,

                /**
                 * Snapshots históricos.
                 */
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
     * Respostas do participante.
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
     * Implementação compartilhada dos três estados de resposta.
     *
     * O documento define os estados, mas não detalha uma máquina
     * rígida de transições. Nesta etapa permitimos que a pessoa
     * altere sua resposta enquanto:
     *
     * - a escala não estiver CANCELADA;
     * - a programação não estiver CANCELADA/REALIZADA;
     * - permite_resposta estiver habilitado.
     *
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

        /**
         * Regra de segurança:
         * o usuário autenticado só responde às próprias escalas.
         */
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

            /**
             * O bloco histórico é o que representa o fato
             * registrado no momento da escala.
             */
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

            /**
             * IDs atuais continuam disponíveis para navegação,
             * mas não substituem os snapshots históricos.
             */
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
}
