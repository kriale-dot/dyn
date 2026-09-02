# SYN Frontend — Etapa 50
## Recuperação e redefinição de senha

O fluxo público de recuperação de senha agora existe no frontend.

## Login

A tela de login ganha:

Esqueci minha senha

## Novas rotas React

/esqueci-senha

/redefinir-senha?token=TOKEN

As duas rotas são públicas.

## API utilizada

POST /auth/esqueci-senha

Body:

{
  "email": "usuario@exemplo.com"
}

POST /auth/redefinir-senha

Body:

{
  "token": "...",
  "nova_senha": "...",
  "confirmar_senha": "..."
}

## Proteção contra enumeração

A tela exibe exatamente a resposta pública genérica da API:

"Se o e-mail estiver cadastrado e ativo..."

Assim, o frontend também não revela se determinado e-mail existe.

## Ambiente development

O backend ainda não possui envio real de e-mail.

Quando APP_ENV=development, a API devolve:

desenvolvimento.token_teste
desenvolvimento.expira_em

A tela detecta esse campo e oferece:

Redefinir senha de teste

Esse botão leva diretamente para:

/redefinir-senha?token=...

Em produção esse bloco não aparece.

## Regras de redefinição

O frontend valida antes do POST:

- token hexadecimal com 64 caracteres;
- nova senha com pelo menos 8 caracteres;
- confirmação igual à nova senha.

A API valida tudo novamente.

## Segurança

O token:

- expira em 30 minutos;
- é de uso único;
- não é armazenado em texto puro no banco;
- uma nova solicitação invalida as anteriores.

Essas regras já pertencem ao backend da Etapa 24.

## Arquivos

Substitua SOMENTE:

src/api/api.js
src/App.jsx
src/pages/LoginPage.jsx

Adicione:

src/pages/EsqueciSenhaPage.jsx
src/pages/RedefinirSenhaPage.jsx
src/pages/AuthPagesEtapa50.css

NÃO substitua a pasta src inteira.

## Teste

1. Saia do SYN.
2. Abra /login.
3. Clique "Esqueci minha senha".
4. Informe admin@syn.local.
5. Em development, clique "Redefinir senha de teste".
6. Digite a nova senha duas vezes.
7. Redefina.
8. Faça login com a nova senha.
9. Tente reutilizar o mesmo token — deve falhar.
