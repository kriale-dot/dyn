<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Exceptions\UsuarioNaoEncontradoException;
use App\Repositories\PerfilRepository;
use DateTimeImmutable;

/**
 * Service do perfil e aniversariantes.
 */
final class PerfilService
{
    public function __construct(
        private PerfilRepository $repository
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function meuPerfil(int $usuarioId): array
    {
        $usuario =
            $this->repository
                ->buscarUsuarioPorId($usuarioId);

        if ($usuario === null) {
            throw new UsuarioNaoEncontradoException(
                $usuarioId
            );
        }

        $resultado =
            $this->formatarPerfil($usuario);

        $resultado['funcoes'] =
            array_map(
                fn (array $funcao): array =>
                    $this->formatarFuncao($funcao),
                $this->repository
                    ->listarFuncoesDoUsuario(
                        $usuarioId
                    )
            );

        return $resultado;
    }

    /**
     * Atualiza apenas dados pessoais permitidos.
     *
     * O próprio usuário NÃO altera:
     * - papel;
     * - status;
     * - funções.
     *
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function atualizarMeuPerfil(
        int $usuarioId,
        array $dados
    ): array {
        $usuarioAtual =
            $this->repository
                ->buscarUsuarioPorId(
                    $usuarioId
                );

        if ($usuarioAtual === null) {
            throw new UsuarioNaoEncontradoException(
                $usuarioId
            );
        }

        $erros = [];

        $nome =
            trim(
                (string) (
                    $dados['nome']
                    ?? $usuarioAtual['nome']
                )
            );

        if ($nome === '') {
            $erros['nome'] =
                'O nome é obrigatório.';
        } elseif (mb_strlen($nome) > 150) {
            $erros['nome'] =
                'O nome deve possuir no máximo 150 caracteres.';
        }

        $email =
            mb_strtolower(
                trim(
                    (string) (
                        $dados['email']
                        ?? $usuarioAtual['email']
                    )
                )
            );

        if (
            $email === ''
            || !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            $erros['email'] =
                'Informe um endereço de e-mail válido.';
        } elseif (
            $this->repository
                ->emailExisteParaOutroUsuario(
                    $email,
                    $usuarioId
                )
        ) {
            $erros['email'] =
                'Já existe outro usuário cadastrado com este e-mail.';
        }

        $dataNascimento =
            array_key_exists(
                'data_nascimento',
                $dados
            )
                ? $this->textoOpcional(
                    $dados[
                        'data_nascimento'
                    ]
                )
                : $usuarioAtual[
                    'data_nascimento'
                ];

        if (
            $dataNascimento !== null
            && !$this->dataValida(
                $dataNascimento
            )
        ) {
            $erros['data_nascimento'] =
                'Use uma data válida no formato YYYY-MM-DD.';
        }

        $telefone =
            array_key_exists(
                'telefone',
                $dados
            )
                ? $this->textoOpcional(
                    $dados['telefone']
                )
                : $usuarioAtual['telefone'];

        if (
            $telefone !== null
            && mb_strlen($telefone) > 30
        ) {
            $erros['telefone'] =
                'O telefone deve possuir no máximo 30 caracteres.';
        }

        /**
         * Nesta etapa foto é uma referência/path/URL armazenada
         * no campo existente. Upload multipart será uma etapa
         * separada, se desejado.
         */
        $foto =
            array_key_exists('foto', $dados)
                ? $this->textoOpcional(
                    $dados['foto']
                )
                : $usuarioAtual['foto'];

        if (
            $foto !== null
            && mb_strlen($foto) > 255
        ) {
            $erros['foto'] =
                'A referência da foto deve possuir no máximo 255 caracteres.';
        }

        if ($erros !== []) {
            throw new DadosInvalidosException(
                $erros
            );
        }

        $this->repository->atualizarPerfil(
            $usuarioId,
            [
                'nome' => $nome,
                'email' => $email,
                'data_nascimento' =>
                    $dataNascimento,
                'telefone' =>
                    $telefone,
                'foto' =>
                    $foto,
            ]
        );

        return $this->meuPerfil(
            $usuarioId
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function aniversariantesHoje(
        ?string $dataReferencia
    ): array {
        $referencia =
            $this->resolverDataReferencia(
                $dataReferencia
            );

        return [
            'data_referencia' =>
                $referencia->format(
                    'Y-m-d'
                ),
            'aniversariantes' =>
                $this->listarAniversariantes(
                    [$referencia]
                ),
        ];
    }

    /**
     * Semana: segunda-feira até domingo.
     *
     * @return array<string, mixed>
     */
    public function aniversariantesSemana(
        ?string $dataReferencia
    ): array {
        $referencia =
            $this->resolverDataReferencia(
                $dataReferencia
            );

        $inicio =
            $referencia
                ->modify('monday this week')
                ->setTime(0, 0);

        $datas = [];

        for ($i = 0; $i < 7; $i++) {
            $datas[] =
                $inicio->modify(
                    "+{$i} days"
                );
        }

        return [
            'data_referencia' =>
                $referencia->format('Y-m-d'),
            'inicio_semana' =>
                $inicio->format('Y-m-d'),
            'fim_semana' =>
                $inicio
                    ->modify('+6 days')
                    ->format('Y-m-d'),
            'aniversariantes' =>
                $this->listarAniversariantes(
                    $datas
                ),
        ];
    }

    /**
     * @param array<int, DateTimeImmutable> $datas
     * @return array<int, array<string, mixed>>
     */
    private function listarAniversariantes(
        array $datas
    ): array {
        $porDiaMes = [];

        foreach ($datas as $data) {
            $porDiaMes[
                $data->format('m-d')
            ] = $data;
        }

        $usuarios =
            $this->repository
                ->listarAniversariantesPorDiasMes(
                    array_keys($porDiaMes)
                );

        $resultado = [];

        foreach ($usuarios as $usuario) {
            /**
             * Não devolvemos ano de nascimento nem idade.
             * É uma escolha conservadora para apresentar o
             * aniversário de forma discreta.
             */
            $nascimento =
                new DateTimeImmutable(
                    $usuario[
                        'data_nascimento'
                    ]
                );

            $chave =
                $nascimento->format('m-d');

            $dataDoAniversario =
                $porDiaMes[$chave]
                ?? null;

            if ($dataDoAniversario === null) {
                continue;
            }

            $resultado[] = [
                'usuario_id' =>
                    (int) $usuario['id'],
                'nome' =>
                    $usuario['nome'],
                'foto' =>
                    $usuario['foto'],
                'data' =>
                    $dataDoAniversario
                        ->format('Y-m-d'),
                'dia' =>
                    (int) $dataDoAniversario
                        ->format('d'),
                'mes' =>
                    (int) $dataDoAniversario
                        ->format('m'),
            ];
        }

        usort(
            $resultado,
            static function (
                array $a,
                array $b
            ): int {
                $data =
                    strcmp(
                        $a['data'],
                        $b['data']
                    );

                if ($data !== 0) {
                    return $data;
                }

                return strcmp(
                    $a['nome'],
                    $b['nome']
                );
            }
        );

        return $resultado;
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

        $dataReferencia =
            trim($dataReferencia);

        $data =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $dataReferencia
            );

        if (
            $data === false
            || $data->format('Y-m-d')
                !== $dataReferencia
        ) {
            throw new DadosInvalidosException([
                'data_referencia' =>
                    'Use uma data válida no formato YYYY-MM-DD.',
            ]);
        }

        return $data;
    }

    private function dataValida(
        string $data
    ): bool {
        $objeto =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $data
            );

        return $objeto !== false
            && $objeto->format('Y-m-d')
                === $data;
    }

    private function textoOpcional(
        mixed $valor
    ): ?string {
        if ($valor === null) {
            return null;
        }

        $texto =
            trim((string) $valor);

        return $texto === ''
            ? null
            : $texto;
    }

    /**
     * @param array<string, mixed> $usuario
     * @return array<string, mixed>
     */
    private function formatarPerfil(
        array $usuario
    ): array {
        return [
            'id' =>
                (int) $usuario['id'],
            'nome' =>
                $usuario['nome'],
            'data_nascimento' =>
                $usuario[
                    'data_nascimento'
                ],
            'telefone' =>
                $usuario['telefone'],
            'email' =>
                $usuario['email'],
            'foto' =>
                $usuario['foto'],
            'status' =>
                $usuario['status'],
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
            'ultimo_login_em' =>
                $usuario[
                    'ultimo_login_em'
                ],
            'criado_em' =>
                $usuario['criado_em'],
            'atualizado_em' =>
                $usuario[
                    'atualizado_em'
                ],
        ];
    }

    /**
     * @param array<string, mixed> $funcao
     * @return array<string, mixed>
     */
    private function formatarFuncao(
        array $funcao
    ): array {
        $departamento = null;

        if (
            $funcao[
                'departamento_id'
            ] !== null
        ) {
            $departamento = [
                'id' =>
                    (int) $funcao[
                        'departamento_id'
                    ],
                'nome' =>
                    $funcao[
                        'departamento_nome'
                    ],
            ];
        }

        return [
            'id' =>
                (int) $funcao['id'],
            'nome' =>
                $funcao['nome'],
            'ativo' =>
                (bool) $funcao['ativo'],
            'departamento' =>
                $departamento,
        ];
    }
}
