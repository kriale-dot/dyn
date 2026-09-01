# SYN Frontend — recuperação completa até a Etapa 42

Este pacote foi montado porque o diretório `src` perdeu arquivos-base
ao aplicar pacotes incrementais.

Ele contém o frontend completo acumulado até a Etapa 42, inclusive:

- src/main.jsx
- src/contexts/AuthContext.jsx
- src/components/AppShell.jsx
- src/components/ProtectedRoute.jsx
- Login
- Home
- Minha Semana
- Detalhe da Programação
- Programações
- Gestão de Programações
- Gestão de Escala
- Administração de Usuários
- Estrutura da Igreja
- api.js atual
- styles.css atual
- App.jsx atual

## Como recuperar

1. Pare o Vite com Ctrl+C.
2. Faça uma cópia de segurança da pasta `syn-web`.
3. Extraia este ZIP na raiz de `syn-web`, permitindo substituir arquivos.
4. Confirme que existem:

   src/main.jsx
   src/contexts/AuthContext.jsx
   src/components/AppShell.jsx
   src/components/ProtectedRoute.jsx

5. Rode:

   npm run dev

Não é necessário executar `npm install` novamente se `node_modules`
já existe e o package.json não foi alterado.

Este pacote é um snapshot completo. A partir daqui, novos pacotes
incrementais não devem ser usados para substituir a pasta `src`
inteira; devem apenas sobrescrever/adicionar os arquivos indicados.
