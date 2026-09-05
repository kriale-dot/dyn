import {
  useEffect,
  useMemo,
  useState,
} from 'react'

import QRCode
  from 'qrcode'

import {
  Link,
  Navigate,
  useNavigate,
} from 'react-router-dom'

import {
  getIgrejaPublica,
} from '../api/api'

import {
  useAuth,
} from '../contexts/AuthContext'

import './AuthPagesEtapa50.css'
import './LoginPageEtapa81.css'
import './LoginPageEtapa104.css'

/**
 * Endereço da API.
 *
 * Se VITE_API_URL não estiver definido, usa o mesmo host
 * pelo qual o frontend foi aberto e a porta 8282.
 */
const API_URL =
  String(
    import.meta.env.VITE_API_URL
    || '',
  ).trim()
  || (
    typeof window !== 'undefined'
      ? `${window.location.protocol}//${window.location.hostname}:8282`
      : 'http://localhost:8282'
  )

function getAppUrl() {
  const configuredUrl =
    import.meta.env
      .VITE_APP_URL
      ?.trim()

  if (configuredUrl) {
    const cleanUrl =
      configuredUrl
        .replace(/\/+$/, '')

    return cleanUrl
      .endsWith('/login')
      ? cleanUrl
      : `${cleanUrl}/login`
  }

  return `${window.location.origin}/login`
}

function resolverArquivoApi(
  caminho,
) {
  if (!caminho) {
    return null
  }

  if (
    /^https?:\/\//i.test(
      caminho,
    )
  ) {
    return caminho
  }

  return `${
    API_URL.replace(
      /\/+$/,
      '',
    )
  }/${
    String(caminho).replace(
      /^\/+/,
      '',
    )
  }`
}

function estaEmModoAplicativo() {
  if (
    typeof window === 'undefined'
  ) {
    return false
  }

  return (
    window.matchMedia?.(
      '(display-mode: standalone)',
    )?.matches
    || window.navigator
      .standalone === true
  )
}

function ehIOS() {
  if (
    typeof navigator === 'undefined'
  ) {
    return false
  }

  const ua =
    navigator.userAgent
    || ''

  const iOSTradicional =
    /iPad|iPhone|iPod/i
      .test(
        ua,
      )

  const iPadOS =
    navigator.platform
      === 'MacIntel'
    && navigator.maxTouchPoints
      > 1

  return (
    iOSTradicional
    || iPadOS
  )
}

export default function LoginPage() {
  const navigate =
    useNavigate()

  const {
    signIn,
    isAuthenticated,
    loading,
  } = useAuth()

  const [email, setEmail] =
    useState('')

  const [senha, setSenha] =
    useState('')

  const [submitting, setSubmitting] =
    useState(false)

  const [error, setError] =
    useState('')

  const [qrCode, setQrCode] =
    useState('')

  const [igreja, setIgreja] =
    useState(null)

  /*
   * Evento oferecido por navegadores Chromium quando o PWA
   * está apto a ser instalado.
   */
  const [
    installPrompt,
    setInstallPrompt,
  ] = useState(null)

  const [
    aplicativoInstalado,
    setAplicativoInstalado,
  ] = useState(
    () =>
      estaEmModoAplicativo(),
  )

  const [
    mensagemInstalacao,
    setMensagemInstalacao,
  ] = useState('')

  const appUrl =
    useMemo(
      () => getAppUrl(),
      [],
    )

  const isIOS =
    useMemo(
      () => ehIOS(),
      [],
    )

  useEffect(
    () => {
      let active = true

      QRCode
        .toDataURL(
          appUrl,
          {
            width: 220,
            margin: 1,
            errorCorrectionLevel: 'M',
          },
        )
        .then(
          (dataUrl) => {
            if (active) {
              setQrCode(
                dataUrl,
              )
            }
          },
        )
        .catch(
          () => {
            if (active) {
              setQrCode('')
            }
          },
        )

      return () => {
        active = false
      }
    },
    [
      appUrl,
    ],
  )

  useEffect(
    () => {
      let active = true

      async function carregarIgreja() {
        try {
          const response =
            await getIgrejaPublica()

          if (!active) {
            return
          }

          setIgreja(
            response?.dados
            ?? null,
          )
        } catch {
          if (active) {
            setIgreja(null)
          }
        }
      }

      carregarIgreja()

      return () => {
        active = false
      }
    },
    [],
  )

  useEffect(
    () => {
      function handleBeforeInstallPrompt(
        event,
      ) {
        /*
         * Impede o navegador de mostrar um prompt automático.
         * Guardamos o evento para o botão "Instalar aplicativo".
         */
        event.preventDefault()

        setInstallPrompt(
          event,
        )

        setMensagemInstalacao('')
      }

      function handleAppInstalled() {
        setAplicativoInstalado(
          true,
        )

        setInstallPrompt(
          null,
        )

        setMensagemInstalacao(
          'Aplicativo instalado.',
        )
      }

      window.addEventListener(
        'beforeinstallprompt',
        handleBeforeInstallPrompt,
      )

      window.addEventListener(
        'appinstalled',
        handleAppInstalled,
      )

      return () => {
        window.removeEventListener(
          'beforeinstallprompt',
          handleBeforeInstallPrompt,
        )

        window.removeEventListener(
          'appinstalled',
          handleAppInstalled,
        )
      }
    },
    [],
  )

  if (loading) {
    return (
      <main className="screen-center">
        <div className="loading-card">
          <strong>
            Carregando SYN...
          </strong>
        </div>
      </main>
    )
  }

  if (isAuthenticated) {
    return (
      <Navigate
        to="/"
        replace
      />
    )
  }

  async function handleSubmit(
    event,
  ) {
    event.preventDefault()
    setError('')
    setSubmitting(true)

    try {
      await signIn(
        email,
        senha,
      )

      navigate(
        '/',
        {
          replace: true,
        },
      )
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível entrar.',
      )
    } finally {
      setSubmitting(false)
    }
  }

  async function handleInstallApp() {
    if (
      !installPrompt
    ) {
      return
    }

    try {
      await installPrompt
        .prompt()

      const choice =
        await installPrompt
          .userChoice

      if (
        choice?.outcome
        === 'accepted'
      ) {
        setMensagemInstalacao(
          'Instalação iniciada.',
        )
      }

      setInstallPrompt(
        null,
      )
    } catch {
      setMensagemInstalacao(
        'Não foi possível iniciar a instalação.',
      )
    }
  }

  const logoIgreja =
    resolverArquivoApi(
      igreja?.logotipo,
    )

  const mostrarBotaoInstalar =
    Boolean(
      installPrompt,
    )
    && !aplicativoInstalado

  const mostrarOrientacaoIOS =
    isIOS
    && !aplicativoInstalado
    && !mostrarBotaoInstalar

  return (
    <main className="login-page login104-page">
      <section className="login-card login104-card">
        <div className="login104-access">

          <div className="login104-church">
            {logoIgreja ? (
              <div className="login104-church-logo">
                <img
                  src={logoIgreja}
                  alt={
                    igreja?.nome
                      ? `Logotipo de ${igreja.nome}`
                      : 'Logotipo da igreja'
                  }
                />
              </div>
            ) : (
              <div
                className="login104-church-logo login104-church-logo--fallback"
                aria-hidden="true"
              >
                S
              </div>
            )}

            <div className="login104-church-text">
              <span className="login104-system-name">
                SYN
              </span>

              <h1>
                {igreja?.nome
                  || 'Organização da Igreja'}
              </h1>

              <p>
                Programação, compromissos
                e escalas em um só lugar.
              </p>
            </div>
          </div>

          <form
            onSubmit={handleSubmit}
            className="login-form login104-form"
          >
            <label>
              E-mail

              <input
                type="email"
                value={email}
                onChange={(event) =>
                  setEmail(
                    event.target.value,
                  )
                }
                autoComplete="email"
                required
              />
            </label>

            <label>
              <span className="auth-password-label">
                <span>
                  Senha
                </span>

                <Link
                  to="/esqueci-senha"
                  className="auth-inline-link"
                >
                  Esqueci minha senha
                </Link>
              </span>

              <input
                type="password"
                value={senha}
                onChange={(event) =>
                  setSenha(
                    event.target.value,
                  )
                }
                autoComplete="current-password"
                required
              />
            </label>

            {error && (
              <div
                className="error-message"
                role="alert"
              >
                {error}
              </div>
            )}

            <button
              type="submit"
              className="button-primary"
              disabled={submitting}
            >
              {submitting
                ? 'Entrando...'
                : 'Entrar'}
            </button>

            <div className="auth81-register-link">
              <span>
                Ainda não possui cadastro?
              </span>

              <Link
                to="/cadastro"
              >
                Solicitar cadastro
              </Link>
            </div>
          </form>

          {(mostrarBotaoInstalar
            || mostrarOrientacaoIOS
            || aplicativoInstalado
            || mensagemInstalacao) && (
            <div
              className="login106-install"
              aria-live="polite"
            >
              {mostrarBotaoInstalar && (
                <button
                  type="button"
                  className="login106-install-button"
                  onClick={
                    handleInstallApp
                  }
                >
                  <span
                    className="login106-install-icon"
                    aria-hidden="true"
                  >
                    ↓
                  </span>

                  Instalar aplicativo
                </button>
              )}

              {mostrarOrientacaoIOS && (
                <p className="login106-ios-hint">
                  No iPhone/iPad:
                  use Compartilhar e depois
                  “Adicionar à Tela de Início”.
                </p>
              )}

              {aplicativoInstalado && (
                <p className="login106-installed">
                  SYN já está instalado
                  neste dispositivo.
                </p>
              )}

              {mensagemInstalacao && (
                <small>
                  {mensagemInstalacao}
                </small>
              )}
            </div>
          )}
        </div>

        <aside
          className="login104-mobile"
          aria-label="Acesso pelo celular"
        >
          <div className="login104-mobile-content">
            <span className="login104-kicker">
              Acesso pelo celular
            </span>

            <h2>
              Abra o SYN no celular
            </h2>

            <p>
              Aponte a câmera para o
              QR Code e abra o aplicativo.
            </p>

            <div className="login104-qr">
              {qrCode ? (
                <img
                  src={qrCode}
                  alt="QR Code para abrir o SYN no celular"
                />
              ) : (
                <span>
                  Gerando QR Code...
                </span>
              )}
            </div>

            <small>
              O QR Code abre diretamente
              a tela de login.
            </small>

            {mostrarBotaoInstalar && (
              <p className="login106-qr-note">
                Depois de abrir no celular,
                você poderá instalar o SYN
                na tela inicial.
              </p>
            )}
          </div>
        </aside>
      </section>
    </main>
  )
}
