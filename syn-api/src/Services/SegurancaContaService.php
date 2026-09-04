<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AutenticacaoException;
use App\Repositories\SegurancaContaRepository;
use Throwable;

/**
 * Regras para alteração da senha pelo próprio usuário autenticado.
 */
final class SegurancaContaService
{
    public function __construct(
        private SegurancaContaRepository $repository,
        private EmailService $emailService
    ) {
    }

    /**
     * @param array<string, mixed> $dados
     * @return array<string, mixed>
     */
    public function alterarSenha(
        int $usuarioId,
        array $dados
    ): array {
        if ($usuarioId < 1) {
            throw new AutenticacaoException(
                'Usuário autenticado inválido.'
            );
        }

        $senhaAtual =
            (string)
            ($dados['senha_atual'] ?? '');

        $novaSenha =
            (string)
            ($dados['nova_senha'] ?? '');

        $confirmacao =
            (string)
            (
                $dados[
                    'confirmar_nova_senha'
                ]
                ?? ''
            );

        if ($senhaAtual === '') {
            throw new AutenticacaoException(
                'Informe sua senha atual.'
            );
        }

        /**
         * Mantém a regra definida para o SYN:
         * mínimo de 5 caracteres.
         */
        if (
            mb_strlen(
                $novaSenha
            ) < 5
        ) {
            throw new AutenticacaoException(
                'A nova senha deve possuir pelo menos 5 caracteres.'
            );
        }

        if (
            mb_strlen(
                $novaSenha
            ) > 255
        ) {
            throw new AutenticacaoException(
                'A nova senha é muito longa.'
            );
        }

        if (
            $novaSenha
            !== $confirmacao
        ) {
            throw new AutenticacaoException(
                'A confirmação da nova senha não corresponde.'
            );
        }

        $usuario =
            $this->repository
                ->buscarUsuarioAtivo(
                    $usuarioId
                );

        if ($usuario === null) {
            throw new AutenticacaoException(
                'Usuário não encontrado ou inativo.'
            );
        }

        if (
            !password_verify(
                $senhaAtual,
                (string)
                $usuario['senha_hash']
            )
        ) {
            throw new AutenticacaoException(
                'A senha atual está incorreta.'
            );
        }

        if (
            password_verify(
                $novaSenha,
                (string)
                $usuario['senha_hash']
            )
        ) {
            throw new AutenticacaoException(
                'A nova senha deve ser diferente da senha atual.'
            );
        }

        $novoHash =
            password_hash(
                $novaSenha,
                PASSWORD_DEFAULT
            );

        if ($novoHash === false) {
            throw new AutenticacaoException(
                'Não foi possível proteger a nova senha.'
            );
        }

        $alterou =
            $this->repository
                ->alterarSenhaERevogarSessoes(
                    $usuarioId,
                    $novoHash
                );

        if (!$alterou) {
            throw new AutenticacaoException(
                'Não foi possível alterar a senha.'
            );
        }

        /**
         * A alteração da senha já foi confirmada no banco.
         *
         * Uma eventual falha do SMTP NÃO pode desfazer a operação de
         * segurança. Apenas registramos no log e informamos o resultado.
         */
        $emailEnviado =
            true;

        try {
            $this->emailService
                ->enviarAvisoSenhaAlterada(
                    (string)
                    $usuario['email'],
                    (string)
                    $usuario['nome']
                );
        } catch (Throwable $e) {
            $emailEnviado =
                false;

            error_log(
                '[SYN] Falha ao enviar aviso de senha alterada para '
                . $usuario['email']
                . ': '
                . $e->getMessage()
            );
        }

        return [
            'senha_alterada' =>
                true,
            'sessoes_encerradas' =>
                true,
            'email_seguranca_enviado' =>
                $emailEnviado,
            'mensagem' =>
                $emailEnviado
                    ? 'Senha alterada com sucesso. Todas as sessões foram encerradas e enviamos um aviso de segurança por e-mail. Entre novamente com a nova senha.'
                    : 'Senha alterada com sucesso e todas as sessões foram encerradas. Não foi possível enviar o aviso de segurança por e-mail, mas a alteração está concluída.',
        ];
    }
}
