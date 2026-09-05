# SYN — Etapa 92 — Checkpoint Consolidado

Este ZIP é um ponto de consolidação do projeto após a Etapa 91.

Ele reúne em um único pacote:

- `syn-api/` — backend Slim 4/PHP;
- `syn-web/` — frontend React/Vite;
- `documentos/` — banco inicial, migrations em ordem consolidada e requisitos.

## Por que este checkpoint existe

Até aqui o desenvolvimento foi feito por patches incrementais. A partir deste
checkpoint, não é mais necessário reconstruir o sistema aplicando dezenas de ZIPs
anteriores em sequência.

Use este pacote como nova referência do projeto.

## Importante

O ZIP NÃO contém:

- `.env` real;
- senha do Gmail;
- `JWT_SECRET` real;
- pasta `vendor`;
- `node_modules`.

Isso é intencional.

## API

Entre em:

`SYN_Etapa92_Checkpoint_Consolidado/syn-api`

Copie:

`.env.example` → `.env`

Depois:

```bash
composer install
composer dump-autoload
php -S localhost:8282 -t public
```

## Frontend

Entre em:

`SYN_Etapa92_Checkpoint_Consolidado/syn-web`

Copie:

`.env.example` → `.env`

Depois:

```bash
npm install
npm run dev
```

Frontend:

`http://localhost:5173`

API:

`http://localhost:8282`

## Banco — instalação do zero

Execute os arquivos de `documentos/` nesta ordem:

1. `01_create_database_syn.sql`
2. `02_dados_iniciais_syn.sql`
3. `03_permissoes_organizador.sql`
4. `04_permissoes_especiais.sql`
5. `05_recuperacao_senha.sql`
6. `06_notificacoes.sql`
7. `07_eventos_programacao.sql`
8. `08_auditoria.sql`
9. `09_modo_publico.sql`
10. `10_cadastro_com_aprovacao.sql`
11. `11_rate_limit.sql`
12. `12_confirmacao_email_cadastro.sql`
13. `13_revogacao_sessoes.sql`
14. `14_alteracao_segura_email.sql`
15. `15_expiracao_cadastros_email.sql`
16. `16_eventos_seguranca_conta.sql`

`99_testes_banco_syn.sql` é apenas para testes.

Se o seu banco atual já recebeu essas migrations durante o desenvolvimento,
NÃO execute tudo novamente. O diretório consolidado é principalmente para uma
instalação nova e para termos uma referência organizada.

## Funcionalidades reunidas

Este checkpoint inclui, entre outras:

- autenticação JWT;
- papéis Administrador, Organizador e Membro;
- permissões especiais;
- usuários, funções e departamentos;
- tipos e elegibilidade;
- locais;
- programações e conflitos;
- escalas e confirmações;
- Minha Semana;
- programas recorrentes;
- mapa da semana;
- modo público;
- cadastro público com aprovação;
- confirmação de e-mail;
- rate limit;
- recuperação por Gmail;
- alteração de senha;
- alteração segura de e-mail;
- revogação de sessões;
- notificações internas;
- auditoria;
- histórico de segurança;
- alertas de segurança por e-mail.

## Regra para as próximas etapas

A partir da Etapa 92, novos patches devem ser produzidos contra este checkpoint,
não contra pacotes antigos.


## Dependência do QR Code

O frontend consolidado já declara a dependência `qrcode`, usada na página
pública de divulgação. `npm install` instalará essa biblioteca automaticamente.
