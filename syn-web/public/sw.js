const CACHE_NAME = 'syn-pwa-v112c'

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
        .open(CACHE_NAME)
        .then(
          (cache) =>
            cache.addAll(
              APP_SHELL,
            ),
        ),
    )

    self.skipWaiting()
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
        ),
    )

    self.clients.claim()
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
     * Nunca intercepta chamadas da API ou de outra origem.
     * Dados de escala/programação continuam vindo sempre
     * da API para evitar conteúdo antigo.
     */
    if (
      url.origin
      !== self.location.origin
    ) {
      return
    }

    /*
     * React Router:
     * tenta a rede e, se estiver offline, abre o shell.
     */
    if (
      request.mode
      === 'navigate'
    ) {
      event.respondWith(
        fetch(
          request,
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
     * cache-first.
     */
    event.respondWith(
      caches
        .match(
          request,
        )
        .then(
          (cached) =>
            cached
            || fetch(
              request,
            )
              .then(
                (response) => {
                  if (
                    response.ok
                  ) {
                    const copy =
                      response.clone()

                    caches
                      .open(
                        CACHE_NAME,
                      )
                      .then(
                        (cache) =>
                          cache.put(
                            request,
                            copy,
                          ),
                      )
                  }

                  return response
                },
              ),
        ),
    )
  },
)
