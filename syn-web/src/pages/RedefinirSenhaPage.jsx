import {
  useMemo,
  useState,
} from 'react'

import {
  Link,
  useNavigate,
  useSearchParams,
} from 'react-router-dom'

import {
  redefinirSenha,
} from '../api/api'

import './AuthPagesEtapa50.css'

export default function RedefinirSenhaPage() {
  const navigate =
    useNavigate()

  const [
    searchParams,
  ] =
    useSearchParams()

  const tokenInicial =
    searchParams.get('token')
    || ''

  const [token, setToken] =
    useState(tokenInicial)

  const [novaSenha, setNovaSenha] =
    useState('')

  const [
    confirmarSenha,
    setConfirmarSenha,
  ] =
    useState('')

  const [submitting, setSubmitting] =
    useState(false)

  const [error, setError] =
    useState('')

  const [success, setSuccess] =
    useState(false)

  const validacao =
    useMemo(
      () => ({
        tokenValido:
          /^[a-fA-F0-9]{64}$/.test(
            token.trim(),
          ),

        tamanhoSenha:
          novaSenha.length >= 5,

        senhasIguais:
          novaSenha.length > 0
          && novaSenha
            === confirmarSenha,
      }),
      [
        token,
        novaSenha,
        confirmarSenha,
      ],
    )

  async function salvar(
    event,
  ) {
    event.preventDefault()
    setError('')

    if (!validacao.tokenValido) {
      setError(
        'O token de recuperação é inválido.',
      )
      return
    }

    if (!validacao.tamanhoSenha) {
      setError(
        'A nova senha deve possuir pelo menos 5 caracteres.',
      )
      return
    }

    if (!validacao.senhasIguais) {
      setError(
        'A confirmação da senha não confere.',
      )
      return
    }

    setSubmitting(true)

    try {
      await redefinirSenha(
        token.trim(),
        novaSenha,
        confirmarSenha,
      )

      setSuccess(true)
    } catch (err) {
      setError(
        mensagemErro(
          err,
          'Não foi possível redefinir a senha.',
        ),
      )
    } finally {
      setSubmitting(false)
    }
  }

  if (success) {
    return (
      <main className="auth-public-page">
        <section className="auth-public-card">
          <div className="auth-success-state">
            <span className="auth-success-icon">
              ✓
            </span>

            <h1>
              Senha redefinida
            </h1>

            <p>
              Sua nova senha já pode ser
              usada para entrar no SYN.
            </p>

            <button
              type="button"
              className="button-primary"
              onClick={() =>
                navigate(
                  '/login',
                  {
                    replace: true,
                  },
                )
              }
            >
              Entrar no SYN
            </button>
          </div>
        </section>
      </main>
    )
  }

  return (
    <main className="auth-public-page">
      <section className="auth-public-card">
        <Link
          to="/login"
          className="auth-back-link"
        >
          ← Voltar para entrar
        </Link>

        <div className="auth-public-heading">
          <span className="eyebrow">
            Segurança
          </span>

          <h1>
            Definir nova senha
          </h1>

          <p>
            Escolha uma senha com pelo menos
            5 caracteres. O token é de uso único
            e expira em 30 minutos.
          </p>
        </div>

        <form
          className="auth-public-form"
          onSubmit={salvar}
        >
          <label>
            <span>
              Token de recuperação
            </span>

            <input
              type="text"
              required
              value={token}
              onChange={(event) =>
                setToken(
                  event.target.value,
                )
              }
              autoComplete="off"
              className="auth-token-input"
            />

            <small
              className={
                validacao.tokenValido
                  ? 'auth-validation ok'
                  : 'auth-validation'
              }
            >
              {validacao.tokenValido
                ? 'Token com formato válido.'
                : 'O token deve possuir 64 caracteres hexadecimais.'}
            </small>
          </label>

          <label>
            <span>
              Nova senha
            </span>

            <input
              type="password"
              required
              minLength={5}
              autoComplete="new-password"
              value={novaSenha}
              onChange={(event) =>
                setNovaSenha(
                  event.target.value,
                )
              }
            />

            <small
              className={
                validacao.tamanhoSenha
                  ? 'auth-validation ok'
                  : 'auth-validation'
              }
            >
              Pelo menos 5 caracteres.
            </small>
          </label>

          <label>
            <span>
              Confirmar nova senha
            </span>

            <input
              type="password"
              required
              minLength={5}
              autoComplete="new-password"
              value={confirmarSenha}
              onChange={(event) =>
                setConfirmarSenha(
                  event.target.value,
                )
              }
            />

            {confirmarSenha && (
              <small
                className={
                  validacao.senhasIguais
                    ? 'auth-validation ok'
                    : 'auth-validation error'
                }
              >
                {validacao.senhasIguais
                  ? 'As senhas conferem.'
                  : 'As senhas ainda não conferem.'}
              </small>
            )}
          </label>

          {error && (
            <div className="error-message">
              {error}
            </div>
          )}

          <button
            type="submit"
            className="button-primary"
            disabled={submitting}
          >
            {submitting
              ? 'Redefinindo...'
              : 'Redefinir senha'}
          </button>
        </form>
      </section>
    </main>
  )
}

function mensagemErro(
  err,
  fallback,
) {
  const erros =
    err?.payload?.erros

  if (
    erros
    && typeof erros
      === 'object'
  ) {
    const primeira =
      Object.values(erros)
        .find(
          (valor) =>
            typeof valor
            === 'string',
        )

    if (primeira) {
      return primeira
    }
  }

  return (
    err?.message
    || fallback
  )
}
