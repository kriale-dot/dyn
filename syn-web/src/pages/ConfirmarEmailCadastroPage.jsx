import {
  useEffect,
  useRef,
  useState,
} from 'react'

import {
  Link,
  useSearchParams,
} from 'react-router-dom'

import {
  confirmarEmailCadastro,
  reenviarConfirmacaoCadastro,
} from '../api/api'

import './ConfirmarEmailCadastroPage.css'

export default function ConfirmarEmailCadastroPage() {
  const [
    searchParams,
  ] =
    useSearchParams()

  const token =
    searchParams.get(
      'token',
    )
    || ''

  const executou =
    useRef(false)

  const [state, setState] =
    useState(
      token
        ? 'loading'
        : 'error',
    )

  const [message, setMessage] =
    useState(
      token
        ? 'Confirmando seu e-mail...'
        : 'O link de confirmação não possui um token válido.',
    )

  const [email, setEmail] =
    useState('')

  const [resending, setResending] =
    useState(false)

  const [resendMessage, setResendMessage] =
    useState('')

  useEffect(() => {
    if (
      !token
      || executou.current
    ) {
      return
    }

    executou.current = true

    async function confirmar() {
      try {
        const response =
          await confirmarEmailCadastro(
            token,
          )

        setState(
          'success',
        )

        setMessage(
          response
            ?.dados
            ?.mensagem
          || 'E-mail confirmado. Seu cadastro está aguardando aprovação.',
        )
      } catch (err) {
        setState(
          'error',
        )

        setMessage(
          err?.message
          || 'Não foi possível confirmar este e-mail.',
        )
      }
    }

    confirmar()
  }, [token])

  async function reenviar(
    event,
  ) {
    event.preventDefault()

    if (!email.trim()) {
      return
    }

    setResending(true)
    setResendMessage('')

    try {
      const response =
        await reenviarConfirmacaoCadastro(
          email.trim(),
        )

      setResendMessage(
        response
          ?.dados
          ?.mensagem
        || 'Se houver uma solicitação aguardando confirmação, um novo link será enviado.',
      )
    } catch (err) {
      setResendMessage(
        err?.message
        || 'Não foi possível solicitar um novo link.',
      )
    } finally {
      setResending(false)
    }
  }

  return (
    <main className="confirm84-page">
      <header className="confirm84-topbar">
        <Link to="/">
          ← Programação pública
        </Link>

        <strong>
          SYN
        </strong>

        <Link to="/login">
          Entrar
        </Link>
      </header>

      <section className="confirm84-card">
        <div
          className={
            `confirm84-icon ${state}`
          }
        >
          {state === 'loading'
            ? '…'
            : state === 'success'
              ? '✓'
              : '!'}
        </div>

        <span className="confirm84-eyebrow">
          Confirmação de cadastro
        </span>

        <h1>
          {state === 'loading'
            ? 'Verificando o link'
            : state === 'success'
              ? 'E-mail confirmado'
              : 'Não foi possível confirmar'}
        </h1>

        <p>
          {message}
        </p>

        {state === 'success' ? (
          <>
            <div className="confirm84-flow">
              <span className="done">
                ✓ Cadastro enviado
              </span>

              <span className="done">
                ✓ E-mail confirmado
              </span>

              <span>
                3. Aguardando aprovação
              </span>
            </div>

            <div className="confirm84-actions">
              <Link
                to="/login"
                className="primary"
              >
                Ir para o login
              </Link>

              <Link to="/">
                Ver programação pública
              </Link>
            </div>
          </>
        ) : state === 'error' ? (
          <form
            className="confirm84-resend"
            onSubmit={reenviar}
          >
            <strong>
              Precisa de um novo link?
            </strong>

            <span>
              Informe o mesmo e-mail utilizado no cadastro.
            </span>

            <input
              type="email"
              required
              placeholder="seuemail@exemplo.com"
              value={email}
              onChange={(event) =>
                setEmail(
                  event.target.value,
                )
              }
            />

            <button
              type="submit"
              disabled={resending}
            >
              {resending
                ? 'Enviando...'
                : 'Reenviar confirmação'}
            </button>

            {resendMessage && (
              <p className="confirm84-resend-message">
                {resendMessage}
              </p>
            )}
          </form>
        ) : null}
      </section>
    </main>
  )
}
