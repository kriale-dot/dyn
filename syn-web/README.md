# SYN Frontend — Etapa 48B
## Correção de Tipo e Local na tela Programações

Na tela:

/programacoes

o frontend mostrava:

Programação
Local não informado

mesmo quando o banco e a API possuíam o tipo e o local.

## Causa

A API devolve os snapshots históricos em objetos aninhados:

```json
{
  "tipo_programacao": {
    "id": 1,
    "nome_historico": "Culto Infantil"
  },
  "local": {
    "id": 1,
    "nome_historico": "Sala Infantil"
  },
  "organizador": {
    "id": 2,
    "nome_historico": "Organizador SYN"
  }
}
```

Mas a tela antiga procurava principalmente:

```text
tipo_programacao_nome_historico
local_nome_historico
organizador_nome_historico
```

Por isso o fallback acabava exibindo:

Programação
Local não informado

## Correção

A normalização agora procura primeiro:

tipo_programacao.nome_historico
local.nome_historico
organizador.nome_historico

e mantém os formatos anteriores como fallback.

## Arquivo

Substitua SOMENTE:

src/pages/ProgramacoesPage.jsx

Não substitua a pasta src inteira.

Não há alteração na API, banco ou CSS.

## Resultado esperado

Por exemplo:

Culto Infantil
10:00 — 11:30
Sala Infantil

em vez de:

Programação
10:00 — 11:30
Local não informado
