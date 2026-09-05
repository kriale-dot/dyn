/**
 * SYN — Etapa 105
 * Registro do Service Worker.
 *
 * IMPORTANTE:
 * Em desenvolvimento não registramos o SW para evitar cache antigo
 * durante as alterações do Vite.
 *
 * O PWA passa a funcionar no build de produção, servido por HTTPS
 * (ou localhost, que os navegadores tratam como origem segura).
 */
export function registrarServiceWorker() {
  if (
    !import.meta.env.PROD
    || !(
      'serviceWorker'
      in navigator
    )
  ) {
    return
  }

  window.addEventListener(
    'load',
    async () => {
      try {
        await navigator
          .serviceWorker
          .register(
            '/sw.js',
            {
              scope: '/',
            },
          )
      } catch (error) {
        console.error(
          'Não foi possível registrar o Service Worker do SYN.',
          error,
        )
      }
    },
  )
}
