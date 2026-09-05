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
  useAuth,
} from '../contexts/AuthContext'

import './AuthPagesEtapa50.css'
import './LoginPageEtapa81.css'
import './LoginPageEtapa104.css'

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

  const appUrl =
    useMemo(
      () => getAppUrl(),
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

  return (
    <main className="login-page login104-page">
      <section className="login-card login104-card">
        <div className="login104-access">
          <div className="login-brand login104-brand">
            <div className="brand-mark large">
              S
            </div>

            <div>
              <h1>SYN</h1>

              <p>
                Organização, programação
                e escalas da igreja
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
          </div>
        </aside>
      </section>
    </main>
  )
}
