# SYN Frontend — Etapa 47
## Home focada no que importa nesta semana

A Home foi reorganizada para deixar de parecer um painel administrativo.

O objetivo é responder rapidamente:

1. Qual é meu próximo compromisso?
2. Tenho algo para confirmar?
3. Como está distribuída esta semana?
4. O que acontece na igreja?
5. Quem faz aniversário?

## API

Nenhuma alteração na API.

A tela continua usando:

GET /dashboard

Para confirmação rápida, também usa o endpoint já existente:

PATCH /participacoes/{id}/confirmar

## Nova organização

### Próximo compromisso

É o elemento visual principal.

Mostra:

- dia;
- data;
- hora;
- programação;
- função;
- local;
- estado da confirmação.

Se estiver ESCALADO, aparece:

Confirmar participação

### Precisa de você

Mostra somente pendências de confirmação.

Isso reduz a necessidade de procurar a programação para descobrir se
existe alguma ação pendente.

### Visão da semana

A semana aparece como uma faixa de sete dias.

Não é uma grade de agenda.

Cada dia mostra:

- ponto pessoal quando existe compromisso do usuário;
- ponto geral quando existem programações da igreja;
- indicação visual do dia atual.

Essa visualização mantém o conceito do SYN como mapa temporal da semana.

### Acontece nesta semana

Mostra as próximas programações em uma lista compacta.

Cada item abre o detalhe da programação.

### Aniversários

Mostra os aniversariantes da semana sem calcular ou exibir idade.

Quando houver foto, ela é exibida.

## Arquivos

Substitua SOMENTE:

src/pages/HomePage.jsx

Adicione:

src/pages/HomePageEtapa47.css

Não substitua a pasta `src` inteira.

Não é necessário alterar:

App.jsx
api.js
styles.css
banco de dados
API

## Teste

Abra:

http://localhost:5173/

Teste especialmente com Maria, porque ela possui participação no
Culto Infantil e permite visualizar a Home sob a perspectiva de um
membro escalado.

Depois teste como Administrador.
