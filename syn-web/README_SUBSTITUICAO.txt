SYN - NOVA TELA DE LOGIN
==========================

Este ZIP contém apenas os arquivos para substituição da tela de login:

src/pages/Login/Login.jsx
src/pages/Login/Login.css

O que foi mantido
-----------------
- useAuth()
- login(email, senha)
- estados de email, senha, erro e envio
- mensagem de erro da autenticação
- botão "Entrando..."

O que foi alterado
------------------
- card mais largo no desktop
- formulário com largura confortável
- QR Code como recurso secundário
- menos padding
- menos arredondamento
- "Esqueci minha senha" sem quebra feia
- QR Code oculto no celular
- interface técnica sobre VITE_APP_URL removida da tela
- layout responsivo para desktop, tablet e celular

QR CODE NA REDE LOCAL
---------------------
O QR usa primeiro a variável VITE_APP_URL.

Exemplo de .env:

VITE_APP_URL=http://192.168.1.50:5173

Troque 192.168.1.50 pelo IPv4 do computador.

Se VITE_APP_URL não existir, o sistema usa window.location.origin.

Observação:
O QR Code é gerado como imagem por api.qrserver.com, portanto não é
necessário instalar nenhum pacote npm adicional.

SUBSTITUIÇÃO
------------
1. Faça um backup dos arquivos atuais, se desejar.
2. Extraia este ZIP na raiz do frontend SYN.
3. Confirme a substituição dos dois arquivos.
4. Reinicie o Vite se necessário:

npm run dev

Se estiver acessando de outro aparelho da rede, inicie o Vite aceitando
conexões externas, por exemplo:

npm run dev -- --host 0.0.0.0
