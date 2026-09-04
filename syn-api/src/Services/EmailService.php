<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

/**
 * Serviço de e-mail do SYN.
 *
 * Transportes:
 *
 * - log:
 *   útil somente para desenvolvimento. O link é escrito no log do PHP.
 *
 * - smtp:
 *   envio real usando PHPMailer.
 *
 * Segurança:
 * nenhuma credencial SMTP deve ser escrita no código-fonte.
 * Todas são lidas do .env.
 */
final class EmailService
{
    public function enviarRecuperacaoSenha(
        string $destinatarioEmail,
        string $destinatarioNome,
        string $urlRedefinicao,
        int $expiracaoMinutos
    ): void {
        $transport =
            mb_strtolower(
                trim(
                    $this->env(
                        'MAIL_TRANSPORT',
                        'log'
                    )
                )
            );

        if ($transport === 'log') {
            $this->registrarEmLog(
                $destinatarioEmail,
                $urlRedefinicao,
                $expiracaoMinutos
            );

            return;
        }

        if ($transport !== 'smtp') {
            throw new RuntimeException(
                'MAIL_TRANSPORT deve ser log ou smtp.'
            );
        }

        $this->enviarPorSmtp(
            $destinatarioEmail,
            $destinatarioNome,
            $urlRedefinicao,
            $expiracaoMinutos
        );
    }

    /**
     * Aviso depois que a senha foi alterada por um usuário autenticado.
     *
     * Nenhuma senha, hash ou token é incluído na mensagem.
     */
    public function enviarAvisoSenhaAlterada(
        string $destinatarioEmail,
        string $destinatarioNome
    ): void {
        $urlLogin =
            rtrim(
                $this->env(
                    'APP_WEB_URL',
                    'http://localhost:5173'
                ),
                '/'
            )
            . '/login';

        $assunto =
            'Sua senha foi alterada — SYN';

        $nomeSeguro =
            htmlspecialchars(
                $destinatarioNome,
                ENT_QUOTES
                | ENT_SUBSTITUTE,
                'UTF-8'
            );

        $urlSegura =
            htmlspecialchars(
                $urlLogin,
                ENT_QUOTES
                | ENT_SUBSTITUTE,
                'UTF-8'
            );

        $html = <<<HTML
<!doctype html>
<html lang="pt-BR">
<body style="margin:0;background:#f4f7f6;font-family:Arial,sans-serif;color:#314a42;">
  <div style="max-width:560px;margin:32px auto;background:#fff;border:1px solid #dce5e1;border-radius:16px;padding:28px;">
    <div style="font-size:12px;font-weight:700;color:#668078;text-transform:uppercase;">SYN</div>

    <h1 style="font-size:22px;margin:8px 0 14px;">
      Sua senha foi alterada
    </h1>

    <p style="font-size:14px;line-height:1.6;">
      Olá, {$nomeSeguro}.
    </p>

    <p style="font-size:14px;line-height:1.6;">
      A senha da sua conta no SYN foi alterada com sucesso.
      Por segurança, todas as sessões anteriores foram encerradas.
    </p>

    <p style="font-size:13px;line-height:1.6;color:#6f7d78;">
      Se foi você quem fez a alteração, nenhuma ação adicional é necessária.
      Se não reconhece esta mudança, entre em contato com a administração
      da igreja imediatamente.
    </p>

    <p style="margin:24px 0;">
      <a href="{$urlSegura}" style="display:inline-block;background:#3f7565;color:#fff;text-decoration:none;border-radius:9px;padding:12px 18px;font-size:14px;font-weight:700;">
        Entrar no SYN
      </a>
    </p>
  </div>
</body>
</html>
HTML;

        $texto =
            "Olá, {$destinatarioNome}.\n\n"
            . "A senha da sua conta no SYN foi alterada com sucesso.\n"
            . "Todas as sessões anteriores foram encerradas.\n\n"
            . "Se você não reconhece esta alteração, entre em contato com "
            . "a administração da igreja imediatamente.\n\n"
            . "Entrar: {$urlLogin}\n";

        $this->enviarMensagem(
            $destinatarioEmail,
            $destinatarioNome,
            $assunto,
            $html,
            $texto
        );
    }

    /**
     * Aviso depois de uma redefinição concluída pelo fluxo
     * "Esqueci minha senha".
     */
    public function enviarAvisoSenhaRedefinida(
        string $destinatarioEmail,
        string $destinatarioNome
    ): void {
        $urlLogin =
            rtrim(
                $this->env(
                    'APP_WEB_URL',
                    'http://localhost:5173'
                ),
                '/'
            )
            . '/login';

        $assunto =
            'Sua senha foi redefinida — SYN';

        $nomeSeguro =
            htmlspecialchars(
                $destinatarioNome,
                ENT_QUOTES
                | ENT_SUBSTITUTE,
                'UTF-8'
            );

        $urlSegura =
            htmlspecialchars(
                $urlLogin,
                ENT_QUOTES
                | ENT_SUBSTITUTE,
                'UTF-8'
            );

        $html = <<<HTML
<!doctype html>
<html lang="pt-BR">
<body style="margin:0;background:#f4f7f6;font-family:Arial,sans-serif;color:#314a42;">
  <div style="max-width:560px;margin:32px auto;background:#fff;border:1px solid #dce5e1;border-radius:16px;padding:28px;">
    <div style="font-size:12px;font-weight:700;color:#668078;text-transform:uppercase;">SYN</div>

    <h1 style="font-size:22px;margin:8px 0 14px;">
      Sua senha foi redefinida
    </h1>

    <p style="font-size:14px;line-height:1.6;">
      Olá, {$nomeSeguro}.
    </p>

    <p style="font-size:14px;line-height:1.6;">
      A recuperação de senha da sua conta no SYN foi concluída.
      Todas as sessões anteriores foram encerradas automaticamente.
    </p>

    <p style="font-size:13px;line-height:1.6;color:#6f7d78;">
      Se você não solicitou ou não concluiu essa redefinição, comunique
      imediatamente a administração da igreja.
    </p>

    <p style="margin:24px 0;">
      <a href="{$urlSegura}" style="display:inline-block;background:#3f7565;color:#fff;text-decoration:none;border-radius:9px;padding:12px 18px;font-size:14px;font-weight:700;">
        Entrar no SYN
      </a>
    </p>
  </div>
</body>
</html>
HTML;

        $texto =
            "Olá, {$destinatarioNome}.\n\n"
            . "A recuperação de senha da sua conta no SYN foi concluída.\n"
            . "Todas as sessões anteriores foram encerradas.\n\n"
            . "Se você não reconhece esta redefinição, entre em contato "
            . "com a administração da igreja imediatamente.\n\n"
            . "Entrar: {$urlLogin}\n";

        $this->enviarMensagem(
            $destinatarioEmail,
            $destinatarioNome,
            $assunto,
            $html,
            $texto
        );
    }

    /**
     * Confirmação enviada ao NOVO endereço.
     */
    public function enviarConfirmacaoAlteracaoEmail(
        string $destinatarioEmail,
        string $destinatarioNome,
        string $urlConfirmacao,
        int $expiracaoHoras
    ): void {
        $assunto =
            'Confirme seu novo e-mail — SYN';

        $nomeSeguro =
            htmlspecialchars(
                $destinatarioNome,
                ENT_QUOTES
                | ENT_SUBSTITUTE,
                'UTF-8'
            );

        $urlSegura =
            htmlspecialchars(
                $urlConfirmacao,
                ENT_QUOTES
                | ENT_SUBSTITUTE,
                'UTF-8'
            );

        $html = <<<HTML
<!doctype html>
<html lang="pt-BR">
<body style="margin:0;background:#f4f7f6;font-family:Arial,sans-serif;color:#314a42;">
  <div style="max-width:560px;margin:32px auto;background:#fff;border:1px solid #dce5e1;border-radius:16px;padding:28px;">
    <div style="font-size:12px;font-weight:700;color:#668078;text-transform:uppercase;">SYN</div>
    <h1 style="font-size:22px;margin:8px 0 14px;">Confirme seu novo e-mail</h1>
    <p style="font-size:14px;line-height:1.6;">Olá, {$nomeSeguro}.</p>
    <p style="font-size:14px;line-height:1.6;">
      Uma alteração de e-mail foi solicitada para sua conta do SYN.
      Para concluir, confirme que você tem acesso a este novo endereço.
    </p>
    <p style="margin:24px 0;">
      <a href="{$urlSegura}" style="display:inline-block;background:#3f7565;color:#fff;text-decoration:none;border-radius:9px;padding:12px 18px;font-size:14px;font-weight:700;">
        Confirmar novo e-mail
      </a>
    </p>
    <p style="font-size:12px;line-height:1.6;color:#71817b;">
      O link é válido por {$expiracaoHoras} horas. Se você não reconhece
      esta solicitação, não clique no botão.
    </p>
  </div>
</body>
</html>
HTML;

        $texto =
            "Olá, {$destinatarioNome}.\n\n"
            . "Confirme seu novo e-mail do SYN:\n"
            . "{$urlConfirmacao}\n\n"
            . "Validade: {$expiracaoHoras} horas.\n";

        $this->enviarMensagem(
            $destinatarioEmail,
            $destinatarioNome,
            $assunto,
            $html,
            $texto
        );
    }

    /**
     * Aviso enviado ao endereço ATUAL quando a troca é solicitada.
     */
    public function enviarAvisoSolicitacaoAlteracaoEmail(
        string $destinatarioEmail,
        string $destinatarioNome,
        string $novoEmail
    ): void {
        $assunto =
            'Solicitação de alteração de e-mail — SYN';

        $novoSeguro =
            htmlspecialchars(
                $novoEmail,
                ENT_QUOTES
                | ENT_SUBSTITUTE,
                'UTF-8'
            );

        $html = <<<HTML
<!doctype html>
<html lang="pt-BR">
<body style="margin:0;background:#f4f7f6;font-family:Arial,sans-serif;color:#314a42;">
  <div style="max-width:560px;margin:32px auto;background:#fff;border:1px solid #dce5e1;border-radius:16px;padding:28px;">
    <div style="font-size:12px;font-weight:700;color:#668078;text-transform:uppercase;">SYN</div>
    <h1 style="font-size:22px;margin:8px 0 14px;">Alteração de e-mail solicitada</h1>
    <p style="font-size:14px;line-height:1.6;">
      Foi solicitada a alteração do e-mail da sua conta para:
      <strong>{$novoSeguro}</strong>.
    </p>
    <p style="font-size:13px;line-height:1.6;color:#6f7d78;">
      A mudança só será concluída se o novo endereço for confirmado.
      Se você não fez esta solicitação, altere sua senha e encerre todas
      as sessões da conta.
    </p>
  </div>
</body>
</html>
HTML;

        $texto =
            "Foi solicitada a alteração do e-mail da sua conta SYN para "
            . $novoEmail
            . ".\n"
            . "A mudança só será concluída após confirmação do novo endereço.\n";

        $this->enviarMensagem(
            $destinatarioEmail,
            $destinatarioNome,
            $assunto,
            $html,
            $texto
        );
    }

    /**
     * Confirmação final enviada ao NOVO endereço.
     */
    public function enviarEmailAlterado(
        string $destinatarioEmail,
        string $destinatarioNome
    ): void {
        $urlLogin =
            rtrim(
                $this->env(
                    'APP_WEB_URL',
                    'http://localhost:5173'
                ),
                '/'
            )
            . '/login';

        $assunto =
            'Seu e-mail foi alterado — SYN';

        $urlSegura =
            htmlspecialchars(
                $urlLogin,
                ENT_QUOTES
                | ENT_SUBSTITUTE,
                'UTF-8'
            );

        $html = <<<HTML
<!doctype html>
<html lang="pt-BR">
<body style="margin:0;background:#f4f7f6;font-family:Arial,sans-serif;color:#314a42;">
  <div style="max-width:560px;margin:32px auto;background:#fff;border:1px solid #dce5e1;border-radius:16px;padding:28px;">
    <div style="font-size:12px;font-weight:700;color:#668078;text-transform:uppercase;">SYN</div>
    <h1 style="font-size:22px;margin:8px 0 14px;">Novo e-mail confirmado</h1>
    <p style="font-size:14px;line-height:1.6;">
      Seu e-mail de acesso ao SYN foi alterado com sucesso.
      Todas as sessões anteriores foram encerradas por segurança.
    </p>
    <p style="margin:24px 0;">
      <a href="{$urlSegura}" style="display:inline-block;background:#3f7565;color:#fff;text-decoration:none;border-radius:9px;padding:12px 18px;font-size:14px;font-weight:700;">
        Entrar no SYN
      </a>
    </p>
  </div>
</body>
</html>
HTML;

        $texto =
            "Seu e-mail de acesso ao SYN foi alterado com sucesso.\n"
            . "Todas as sessões anteriores foram encerradas.\n"
            . "Entrar: {$urlLogin}\n";

        $this->enviarMensagem(
            $destinatarioEmail,
            $destinatarioNome,
            $assunto,
            $html,
            $texto
        );
    }

    /**
     * Aviso final ao endereço ANTIGO.
     */
    public function enviarAvisoEmailAlterado(
        string $destinatarioEmail,
        string $destinatarioNome,
        string $novoEmail
    ): void {
        $assunto =
            'O e-mail da sua conta foi alterado — SYN';

        $novoSeguro =
            htmlspecialchars(
                $novoEmail,
                ENT_QUOTES
                | ENT_SUBSTITUTE,
                'UTF-8'
            );

        $html = <<<HTML
<!doctype html>
<html lang="pt-BR">
<body style="margin:0;background:#f4f7f6;font-family:Arial,sans-serif;color:#314a42;">
  <div style="max-width:560px;margin:32px auto;background:#fff;border:1px solid #dce5e1;border-radius:16px;padding:28px;">
    <div style="font-size:12px;font-weight:700;color:#668078;text-transform:uppercase;">SYN</div>
    <h1 style="font-size:22px;margin:8px 0 14px;">E-mail da conta alterado</h1>
    <p style="font-size:14px;line-height:1.6;">
      O endereço de acesso da sua conta do SYN foi alterado para
      <strong>{$novoSeguro}</strong>.
    </p>
    <p style="font-size:13px;line-height:1.6;color:#6f7d78;">
      Se você não reconhece esta alteração, entre em contato com a
      administração da igreja imediatamente.
    </p>
  </div>
</body>
</html>
HTML;

        $texto =
            "O e-mail da sua conta SYN foi alterado para "
            . $novoEmail
            . ".\n"
            . "Se você não reconhece esta alteração, contate a administração.\n";

        $this->enviarMensagem(
            $destinatarioEmail,
            $destinatarioNome,
            $assunto,
            $html,
            $texto
        );
    }

    /**
     * Envia o link que comprova que a pessoa realmente controla o
     * endereço de e-mail informado no cadastro.
     */
    public function enviarConfirmacaoCadastro(
        string $destinatarioEmail,
        string $destinatarioNome,
        string $urlConfirmacao,
        int $expiracaoHoras
    ): void {
        $assunto =
            'Confirme seu e-mail — SYN';

        $nomeSeguro =
            htmlspecialchars(
                $destinatarioNome,
                ENT_QUOTES
                | ENT_SUBSTITUTE,
                'UTF-8'
            );

        $urlSegura =
            htmlspecialchars(
                $urlConfirmacao,
                ENT_QUOTES
                | ENT_SUBSTITUTE,
                'UTF-8'
            );

        $html = <<<HTML
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
</head>
<body style="margin:0;background:#f4f7f6;font-family:Arial,sans-serif;color:#314a42;">
  <div style="max-width:560px;margin:32px auto;background:#ffffff;border:1px solid #dce5e1;border-radius:16px;padding:28px;">
    <div style="font-size:12px;font-weight:700;color:#668078;text-transform:uppercase;">
      SYN
    </div>

    <h1 style="font-size:22px;margin:8px 0 14px;">
      Confirme seu e-mail
    </h1>

    <p style="font-size:14px;line-height:1.6;">
      Olá, {$nomeSeguro}.
    </p>

    <p style="font-size:14px;line-height:1.6;">
      Recebemos uma solicitação de cadastro no SYN usando este endereço
      de e-mail. Antes que a administração da igreja possa analisar o
      cadastro, precisamos confirmar que este e-mail pertence a você.
    </p>

    <p style="margin:24px 0;">
      <a
        href="{$urlSegura}"
        style="display:inline-block;background:#3f7565;color:#ffffff;text-decoration:none;border-radius:9px;padding:12px 18px;font-size:14px;font-weight:700;"
      >
        Confirmar meu e-mail
      </a>
    </p>

    <p style="font-size:12px;line-height:1.6;color:#71817b;">
      O link é válido por {$expiracaoHoras} horas. Depois da confirmação,
      sua solicitação ficará aguardando aprovação.
    </p>

    <p style="font-size:12px;line-height:1.6;color:#71817b;">
      Se você não solicitou cadastro no SYN, ignore esta mensagem.
    </p>
  </div>
</body>
</html>
HTML;

        $texto =
            "Olá, {$destinatarioNome}.\n\n"
            . "Recebemos uma solicitação de cadastro no SYN com este "
            . "e-mail.\n"
            . "Confirme o endereço antes da análise pela igreja.\n\n"
            . "Confirmar: {$urlConfirmacao}\n\n"
            . "O link é válido por {$expiracaoHoras} horas.\n";

        $this->enviarMensagem(
            $destinatarioEmail,
            $destinatarioNome,
            $assunto,
            $html,
            $texto
        );
    }

    /**
     * Envia a confirmação de que o cadastro foi aprovado.
     *
     * A conta já foi criada antes desta chamada. Portanto, uma eventual
     * falha de SMTP nunca deve desfazer a aprovação.
     */
    public function enviarCadastroAprovado(
        string $destinatarioEmail,
        string $destinatarioNome
    ): void {
        $urlLogin =
            rtrim(
                $this->env(
                    'APP_WEB_URL',
                    'http://localhost:5173'
                ),
                '/'
            )
            . '/login';

        $assunto =
            'Seu cadastro foi aprovado — SYN';

        $nomeSeguro =
            htmlspecialchars(
                $destinatarioNome,
                ENT_QUOTES
                | ENT_SUBSTITUTE,
                'UTF-8'
            );

        $urlSegura =
            htmlspecialchars(
                $urlLogin,
                ENT_QUOTES
                | ENT_SUBSTITUTE,
                'UTF-8'
            );

        $html = <<<HTML
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
</head>
<body style="margin:0;background:#f4f7f6;font-family:Arial,sans-serif;color:#314a42;">
  <div style="max-width:560px;margin:32px auto;background:#ffffff;border:1px solid #dce5e1;border-radius:16px;padding:28px;">
    <div style="font-size:12px;font-weight:700;color:#668078;text-transform:uppercase;">
      SYN
    </div>

    <h1 style="font-size:22px;margin:8px 0 14px;">
      Cadastro aprovado
    </h1>

    <p style="font-size:14px;line-height:1.6;">
      Olá, {$nomeSeguro}.
    </p>

    <p style="font-size:14px;line-height:1.6;">
      Sua solicitação de cadastro no SYN foi aprovada.
      Sua conta já está ativa e você pode entrar utilizando o mesmo
      e-mail e a senha informados no cadastro.
    </p>

    <p style="margin:24px 0;">
      <a
        href="{$urlSegura}"
        style="display:inline-block;background:#3f7565;color:#ffffff;text-decoration:none;border-radius:9px;padding:12px 18px;font-size:14px;font-weight:700;"
      >
        Entrar no SYN
      </a>
    </p>

    <p style="font-size:12px;line-height:1.6;color:#71817b;">
      Se você não reconhece esta solicitação, entre em contato com a
      administração da igreja.
    </p>
  </div>
</body>
</html>
HTML;

        $texto =
            "Olá, {$destinatarioNome}.\n\n"
            . "Sua solicitação de cadastro no SYN foi aprovada.\n"
            . "Sua conta já está ativa. Entre utilizando o mesmo e-mail "
            . "e a senha informados no cadastro.\n\n"
            . "Acessar: {$urlLogin}\n";

        $this->enviarMensagem(
            $destinatarioEmail,
            $destinatarioNome,
            $assunto,
            $html,
            $texto
        );
    }

    /**
     * Envia a informação de que a solicitação foi rejeitada.
     *
     * Quando houver motivo, ele é incluído no e-mail. A pessoa pode
     * corrigir os dados e fazer uma nova solicitação com o mesmo e-mail.
     */
    public function enviarCadastroRejeitado(
        string $destinatarioEmail,
        string $destinatarioNome,
        ?string $motivo
    ): void {
        $urlCadastro =
            rtrim(
                $this->env(
                    'APP_WEB_URL',
                    'http://localhost:5173'
                ),
                '/'
            )
            . '/cadastro';

        $assunto =
            'Atualização sobre seu cadastro — SYN';

        $nomeSeguro =
            htmlspecialchars(
                $destinatarioNome,
                ENT_QUOTES
                | ENT_SUBSTITUTE,
                'UTF-8'
            );

        $urlSegura =
            htmlspecialchars(
                $urlCadastro,
                ENT_QUOTES
                | ENT_SUBSTITUTE,
                'UTF-8'
            );

        $motivoHtml = '';

        if (
            $motivo !== null
            && trim($motivo) !== ''
        ) {
            $motivoSeguro =
                htmlspecialchars(
                    trim($motivo),
                    ENT_QUOTES
                    | ENT_SUBSTITUTE,
                    'UTF-8'
                );

            $motivoHtml = <<<HTML
    <div style="margin:18px 0;padding:14px 16px;border-left:4px solid #c98a8a;background:#fff8f8;border-radius:8px;">
      <strong style="font-size:13px;">
        Motivo informado:
      </strong>
      <div style="margin-top:6px;font-size:13px;line-height:1.6;">
        {$motivoSeguro}
      </div>
    </div>
HTML;
        }

        $html = <<<HTML
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
</head>
<body style="margin:0;background:#f4f7f6;font-family:Arial,sans-serif;color:#314a42;">
  <div style="max-width:560px;margin:32px auto;background:#ffffff;border:1px solid #dce5e1;border-radius:16px;padding:28px;">
    <div style="font-size:12px;font-weight:700;color:#668078;text-transform:uppercase;">
      SYN
    </div>

    <h1 style="font-size:22px;margin:8px 0 14px;">
      Solicitação de cadastro não aprovada
    </h1>

    <p style="font-size:14px;line-height:1.6;">
      Olá, {$nomeSeguro}.
    </p>

    <p style="font-size:14px;line-height:1.6;">
      Sua solicitação de cadastro no SYN foi analisada e não foi aprovada
      neste momento.
    </p>

{$motivoHtml}

    <p style="font-size:14px;line-height:1.6;">
      Se necessário, você pode corrigir seus dados e enviar uma nova
      solicitação utilizando o mesmo e-mail.
    </p>

    <p style="margin:24px 0;">
      <a
        href="{$urlSegura}"
        style="display:inline-block;background:#3f7565;color:#ffffff;text-decoration:none;border-radius:9px;padding:12px 18px;font-size:14px;font-weight:700;"
      >
        Fazer nova solicitação
      </a>
    </p>
  </div>
</body>
</html>
HTML;

        $texto =
            "Olá, {$destinatarioNome}.\n\n"
            . "Sua solicitação de cadastro no SYN foi analisada e não "
            . "foi aprovada neste momento.\n";

        if (
            $motivo !== null
            && trim($motivo) !== ''
        ) {
            $texto .=
                "\nMotivo informado: "
                . trim($motivo)
                . "\n";
        }

        $texto .=
            "\nVocê pode corrigir seus dados e enviar uma nova solicitação.\n"
            . "Acessar: {$urlCadastro}\n";

        $this->enviarMensagem(
            $destinatarioEmail,
            $destinatarioNome,
            $assunto,
            $html,
            $texto
        );
    }

    /**
     * Envio genérico usado por mensagens que não carregam token sensível.
     */
    private function enviarMensagem(
        string $destinatarioEmail,
        string $destinatarioNome,
        string $assunto,
        string $html,
        string $texto
    ): void {
        $transport =
            mb_strtolower(
                trim(
                    $this->env(
                        'MAIL_TRANSPORT',
                        'log'
                    )
                )
            );

        if ($transport === 'log') {
            if (!$this->ambienteDesenvolvimento()) {
                throw new RuntimeException(
                    'MAIL_TRANSPORT=log é permitido somente em development.'
                );
            }

            error_log(
                '[SYN][EMAIL DEV] '
                . $assunto
                . ' | para '
                . $destinatarioEmail
            );

            return;
        }

        if ($transport !== 'smtp') {
            throw new RuntimeException(
                'MAIL_TRANSPORT deve ser log ou smtp.'
            );
        }

        $host =
            trim(
                $this->env(
                    'MAIL_HOST'
                )
            );

        $fromAddress =
            trim(
                $this->env(
                    'MAIL_FROM_ADDRESS'
                )
            );

        if (
            $host === ''
            || $fromAddress === ''
        ) {
            throw new RuntimeException(
                'MAIL_HOST e MAIL_FROM_ADDRESS são obrigatórios para SMTP.'
            );
        }

        $mail =
            new PHPMailer(true);

        $mail->isSMTP();

        $mail->Host =
            $host;

        $mail->Port =
            (int) $this->env(
                'MAIL_PORT',
                '587'
            );

        $username =
            trim(
                $this->env(
                    'MAIL_USERNAME'
                )
            );

        $mail->SMTPAuth =
            $this->envBool(
                'MAIL_SMTP_AUTH',
                $username !== ''
            );

        if ($mail->SMTPAuth) {
            $mail->Username =
                $username;

            $mail->Password =
                $this->env(
                    'MAIL_PASSWORD'
                );
        }

        $encryption =
            mb_strtolower(
                trim(
                    $this->env(
                        'MAIL_ENCRYPTION',
                        'tls'
                    )
                )
            );

        if (
            $encryption === 'tls'
            || $encryption === 'starttls'
        ) {
            $mail->SMTPSecure =
                PHPMailer::ENCRYPTION_STARTTLS;
        } elseif (
            $encryption === 'ssl'
            || $encryption === 'smtps'
        ) {
            $mail->SMTPSecure =
                PHPMailer::ENCRYPTION_SMTPS;
        } elseif (
            $encryption === ''
            || $encryption === 'none'
        ) {
            $mail->SMTPSecure = '';
            $mail->SMTPAutoTLS = false;
        } else {
            throw new RuntimeException(
                'MAIL_ENCRYPTION deve ser tls, ssl ou none.'
            );
        }

        $mail->CharSet =
            'UTF-8';

        $mail->setFrom(
            $fromAddress,
            $this->env(
                'MAIL_FROM_NAME',
                'SYN'
            )
        );

        $mail->addAddress(
            $destinatarioEmail,
            $destinatarioNome
        );

        $mail->isHTML(true);

        $mail->Subject =
            $assunto;

        $mail->Body =
            $html;

        $mail->AltBody =
            $texto;

        $mail->send();
    }

    private function enviarPorSmtp(
        string $destinatarioEmail,
        string $destinatarioNome,
        string $urlRedefinicao,
        int $expiracaoMinutos
    ): void {
        $host =
            trim(
                $this->env(
                    'MAIL_HOST'
                )
            );

        $fromAddress =
            trim(
                $this->env(
                    'MAIL_FROM_ADDRESS'
                )
            );

        if (
            $host === ''
            || $fromAddress === ''
        ) {
            throw new RuntimeException(
                'MAIL_HOST e MAIL_FROM_ADDRESS são obrigatórios para SMTP.'
            );
        }

        $mail =
            new PHPMailer(true);

        $mail->isSMTP();

        $mail->Host =
            $host;

        $mail->Port =
            (int) $this->env(
                'MAIL_PORT',
                '587'
            );

        $username =
            trim(
                $this->env(
                    'MAIL_USERNAME'
                )
            );

        $password =
            $this->env(
                'MAIL_PASSWORD'
            );

        $mail->SMTPAuth =
            $this->envBool(
                'MAIL_SMTP_AUTH',
                $username !== ''
            );

        if ($mail->SMTPAuth) {
            $mail->Username =
                $username;

            $mail->Password =
                $password;
        }

        $encryption =
            mb_strtolower(
                trim(
                    $this->env(
                        'MAIL_ENCRYPTION',
                        'tls'
                    )
                )
            );

        if (
            $encryption === 'tls'
            || $encryption === 'starttls'
        ) {
            $mail->SMTPSecure =
                PHPMailer::ENCRYPTION_STARTTLS;
        } elseif (
            $encryption === 'ssl'
            || $encryption === 'smtps'
        ) {
            $mail->SMTPSecure =
                PHPMailer::ENCRYPTION_SMTPS;
        } elseif (
            $encryption === ''
            || $encryption === 'none'
        ) {
            $mail->SMTPSecure = '';
            $mail->SMTPAutoTLS = false;
        } else {
            throw new RuntimeException(
                'MAIL_ENCRYPTION deve ser tls, ssl ou none.'
            );
        }

        $mail->CharSet =
            'UTF-8';

        $mail->setFrom(
            $fromAddress,
            $this->env(
                'MAIL_FROM_NAME',
                'SYN'
            )
        );

        $mail->addAddress(
            $destinatarioEmail,
            $destinatarioNome
        );

        $mail->isHTML(true);

        $mail->Subject =
            'Redefinição de senha — SYN';

        $mail->Body =
            $this->htmlRecuperacao(
                $destinatarioNome,
                $urlRedefinicao,
                $expiracaoMinutos
            );

        $mail->AltBody =
            $this->textoRecuperacao(
                $destinatarioNome,
                $urlRedefinicao,
                $expiracaoMinutos
            );

        $mail->send();
    }

    private function registrarEmLog(
        string $destinatarioEmail,
        string $urlRedefinicao,
        int $expiracaoMinutos
    ): void {
        /**
         * Não permitimos MAIL_TRANSPORT=log em produção porque o token
         * acabaria registrado em logs de servidor.
         */
        if (!$this->ambienteDesenvolvimento()) {
            throw new RuntimeException(
                'MAIL_TRANSPORT=log é permitido somente em development.'
            );
        }

        error_log(
            '[SYN][EMAIL DEV] Recuperação para '
            . $destinatarioEmail
            . ' | expira em '
            . $expiracaoMinutos
            . ' min | '
            . $urlRedefinicao
        );
    }

    private function htmlRecuperacao(
        string $nome,
        string $url,
        int $expiracaoMinutos
    ): string {
        $nomeSeguro =
            htmlspecialchars(
                $nome,
                ENT_QUOTES
                | ENT_SUBSTITUTE,
                'UTF-8'
            );

        $urlSegura =
            htmlspecialchars(
                $url,
                ENT_QUOTES
                | ENT_SUBSTITUTE,
                'UTF-8'
            );

        return <<<HTML
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
</head>
<body style="margin:0;background:#f4f7f6;font-family:Arial,sans-serif;color:#314a42;">
  <div style="max-width:560px;margin:32px auto;background:#ffffff;border:1px solid #dce5e1;border-radius:16px;padding:28px;">
    <div style="font-size:12px;font-weight:700;color:#668078;text-transform:uppercase;">
      SYN
    </div>

    <h1 style="font-size:22px;margin:8px 0 14px;">
      Redefinição de senha
    </h1>

    <p style="font-size:14px;line-height:1.6;">
      Olá, {$nomeSeguro}.
    </p>

    <p style="font-size:14px;line-height:1.6;">
      Recebemos uma solicitação para redefinir a senha da sua conta no SYN.
      O link abaixo é válido por {$expiracaoMinutos} minutos e pode ser usado uma única vez.
    </p>

    <p style="margin:24px 0;">
      <a
        href="{$urlSegura}"
        style="display:inline-block;background:#3f7565;color:#ffffff;text-decoration:none;border-radius:9px;padding:12px 18px;font-size:14px;font-weight:700;"
      >
        Redefinir minha senha
      </a>
    </p>

    <p style="font-size:12px;line-height:1.6;color:#71817b;">
      Se você não solicitou esta alteração, ignore esta mensagem.
      Sua senha atual continuará válida.
    </p>

    <p style="font-size:11px;line-height:1.6;color:#8a9692;word-break:break-all;">
      Se o botão não funcionar, copie este endereço:<br>
      {$urlSegura}
    </p>
  </div>
</body>
</html>
HTML;
    }

    private function textoRecuperacao(
        string $nome,
        string $url,
        int $expiracaoMinutos
    ): string {
        return "Olá, {$nome}.\n\n"
            . "Recebemos uma solicitação para redefinir sua senha no SYN.\n"
            . "O link é válido por {$expiracaoMinutos} minutos e pode ser usado uma única vez.\n\n"
            . "{$url}\n\n"
            . "Se você não solicitou esta alteração, ignore esta mensagem.";
    }

    private function ambienteDesenvolvimento(): bool
    {
        return mb_strtolower(
            $this->env(
                'APP_ENV',
                'production'
            )
        ) === 'development';
    }

    private function env(
        string $chave,
        string $padrao = ''
    ): string {
        $valor =
            $_ENV[$chave]
            ?? $_SERVER[$chave]
            ?? getenv($chave);

        if (
            $valor === false
            || $valor === null
        ) {
            return $padrao;
        }

        return (string) $valor;
    }

    private function envBool(
        string $chave,
        bool $padrao
    ): bool {
        $valor =
            mb_strtolower(
                trim(
                    $this->env(
                        $chave,
                        $padrao
                            ? 'true'
                            : 'false'
                    )
                )
            );

        return in_array(
            $valor,
            [
                '1',
                'true',
                'yes',
                'on',
            ],
            true
        );
    }
}
