import {
  useCallback,
  useEffect,
  useRef,
  useState,
} from 'react'

import {
  Link,
} from 'react-router-dom'

import {
  atualizarIgreja,
  enviarLogotipoIgreja,
  getIgreja,
  removerLogotipoIgreja,
} from '../api/api'

import {
  useAuth,
} from '../contexts/AuthContext'

import './IgrejaConfigPage.css'

const API_URL =
  import.meta.env.VITE_API_URL
  || 'http://localhost:8282'

const MAX_LOGO_BYTES =
  5 * 1024 * 1024

const MIME_PERMITIDOS =
  new Set([
    'image/jpeg',
    'image/png',
    'image/webp',
  ])

const FORM_VAZIO = {
  nome: '',
  cep: '',
  logradouro: '',
  numero: '',
  complemento: '',
  bairro: '',
  cidade: '',
  estado: '',
  telefone: '',
  email: '',
  site: '',
}

export default function IgrejaConfigPage() {
  const {
    capacidades,
    refreshBootstrap,
  } = useAuth()

  const inputLogoRef =
    useRef(null)

  const [igreja, setIgreja] =
    useState(null)

  const [form, setForm] =
    useState(FORM_VAZIO)

  const [loading, setLoading] =
    useState(true)

  const [saving, setSaving] =
    useState(false)

  const [logoBusy, setLogoBusy] =
    useState(false)

  const [error, setError] =
    useState('')

  const [success, setSuccess] =
    useState('')

  const podeAdministrar =
    Boolean(
      capacidades
        ?.gerenciar_igreja
      || capacidades
        ?.administrar_estrutura
      || capacidades
        ?.gerenciar_departamentos,
    )

  const carregar =
    useCallback(
      async () => {
        setLoading(true)
        setError('')

        try {
          const response =
            await getIgreja()

          const dados =
            response?.dados
            ?? null

          setIgreja(dados)

          setForm({
            nome:
              dados?.nome
              ?? '',
            cep:
              dados?.cep
              ?? '',
            logradouro:
              dados?.logradouro
              ?? '',
            numero:
              dados?.numero
              ?? '',
            complemento:
              dados?.complemento
              ?? '',
            bairro:
              dados?.bairro
              ?? '',
            cidade:
              dados?.cidade
              ?? '',
            estado:
              dados?.estado
              ?? '',
            telefone:
              dados?.telefone
              ?? '',
            email:
              dados?.email
              ?? '',
            site:
              dados?.site
              ?? '',
          })
        } catch (err) {
          setError(
            mensagemErro(
              err,
              'Não foi possível carregar os dados da igreja.',
            ),
          )
        } finally {
          setLoading(false)
        }
      },
      [],
    )

  useEffect(() => {
    if (podeAdministrar) {
      carregar()
    } else {
      setLoading(false)
    }
  }, [
    carregar,
    podeAdministrar,
  ])

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
        await atualizarIgreja({
          nome:
            form.nome.trim(),

          /**
           * PUT /igreja representa atualização completa.
           * Por isso enviamos também o logotipo atual,
           * evitando apagá-lo acidentalmente ao salvar
           * os demais dados institucionais.
           */
          logotipo:
            igreja?.logotipo
            ?? null,

          cep:
            limparOpcional(
              form.cep,
            ),

          logradouro:
            limparOpcional(
              form.logradouro,
            ),

          numero:
            limparOpcional(
              form.numero,
            ),

          complemento:
            limparOpcional(
              form.complemento,
            ),

          bairro:
            limparOpcional(
              form.bairro,
            ),

          cidade:
            limparOpcional(
              form.cidade,
            ),

          estado:
            limparOpcional(
              form.estado,
            )
              ?.toUpperCase()
            ?? null,

          telefone:
            limparOpcional(
              form.telefone,
            ),

          email:
            limparOpcional(
              form.email,
            )
              ?.toLowerCase()
            ?? null,

          site:
            limparOpcional(
              form.site,
            ),
        })

      const atualizada =
        response?.dados
        ?? null

      if (atualizada) {
        setIgreja(
          atualizada,
        )
      }

      await refreshBootstrap()

      setSuccess(
        'Dados da igreja atualizados com sucesso.',
      )
    } catch (err) {
      setError(
        mensagemErro(
          err,
          'Não foi possível atualizar os dados da igreja.',
        ),
      )
    } finally {
      setSaving(false)
    }
  }

  async function selecionarLogo(
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
      > MAX_LOGO_BYTES
    ) {
      setError(
        'O logotipo deve possuir no máximo 5 MB.',
      )
      return
    }

    setLogoBusy(true)
    setError('')
    setSuccess('')

    try {
      await enviarLogotipoIgreja(
        arquivo,
      )

      await carregar()
      await refreshBootstrap()

      setSuccess(
        'Logotipo atualizado com sucesso.',
      )
    } catch (err) {
      setError(
        mensagemErro(
          err,
          'Não foi possível enviar o logotipo.',
        ),
      )
    } finally {
      setLogoBusy(false)
    }
  }

  async function removerLogo() {
    if (!igreja?.logotipo) {
      return
    }

    const confirmou =
      window.confirm(
        'Remover o logotipo da igreja?',
      )

    if (!confirmou) {
      return
    }

    setLogoBusy(true)
    setError('')
    setSuccess('')

    try {
      await removerLogotipoIgreja()

      await carregar()
      await refreshBootstrap()

      setSuccess(
        'Logotipo removido com sucesso.',
      )
    } catch (err) {
      setError(
        mensagemErro(
          err,
          'Não foi possível remover o logotipo.',
        ),
      )
    } finally {
      setLogoBusy(false)
    }
  }

  if (!podeAdministrar) {
    return (
      <section className="panel">
        <span className="eyebrow">
          Acesso restrito
        </span>

        <h1>
          Dados da igreja
        </h1>

        <p className="empty-state">
          Seu usuário não possui permissão
          administrativa para esta área.
        </p>
      </section>
    )
  }

  if (loading) {
    return (
      <div className="loading-card">
        Carregando dados da igreja...
      </div>
    )
  }

  return (
    <div className="church-settings-page">
      <Link
        to="/admin/estrutura"
        className="text-link"
      >
        ← Estrutura da igreja
      </Link>

      <section className="church-settings-hero">
        <div>
          <span className="eyebrow">
            Institucional
          </span>

          <h1>
            Dados da igreja
          </h1>

          <p>
            Mantenha a identidade e os dados
            institucionais usados em todo o SYN.
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

      <section className="church-settings-grid">
        <aside className="church-brand-card">
          <span className="eyebrow">
            Identidade
          </span>

          <h2>
            Logotipo
          </h2>

          <div className="church-logo-preview">
            {igreja?.logotipo ? (
              <img
                src={
                  resolverArquivoApi(
                    igreja.logotipo,
                  )
                }
                alt={`Logotipo de ${igreja.nome}`}
              />
            ) : (
              <div className="church-logo-placeholder">
                SYN
              </div>
            )}
          </div>

          <input
            ref={inputLogoRef}
            type="file"
            hidden
            accept="image/jpeg,image/png,image/webp"
            onChange={
              selecionarLogo
            }
          />

          <div className="church-logo-actions">
            <button
              type="button"
              className="small-primary-button"
              disabled={logoBusy}
              onClick={() =>
                inputLogoRef
                  .current
                  ?.click()
              }
            >
              {logoBusy
                ? 'Aguarde...'
                : igreja?.logotipo
                  ? 'Trocar logotipo'
                  : 'Adicionar logotipo'}
            </button>

            {igreja?.logotipo && (
              <button
                type="button"
                className="small-danger-button"
                disabled={logoBusy}
                onClick={removerLogo}
              >
                Remover
              </button>
            )}
          </div>

          <p className="church-logo-help">
            JPEG, PNG ou WEBP.
            Máximo de 5 MB.
          </p>
        </aside>

        <form
          className="church-data-card"
          onSubmit={salvar}
        >
          <header>
            <span className="eyebrow">
              Cadastro institucional
            </span>

            <h2>
              Informações principais
            </h2>
          </header>

          <div className="church-form-grid">
            <Field
              label="Nome da igreja *"
              full
            >
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
            </Field>

            <Field label="CEP">
              <input
                type="text"
                maxLength={12}
                value={form.cep}
                onChange={(event) =>
                  alterar(
                    'cep',
                    event.target.value,
                  )
                }
              />
            </Field>

            <Field label="Logradouro">
              <input
                type="text"
                value={form.logradouro}
                onChange={(event) =>
                  alterar(
                    'logradouro',
                    event.target.value,
                  )
                }
              />
            </Field>

            <Field label="Número">
              <input
                type="text"
                value={form.numero}
                onChange={(event) =>
                  alterar(
                    'numero',
                    event.target.value,
                  )
                }
              />
            </Field>

            <Field label="Complemento">
              <input
                type="text"
                value={form.complemento}
                onChange={(event) =>
                  alterar(
                    'complemento',
                    event.target.value,
                  )
                }
              />
            </Field>

            <Field label="Bairro">
              <input
                type="text"
                value={form.bairro}
                onChange={(event) =>
                  alterar(
                    'bairro',
                    event.target.value,
                  )
                }
              />
            </Field>

            <Field label="Cidade">
              <input
                type="text"
                value={form.cidade}
                onChange={(event) =>
                  alterar(
                    'cidade',
                    event.target.value,
                  )
                }
              />
            </Field>

            <Field label="Estado">
              <input
                type="text"
                maxLength={2}
                placeholder="SP"
                value={form.estado}
                onChange={(event) =>
                  alterar(
                    'estado',
                    event.target.value,
                  )
                }
              />
            </Field>

            <Field label="Telefone">
              <input
                type="text"
                value={form.telefone}
                onChange={(event) =>
                  alterar(
                    'telefone',
                    event.target.value,
                  )
                }
              />
            </Field>

            <Field label="E-mail">
              <input
                type="email"
                value={form.email}
                onChange={(event) =>
                  alterar(
                    'email',
                    event.target.value,
                  )
                }
              />
            </Field>

            <Field
              label="Site"
              full
            >
              <input
                type="url"
                placeholder="https://..."
                value={form.site}
                onChange={(event) =>
                  alterar(
                    'site',
                    event.target.value,
                  )
                }
              />
            </Field>
          </div>

          <footer className="church-form-actions">
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

      <section className="church-settings-note">
        <strong>
          Atualização completa
        </strong>

        <p>
          O cadastro institucional usa PUT.
          O frontend envia todos os campos atuais
          em cada salvamento, inclusive a referência
          do logotipo já existente, para evitar perda
          acidental de dados.
        </p>
      </section>
    </div>
  )
}

function Field({
  label,
  full = false,
  children,
}) {
  return (
    <label
      className={
        full
          ? 'church-field full'
          : 'church-field'
      }
    >
      <span>
        {label}
      </span>

      {children}
    </label>
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

function limparOpcional(
  valor,
) {
  const texto =
    String(
      valor
      ?? '',
    ).trim()

  return texto === ''
    ? null
    : texto
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
