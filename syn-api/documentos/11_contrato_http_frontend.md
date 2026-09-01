# Contrato HTTP básico do SYN

## API local

`http://localhost:8282`

## Frontend local previsto

`http://localhost:5173`

## Autorização

As rotas protegidas recebem:

```http
Authorization: Bearer <JWT>
```

## Content-Type

Para JSON:

```http
Content-Type: application/json
Accept: application/json
```

Upload de imagem utiliza `multipart/form-data` e o navegador/Postman
deve definir o boundary automaticamente.

## Resposta de sucesso

Padrão:

```json
{
  "status": "ok",
  "dados": {}
}
```

Quando útil:

```json
{
  "status": "ok",
  "mensagem": "Operação realizada com sucesso.",
  "dados": {}
}
```

## Resposta de erro

```json
{
  "status": "erro",
  "mensagem": "Descrição do problema."
}
```

Para validação:

```json
{
  "status": "erro",
  "mensagem": "Dados inválidos.",
  "erros": {
    "campo": "Descrição do erro."
  }
}
```

## Status HTTP que o frontend deve tratar

- `200` — leitura/alteração concluída
- `201` — recurso criado
- `204` — resposta sem corpo, inclusive preflight OPTIONS
- `400` — requisição malformada
- `401` — não autenticado / token inválido ou expirado
- `403` — autenticado, mas sem permissão
- `404` — recurso não encontrado
- `409` — conflito de regra de negócio
- `422` — validação/regra de negócio inválida
- `500` — falha inesperada

## Regra importante

O frontend pode esconder botões com base em `/app-bootstrap`,
mas autorização nunca depende do React.

A API é a autoridade final.
