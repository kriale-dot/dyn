<?php

declare(strict_types=1);

namespace App\Logging;

use Throwable;

/**
 * Logger técnico simples do SYN.
 *
 * Características:
 * - grava uma linha JSON por evento;
 * - não grava Authorization, senhas, JWT ou corpo da requisição;
 * - faz rotação simples quando o arquivo cresce demais;
 * - se o arquivo não puder ser escrito, usa error_log() como fallback.
 */
final class AppLogger
{
    private const DEFAULT_MAX_BYTES = 10485760; // 10 MB

    public function __construct(
        private string $arquivoLog
    ) {
    }

    /**
     * @param array<string, mixed> $contexto
     */
    public function info(
        string $evento,
        array $contexto = []
    ): void {
        $this->gravar(
            'info',
            $evento,
            $contexto
        );
    }

    /**
     * @param array<string, mixed> $contexto
     */
    public function warning(
        string $evento,
        array $contexto = []
    ): void {
        $this->gravar(
            'warning',
            $evento,
            $contexto
        );
    }

    /**
     * @param array<string, mixed> $contexto
     */
    public function error(
        string $evento,
        array $contexto = []
    ): void {
        $this->gravar(
            'error',
            $evento,
            $contexto
        );
    }

    /**
     * Gera contexto seguro a partir de uma exceção.
     *
     * @return array<string, mixed>
     */
    public function contextoExcecao(
        Throwable $exception
    ): array {
        return [
            'exception_class' =>
                $exception::class,
            'exception_message' =>
                mb_substr(
                    $exception->getMessage(),
                    0,
                    1000
                ),
            'exception_file' =>
                $exception->getFile(),
            'exception_line' =>
                $exception->getLine(),
        ];
    }

    /**
     * @param array<string, mixed> $contexto
     */
    private function gravar(
        string $nivel,
        string $evento,
        array $contexto
    ): void {
        try {
            $this->prepararDiretorio();
            $this->rotacionarSeNecessario();

            $payload = [
                'timestamp_utc' =>
                    gmdate(
                        DATE_ATOM
                    ),
                'nivel' =>
                    $nivel,
                'evento' =>
                    $evento,
                'contexto' =>
                    $this->sanitizar(
                        $contexto
                    ),
            ];

            $linha =
                json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                    | JSON_THROW_ON_ERROR
                )
                . PHP_EOL;

            $ok =
                @file_put_contents(
                    $this->arquivoLog,
                    $linha,
                    FILE_APPEND
                    | LOCK_EX
                );

            if ($ok === false) {
                error_log(
                    '[SYN] Falha ao gravar log técnico: '
                    . $evento
                );
            }
        } catch (Throwable) {
            /*
             * Logger nunca pode derrubar a aplicação.
             */
            error_log(
                '[SYN] Falha interna do logger técnico.'
            );
        }
    }

    private function prepararDiretorio(): void
    {
        $diretorio =
            dirname(
                $this->arquivoLog
            );

        if (
            !is_dir(
                $diretorio
            )
        ) {
            @mkdir(
                $diretorio,
                0775,
                true
            );
        }
    }

    private function rotacionarSeNecessario(): void
    {
        if (
            !is_file(
                $this->arquivoLog
            )
        ) {
            return;
        }

        $maxBytes =
            (int) (
                $_ENV[
                    'LOG_MAX_BYTES'
                ]
                ?? getenv(
                    'LOG_MAX_BYTES'
                )
                ?: self::DEFAULT_MAX_BYTES
            );

        if (
            $maxBytes <= 0
        ) {
            $maxBytes =
                self::DEFAULT_MAX_BYTES;
        }

        $tamanho =
            @filesize(
                $this->arquivoLog
            );

        if (
            $tamanho === false
            || $tamanho < $maxBytes
        ) {
            return;
        }

        $arquivoAnterior =
            $this->arquivoLog
            . '.1';

        if (
            is_file(
                $arquivoAnterior
            )
        ) {
            @unlink(
                $arquivoAnterior
            );
        }

        @rename(
            $this->arquivoLog,
            $arquivoAnterior
        );
    }

    /**
     * Remove campos que eventualmente tenham nomes sensíveis.
     *
     * @param array<string, mixed> $contexto
     * @return array<string, mixed>
     */
    private function sanitizar(
        array $contexto
    ): array {
        $resultado = [];

        foreach (
            $contexto
            as $chave => $valor
        ) {
            $nome =
                strtolower(
                    (string) $chave
                );

            if (
                preg_match(
                    '/password|senha|secret|token|authorization|cookie/i',
                    $nome
                )
            ) {
                $resultado[
                    (string) $chave
                ] = '[REMOVIDO]';

                continue;
            }

            if (
                is_string(
                    $valor
                )
            ) {
                $resultado[
                    (string) $chave
                ] =
                    mb_substr(
                        $valor,
                        0,
                        2000
                    );

                continue;
            }

            if (
                is_scalar(
                    $valor
                )
                || $valor === null
            ) {
                $resultado[
                    (string) $chave
                ] =
                    $valor;

                continue;
            }

            /*
             * Para evitar dumps acidentais de objetos/arrays grandes.
             */
            $resultado[
                (string) $chave
            ] = '[DADO_COMPLEXO]';
        }

        return $resultado;
    }
}
