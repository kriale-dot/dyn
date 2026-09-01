# SYN API — Etapa 35
## CORS + contrato HTTP para o React

Esta etapa prepara a comunicação entre:

Frontend React/Vite:
http://localhost:5173

API Slim:
http://localhost:8282

## Não há alteração no banco

Nenhum SQL precisa ser executado.

## Novos arquivos

src/Middlewares/CorsMiddleware.php
src/Http/ApiResponse.php
routes/cors.php

documentos/10_configuracao_cors_env.txt
documentos/11_contrato_http_frontend.md

## Configuração do .env

Acrescente:

CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173

## O que o CORS permite

Métodos:

GET
POST
PUT
PATCH
DELETE
OPTIONS

Cabeçalhos:

Authorization
Content-Type
Accept

## Preflight

O navegador pode enviar automaticamente:

OPTIONS /alguma-rota

A API responde:

204 No Content

e informa os cabeçalhos CORS.

## Segurança

Não usamos:

Access-Control-Allow-Origin: *

O SYN trabalha com uma lista explícita de origens permitidas.

Também não habilitamos credenciais por cookie nesta versão,
pois a autenticação atual utiliza Bearer Token.

## Postman

Postman não depende de CORS.

Portanto os testes atuais continuam funcionando normalmente.

## ApiResponse

Foi adicionada a classe:

App\Http\ApiResponse

Ela centraliza o formato recomendado de resposta JSON para novos
Controllers.

Não refatoramos todos os Controllers antigos nesta etapa para evitar
introduzir regressões desnecessárias antes dos testes completos.

O contrato adotado continua:

Sucesso:

{
  "status": "ok",
  "dados": {}
}

Erro:

{
  "status": "erro",
  "mensagem": "...",
  "erros": {}
}

## Configuração futura do frontend

No projeto React/Vite:

.env

VITE_API_URL=http://localhost:8282

Uso:

const API_URL = import.meta.env.VITE_API_URL;

## Teste do preflight no PowerShell

Invoke-WebRequest `
  -Method OPTIONS `
  -Uri "http://localhost:8282/dashboard" `
  -Headers @{
      Origin = "http://localhost:5173"
      "Access-Control-Request-Method" = "GET"
      "Access-Control-Request-Headers" = "Authorization"
  }

Esperado:

StatusCode: 204

e cabeçalhos como:

Access-Control-Allow-Origin: http://localhost:5173
Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS

## Depois de copiar

composer dump-autoload

php -S localhost:8282 -t public
