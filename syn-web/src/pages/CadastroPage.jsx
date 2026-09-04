import {
  useState,
} from 'react'

import {
  Link,
  Navigate,
} from 'react-router-dom'

import {
  reenviarConfirmacaoCadastro,
  solicitarCadastroPublico,
} from '../api/api'

import {
  useAuth,
} from '../contexts/AuthContext'

import './CadastroPage.css'

const FORM_INICIAL = {
  nome: '',
  email: '',
  telefone: '',
  data_nascimento: '',
  senha: '',
  confirmar_senha: '',
}

export default function CadastroPage() {
  const {
    isAuthenticated,
    loading,
  } = useAuth()

  const [form, setForm] =
    useState(
      FORM_INICIAL,
    )

  const [submitting, setSubmitting] =
    useState(false)

  const [error, setError] =
    useState('')

  const [success, setSuccess] =
    useState(null)

  const [successEmail, setSuccessEmail] =
    useState('')

  const [resending, setResending] =
    useState(false)

  const [resendMessage, setResendMessage] =
    useState('')

  if (loading) {
    return (
      <main className="cadastro81-page">
        <div className="cadastro81-loading">
          Abrindo cadastro...
        </div>
      </main>
    )
  }

  if (isAuthenticated) {
    return (
      <Navigate
        to="/inicio"
        replace
      />
    )
  }

  function alterar(
    campo,
    valor,
  ) {
    setForm(
      (atual) => ({
        ...atual,
        [campo]:
          valor,
      }),
    )
  }

  async function enviar(
    event,
  ) {
    event.preventDefault()
    setError('')
    setSuccess(null)

    if (
      form.senha.length < 5
    ) {
      setError(
        'A senha deve possuir pelo menos 5 caracteres.',
      )
      return
    }

    if (
      form.senha
      !== form.confirmar_senha
    ) {
      setError(
        'A confirmação da senha não corresponde.',
      )
      return
    }

    setSubmitting(true)

    try {
      const response =
        await solicitarCadastroPublico({
          nome:
            form.nome.trim(),
          email:
            form.email.trim(),
          telefone:
            form.telefone.trim()
            || null,
          data_nascimento:
            form.data_nascimento
            || null,
          senha:
            form.senha,
        })

      const dados =
        response?.dados
        ?? {
          status:
            'AGUARDANDO_EMAIL',
          mensagem:
            'Solicitação enviada.',
        }

      setSuccess(
        dados,
      )

      setSuccessEmail(
        dados?.email
        || form.email.trim(),
      )

      setResendMessage('')

      setForm(
        FORM_INICIAL,
      )
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível enviar a solicitação.',
      )
    } finally {
      setSubmitting(false)
    }
  }

  async function reenviar() {
    if (!successEmail) {
      return
    }

    setResending(true)
    setResendMessage('')

    try {
      const response =
        await reenviarConfirmacaoCadastro(
          successEmail,
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
        || 'Não foi possível solicitar o reenvio agora.',
      )
    } finally {
      setResending(false)
    }
  }

  return (
    <main className="cadastro81-page">
      <header className="cadastro81-topbar">
        <Link
          to="/"
        >
          ← Programação pública
        </Link>

        <strong>
          SYN
        </strong>

        <Link
          to="/login"
        >
          Entrar
        </Link>
      </header>

      <section className="cadastro81-layout">
        <div className="cadastro81-intro">
          <span className="cadastro81-eyebrow">
            Área de membros
          </span>

          <h1>
            Solicite seu cadastro
            no SYN
          </h1>

          <p>
            Preencha seus dados. Sua solicitação ficará
            aguardando análise antes que o acesso seja
            liberado.
          </p>

          <div className="cadastro81-flow">
            <div>
              <strong>
                1
              </strong>

              <span>
                Você envia a solicitação
              </span>
            </div>

            <div>
              <strong>
                2
              </strong>

              <span>
                Você confirma seu e-mail
              </span>
            </div>

            <div>
              <strong>
                3
              </strong>

              <span>
                A igreja analisa e libera o acesso
              </span>
            </div>
          </div>

          <div className="cadastro81-note">
            <strong>
              Seu acesso não é liberado automaticamente.
            </strong>

            <span>
              Primeiro você confirma o e-mail. Depois,
              Administradores ou Organizadores autorizados
              analisam a solicitação.
            </span>
          </div>
        </div>

        <section className="cadastro81-card">
          {success ? (
            <div className="cadastro81-success">
              <div className="cadastro81-success-icon">
                ✓
              </div>

              <span>
                Primeira etapa concluída
              </span>

              <h2>
                Confirme seu e-mail
              </h2>

              <p>
                {success.mensagem
                  || 'Enviamos um link de confirmação para o seu e-mail.'}
              </p>

              <p>
                Sua solicitação só seguirá para aprovação
                depois que você clicar no link recebido.
              </p>

              {successEmail && (
                <div className="cadastro84-email-box">
                  <span>
                    E-mail informado
                  </span>

                  <strong>
                    {successEmail}
                  </strong>
                </div>
              )}

              {resendMessage && (
                <div className="cadastro84-resend-message">
                  {resendMessage}
                </div>
              )}

              <div className="cadastro81-success-actions">
                <button
                  type="button"
                  className="cadastro84-resend-button"
                  disabled={
                    resending
                    || !successEmail
                  }
                  onClick={reenviar}
                >
                  {resending
                    ? 'Reenviando...'
                    : 'Reenviar confirmação'}
                </button>

                <Link
                  to="/login"
                >
                  Ir para o login
                </Link>

                <Link
                  to="/"
                >
                  Ver programação pública
                </Link>
              </div>
            </div>
          ) : (
            <>
              <header>
                <span>
                  Novo cadastro
                </span>

                <h2>
                  Seus dados
                </h2>

                <p>
                  Todos os novos cadastros precisam confirmar
                  o endereço de e-mail antes da análise.
                </p>
              </header>

              <form
                onSubmit={enviar}
                className="cadastro81-form"
              >
                <label className="full">
                  <span>
                    Nome completo
                  </span>

                  <input
                    type="text"
                    required
                    maxLength={150}
                    autoComplete="name"
                    value={form.nome}
                    onChange={(event) =>
                      alterar(
                        'nome',
                        event.target.value,
                      )
                    }
                  />
                </label>

                <label className="full">
                  <span>
                    E-mail
                  </span>

                  <input
                    type="email"
                    required
                    maxLength={150}
                    autoComplete="email"
                    value={form.email}
                    onChange={(event) =>
                      alterar(
                        'email',
                        event.target.value,
                      )
                    }
                  />
                </label>

                <label>
                  <span>
                    Telefone
                  </span>

                  <input
                    type="tel"
                    maxLength={30}
                    autoComplete="tel"
                    value={form.telefone}
                    onChange={(event) =>
                      alterar(
                        'telefone',
                        event.target.value,
                      )
                    }
                  />
                </label>

                <label>
                  <span>
                    Data de nascimento
                  </span>

                  <input
                    type="date"
                    value={
                      form.data_nascimento
                    }
                    onChange={(event) =>
                      alterar(
                        'data_nascimento',
                        event.target.value,
                      )
                    }
                  />
                </label>

                <label>
                  <span>
                    Senha
                  </span>

                  <input
                    type="password"
                    required
                    minLength={5}
                    autoComplete="new-password"
                    value={form.senha}
                    onChange={(event) =>
                      alterar(
                        'senha',
                        event.target.value,
                      )
                    }
                  />

                  <small>
                    Mínimo de 5 caracteres.
                  </small>
                </label>

                <label>
                  <span>
                    Confirmar senha
                  </span>

                  <input
                    type="password"
                    required
                    minLength={5}
                    autoComplete="new-password"
                    value={
                      form.confirmar_senha
                    }
                    onChange={(event) =>
                      alterar(
                        'confirmar_senha',
                        event.target.value,
                      )
                    }
                  />
                </label>

                {error && (
                  <div
                    className="cadastro81-error full"
                    role="alert"
                  >
                    {error}
                  </div>
                )}

                <div className="cadastro81-form-footer full">
                  <p>
                    Ao enviar, sua conta ainda não estará ativa.
                    Primeiro enviaremos a confirmação do e-mail.
                  </p>

                  <button
                    type="submit"
                    disabled={submitting}
                  >
                    {submitting
                      ? 'Enviando...'
                      : 'Enviar solicitação'}
                  </button>
                </div>
              </form>
            </>
          )}
        </section>
      </section>
    </main>
  )
}
