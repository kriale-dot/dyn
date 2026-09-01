<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\GestaoEscalaAcessoNegadoException;
use App\Repositories\GestaoEscalaRepository;

/**
 * Service da administração da escala.
 *
 * Esta etapa fornece uma projeção de LEITURA pronta para o frontend.
 * As alterações continuam usando os endpoints já existentes de
 * participações, evitando duplicar regras de conflito e histórico.
 */
final class GestaoEscalaService
{
    public function __construct(
        private GestaoEscalaRepository $repository
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

        $programacao = $this->repository->buscarProgramacao($programacaoId);

        if ($programacao === null) {
            throw new DadosInvalidosException([
                'programacao' => 'Programação não encontrada.',
            ]);
        }

        $usuario = $this->repository->buscarUsuarioAutenticado(
            $usuarioAutenticadoId
        );

        if ($usuario === null || $usuario['status'] !== 'ATIVO') {
            throw new GestaoEscalaAcessoNegadoException(
                'Usuário autenticado inválido ou inativo.'
            );
        }

        $this->validarPermissao(
            $usuario,
            (int) $programacao['tipo_programacao_id']
        );

        $funcoes = $this->repository->listarFuncoesPermitidas(
            (int) $programacao['tipo_programacao_id']
        );

        $escalaBruta = $this->repository->listarEscala($programacaoId);

        $candidatosBrutos = $this->repository->listarCandidatos(
            (int) $programacao['tipo_programacao_id']
        );

        $indiceEscala = $this->montarIndiceEscala($escalaBruta);
        $escala = $this->formatarEscala($escalaBruta);

        return [
            'programacao' => [
                'id' => (int) $programacao['id'],
                'titulo' => $programacao['titulo'],
                'descricao' => $programacao['descricao'],
                'status' => $programacao['status'],
                'permite_resposta' => (bool) $programacao['permite_resposta'],
                'quando' => [
                    'inicio_em' => $programacao['inicio_em'],
                    'fim_em' => $programacao['fim_em'],
                ],
                'tipo' => [
                    'id' => (int) $programacao['tipo_programacao_id'],
                    'nome' => $programacao['tipo_programacao_nome_historico'],
                ],
                'local' => [
                    'id' => $programacao['local_id'] !== null
                        ? (int) $programacao['local_id']
                        : null,
                    'nome' => $programacao['local_nome_historico'],
                ],
                'organizador' => [
                    'id' => $programacao['organizador_id'] !== null
                        ? (int) $programacao['organizador_id']
                        : null,
                    'nome' => $programacao['organizador_nome_historico'],
                ],
            ],

            'gestor' => [
                'usuario_id' => (int) $usuario['id'],
                'nome' => $usuario['nome'],
                'papel' => $usuario['papel_codigo'],
            ],

            'resumo' => [
                'funcoes_permitidas' => count($funcoes),
                'participacoes' => count($escala),
                'participacoes_ativas' => count(array_filter(
                    $escala,
                    static fn (array $item): bool => in_array(
                        $item['status'],
                        ['ESCALADO', 'CONFIRMADO'],
                        true
                    )
                )),
                'candidatos_elegiveis' => count($candidatosBrutos),
            ],

            'funcoes' => $this->montarFuncoesComCandidatos(
                $funcoes,
                $candidatosBrutos,
                $indiceEscala
            ),

            'escala' => $escala,

            'acoes_administrativas' => $this->acoesAdministrativas(
                (string) $programacao['status']
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
        $papel = (string) $usuario['papel_codigo'];

        if ($papel === 'ADMINISTRADOR') {
            return;
        }

        if ($papel !== 'ORGANIZADOR') {
            throw new GestaoEscalaAcessoNegadoException(
                'Somente Administrador ou Organizador pode administrar a escala.'
            );
        }

        if (!$this->repository->organizadorPodeAdministrarTipo(
            (int) $usuario['id'],
            $tipoProgramacaoId
        )) {
            throw new GestaoEscalaAcessoNegadoException(
                'O Organizador não possui permissão para este tipo de programação.'
            );
        }
    }

    /**
     * @param array<int, array<string, mixed>> $escala
     * @return array<string, string>
     */
    private function montarIndiceEscala(array $escala): array
    {
        $indice = [];

        foreach ($escala as $item) {
            $chave = (int) $item['usuario_id']
                . ':'
                . (int) $item['funcao_id'];

            $indice[$chave] = (string) $item['status'];
        }

        return $indice;
    }

    /**
     * @param array<int, array<string, mixed>> $escala
     * @return array<int, array<string, mixed>>
     */
    private function formatarEscala(array $escala): array
    {
        $resultado = [];

        foreach ($escala as $item) {
            $resultado[] = [
                'participacao_id' => (int) $item['id'],
                'usuario' => [
                    'id' => (int) $item['usuario_id'],
                    'nome' => $item['usuario_nome_historico'],
                    'foto_atual' => $item['usuario_foto_atual'],
                    'status_atual' => $item['usuario_status_atual'],
                ],
                'funcao' => [
                    'id' => (int) $item['funcao_id'],
                    'nome' => $item['funcao_nome_historico'],
                    'departamento' => $item['departamento_nome_historico'],
                ],
                'status' => $item['status'],
                'ativo_na_escala' => in_array(
                    $item['status'],
                    ['ESCALADO', 'CONFIRMADO'],
                    true
                ),
                'observacao' => $item['observacao'],
            ];
        }

        return $resultado;
    }

    /**
     * @param array<int, array<string, mixed>> $funcoes
     * @param array<int, array<string, mixed>> $candidatos
     * @param array<string, string> $indiceEscala
     * @return array<int, array<string, mixed>>
     */
    private function montarFuncoesComCandidatos(
        array $funcoes,
        array $candidatos,
        array $indiceEscala
    ): array {
        $resultado = [];

        foreach ($funcoes as $funcao) {
            $funcaoId = (int) $funcao['id'];

            $itemFuncao = [
                'id' => $funcaoId,
                'nome' => $funcao['nome'],
                'descricao' => $funcao['descricao'],
                'departamento' => [
                    'id' => $funcao['departamento_id'] !== null
                        ? (int) $funcao['departamento_id']
                        : null,
                    'nome' => $funcao['departamento_nome'],
                ],
                'candidatos' => [],
            ];

            foreach ($candidatos as $candidato) {
                if ((int) $candidato['funcao_id'] !== $funcaoId) {
                    continue;
                }

                $usuarioId = (int) $candidato['usuario_id'];
                $chave = $usuarioId . ':' . $funcaoId;
                $statusEscala = $indiceEscala[$chave] ?? null;

                $itemFuncao['candidatos'][] = [
                    'usuario_id' => $usuarioId,
                    'nome' => $candidato['usuario_nome'],
                    'email' => $candidato['usuario_email'],
                    'foto' => $candidato['usuario_foto'],
                    'ja_na_escala' => $statusEscala !== null,
                    'status_na_escala' => $statusEscala,
                    'sugerir_adicionar' => !in_array(
                        $statusEscala,
                        ['ESCALADO', 'CONFIRMADO'],
                        true
                    ),
                ];
            }

            $resultado[] = $itemFuncao;
        }

        return $resultado;
    }

    /**
     * @return array<int, string>
     */
    private function acoesAdministrativas(
        string $statusProgramacao
    ): array {
        if ($statusProgramacao !== 'AGENDADA') {
            return [];
        }

        return [
            'ADICIONAR_PARTICIPACAO',
            'CANCELAR_PARTICIPACAO',
            'CONSULTAR_CANDIDATOS',
        ];
    }
}
