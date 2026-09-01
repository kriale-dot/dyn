<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\DadosInvalidosException;
use App\Repositories\RecuperacaoSenhaRepository;
use DateInterval;
use DateTimeImmutable;

/**
 * Regras de recuperação de senha.
 *
 * Segurança:
 * - resposta de "esqueci a senha" é genérica;
 * - token é aleatório;
 * - banco recebe somente SHA-256(token);
 * - expiração de 30 minutos;
 * - token de uso único;
 * - nova solicitação invalida as anteriores.
 */
final class RecuperacaoSenhaService
{
    private const EXPIRACAO_MINUTOS = 30;

    public function __construct(
        private RecuperacaoSenhaRepository $repository
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
         * Nesta etapa ainda não integramos provedor de e-mail.
         *
         * Em DEVELOPMENT devolvemos o token para teste no Postman.
         * Em produção esse campo NÃO aparece.
         */
        if ($this->ambienteDesenvolvimento()) {
            $resultado['desenvolvimento'] = [
                'token_teste' =>
                    $token,
                'expira_em' =>
                    $expira->format(
                        'Y-m-d H:i:s'
                    ),
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

        if (strlen($senha) < 8) {
            $erros['nova_senha'] =
                'A nova senha deve possuir pelo menos 8 caracteres.';
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

        return [
            'senha_redefinida' => true,
        ];
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
