<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Repositories\RecuperacaoSenhaRepository;
use DateInterval;
use DateTimeImmutable;
use Throwable;

/**
 * Regras de recuperação de senha.
 *
 * Segurança:
 * - resposta de "esqueci a senha" é genérica;
 * - token é aleatório;
 * - banco recebe somente SHA-256(token);
 * - expiração de 30 minutos;
 * - token de uso único;
 * - nova solicitação invalida as anteriores;
 * - envio real de instruções por e-mail;
 * - falha de SMTP não revela se o usuário existe.
 */
final class RecuperacaoSenhaService
{
    private const EXPIRACAO_MINUTOS = 30;

    public function __construct(
        private RecuperacaoSenhaRepository $repository,
        private EmailService $emailService
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function solicitar(
        array $dados
    ): array {
        $email = mb_strtolower(
            trim(
                (string) (
                    $dados['email'] ?? ''
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
            throw new DadosInvalidosException([
                'email' =>
                    'Informe um endereço de e-mail válido.',
            ]);
        }

        $usuario =
            $this->repository
                ->buscarUsuarioAtivoPorEmail(
                    $email
                );

        /**
         * Mensagem pública genérica para impedir descoberta
         * de quais e-mails estão cadastrados.
         */
        $resultado = [
            'mensagem_publica' =>
                'Se o e-mail estiver cadastrado e ativo, as instruções para redefinição da senha serão enviadas.',
        ];

        if ($usuario === null) {
            return $resultado;
        }

        $token = bin2hex(
            random_bytes(32)
        );

        $tokenHash =
            hash('sha256', $token);

        $agora =
            new DateTimeImmutable();

        $expira =
            $agora->add(
                new DateInterval(
                    'PT'
                    . self::EXPIRACAO_MINUTOS
                    . 'M'
                )
            );

        $this->repository
            ->criarSolicitacao(
                (int) $usuario['id'],
                $tokenHash,
                $expira->format(
                    'Y-m-d H:i:s'
                )
            );

        /**
         * Envio de e-mail real.
         *
         * A URL do frontend contém o token em texto puro apenas durante
         * o trânsito para o usuário. O banco continua armazenando somente
         * SHA-256(token).
         *
         * IMPORTANTE:
         * uma falha de SMTP NÃO muda a resposta pública deste endpoint.
         * Isso evita que um atacante descubra quais e-mails existem
         * comparando respostas de sucesso e falha.
         */
        $urlRedefinicao =
            $this->montarUrlRedefinicao(
                $token
            );

        try {
            $this->emailService
                ->enviarRecuperacaoSenha(
                    (string) $usuario['email'],
                    (string) $usuario['nome'],
                    $urlRedefinicao,
                    self::EXPIRACAO_MINUTOS
                );
        } catch (\Throwable $e) {
            error_log(
                '[SYN] Falha ao enviar recuperação de senha: '
                . $e->getMessage()
            );
        }

        /**
         * Em DEVELOPMENT o token continua disponível para testes locais.
         * Em produção este bloco nunca é devolvido.
         */
        if ($this->ambienteDesenvolvimento()) {
            $resultado['desenvolvimento'] = [
                'token_teste' =>
                    $token,
                'expira_em' =>
                    $expira->format(
                        'Y-m-d H:i:s'
                    ),
                'url_redefinicao' =>
                    $urlRedefinicao,
                'observacao' =>
                    'Somente ambiente development. Em produção o token deve ser enviado por e-mail.',
            ];
        }

        return $resultado;
    }

    /**
     * @return array<string, mixed>
     */
    public function redefinir(
        array $dados
    ): array {
        $token =
            trim(
                (string) (
                    $dados['token'] ?? ''
                )
            );

        $senha =
            (string) (
                $dados['nova_senha']
                ?? ''
            );

        $confirmacao =
            (string) (
                $dados[
                    'confirmar_senha'
                ] ?? ''
            );

        $erros = [];

        if (
            $token === ''
            || strlen($token) !== 64
            || !ctype_xdigit($token)
        ) {
            $erros['token'] =
                'Token de recuperação inválido.';
        }

        if (strlen($senha) < 5) {
            $erros['nova_senha'] =
                'A nova senha deve possuir pelo menos 5 caracteres.';
        }

        if (
            $senha !== ''
            && $senha !== $confirmacao
        ) {
            $erros['confirmar_senha'] =
                'A confirmação da senha não confere.';
        }

        if ($erros !== []) {
            throw new DadosInvalidosException(
                $erros
            );
        }

        $tokenHash =
            hash('sha256', $token);

        $registro =
            $this->repository
                ->buscarTokenValido(
                    $tokenHash
                );

        if ($registro === null) {
            throw new DadosInvalidosException([
                'token' =>
                    'O token é inválido, expirou ou já foi utilizado.',
            ]);
        }

        $senhaHash =
            password_hash(
                $senha,
                PASSWORD_DEFAULT
            );

        if ($senhaHash === false) {
            throw new \RuntimeException(
                'Não foi possível proteger a nova senha.'
            );
        }

        $this->repository
            ->redefinirSenha(
                (int) $registro['id'],
                (int) $registro[
                    'usuario_id'
                ],
                $senhaHash
            );

        /**
         * Neste ponto a senha já foi redefinida e todas as sessões foram
         * revogadas. Falha de SMTP não faz rollback da operação.
         */
        $emailEnviado =
            true;

        try {
            $this->emailService
                ->enviarAvisoSenhaRedefinida(
                    (string)
                    $registro['usuario_email'],
                    (string)
                    $registro['usuario_nome']
                );
        } catch (Throwable $e) {
            $emailEnviado =
                false;

            error_log(
                '[SYN] Falha ao enviar aviso de senha redefinida para '
                . $registro['usuario_email']
                . ': '
                . $e->getMessage()
            );
        }

        return [
            'senha_redefinida' =>
                true,
            'sessoes_encerradas' =>
                true,
            'email_seguranca_enviado' =>
                $emailEnviado,
        ];
    }

    /**
     * Monta o endereço do frontend responsável pelo formulário de
     * redefinição.
     *
     * APP_WEB_URL deve apontar para a origem do React, sem barra final.
     */
    private function montarUrlRedefinicao(
        string $token
    ): string {
        $appWebUrl =
            $_ENV['APP_WEB_URL']
            ?? $_SERVER['APP_WEB_URL']
            ?? getenv('APP_WEB_URL')
            ?: 'http://localhost:5173';

        return rtrim(
            (string) $appWebUrl,
            '/'
        )
        . '/redefinir-senha?token='
        . rawurlencode($token);
    }

    private function ambienteDesenvolvimento(): bool
    {
        $ambiente =
            $_ENV['APP_ENV']
            ?? $_SERVER['APP_ENV']
            ?? getenv('APP_ENV')
            ?: 'production';

        return mb_strtolower(
            (string) $ambiente
        ) === 'development';
    }
}
