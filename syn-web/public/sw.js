/**
 * SYN — Etapa 105
 * Service Worker simples e conservador.
 *
 * Objetivos:
 * - permitir instalação como PWA;
 * - manter o shell visual disponível após a primeira carga;
 * - não armazenar respostas da API nem uploads;
 * - evitar comportamento agressivo de cache.
 */

const CACHE_NAME =
  'syn-pwa-v105'

const APP_SHELL = [
  '/',
  '/index.html',
  '/manifest.webmanifest',
  '/icons/syn-192.png',
  '/icons/syn-512.png',
  '/icons/syn-maskable-512.png',
]

self.addEventListener(
  'install',
  (event) => {
    event.waitUntil(
      caches
        .open(
          CACHE_NAME,
        )
        .then(
          (cache) =>
            cache.addAll(
              APP_SHELL,
            ),
        )
        .then(
          () =>
            self.skipWaiting(),
        ),
    )
  },
)

self.addEventListener(
  'activate',
  (event) => {
    event.waitUntil(
      caches
        .keys()
        .then(
          (keys) =>
            Promise.all(
              keys
                .filter(
                  (key) =>
                    key !== CACHE_NAME,
                )
                .map(
                  (key) =>
                    caches.delete(
                      key,
                    ),
                ),
            ),
        )
        .then(
          () =>
            self.clients.claim(),
        ),
    )
  },
)

self.addEventListener(
  'fetch',
  (event) => {
    const request =
      event.request

    if (
      request.method !== 'GET'
    ) {
      return
    }

    const url =
      new URL(
        request.url,
      )

    /*
     * Nunca cacheia serviços externos.
     */
    if (
      url.origin
      !== self.location.origin
    ) {
      return
    }

    /*
     * Nunca cacheia API nem arquivos enviados pelo usuário.
     */
    if (
      url.pathname
        .startsWith('/api/')
      || url.pathname
        .startsWith('/uploads/')
    ) {
      return
    }

    /*
     * Navegação SPA:
     * tenta a rede primeiro para sempre receber a versão mais nova.
     * Se estiver offline, usa o index.html já armazenado.
     */
    if (
      request.mode === 'navigate'
    ) {
      event.respondWith(
        fetch(
          request,
        )
          .then(
            (response) => {
              const clone =
                response.clone()

              caches
                .open(
                  CACHE_NAME,
                )
                .then(
                  (cache) =>
                    cache.put(
                      '/index.html',
                      clone,
                    ),
                )

              return response
            },
          )
          .catch(
            () =>
              caches.match(
                '/index.html',
              ),
          ),
      )

      return
    }

    /*
     * Assets estáticos:
     * responde do cache quando existir.
     * Caso contrário, busca na rede e guarda uma cópia válida.
     */
    event.respondWith(
      caches
        .match(
          request,
        )
        .then(
          (cached) => {
            if (cached) {
              return cached
            }

            return fetch(
              request,
            )
              .then(
                (response) => {
                  if (
                    !response
                    || response.status !== 200
                    || response.type === 'opaque'
                  ) {
                    return response
                  }

                  const clone =
                    response.clone()

                  caches
                    .open(
                      CACHE_NAME,
                    )
                    .then(
                      (cache) =>
                        cache.put(
                          request,
                          clone,
                        ),
                    )

                  return response
                },
              )
          },
        ),
    )
  },
)
