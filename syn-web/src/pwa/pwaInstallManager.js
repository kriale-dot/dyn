/*
 * SYN — Etapa 112C
 *
 * Gerenciador global da instalação PWA.
 *
 * Motivo:
 * o evento beforeinstallprompt pode ocorrer antes de a tela
 * LoginPage terminar de montar. Se escutarmos o evento apenas
 * dentro do componente, o botão "Instalar aplicativo" pode nunca
 * receber o prompt.
 */

let promptInstalacao = null

const listeners =
  new Set()

function estaStandalone() {
  if (
    typeof window
    === 'undefined'
  ) {
    return false
  }

  return Boolean(
    window.matchMedia?.(
      '(display-mode: standalone)',
    )?.matches
    || window.navigator
      .standalone === true,
  )
}

function ehIOS() {
  if (
    typeof navigator
    === 'undefined'
  ) {
    return false
  }

  const userAgent =
    navigator.userAgent
    || ''

  const tradicional =
    /iPad|iPhone|iPod/i
      .test(
        userAgent,
      )

  const ipadOS =
    navigator.platform
      === 'MacIntel'
    && navigator.maxTouchPoints
      > 1

  return (
    tradicional
    || ipadOS
  )
}

function estadoAtual() {
  return {
    podeInstalar:
      Boolean(
        promptInstalacao,
      ),
    instalado:
      estaStandalone(),
    ios:
      ehIOS(),
    serviceWorkerDisponivel:
      typeof navigator
        !== 'undefined'
      && 'serviceWorker'
        in navigator,
    serviceWorkerControlando:
      typeof navigator
        !== 'undefined'
      && Boolean(
        navigator
          .serviceWorker
          ?.controller,
      ),
  }
}

function notificar() {
  const estado =
    estadoAtual()

  for (
    const listener
    of listeners
  ) {
    listener(
      estado,
    )
  }

  if (
    typeof window
    !== 'undefined'
  ) {
    window.__SYN_PWA_STATE__ =
      estado
  }
}

/*
 * IMPORTANTE:
 * estes listeners são registrados no carregamento do módulo,
 * antes do React renderizar a tela de Login.
 */
if (
  typeof window
  !== 'undefined'
) {
  window.addEventListener(
    'beforeinstallprompt',
    (event) => {
      event.preventDefault()

      promptInstalacao =
        event

      console.info(
        'SYN PWA: beforeinstallprompt capturado.',
      )

      notificar()
    },
  )

  window.addEventListener(
    'appinstalled',
    () => {
      promptInstalacao =
        null

      console.info(
        'SYN PWA: aplicativo instalado.',
      )

      notificar()
    },
  )

  window
    .matchMedia?.(
      '(display-mode: standalone)',
    )
    ?.addEventListener?.(
      'change',
      notificar,
    )

  window.__SYN_PWA__ = {
    estado:
      () =>
        estadoAtual(),
    instalar:
      () =>
        solicitarInstalacaoPwa(),
  }

  notificar()
}

export function obterEstadoPwa() {
  return estadoAtual()
}

export function assinarEstadoPwa(
  listener,
) {
  listeners.add(
    listener,
  )

  listener(
    estadoAtual(),
  )

  return () => {
    listeners.delete(
      listener,
    )
  }
}

export async function solicitarInstalacaoPwa() {
  if (
    !promptInstalacao
  ) {
    return {
      outcome:
        'unavailable',
    }
  }

  const prompt =
    promptInstalacao

  await prompt.prompt()

  const escolha =
    await prompt.userChoice

  promptInstalacao =
    null

  notificar()

  return escolha
}
