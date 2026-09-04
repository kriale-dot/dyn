# SYN Frontend — Etapa 90
## Atividade recente de segurança

Meu Perfil ganha uma nova seção:

Segurança da conta
Atividade recente

Ela mostra eventos como:

- Conta criada
- Login realizado
- Senha alterada
- Senha redefinida
- Todas as sessões foram encerradas
- E-mail de acesso alterado

## Arquivos

Substitua SOMENTE:

src/api/authSecurity.js
src/pages/PerfilPage.jsx

Adicione:

src/pages/PerfilPageEtapa90.css

O pacote também contém os CSS das Etapas 85, 86 e 87 apenas para manter
as dependências do PerfilPage. Se já estão corretos no seu projeto,
mantenha seus arquivos atuais.

NÃO substitua a pasta src inteira.
NÃO substitua src/api/api.js.
NÃO precisa alterar src/App.jsx.

## Funcionamento

Ao abrir Meu Perfil, o frontend consulta:

GET /meu-perfil/atividade-seguranca?limite=20

A tela mostra os 8 eventos mais recentes e permite atualizar a lista.

A API nunca envia senha, token ou credenciais no histórico.
