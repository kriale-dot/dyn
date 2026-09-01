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

import './PerfilPage.css'

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
  } = useAuth()

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
  }, [carregar])

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

          email:
            form.email
              .trim()
              .toLowerCase(),

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
                  required
                  value={form.email}
                  onChange={(event) =>
                    alterar(
                      'email',
                      event.target.value,
                    )
                  }
                />
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
