import {
  useState,
} from 'react'

import {
  Link,
  useNavigate,
} from 'react-router-dom'

import {
  solicitarRecuperacaoSenha,
} from '../api/api'

import './AuthPagesEtapa50.css'

export default function EsqueciSenhaPage() {
  const navigate =
    useNavigate()

  const [email, setEmail] =
    useState('')

  const [submitting, setSubmitting] =
    useState(false)

  const [error, setError] =
    useState('')

  const [resultado, setResultado] =
    useState(null)

  async function enviar(
    event,
  ) {
    event.preventDefault()
    setError('')
    setResultado(null)
    setSubmitting(true)

    try {
      const response =
        await solicitarRecuperacaoSenha(
          email
            .trim()
            .toLowerCase(),
        )

      setResultado(response)
    } catch (err) {
      setError(
        mensagemErro(
          err,
          'Não foi possível solicitar a recuperação.',
        ),
      )
    } finally {
      setSubmitting(false)
    }
  }

  const tokenTeste =
    resultado
      ?.desenvolvimento
      ?.token_teste
    ?? null

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
            Acesso
          </span>

          <h1>
            Recuperar senha
          </h1>

          <p>
            Informe o e-mail usado no SYN.
            Se ele estiver cadastrado e ativo,
            o processo de redefinição será iniciado.
          </p>
        </div>

        {!resultado ? (
          <form
            className="auth-public-form"
            onSubmit={enviar}
          >
            <label>
              <span>
                E-mail
              </span>

              <input
                type="email"
                autoComplete="email"
                required
                autoFocus
                value={email}
                onChange={(event) =>
                  setEmail(
                    event.target.value,
                  )
                }
                placeholder="voce@exemplo.com"
              />
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
                ? 'Enviando...'
                : 'Continuar'}
            </button>
          </form>
        ) : (
          <div className="auth-success-state">
            <span className="auth-success-icon">
              ✓
            </span>

            <h2>
              Solicitação recebida
            </h2>

            <p>
              {resultado?.mensagem
                || 'Se o e-mail estiver cadastrado e ativo, as instruções serão enviadas.'}
            </p>

            {tokenTeste && (
              <section className="auth-development-box">
                <span className="eyebrow">
                  Ambiente de desenvolvimento
                </span>

                <strong>
                  O backend ainda não envia e-mail.
                </strong>

                <p>
                  Para testar o fluxo agora,
                  use o token temporário retornado
                  pela própria API.
                </p>

                <div className="auth-token-preview">
                  {abreviarToken(
                    tokenTeste,
                  )}
                </div>

                <button
                  type="button"
                  className="button-primary"
                  onClick={() =>
                    navigate(
                      `/redefinir-senha?token=${encodeURIComponent(
                        tokenTeste,
                      )}`,
                    )
                  }
                >
                  Redefinir senha de teste
                </button>

                {resultado
                  ?.desenvolvimento
                  ?.expira_em && (
                  <small>
                    Expira em:
                    {' '}
                    {
                      resultado
                        .desenvolvimento
                        .expira_em
                    }
                  </small>
                )}
              </section>
            )}

            <Link
              to="/login"
              className="button-secondary auth-link-button"
            >
              Voltar para o login
            </Link>
          </div>
        )}
      </section>
    </main>
  )
}

function abreviarToken(
  token,
) {
  if (
    !token
    || token.length <= 22
  ) {
    return token
  }

  return `${token.slice(
    0,
    12,
  )}…${token.slice(-10)}`
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
