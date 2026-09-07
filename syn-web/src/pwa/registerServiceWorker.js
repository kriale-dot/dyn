import './pwaInstallManager'

/**
 * SYN — Etapa 112C
 *
 * Registra o Service Worker somente no build de produção.
 * O import acima também captura o beforeinstallprompt ANTES
 * de o React montar a LoginPage.
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

  async function registrar() {
    try {
      const registration =
        await navigator
          .serviceWorker
          .register(
            '/sw.js',
            {
              scope: '/',
              updateViaCache:
                'none',
            },
          )

      await registration
        .update()

      console.info(
        'SYN PWA: Service Worker registrado.',
      )
    } catch (
      error
    ) {
      console.error(
        'SYN PWA: erro ao registrar Service Worker.',
        error,
      )
    }
  }

  if (
    document.readyState
    === 'complete'
  ) {
    registrar()
  } else {
    window.addEventListener(
      'load',
      registrar,
      {
        once: true,
      },
    )
  }
}

/*
 * Alias para manter compatibilidade.
 */
export const registerServiceWorker =
  registrarServiceWorker
