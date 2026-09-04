<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\RateLimitException;
use DateTimeImmutable;
use PDO;
use Throwable;

/**
 * Rate limit persistente em MariaDB.
 *
 * Diferente de um contador em memória, esta implementação continua
 * funcionando mesmo quando o PHP encerra a requisição.
 *
 * A tabela guarda apenas SHA-256 da chave utilizada para limitar.
 */
final class RateLimitService
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Consome uma tentativa dentro de uma determinada janela.
     *
     * @return array{
     *     limit:int,
     *     remaining:int,
     *     retry_after:int
     * }
     */
    public function consumir(
        string $acao,
        string $identificador,
        int $limite,
        int $janelaSegundos
    ): array {
        if (
            $limite < 1
            || $janelaSegundos < 1
        ) {
            throw new \InvalidArgumentException(
                'Configuração de rate limit inválida.'
            );
        }

        $acao =
            trim($acao);

        $identificador =
            trim($identificador);

        if (
            $acao === ''
            || $identificador === ''
        ) {
            throw new \InvalidArgumentException(
                'Ação e identificador do rate limit são obrigatórios.'
            );
        }

        /**
         * Nenhum e-mail ou endereço IP puro é persistido.
         */
        $chaveHash =
            hash(
                'sha256',
                $identificador
            );

        $agora =
            new DateTimeImmutable();

        $this->pdo
            ->beginTransaction();

        try {
            $stmt =
                $this->pdo
                    ->prepare(
                        'SELECT
                            id,
                            contador,
                            limite,
                            janela_segundos,
                            janela_iniciada_em
                         FROM limites_requisicao
                         WHERE acao = :acao
                           AND chave_hash = :chave_hash
                         LIMIT 1
                         FOR UPDATE'
                    );

            $stmt->execute([
                ':acao' =>
                    $acao,
                ':chave_hash' =>
                    $chaveHash,
            ]);

            $registro =
                $stmt->fetch();

            if ($registro === false) {
                $stmt =
                    $this->pdo
                        ->prepare(
                            'INSERT INTO limites_requisicao (
                                acao,
                                chave_hash,
                                contador,
                                limite,
                                janela_segundos,
                                janela_iniciada_em
                             )
                             VALUES (
                                :acao,
                                :chave_hash,
                                1,
                                :limite,
                                :janela_segundos,
                                :janela_iniciada_em
                             )'
                        );

                $stmt->execute([
                    ':acao' =>
                        $acao,
                    ':chave_hash' =>
                        $chaveHash,
                    ':limite' =>
                        $limite,
                    ':janela_segundos' =>
                        $janelaSegundos,
                    ':janela_iniciada_em' =>
                        $agora->format(
                            'Y-m-d H:i:s'
                        ),
                ]);

                $this->pdo
                    ->commit();

                return [
                    'limit' =>
                        $limite,
                    'remaining' =>
                        max(
                            0,
                            $limite - 1
                        ),
                    'retry_after' =>
                        $janelaSegundos,
                ];
            }

            $inicio =
                new DateTimeImmutable(
                    (string)
                    $registro[
                        'janela_iniciada_em'
                    ]
                );

            $decorrido =
                max(
                    0,
                    $agora->getTimestamp()
                    - $inicio->getTimestamp()
                );

            /**
             * A janela terminou: reinicia o contador.
             */
            if (
                $decorrido
                >= $janelaSegundos
            ) {
                $stmt =
                    $this->pdo
                        ->prepare(
                            'UPDATE limites_requisicao
                             SET
                                contador = 1,
                                limite = :limite,
                                janela_segundos =
                                    :janela_segundos,
                                janela_iniciada_em =
                                    :janela_iniciada_em
                             WHERE id = :id'
                        );

                $stmt->execute([
                    ':limite' =>
                        $limite,
                    ':janela_segundos' =>
                        $janelaSegundos,
                    ':janela_iniciada_em' =>
                        $agora->format(
                            'Y-m-d H:i:s'
                        ),
                    ':id' =>
                        (int)
                        $registro['id'],
                ]);

                $this->pdo
                    ->commit();

                return [
                    'limit' =>
                        $limite,
                    'remaining' =>
                        max(
                            0,
                            $limite - 1
                        ),
                    'retry_after' =>
                        $janelaSegundos,
                ];
            }

            $contador =
                (int)
                $registro['contador'];

            if (
                $contador >= $limite
            ) {
                $retryAfter =
                    max(
                        1,
                        $janelaSegundos
                        - $decorrido
                    );

                $this->pdo
                    ->commit();

                throw new RateLimitException(
                    'Muitas tentativas em pouco tempo. Aguarde alguns instantes antes de tentar novamente.',
                    $retryAfter,
                    $limite
                );
            }

            $novoContador =
                $contador + 1;

            $stmt =
                $this->pdo
                    ->prepare(
                        'UPDATE limites_requisicao
                         SET
                            contador = :contador,
                            limite = :limite,
                            janela_segundos =
                                :janela_segundos
                         WHERE id = :id'
                    );

            $stmt->execute([
                ':contador' =>
                    $novoContador,
                ':limite' =>
                    $limite,
                ':janela_segundos' =>
                    $janelaSegundos,
                ':id' =>
                    (int)
                    $registro['id'],
            ]);

            $this->pdo
                ->commit();

            return [
                'limit' =>
                    $limite,
                'remaining' =>
                    max(
                        0,
                        $limite
                        - $novoContador
                    ),
                'retry_after' =>
                    max(
                        1,
                        $janelaSegundos
                        - $decorrido
                    ),
            ];
        } catch (Throwable $e) {
            if (
                $this->pdo
                    ->inTransaction()
            ) {
                $this->pdo
                    ->rollBack();
            }

            throw $e;
        }
    }
}
