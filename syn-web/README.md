# SYN — Etapa 112C — Correção final do PWA

Esta correção foi montada a partir do build `dist.zip` enviado.

## Problemas concretos corrigidos

1. O pacote anterior da Etapa 112 havia enfraquecido o manifesto que já existia:
   - retirou o `id`;
   - mudou o `start_url`;
   - reutilizou o mesmo ícone como `maskable`.

2. O `beforeinstallprompt` era ouvido apenas dentro de `LoginPage`.
   O evento pode ocorrer antes da tela terminar de montar.
   Agora ele é capturado globalmente assim que `main.jsx` importa
   `registerServiceWorker.js`.

3. O Service Worker e o manifesto podem ficar presos no cache do Chrome.
   Há um snippet Apache para evitar cache desses dois arquivos.

4. O ícone `maskable` agora é um arquivo separado com fundo opaco
   e área segura.

## Arquivos para substituir no projeto local

Substitua SOMENTE:

- `index.html`
- `public/manifest.webmanifest`
- `public/sw.js`
- `public/icons/syn-180.png`
- `public/icons/syn-192.png`
- `public/icons/syn-512.png`
- `public/icons/syn-maskable-512.png`
- `src/pwa/registerServiceWorker.js`
- `src/pages/LoginPage.jsx`

Adicione:

- `src/pwa/pwaInstallManager.js`

NÃO substitua a pasta `src` inteira.

## Build

Confirme `.env.production`:

```env
VITE_API_URL=https://api.ipfiladelfia.syn.social.br
VITE_APP_URL=https://ipfiladelfia.syn.social.br
```

Depois:

```powershell
npm run build
```

O `dist` deve conter:

```text
index.html
manifest.webmanifest
sw.js
icons/
  syn-180.png
  syn-192.png
  syn-512.png
  syn-maskable-512.png
assets/
```

## Apache

Ative headers:

```bash
sudo a2enmod headers
```

Abra:

```bash
sudo nano /etc/apache2/sites-available/ipfiladelfia.syn.social.br-le-ssl.conf
```

Cole o conteúdo de `apache/pwa-vhost-snippet.conf` DENTRO do `<VirtualHost *:443>`.

Depois:

```bash
sudo apache2ctl configtest
sudo systemctl reload apache2
```

## Limpeza do Chrome

Depois de publicar o novo dist:

1. Remova o SYN/atalho já existente do aparelho.
2. Chrome -> Configurações -> Configurações do site -> Todos os sites.
3. Abra `ipfiladelfia.syn.social.br`.
4. Limpe os dados do site.
5. Feche totalmente o Chrome.
6. Abra novamente o SYN.

## Diagnóstico incorporado

No console do Chrome, execute:

```js
window.__SYN_PWA__.estado()
```

Exemplo desejado:

```js
{
  podeInstalar: true,
  instalado: false,
  ios: false,
  serviceWorkerDisponivel: true,
  serviceWorkerControlando: true
}
```

Se `podeInstalar` for `true`, o botão "Instalar aplicativo" aparece
na tela de login.

Também pode testar no console:

```js
window.__SYN_PWA__.instalar()
```

Isso usa exatamente o mesmo prompt de instalação do botão.

## Testes HTTP

```bash
curl -I https://ipfiladelfia.syn.social.br/manifest.webmanifest
curl -I https://ipfiladelfia.syn.social.br/sw.js
curl -I https://ipfiladelfia.syn.social.br/icons/syn-192.png
curl -I https://ipfiladelfia.syn.social.br/icons/syn-512.png
```

O manifesto deve ser entregue como `application/manifest+json`.
