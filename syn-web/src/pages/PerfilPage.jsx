import {
  useCallback,
  useEffect,
  useMemo,
  useRef,
  useState,
} from 'react'

import {
  atualizarMeuPerfil,
  enviarFotoPerfil,
  getMeuPerfil,
  removerFotoPerfil,
} from '../api/api'

import {
  useAuth,
} from '../contexts/AuthContext'

import {
  alterarMinhaSenha,
  encerrarTodasSessoes,
  getMinhaAtividadeSeguranca,
  solicitarAlteracaoEmail,
} from '../api/authSecurity'

import {
  useNavigate,
} from 'react-router-dom'

import './PerfilPage.css'
import './PerfilPageEtapa85.css'
import './PerfilPageEtapa86.css'
import './PerfilPageEtapa87.css'
import './PerfilPageEtapa90.css'

const API_URL =
  import.meta.env.VITE_API_URL
  || 'http://localhost:8282'

const MAX_FOTO_BYTES =
  5 * 1024 * 1024

const MIME_PERMITIDOS =
  new Set([
    'image/jpeg',
    'image/png',
    'image/webp',
  ])

export default function PerfilPage() {
  const {
    refreshBootstrap,
    signOut,
  } = useAuth()

  const navigate =
    useNavigate()

  const inputFotoRef =
    useRef(null)

  const [perfil, setPerfil] =
    useState(null)

  const [form, setForm] =
    useState({
      nome: '',
      email: '',
      telefone: '',
      data_nascimento: '',
    })

  const [loading, setLoading] =
    useState(true)

  const [saving, setSaving] =
    useState(false)

  const [photoBusy, setPhotoBusy] =
    useState(false)

  const [error, setError] =
    useState('')

  const [success, setSuccess] =
    useState('')

  const [
    sessionBusy,
    setSessionBusy,
  ] =
    useState(false)


  const [
    passwordBusy,
    setPasswordBusy,
  ] =
    useState(false)

  const [
    passwordForm,
    setPasswordForm,
  ] =
    useState({
      senha_atual: '',
      nova_senha: '',
      confirmar_nova_senha: '',
    })


  const [
    emailBusy,
    setEmailBusy,
  ] =
    useState(false)

  const [
    emailForm,
    setEmailForm,
  ] =
    useState({
      novo_email: '',
      senha_atual: '',
    })

  const [
    emailRequestMessage,
    setEmailRequestMessage,
  ] =
    useState('')


  const [
    securityEvents,
    setSecurityEvents,
  ] =
    useState([])

  const [
    securityEventsLoading,
    setSecurityEventsLoading,
  ] =
    useState(false)

  const [
    securityEventsError,
    setSecurityEventsError,
  ] =
    useState('')

  const carregarAtividadeSeguranca =
    useCallback(
      async () => {
        setSecurityEventsLoading(true)
        setSecurityEventsError('')

        try {
          const response =
            await getMinhaAtividadeSeguranca(
              20,
            )

          const eventos =
            response
              ?.dados
              ?.eventos

          setSecurityEvents(
            Array.isArray(eventos)
              ? eventos
              : [],
          )
        } catch (err) {
          setSecurityEventsError(
            err?.message
            || 'Não foi possível carregar a atividade de segurança.',
          )
        } finally {
          setSecurityEventsLoading(false)
        }
      },
      [],
    )

  const carregar =
    useCallback(
      async () => {
        setLoading(true)
        setError('')

        try {
          const response =
            await getMeuPerfil()

          const dados =
            response?.dados
            ?? null

          setPerfil(dados)

          setForm({
            nome:
              dados?.nome
              ?? '',

            email:
              dados?.email
              ?? '',

            telefone:
              dados?.telefone
              ?? '',

            data_nascimento:
              normalizarData(
                dados?.data_nascimento,
              ),
          })
        } catch (err) {
          setError(
            err?.message
            || 'Não foi possível carregar seu perfil.',
          )
        } finally {
          setLoading(false)
        }
      },
      [],
    )

  useEffect(() => {
    carregar()
    carregarAtividadeSeguranca()
  }, [
    carregar,
    carregarAtividadeSeguranca,
  ])

  const funcoes =
    useMemo(
      () =>
        Array.isArray(
          perfil?.funcoes,
        )
          ? perfil.funcoes
          : [],
      [perfil],
    )

  function alterar(
    campo,
    valor,
  ) {
    setForm(
      (atual) => ({
        ...atual,
        [campo]: valor,
      }),
    )
  }

  async function salvar(
    event,
  ) {
    event.preventDefault()

    setSaving(true)
    setError('')
    setSuccess('')

    try {
      const response =
        await atualizarMeuPerfil({
          nome:
            form.nome.trim(),

          telefone:
            form.telefone.trim()
            || null,

          data_nascimento:
            form.data_nascimento
            || null,
        })

      const dados =
        response?.dados
        ?? null

      if (dados) {
        setPerfil(dados)
      }

      await refreshBootstrap()

      setSuccess(
        'Perfil atualizado com sucesso.',
      )
    } catch (err) {
      setError(
        mensagemErro(
          err,
          'Não foi possível atualizar seu perfil.',
        ),
      )
    } finally {
      setSaving(false)
    }
  }

  async function selecionarFoto(
    event,
  ) {
    const arquivo =
      event.target.files?.[0]

    event.target.value = ''

    if (!arquivo) {
      return
    }

    if (
      !MIME_PERMITIDOS.has(
        arquivo.type,
      )
    ) {
      setError(
        'Escolha uma imagem JPEG, PNG ou WEBP.',
      )
      return
    }

    if (
      arquivo.size
      > MAX_FOTO_BYTES
    ) {
      setError(
        'A foto deve possuir no máximo 5 MB.',
      )
      return
    }

    setPhotoBusy(true)
    setError('')
    setSuccess('')

    try {
      await enviarFotoPerfil(
        arquivo,
      )

      await carregar()
      await refreshBootstrap()

      setSuccess(
        'Foto de perfil atualizada.',
      )
    } catch (err) {
      setError(
        mensagemErro(
          err,
          'Não foi possível enviar a foto.',
        ),
      )
    } finally {
      setPhotoBusy(false)
    }
  }

  async function excluirFoto() {
    if (!perfil?.foto) {
      return
    }

    const confirmou =
      window.confirm(
        'Remover sua foto de perfil?',
      )

    if (!confirmou) {
      return
    }

    setPhotoBusy(true)
    setError('')
    setSuccess('')

    try {
      await removerFotoPerfil()

      await carregar()
      await refreshBootstrap()

      setSuccess(
        'Foto removida com sucesso.',
      )
    } catch (err) {
      setError(
        mensagemErro(
          err,
          'Não foi possível remover a foto.',
        ),
      )
    } finally {
      setPhotoBusy(false)
    }
  }

  function alterarCampoEmail(
    campo,
    valor,
  ) {
    setEmailForm(
      (atual) => ({
        ...atual,
        [campo]: valor,
      }),
    )
  }

  async function solicitarNovoEmail(
    event,
  ) {
    event.preventDefault()

    setError('')
    setSuccess('')
    setEmailRequestMessage('')

    const novoEmail =
      emailForm
        .novo_email
        .trim()
        .toLowerCase()

    if (!novoEmail) {
      setError(
        'Informe o novo endereço de e-mail.',
      )
      return
    }

    if (
      novoEmail
      === String(
        perfil?.email
        || '',
      ).toLowerCase()
    ) {
      setError(
        'O novo e-mail deve ser diferente do e-mail atual.',
      )
      return
    }

    setEmailBusy(true)

    try {
      const response =
        await solicitarAlteracaoEmail({
          novo_email:
            novoEmail,
          senha_atual:
            emailForm.senha_atual,
        })

      setEmailRequestMessage(
        response
          ?.dados
          ?.mensagem
        || 'Enviamos um link de confirmação para o novo endereço.',
      )

      setEmailForm({
        novo_email: '',
        senha_atual: '',
      })
    } catch (err) {
      setError(
        mensagemErro(
          err,
          'Não foi possível solicitar a alteração do e-mail.',
        ),
      )
    } finally {
      setEmailBusy(false)
    }
  }

  function alterarCampoSenha(
    campo,
    valor,
  ) {
    setPasswordForm(
      (atual) => ({
        ...atual,
        [campo]: valor,
      }),
    )
  }

  async function alterarSenha(
    event,
  ) {
    event.preventDefault()

    setError('')
    setSuccess('')

    if (
      passwordForm
        .nova_senha
        .length < 5
    ) {
      setError(
        'A nova senha deve possuir pelo menos 5 caracteres.',
      )
      return
    }

    if (
      passwordForm.nova_senha
      !== passwordForm
        .confirmar_nova_senha
    ) {
      setError(
        'A confirmação da nova senha não corresponde.',
      )
      return
    }

    setPasswordBusy(true)

    try {
      const response =
        await alterarMinhaSenha({
          senha_atual:
            passwordForm.senha_atual,

          nova_senha:
            passwordForm.nova_senha,

          confirmar_nova_senha:
            passwordForm
              .confirmar_nova_senha,
        })

      /**
       * A alteração incrementa sessao_versao no servidor.
       * O token atual já não pode ser utilizado.
       */
      signOut()

      window.alert(
        response
          ?.dados
          ?.mensagem
        || 'Senha alterada. Entre novamente com a nova senha.',
      )

      navigate(
        '/login',
        {
          replace: true,
        },
      )
    } catch (err) {
      setError(
        mensagemErro(
          err,
          'Não foi possível alterar a senha.',
        ),
      )
      setPasswordBusy(false)
    }
  }

  async function sairDeTodosDispositivos() {
    const confirmou =
      window.confirm(
        'Encerrar todas as suas sessões? Você precisará entrar novamente neste computador e em qualquer outro dispositivo.',
      )

    if (!confirmou) {
      return
    }

    setSessionBusy(true)
    setError('')
    setSuccess('')

    try {
      await encerrarTodasSessoes()

      /**
       * O backend já invalidou o JWT.
       * Agora removemos a cópia local e voltamos ao login.
       */
      signOut()

      window.alert(
        'Todas as sessões foram encerradas. Entre novamente para continuar.',
      )

      navigate(
        '/login',
        {
          replace: true,
        },
      )
    } catch (err) {
      setError(
        mensagemErro(
          err,
          'Não foi possível encerrar as sessões.',
        ),
      )
      setSessionBusy(false)
    }
  }

  if (loading) {
    return (
      <div className="loading-card">
        Carregando seu perfil...
      </div>
    )
  }

  if (!perfil) {
    return (
      <section className="panel">
        <h1>
          Meu Perfil
        </h1>

        {error && (
          <div className="error-message">
            {error}
          </div>
        )}
      </section>
    )
  }

  return (
    <div className="profile-page">
      <section className="profile-hero">
        <div>
          <span className="eyebrow">
            Conta pessoal
          </span>

          <h1>
            Meu Perfil
          </h1>

          <p>
            Mantenha seus dados de contato
            atualizados e consulte suas funções
            atuais no SYN.
          </p>
        </div>
      </section>

      {error && (
        <div
          className="error-message"
          role="alert"
        >
          {error}
        </div>
      )}

      {success && (
        <div
          className="success-message"
          role="status"
        >
          {success}
        </div>
      )}

      <section className="profile-grid">
        <aside className="profile-side-card">
          <div className="profile-photo-wrap">
            {perfil.foto ? (
              <img
                className="profile-photo"
                src={
                  resolverArquivoApi(
                    perfil.foto,
                  )
                }
                alt={`Foto de ${perfil.nome}`}
              />
            ) : (
              <div className="profile-photo-placeholder">
                {iniciais(
                  perfil.nome,
                )}
              </div>
            )}
          </div>

          <div className="profile-identity">
            <strong>
              {perfil.nome}
            </strong>

            <span>
              {perfil?.papel?.nome}
            </span>

            <span
              className={
                perfil.status === 'ATIVO'
                  ? 'user-status active'
                  : 'user-status inactive'
              }
            >
              {perfil.status === 'ATIVO'
                ? 'Ativo'
                : 'Inativo'}
            </span>
          </div>

          <div className="profile-photo-actions">
            <input
              ref={inputFotoRef}
              type="file"
              accept="image/jpeg,image/png,image/webp"
              hidden
              onChange={
                selecionarFoto
              }
            />

            <button
              type="button"
              className="small-primary-button"
              disabled={photoBusy}
              onClick={() =>
                inputFotoRef
                  .current
                  ?.click()
              }
            >
              {photoBusy
                ? 'Aguarde...'
                : perfil.foto
                  ? 'Trocar foto'
                  : 'Adicionar foto'}
            </button>

            {perfil.foto && (
              <button
                type="button"
                className="small-danger-button"
                disabled={photoBusy}
                onClick={excluirFoto}
              >
                Remover
              </button>
            )}
          </div>

          <p className="profile-photo-help">
            JPEG, PNG ou WEBP.
            Máximo de 5 MB.
          </p>
        </aside>

        <section className="profile-main-card">
          <header className="profile-section-heading">
            <div>
              <span className="eyebrow">
                Dados pessoais
              </span>

              <h2>
                Informações do perfil
              </h2>
            </div>
          </header>

          <form
            className="profile-form"
            onSubmit={salvar}
          >
            <label className="profile-field full">
              <span>Nome *</span>

              <input
                type="text"
                required
                maxLength={150}
                value={form.nome}
                onChange={(event) =>
                  alterar(
                    'nome',
                    event.target.value,
                  )
                }
              />
            </label>

            <div className="profile-form-row">
              <label className="profile-field">
                <span>E-mail *</span>

                <input
                  type="email"
                  value={form.email}
                  readOnly
                  title="O e-mail é alterado na área de segurança da conta."
                />

                <small className="profile87-email-help">
                  Para trocar o e-mail, use “Alterar e-mail” abaixo.
                </small>
              </label>

              <label className="profile-field">
                <span>Telefone</span>

                <input
                  type="text"
                  maxLength={30}
                  value={form.telefone}
                  onChange={(event) =>
                    alterar(
                      'telefone',
                      event.target.value,
                    )
                  }
                />
              </label>
            </div>

            <div className="profile-form-row">
              <label className="profile-field">
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

              <label className="profile-field readonly">
                <span>
                  Papel no sistema
                </span>

                <input
                  type="text"
                  value={
                    perfil
                      ?.papel
                      ?.nome
                    || ''
                  }
                  readOnly
                />
              </label>
            </div>

            <footer className="profile-form-actions">
              <button
                type="submit"
                className="button-primary"
                disabled={saving}
              >
                {saving
                  ? 'Salvando...'
                  : 'Salvar alterações'}
              </button>
            </footer>
          </form>
        </section>
      </section>

      <section className="profile-functions-card">
        <header className="profile-section-heading">
          <div>
            <span className="eyebrow">
              Habilitações
            </span>

            <h2>
              Minhas funções
            </h2>

            <p>
              Estas funções são definidas
              pela administração e determinam
              em quais escalas você pode atuar.
            </p>
          </div>
        </header>

        {funcoes.length === 0 ? (
          <p className="empty-state">
            Nenhuma função foi atribuída
            ao seu usuário.
          </p>
        ) : (
          <div className="profile-functions-list">
            {funcoes.map(
              (funcao) => (
                <article
                  key={funcao.id}
                  className="profile-function-item"
                >
                  <div className="profile-function-mark">
                    ✓
                  </div>

                  <div>
                    <strong>
                      {funcao.nome}
                    </strong>

                    <span>
                      {
                        funcao
                          ?.departamento
                          ?.nome
                        ?? funcao
                          ?.departamento_nome
                        ?? 'Sem departamento'
                      }
                    </span>
                  </div>
                </article>
              ),
            )}
          </div>
        )}
      </section>

      <section className="profile87-email-card">
        <header className="profile87-email-heading">
          <div className="profile87-email-icon">
            @
          </div>

          <div>
            <span className="eyebrow">
              Segurança da conta
            </span>

            <h2>
              Alterar e-mail
            </h2>

            <p>
              O e-mail é usado para entrar no SYN e recuperar sua senha.
              Por isso, a alteração precisa ser confirmada no novo
              endereço.
            </p>
          </div>
        </header>

        <div className="profile87-current-email">
          <span>
            E-mail atual
          </span>

          <strong>
            {perfil.email}
          </strong>
        </div>

        {emailRequestMessage && (
          <div className="profile87-request-message">
            {emailRequestMessage}
          </div>
        )}

        <form
          className="profile87-email-form"
          onSubmit={solicitarNovoEmail}
        >
          <label>
            <span>
              Novo e-mail
            </span>

            <input
              type="email"
              required
              autoComplete="email"
              value={
                emailForm
                  .novo_email
              }
              onChange={(event) =>
                alterarCampoEmail(
                  'novo_email',
                  event.target.value,
                )
              }
            />
          </label>

          <label>
            <span>
              Senha atual
            </span>

            <input
              type="password"
              required
              autoComplete="current-password"
              value={
                emailForm
                  .senha_atual
              }
              onChange={(event) =>
                alterarCampoEmail(
                  'senha_atual',
                  event.target.value,
                )
              }
            />
          </label>

          <div className="profile87-email-action">
            <div>
              O e-mail atual continuará válido até você confirmar
              o novo endereço.
            </div>

            <button
              type="submit"
              disabled={emailBusy}
            >
              {emailBusy
                ? 'Enviando...'
                : 'Enviar confirmação'}
            </button>
          </div>
        </form>
      </section>

      <section className="profile86-password-card">
        <header className="profile86-password-heading">
          <div className="profile86-password-icon">
            ●
          </div>

          <div>
            <span className="eyebrow">
              Segurança da conta
            </span>

            <h2>
              Alterar senha
            </h2>

            <p>
              Confirme sua senha atual e escolha uma nova senha.
              Depois da alteração, todas as sessões serão encerradas.
            </p>
          </div>
        </header>

        <form
          className="profile86-password-form"
          onSubmit={alterarSenha}
        >
          <label>
            <span>
              Senha atual
            </span>

            <input
              type="password"
              required
              autoComplete="current-password"
              value={
                passwordForm
                  .senha_atual
              }
              onChange={(event) =>
                alterarCampoSenha(
                  'senha_atual',
                  event.target.value,
                )
              }
            />
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
              value={
                passwordForm
                  .nova_senha
              }
              onChange={(event) =>
                alterarCampoSenha(
                  'nova_senha',
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
              Confirmar nova senha
            </span>

            <input
              type="password"
              required
              minLength={5}
              autoComplete="new-password"
              value={
                passwordForm
                  .confirmar_nova_senha
              }
              onChange={(event) =>
                alterarCampoSenha(
                  'confirmar_nova_senha',
                  event.target.value,
                )
              }
            />
          </label>

          <div className="profile86-password-action">
            <div>
              Ao salvar, você precisará entrar novamente
              em todos os dispositivos.
            </div>

            <button
              type="submit"
              disabled={passwordBusy}
            >
              {passwordBusy
                ? 'Alterando...'
                : 'Alterar senha'}
            </button>
          </div>
        </form>
      </section>

      <section className="profile85-security-card">
        <div className="profile85-security-icon">
          ◈
        </div>

        <div className="profile85-security-copy">
          <span className="eyebrow">
            Segurança da conta
          </span>

          <h2>
            Sessões e dispositivos
          </h2>

          <p>
            Use esta opção se você entrou no SYN em um computador
            compartilhado, perdeu um dispositivo ou suspeita que outra
            pessoa possa ter acesso à sua sessão.
          </p>

          <div className="profile85-security-note">
            Ao encerrar as sessões, todos os dispositivos precisarão
            fazer login novamente.
          </div>
        </div>

        <button
          type="button"
          className="profile85-end-sessions"
          disabled={sessionBusy}
          onClick={sairDeTodosDispositivos}
        >
          {sessionBusy
            ? 'Encerrando...'
            : 'Encerrar todas as sessões'}
        </button>
      </section>

      <section className="profile90-activity-card">
        <header className="profile90-activity-heading">
          <div>
            <span className="eyebrow">
              Segurança da conta
            </span>

            <h2>
              Atividade recente
            </h2>

            <p>
              Veja alterações importantes relacionadas ao acesso
              e às credenciais da sua conta.
            </p>
          </div>

          <button
            type="button"
            disabled={securityEventsLoading}
            onClick={carregarAtividadeSeguranca}
          >
            {securityEventsLoading
              ? 'Atualizando...'
              : 'Atualizar'}
          </button>
        </header>

        {securityEventsError && (
          <div className="profile90-activity-error">
            {securityEventsError}
          </div>
        )}

        {securityEventsLoading
          && securityEvents.length === 0 ? (
          <div className="profile90-activity-empty">
            Carregando atividade...
          </div>
        ) : securityEvents.length === 0 ? (
          <div className="profile90-activity-empty">
            Nenhum evento de segurança registrado ainda.
          </div>
        ) : (
          <div className="profile90-activity-list">
            {securityEvents
              .slice(0, 8)
              .map(
                (evento) => (
                  <article
                    key={evento.id}
                    className="profile90-event"
                  >
                    <div
                      className={
                        `profile90-event-icon ${classeEventoSeguranca(
                          evento.tipo,
                        )}`
                      }
                    >
                      {iconeEventoSeguranca(
                        evento.tipo,
                      )}
                    </div>

                    <div className="profile90-event-copy">
                      <strong>
                        {evento.titulo}
                      </strong>

                      {evento.detalhe && (
                        <span>
                          {evento.detalhe}
                        </span>
                      )}
                    </div>

                    <time>
                      {formatarDataHora(
                        evento.criado_em,
                      )}
                    </time>
                  </article>
                ),
              )}
          </div>
        )}

        {securityEvents.length > 8 && (
          <div className="profile90-activity-more">
            Exibindo os 8 eventos mais recentes de {
              securityEvents.length
            } carregados.
          </div>
        )}
      </section>

      <section className="profile-account-info">
        <span>
          Último acesso:
          {' '}
          <strong>
            {formatarDataHora(
              perfil.ultimo_login_em,
            )}
          </strong>
        </span>

        <span>
          Cadastro:
          {' '}
          <strong>
            {formatarDataHora(
              perfil.criado_em,
            )}
          </strong>
        </span>
      </section>
    </div>
  )
}

function classeEventoSeguranca(
  tipo,
) {
  const valor =
    String(
      tipo
      || '',
    )
      .toUpperCase()

  if (
    valor.includes(
      'SENHA',
    )
  ) {
    return 'password'
  }

  if (
    valor.includes(
      'EMAIL',
    )
  ) {
    return 'email'
  }

  if (
    valor.includes(
      'SESSOES',
    )
  ) {
    return 'sessions'
  }

  if (
    valor.includes(
      'CONTA',
    )
  ) {
    return 'account'
  }

  return 'login'
}

function iconeEventoSeguranca(
  tipo,
) {
  const classe =
    classeEventoSeguranca(
      tipo,
    )

  const mapa = {
    password: '●',
    email: '@',
    sessions: '×',
    account: '+',
    login: '→',
  }

  return mapa[classe]
    || '•'
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

  return `${API_URL}${caminho}`
}

function normalizarData(
  valor,
) {
  if (!valor) {
    return ''
  }

  return String(valor)
    .slice(0, 10)
}

function formatarDataHora(
  valor,
) {
  if (!valor) {
    return 'Não informado'
  }

  const data =
    new Date(
      String(valor)
        .replace(' ', 'T'),
    )

  if (
    Number.isNaN(
      data.getTime(),
    )
  ) {
    return String(valor)
  }

  return data.toLocaleString(
    'pt-BR',
    {
      dateStyle: 'short',
      timeStyle: 'short',
    },
  )
}

function iniciais(
  nome,
) {
  if (!nome) {
    return '?'
  }

  const partes =
    nome
      .trim()
      .split(/\s+/)

  const primeira =
    partes[0]?.[0]
    ?? ''

  const ultima =
    partes.length > 1
      ? partes[
          partes.length - 1
        ]?.[0]
      : ''

  return (
    primeira + ultima
  ).toUpperCase()
}

function mensagemErro(
  err,
  fallback,
) {
  const erros =
    err?.payload?.erros

  if (
    erros
    && typeof erros === 'object'
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
