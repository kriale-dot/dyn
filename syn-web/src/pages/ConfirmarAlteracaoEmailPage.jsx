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
  confirmarAlteracaoEmail,
} from '../api/authSecurity'

import './ConfirmarAlteracaoEmailPage.css'

export default function ConfirmarAlteracaoEmailPage() {
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
        ? 'Confirmando o novo endereço...'
        : 'O link não contém um token válido.',
    )

  const [novoEmail, setNovoEmail] =
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
          await confirmarAlteracaoEmail(
            token,
          )

        setState('success')

        setMessage(
          response
            ?.dados
            ?.mensagem
          || 'E-mail alterado com sucesso.',
        )

        setNovoEmail(
          response
            ?.dados
            ?.novo_email
          || '',
        )
      } catch (err) {
        setState('error')

        setMessage(
          err?.message
          || 'Não foi possível confirmar esta alteração.',
        )
      }
    }

    confirmar()
  }, [token])

  return (
    <main className="confirm87-page">
      <section className="confirm87-card">
        <div
          className={
            `confirm87-icon ${state}`
          }
        >
          {state === 'loading'
            ? '…'
            : state === 'success'
              ? '✓'
              : '!'}
        </div>

        <span className="confirm87-eyebrow">
          Segurança da conta
        </span>

        <h1>
          {state === 'loading'
            ? 'Confirmando novo e-mail'
            : state === 'success'
              ? 'E-mail alterado'
              : 'Não foi possível alterar'}
        </h1>

        <p>
          {message}
        </p>

        {state === 'success' && (
          <>
            {novoEmail && (
              <div className="confirm87-email">
                <span>
                  Novo e-mail de acesso
                </span>

                <strong>
                  {novoEmail}
                </strong>
              </div>
            )}

            <div className="confirm87-session-note">
              Todas as sessões anteriores foram encerradas.
              Faça login novamente usando o novo endereço.
            </div>
          </>
        )}

        <div className="confirm87-actions">
          <Link
            to="/login"
            className="primary"
          >
            Ir para o login
          </Link>

          <Link to="/">
            Programação pública
          </Link>
        </div>
      </section>
    </main>
  )
}
