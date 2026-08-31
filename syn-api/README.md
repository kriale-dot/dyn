# SYN API — Etapa 18 — Autenticação e Autorização

Nesta etapa implementamos:

- login com e-mail e senha;
- JWT Bearer;
- GET /auth/me;
- bloqueio de usuário INATIVO;
- autorização básica por papel;
- Minha Semana baseada no usuário autenticado;
- confirmação/recusa/indisponibilidade somente da própria participação;
- proteção das rotas administrativas.

---

# 1. Instalar a biblioteca JWT

Dentro de syn-api:

composer require firebase/php-jwt:^6.11

Depois:

composer dump-autoload

Não basta apenas copiar os arquivos deste ZIP.
A biblioteca precisa existir em vendor/.

---

# 2. Configurar .env

Acrescente:

JWT_SECRET=SEU_SEGREDO_AQUI
JWT_TTL_SECONDS=3600

Para gerar um segredo seguro no PowerShell:

php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"

Copie o resultado para JWT_SECRET.

Exemplo:

JWT_SECRET=9b...cole_o_valor_completo...
JWT_TTL_SECONDS=3600

Não versione o .env no Git.

---

# 3. Reiniciar a API

php -S localhost:8282 -t public

---

# 4. Usuários de desenvolvimento

O script 02_dados_iniciais_syn.sql criou:

Administrador:
admin@syn.local

Organizador:
organizador@syn.local

Membro:
maria@syn.local

Senha de desenvolvimento para os três:

123456

Essa senha existe apenas para testes locais.

---

# 5. Teste de login

POST {{base_url}}/auth/login

Body JSON:

{
  "email": "admin@syn.local",
  "senha": "123456"
}

Esperado:

HTTP 200

{
  "status": "ok",
  "dados": {
    "token": "...",
    "token_tipo": "Bearer",
    "expira_em_segundos": 3600,
    "usuario": {
      "papel": {
        "codigo": "ADMINISTRADOR"
      }
    }
  }
}

Copie apenas o valor de "token".

---

# 6. Configurar Bearer Token no Postman

Crie uma variável de ambiente:

token

com o JWT retornado.

Depois nas requisições protegidas use:

Authorization
Type: Bearer Token

Token:

{{token}}

Ou configure o Bearer Token no nível da Collection.

---

# 7. Testar /auth/me

GET {{base_url}}/auth/me

Header:

Authorization: Bearer {{token}}

Esperado:

HTTP 200

com o usuário autenticado.

---

# 8. Testar sem token

GET {{base_url}}/auth/me

sem Authorization.

Esperado:

HTTP 401

"Token de autenticação não informado."

---

# 9. Testar token inválido

Authorization:

Bearer abc123

Esperado:

HTTP 401

"Token inválido ou expirado."

---

# 10. Minha Semana agora não recebe ID

ANTES:

GET /usuarios/3/minha-semana

AGORA:

GET /minha-semana

A API usa o ID presente no token autenticado.

Para testar o cenário inicial:

Faça login como Maria:

POST /auth/login

{
  "email": "maria@syn.local",
  "senha": "123456"
}

Salve o token da Maria.

Depois:

GET {{base_url}}/minha-semana?data_referencia=2026-09-06

Authorization:
Bearer TOKEN_DA_MARIA

A API mostrará apenas os compromissos da Maria autenticada.

---

# 11. Regra de respostas à escala

Maria só pode responder a uma participação cujo:

participacoes.usuario_id

seja o mesmo usuário do token.

Exemplo correto:

Maria autenticada
PATCH /participacoes/1/confirmar

se participação 1 pertence a Maria.

Se Maria tentar confirmar uma participação de João:

HTTP 422

"Você não pode responder à participação de outro usuário."

---

# 12. Papéis implementados

ADMINISTRADOR

Pode:
- gerenciar igreja;
- usuários;
- departamentos;
- funções;
- tipos de programação;
- locais;
- programações;
- escalas.

ORGANIZADOR

Nesta etapa pode:
- consultar usuários/funções/departamentos/locais;
- criar/editar/cancelar/realizar programações;
- montar e acompanhar escalas.

MEMBRO

Pode:
- consultar programação geral autenticada;
- ver Minha Semana;
- confirmar;
- recusar;
- informar indisponibilidade nas próprias escalas.

---

# 13. Testar autorização por papel

## Como Maria

Login:

maria@syn.local
123456

Tente:

POST {{base_url}}/usuarios

Esperado:

HTTP 403

"Você não possui permissão para executar esta operação."

---

# 14. Como Organizador

Login:

organizador@syn.local
123456

Tente:

POST {{base_url}}/programacoes

Com Body válido.

A rota passa pelo controle de papel ADMINISTRADOR ou ORGANIZADOR.

Mas tente:

POST {{base_url}}/usuarios

Esperado:

HTTP 403

porque cadastrar usuário é responsabilidade do Administrador.

---

# 15. Como Administrador

Login:

admin@syn.local
123456

As operações administrativas gerais estarão liberadas.

---

# 16. Usuário inativo perde acesso imediatamente

Faça login como um usuário ativo e guarde seu token.

Depois, como Administrador:

PATCH /usuarios/{id}/desativar

Agora tente usar o token antigo do usuário desativado:

GET /auth/me

Esperado:

HTTP 401

Mesmo que o JWT ainda não tenha expirado.

Isso acontece porque o AuthMiddleware recarrega o usuário do banco
em cada requisição e confere status = ATIVO.

---

# 17. Por que não colocamos o papel como autoridade no JWT?

O token guarda essencialmente o ID do usuário.

A cada requisição:

JWT
 ↓
usuario_id
 ↓
banco de dados
 ↓
status atual
papel atual

Portanto, se o Administrador mudar:

Organizador -> Membro

o token antigo passa a obedecer imediatamente o novo papel.

---

# 18. Códigos HTTP usados

200
Operação autenticada válida.

401 Unauthorized
Não está autenticado / token inválido / usuário inativo.

403 Forbidden
Está autenticado, mas não possui papel permitido.

422 Unprocessable Entity
A operação viola uma regra de negócio.

---

# 19. Sobre o Organizador

O documento diz que o Organizador administra programações e escalas
"conforme as permissões definidas para seu acesso".

O documento atual não detalha o modelo físico dessas permissões
granulares por área/atividade.

Por isso esta etapa implementa apenas a barreira base:

ADMINISTRADOR
ORGANIZADOR
MEMBRO

Não inventamos ainda um modelo adicional de permissões do Organizador.

Essa granularidade deve ser uma etapa própria.

---

# 20. JWT é uma decisão desta implementação

O requisito exige autenticação e sessão segura.

Para facilitar o desenvolvimento da API e os testes no Postman,
esta etapa usa:

Authorization: Bearer <JWT>

O token tem expiração configurável e não contém senha.

Quando formos integrar o frontend React, podemos decidir entre:

- continuar com access token de curta duração + refresh;
- ou migrar para cookie seguro HttpOnly;

de acordo com a estratégia final de implantação.

---

# Próximos testes recomendados

1. Login Administrador.
2. GET /auth/me.
3. GET /usuarios com token Admin.
4. Login Maria.
5. GET /minha-semana com token Maria.
6. Maria tenta POST /usuarios -> 403.
7. Maria confirma a própria participação.
8. Maria tenta confirmar participação de outro usuário -> 422.
9. Login Organizador.
10. Organizador cria programação.
11. Organizador tenta criar usuário -> 403.
12. Desativar usuário e testar o token antigo -> 401.
