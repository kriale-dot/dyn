# SYN API — Etapa 91
## Alertas de segurança por e-mail

Esta etapa conclui o bloco de proteção das credenciais enviando avisos
quando uma senha é realmente alterada.

Não há alteração no banco e não há alteração no frontend.

## 1. Alteração de senha em Meu Perfil

Depois de:

POST /auth/alterar-senha

e somente após a senha ter sido gravada com sucesso, o usuário recebe:

Assunto:

Sua senha foi alterada — SYN

A mensagem informa que:

- a senha foi alterada;
- todas as sessões anteriores foram encerradas;
- se o usuário não reconhece a alteração, deve comunicar a administração.

## 2. Recuperação de senha

Depois de concluir:

POST /auth/redefinir-senha

o usuário recebe:

Assunto:

Sua senha foi redefinida — SYN

A mensagem informa que:

- a recuperação foi concluída;
- todas as sessões anteriores foram revogadas;
- se a ação não foi reconhecida, deve comunicar a administração.

## Regra importante

O Gmail NÃO participa da transação que troca a senha.

Fluxo:

senha alterada no banco
    ↓
sessões revogadas
    ↓
evento de segurança gravado
    ↓
tenta enviar e-mail

Se o Gmail falhar:

- a nova senha continua válida;
- as sessões continuam revogadas;
- o evento de segurança continua gravado;
- a falha é registrada no log.

Nunca fazemos rollback de uma mudança de segurança porque o SMTP ficou
indisponível.

## Instalação

Substitua SOMENTE:

src/Services/EmailService.php
src/Services/SegurancaContaService.php
src/Services/RecuperacaoSenhaService.php
routes/auth.php

Não existe SQL nesta etapa.

NÃO substitua:

routes/routes.php
routes/recuperacao_senha.php
routes/cadastros.php
routes/perfil.php

## Compatibilidade

O EmailService parte da versão usada pela Etapa 87 e preserva:

- recuperação de senha;
- aprovação/rejeição de cadastro;
- confirmação de e-mail do cadastro;
- alteração segura do e-mail.

O routes/auth.php preserva:

- rate limit;
- alteração de senha;
- revogação de sessões;
- autenticação JWT.

## Teste A — alteração de senha

1. Faça login.
2. Meu Perfil → Alterar senha.
3. Troque a senha.
4. O SYN deve encerrar a sessão.
5. Confira o Gmail.

Esperado:

Sua senha foi alterada — SYN

6. Faça login com a nova senha.

## Teste B — recuperação

1. Use "Esqueci minha senha".
2. Abra o link recebido.
3. Redefina a senha.
4. Confira a caixa de entrada novamente.

Esperado:

Sua senha foi redefinida — SYN

O primeiro e-mail é o link de recuperação.
O segundo é o aviso de que a redefinição foi concluída.
