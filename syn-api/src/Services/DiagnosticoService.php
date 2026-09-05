<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use Throwable;

/**
 * Diagnóstico técnico da API.
 *
 * Esta classe NÃO expõe senhas, tokens, chaves ou valores
 * sensíveis do arquivo .env.
 *
 * Ela responde apenas se os componentes necessários estão
 * disponíveis e configurados.
 */
final class DiagnosticoService
{
    /**
     * Verifica se a API está pronta para receber tráfego.
     *
     * @return array{
     *     pronto: bool,
     *     ambiente: string,
     *     verificacoes: array<int, array{
     *         nome: string,
     *         status: string,
     *         mensagem: string
     *     }>
     * }
     */
    public function verificarProntidao(): array
    {
        $verificacoes = [];

        $verificacoes[] =
            $this->verificarPhp();

        $verificacoes[] =
            $this->verificarExtensoes();

        $verificacoes[] =
            $this->verificarBanco();

        $verificacoes[] =
            $this->verificarUploads();

        foreach (
            $this->verificarConfiguracaoProducao()
            as $verificacao
        ) {
            $verificacoes[] =
                $verificacao;
        }

        $temErro = false;

        foreach (
            $verificacoes
            as $verificacao
        ) {
            if (
                $verificacao['status']
                === 'erro'
            ) {
                $temErro = true;
                break;
            }
        }

        return [
            'pronto' =>
                !$temErro,
            'ambiente' =>
                $this->ambiente(),
            'verificacoes' =>
                $verificacoes,
        ];
    }

    /**
     * @return array{
     *     nome: string,
     *     status: string,
     *     mensagem: string
     * }
     */
    private function verificarPhp(): array
    {
        $ok =
            version_compare(
                PHP_VERSION,
                '8.2.0',
                '>='
            );

        return [
            'nome' =>
                'php',
            'status' =>
                $ok
                    ? 'ok'
                    : 'erro',
            'mensagem' =>
                $ok
                    ? 'Versão do PHP compatível.'
                    : 'O SYN requer PHP 8.2 ou superior.',
        ];
    }

    /**
     * @return array{
     *     nome: string,
     *     status: string,
     *     mensagem: string
     * }
     */
    private function verificarExtensoes(): array
    {
        $obrigatorias = [
            'pdo',
            'pdo_mysql',
            'openssl',
        ];

        $faltando = [];

        foreach (
            $obrigatorias
            as $extensao
        ) {
            if (
                !extension_loaded(
                    $extensao
                )
            ) {
                $faltando[] =
                    $extensao;
            }
        }

        if ($faltando !== []) {
            return [
                'nome' =>
                    'extensoes_php',
                'status' =>
                    'erro',
                'mensagem' =>
                    'Extensões PHP obrigatórias ausentes: '
                    . implode(
                        ', ',
                        $faltando
                    )
                    . '.',
            ];
        }

        return [
            'nome' =>
                'extensoes_php',
            'status' =>
                'ok',
            'mensagem' =>
                'Extensões PHP obrigatórias disponíveis.',
        ];
    }

    /**
     * @return array{
     *     nome: string,
     *     status: string,
     *     mensagem: string
     * }
     */
    private function verificarBanco(): array
    {
        try {
            $pdo =
                Database::conectar();

            $pdo
                ->query(
                    'SELECT 1'
                )
                ->fetchColumn();

            return [
                'nome' =>
                    'banco_de_dados',
                'status' =>
                    'ok',
                'mensagem' =>
                    'Conexão com o banco de dados disponível.',
            ];
        } catch (Throwable) {
            /*
             * Não retornamos a mensagem original da exceção,
             * pois ela poderia revelar dados internos.
             */
            return [
                'nome' =>
                    'banco_de_dados',
                'status' =>
                    'erro',
                'mensagem' =>
                    'Não foi possível validar a conexão com o banco de dados.',
            ];
        }
    }

    /**
     * @return array{
     *     nome: string,
     *     status: string,
     *     mensagem: string
     * }
     */
    private function verificarUploads(): array
    {
        $raizProjeto =
            dirname(
                __DIR__,
                2
            );

        $pastas = [
            $raizProjeto
                . '/public/uploads/igreja',
            $raizProjeto
                . '/public/uploads/perfis',
        ];

        foreach (
            $pastas
            as $pasta
        ) {
            if (
                !is_dir(
                    $pasta
                )
                || !is_writable(
                    $pasta
                )
            ) {
                return [
                    'nome' =>
                        'uploads',
                    'status' =>
                        'erro',
                    'mensagem' =>
                        'Uma ou mais pastas de upload não existem ou não possuem permissão de escrita.',
                ];
            }
        }

        return [
            'nome' =>
                'uploads',
            'status' =>
                'ok',
            'mensagem' =>
                'Pastas de upload disponíveis para escrita.',
        ];
    }

    /**
     * Em development as regras rígidas de produção não bloqueiam
     * a prontidão.
     *
     * @return array<int, array{
     *     nome: string,
     *     status: string,
     *     mensagem: string
     * }>
     */
    private function verificarConfiguracaoProducao(): array
    {
        if (
            $this->ambiente()
            !== 'production'
        ) {
            return [
                [
                    'nome' =>
                        'configuracao_producao',
                    'status' =>
                        'ok',
                    'mensagem' =>
                        'Validação rígida de produção não aplicada neste ambiente.',
                ],
            ];
        }

        return [
            $this->verificarDebugProducao(),
            $this->verificarJwtProducao(),
            $this->verificarUrlWebProducao(),
            $this->verificarCorsProducao(),
            $this->verificarEmailProducao(),
        ];
    }

    /**
     * @return array{
     *     nome: string,
     *     status: string,
     *     mensagem: string
     * }
     */
    private function verificarDebugProducao(): array
    {
        $debug =
            filter_var(
                $_ENV['APP_DEBUG']
                ?? false,
                FILTER_VALIDATE_BOOL
            );

        return [
            'nome' =>
                'app_debug',
            'status' =>
                $debug
                    ? 'erro'
                    : 'ok',
            'mensagem' =>
                $debug
                    ? 'APP_DEBUG deve estar desativado em produção.'
                    : 'APP_DEBUG está desativado.',
        ];
    }

    /**
     * @return array{
     *     nome: string,
     *     status: string,
     *     mensagem: string
     * }
     */
    private function verificarJwtProducao(): array
    {
        $secret =
            trim(
                (string) (
                    $_ENV[
                        'JWT_SECRET'
                    ]
                    ?? ''
                )
            );

        $inseguras = [
            '',
            'secret',
            'change-me',
            'troque-por-uma-chave-longa-e-aleatoria',
        ];

        $ok =
            strlen(
                $secret
            ) >= 32
            && !in_array(
                strtolower(
                    $secret
                ),
                $inseguras,
                true
            );

        return [
            'nome' =>
                'jwt_secret',
            'status' =>
                $ok
                    ? 'ok'
                    : 'erro',
            'mensagem' =>
                $ok
                    ? 'JWT_SECRET possui configuração compatível com produção.'
                    : 'JWT_SECRET precisa ser substituído por uma chave forte em produção.',
        ];
    }

    /**
     * @return array{
     *     nome: string,
     *     status: string,
     *     mensagem: string
     * }
     */
    private function verificarUrlWebProducao(): array
    {
        $url =
            trim(
                (string) (
                    $_ENV[
                        'APP_WEB_URL'
                    ]
                    ?? ''
                )
            );

        $scheme =
            strtolower(
                (string) parse_url(
                    $url,
                    PHP_URL_SCHEME
                )
            );

        $host =
            strtolower(
                (string) parse_url(
                    $url,
                    PHP_URL_HOST
                )
            );

        $hostLocal =
            in_array(
                $host,
                [
                    'localhost',
                    '127.0.0.1',
                    '::1',
                ],
                true
            );

        $ok =
            $url !== ''
            && $scheme === 'https'
            && $host !== ''
            && !$hostLocal;

        return [
            'nome' =>
                'app_web_url',
            'status' =>
                $ok
                    ? 'ok'
                    : 'erro',
            'mensagem' =>
                $ok
                    ? 'APP_WEB_URL utiliza HTTPS e endereço de produção.'
                    : 'APP_WEB_URL deve apontar para o domínio HTTPS real do frontend.',
        ];
    }

    /**
     * @return array{
     *     nome: string,
     *     status: string,
     *     mensagem: string
     * }
     */
    private function verificarCorsProducao(): array
    {
        $valor =
            trim(
                (string) (
                    $_ENV[
                        'CORS_ALLOWED_ORIGINS'
                    ]
                    ?? ''
                )
            );

        if ($valor === '') {
            return [
                'nome' =>
                    'cors',
                'status' =>
                    'erro',
                'mensagem' =>
                    'CORS_ALLOWED_ORIGINS precisa ser definido explicitamente em produção.',
            ];
        }

        $origens =
            array_filter(
                array_map(
                    'trim',
                    explode(
                        ',',
                        $valor
                    )
                )
            );

        foreach (
            $origens
            as $origem
        ) {
            $host =
                strtolower(
                    (string) parse_url(
                        $origem,
                        PHP_URL_HOST
                    )
                );

            $scheme =
                strtolower(
                    (string) parse_url(
                        $origem,
                        PHP_URL_SCHEME
                    )
                );

            if (
                $scheme !== 'https'
                || in_array(
                    $host,
                    [
                        'localhost',
                        '127.0.0.1',
                        '::1',
                    ],
                    true
                )
            ) {
                return [
                    'nome' =>
                        'cors',
                    'status' =>
                        'erro',
                    'mensagem' =>
                        'CORS_ALLOWED_ORIGINS contém uma origem inadequada para produção.',
                ];
            }
        }

        return [
            'nome' =>
                'cors',
            'status' =>
                'ok',
            'mensagem' =>
                'CORS possui origens explícitas de produção.',
        ];
    }

    /**
     * @return array{
     *     nome: string,
     *     status: string,
     *     mensagem: string
     * }
     */
    private function verificarEmailProducao(): array
    {
        $transport =
            strtolower(
                trim(
                    (string) (
                        $_ENV[
                            'MAIL_TRANSPORT'
                        ]
                        ?? ''
                    )
                )
            );

        if ($transport !== 'smtp') {
            return [
                'nome' =>
                    'email',
                'status' =>
                    'erro',
                'mensagem' =>
                    'MAIL_TRANSPORT deve estar configurado como smtp em produção.',
            ];
        }

        $obrigatorias = [
            'MAIL_HOST',
            'MAIL_FROM_ADDRESS',
        ];

        $smtpAuth =
            filter_var(
                $_ENV[
                    'MAIL_SMTP_AUTH'
                ]
                ?? true,
                FILTER_VALIDATE_BOOL
            );

        if ($smtpAuth) {
            $obrigatorias[] =
                'MAIL_USERNAME';

            $obrigatorias[] =
                'MAIL_PASSWORD';
        }

        foreach (
            $obrigatorias
            as $variavel
        ) {
            if (
                trim(
                    (string) (
                        $_ENV[
                            $variavel
                        ]
                        ?? ''
                    )
                ) === ''
            ) {
                return [
                    'nome' =>
                        'email',
                    'status' =>
                        'erro',
                    'mensagem' =>
                        'A configuração SMTP de produção está incompleta.',
                ];
            }
        }

        return [
            'nome' =>
                'email',
            'status' =>
                'ok',
            'mensagem' =>
                'Configuração SMTP básica disponível.',
        ];
    }

    private function ambiente(): string
    {
        $ambiente =
            strtolower(
                trim(
                    (string) (
                        $_ENV[
                            'APP_ENV'
                        ]
                        ?? 'production'
                    )
                )
            );

        return $ambiente !== ''
            ? $ambiente
            : 'production';
    }
}
