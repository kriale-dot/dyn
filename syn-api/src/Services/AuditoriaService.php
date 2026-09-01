<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AuditoriaAcessoNegadoException;
use App\Exceptions\DadosInvalidosException;
use App\Repositories\AuditoriaRepository;

/**
 * Consulta administrativa da auditoria.
 */
final class AuditoriaService
{
    private const LIMITE_PADRAO = 50;
    private const LIMITE_MAXIMO = 100;

    public function __construct(
        private AuditoriaRepository $repository
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function listar(
        int $usuarioAutenticadoId,
        ?int $pagina,
        ?int $limite,
        ?int $usuarioId,
        ?string $metodo,
        ?string $recurso,
        ?bool $somenteErros
    ): array {
        $this->validarAdministrador(
            $usuarioAutenticadoId
        );

        $paginaFinal =
            max(
                1,
                $pagina ?? 1
            );

        $limiteFinal =
            $limite ?? self::LIMITE_PADRAO;

        if ($limiteFinal < 1) {
            throw new DadosInvalidosException([
                'limite' =>
                    'O limite deve ser maior que zero.',
            ]);
        }

        $limiteFinal =
            min(
                $limiteFinal,
                self::LIMITE_MAXIMO
            );

        $metodoFinal =
            $metodo !== null
                ? strtoupper(
                    trim($metodo)
                )
                : null;

        if (
            $metodoFinal !== null
            && !in_array(
                $metodoFinal,
                [
                    'POST',
                    'PUT',
                    'PATCH',
                    'DELETE',
                ],
                true
            )
        ) {
            throw new DadosInvalidosException([
                'metodo' =>
                    'Método inválido para a auditoria.',
            ]);
        }

        $recursoFinal =
            $recurso !== null
                && trim($recurso) !== ''
                    ? trim($recurso)
                    : null;

        $offset =
            ($paginaFinal - 1)
            * $limiteFinal;

        $itens =
            $this->repository
                ->listar(
                    $limiteFinal,
                    $offset,
                    $usuarioId,
                    $metodoFinal,
                    $recursoFinal,
                    $somenteErros
                );

        return [
            'pagina' =>
                $paginaFinal,
            'limite' =>
                $limiteFinal,
            'quantidade' =>
                count($itens),
            'filtros' => [
                'usuario_id' =>
                    $usuarioId,
                'metodo' =>
                    $metodoFinal,
                'recurso' =>
                    $recursoFinal,
                'somente_erros' =>
                    $somenteErros
                    ?? false,
            ],
            'operacoes' =>
                array_map(
                    static fn (
                        array $item
                    ): array =>
                        self::formatar(
                            $item
                        ),
                    $itens
                ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buscar(
        int $usuarioAutenticadoId,
        int $auditoriaId
    ): array {
        $this->validarAdministrador(
            $usuarioAutenticadoId
        );

        if ($auditoriaId <= 0) {
            throw new DadosInvalidosException([
                'auditoria_id' =>
                    'O ID da auditoria deve ser maior que zero.',
            ]);
        }

        $item =
            $this->repository
                ->buscarPorId(
                    $auditoriaId
                );

        if ($item === null) {
            throw new DadosInvalidosException([
                'auditoria' =>
                    'Registro de auditoria não encontrado.',
            ]);
        }

        return self::formatar(
            $item
        );
    }

    private function validarAdministrador(
        int $usuarioId
    ): void {
        $usuario =
            $this->repository
                ->buscarUsuario(
                    $usuarioId
                );

        if (
            $usuario === null
            || $usuario['status']
                !== 'ATIVO'
            || $usuario['papel_codigo']
                !== 'ADMINISTRADOR'
        ) {
            throw new AuditoriaAcessoNegadoException(
                'Somente Administradores podem consultar a auditoria.'
            );
        }
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private static function formatar(
        array $item
    ): array {
        return [
            'id' =>
                (int) $item['id'],
            'request_id' =>
                $item['request_id'],

            'usuario' => [
                'id' =>
                    $item['usuario_id'] !== null
                        ? (int) $item['usuario_id']
                        : null,
                'nome_historico' =>
                    $item[
                        'usuario_nome_historico'
                    ],
                'papel_historico' =>
                    $item[
                        'papel_codigo_historico'
                    ],
            ],

            'operacao' => [
                'metodo' =>
                    $item['metodo'],
                'caminho' =>
                    $item['caminho'],
                'recurso' =>
                    $item['recurso'],
                'entidade_id' =>
                    $item['entidade_id'] !== null
                        ? (int) $item['entidade_id']
                        : null,
            ],

            'resultado' => [
                'http_status' =>
                    (int) $item['http_status'],
                'sucesso' =>
                    (bool) $item['sucesso'],
                'mensagem' =>
                    $item[
                        'mensagem_resultado'
                    ],
            ],

            'origem' => [
                'ip' =>
                    $item['ip'],
                'user_agent' =>
                    $item['user_agent'],
            ],

            'criado_em' =>
                $item['criado_em'],
        ];
    }
}
