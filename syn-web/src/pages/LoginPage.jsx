import {
  useState,
} from 'react'

import {
  Navigate,
  useNavigate,
} from 'react-router-dom'

import {
  useAuth,
} from '../contexts/AuthContext'

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
          <strong>Carregando SYN...</strong>
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

  async function handleSubmit(event) {
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
            Senha

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
        </form>
      </section>
    </main>
  )
}
