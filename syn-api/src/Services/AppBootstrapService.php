<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Repositories\AppBootstrapRepository;

/**
 * Monta o pacote de inicialização do frontend.
 *
 * O frontend não deve "adivinhar" permissões a partir do nome
 * do papel. A API devolve capacidades explícitas.
 *
 * Mesmo assim, toda rota protegida continua validando autorização
 * no backend. As capacidades servem para montar a interface,
 * não para substituir segurança.
 */
final class AppBootstrapService
{
    private const PERMISSAO_NECESSIDADES =
        'NECESSIDADES_ESPECIFICAS_GERENCIAR';

    private const PERMISSAO_CADASTROS_APROVAR =
        'CADASTROS_APROVAR';

    public function __construct(
        private AppBootstrapRepository $repository
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function obter(
        int $usuarioId
    ): array {
        $usuario =
            $this->repository
                ->buscarUsuario(
                    $usuarioId
                );

        if ($usuario === null) {
            throw new DadosInvalidosException([
                'usuario' =>
                    'Usuário autenticado não foi encontrado.',
            ]);
        }

        if (
            $usuario['status']
            !== 'ATIVO'
        ) {
            throw new DadosInvalidosException([
                'usuario' =>
                    'Usuário inativo não pode inicializar a aplicação.',
            ]);
        }

        $igreja =
            $this->repository
                ->buscarIgreja();

        $papel =
            (string) $usuario[
                'papel_codigo'
            ];

        $tiposOrganizador = [];
        $permissoesEspeciais =
            $this->repository
                ->listarPermissoesEspeciais(
                    $usuarioId
                );

        if ($papel === 'ORGANIZADOR') {
            $tiposOrganizador =
                $this->repository
                    ->listarTiposDoOrganizador(
                        $usuarioId
                    );
        }

        $codigosPermissoes =
            array_values(
                array_map(
                    static fn (
                        array $item
                    ): string =>
                        (string) $item['codigo'],
                    $permissoesEspeciais
                )
            );

        $ehAdmin =
            $papel === 'ADMINISTRADOR';

        $ehOrganizador =
            $papel === 'ORGANIZADOR';

        $temEscopoOrganizador =
            count($tiposOrganizador) > 0;

        $podeGerenciarProgramacoes =
            $ehAdmin
            || (
                $ehOrganizador
                && $temEscopoOrganizador
            );

        $podeGerenciarNecessidades =
            $ehAdmin
            || in_array(
                self::PERMISSAO_NECESSIDADES,
                $codigosPermissoes,
                true
            );

        $podeAprovarCadastros =
            $ehAdmin
            || (
                $ehOrganizador
                && in_array(
                    self::PERMISSAO_CADASTROS_APROVAR,
                    $codigosPermissoes,
                    true
                )
            );

        $capacidades = [
            /**
             * Recursos comuns a todo usuário autenticado.
             */
            'visualizar_dashboard' => true,
            'visualizar_mapa_semana' => true,
            'visualizar_programacoes' => true,
            'visualizar_meu_perfil' => true,
            'editar_meu_perfil' => true,
            'responder_minhas_participacoes' => true,

            /**
             * Administração institucional.
             */
            'editar_igreja' =>
                $ehAdmin,
            'gerenciar_usuarios' =>
                $ehAdmin,
            'gerenciar_departamentos' =>
                $ehAdmin,
            'gerenciar_funcoes' =>
                $ehAdmin,
            'gerenciar_tipos_programacao' =>
                $ehAdmin,
            'gerenciar_locais' =>
                $ehAdmin,

            /**
             * Programações e escalas.
             */
            'gerenciar_programacoes' =>
                $podeGerenciarProgramacoes,
            'gerenciar_escalas' =>
                $podeGerenciarProgramacoes,
            'gerenciar_series' =>
                $podeGerenciarProgramacoes,

            /**
             * Permissões administrativas especiais.
             */
            'gerenciar_permissoes_organizador' =>
                $ehAdmin,
            'gerenciar_permissoes_especiais' =>
                $ehAdmin,

            /**
             * Cadastro público.
             */
            'aprovar_cadastros' =>
                $podeAprovarCadastros,

            /**
             * Dado sensível.
             */
            'gerenciar_necessidades_especificas' =>
                $podeGerenciarNecessidades,
        ];

        return [
            'usuario' => [
                'id' =>
                    (int) $usuario['id'],
                'nome' =>
                    $usuario['nome'],
                'email' =>
                    $usuario['email'],
                'telefone' =>
                    $usuario['telefone'],
                'foto' =>
                    $usuario['foto'],
                'data_nascimento' =>
                    $usuario['data_nascimento'],
                'papel' => [
                    'id' =>
                        (int) $usuario[
                            'papel_id'
                        ],
                    'codigo' =>
                        $papel,
                    'nome' =>
                        $usuario[
                            'papel_nome'
                        ],
                ],
            ],

            'igreja' =>
                $igreja !== null
                    ? [
                        'id' =>
                            (int) $igreja['id'],
                        'nome' =>
                            $igreja['nome'],
                        'logotipo' =>
                            $igreja['logotipo'],
                        'contato' => [
                            'telefone' =>
                                $igreja['telefone'],
                            'email' =>
                                $igreja['email'],
                            'site' =>
                                $igreja['site'],
                        ],
                        'endereco' => [
                            'cep' =>
                                $igreja['cep'],
                            'logradouro' =>
                                $igreja['logradouro'],
                            'numero' =>
                                $igreja['numero'],
                            'complemento' =>
                                $igreja['complemento'],
                            'bairro' =>
                                $igreja['bairro'],
                            'cidade' =>
                                $igreja['cidade'],
                            'estado' =>
                                $igreja['estado'],
                        ],
                    ]
                    : null,

            'escopo_organizador' => [
                'possui_escopo' =>
                    $temEscopoOrganizador,
                'tipos_programacao' =>
                    array_map(
                        static fn (
                            array $item
                        ): array => [
                            'id' =>
                                (int) $item['id'],
                            'nome' =>
                                $item['nome'],
                            'descricao' =>
                                $item['descricao'],
                            'ativo' =>
                                (bool) $item['ativo'],
                        ],
                        $tiposOrganizador
                    ),
            ],

            'permissoes_especiais' =>
                array_map(
                    static fn (
                        array $item
                    ): array => [
                        'id' =>
                            (int) $item['id'],
                        'codigo' =>
                            $item['codigo'],
                        'nome' =>
                            $item['nome'],
                        'descricao' =>
                            $item['descricao'],
                    ],
                    $permissoesEspeciais
                ),

            'capacidades' =>
                $capacidades,

            /**
             * Ajuda o React a decidir quais áreas principais
             * devem aparecer no menu.
             */
            'navegacao' =>
                $this->montarNavegacao(
                    $capacidades
                ),
        ];
    }

    /**
     * @param array<string, bool> $capacidades
     * @return array<int, array<string, mixed>>
     */
    private function montarNavegacao(
        array $capacidades
    ): array {
        $itens = [
            [
                'codigo' => 'INICIO',
                'rotulo' => 'Início',
                'rota' => '/',
                'visivel' => true,
            ],
            [
                'codigo' => 'MAPA_SEMANA',
                'rotulo' => 'Minha Semana',
                'rota' => '/semana',
                'visivel' =>
                    $capacidades[
                        'visualizar_mapa_semana'
                    ],
            ],
            [
                'codigo' => 'PROGRAMACOES',
                'rotulo' => 'Programações',
                'rota' => '/programacoes',
                'visivel' =>
                    $capacidades[
                        'visualizar_programacoes'
                    ],
            ],
            [
                'codigo' => 'GESTAO_PROGRAMACOES',
                'rotulo' => 'Gerenciar Programações',
                'rota' => '/gestao/programacoes',
                'visivel' =>
                    $capacidades[
                        'gerenciar_programacoes'
                    ],
            ],
            [
                'codigo' => 'ESCALAS_SEMANA',
                'rotulo' => 'Escalas da Semana',
                'rota' => '/gestao/escalas-semana',
                'visivel' =>
                    $capacidades[
                        'gerenciar_escalas'
                    ],
            ],
            [
                'codigo' => 'CADASTROS',
                'rotulo' => 'Cadastros Pendentes',
                'rota' => '/gestao/cadastros',
                'visivel' =>
                    $capacidades[
                        'aprovar_cadastros'
                    ],
            ],
            [
                'codigo' => 'USUARIOS',
                'rotulo' => 'Usuários',
                'rota' => '/admin/usuarios',
                'visivel' =>
                    $capacidades[
                        'gerenciar_usuarios'
                    ],
            ],
            [
                'codigo' => 'ESTRUTURA',
                'rotulo' => 'Estrutura',
                'rota' => '/admin/estrutura',
                'visivel' =>
                    $capacidades[
                        'gerenciar_departamentos'
                    ],
            ],
            [
                'codigo' => 'NECESSIDADES',
                'rotulo' => 'Necessidades Específicas',
                'rota' => '/gestao/necessidades',
                'visivel' =>
                    $capacidades[
                        'gerenciar_necessidades_especificas'
                    ],
            ],
            [
                'codigo' => 'PERFIL',
                'rotulo' => 'Meu Perfil',
                'rota' => '/perfil',
                'visivel' => true,
            ],
        ];

        return array_values(
            array_filter(
                $itens,
                static fn (
                    array $item
                ): bool =>
                    $item['visivel'] === true
            )
        );
    }
}
