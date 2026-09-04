import {
  useState,
} from 'react'

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
    <main className="login-page">
      <section className="login-card">
        <div className="login-brand">
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
          className="login-form"
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
      </section>
    </main>
  )
}
