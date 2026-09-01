import {
  useCallback,
  useEffect,
  useMemo,
  useState,
} from 'react'

import {
  desativarNecessidadeEspecifica,
  getNecessidadeEspecificaUsuario,
  getNecessidadesEspecificas,
  getUsuarios,
  salvarNecessidadeEspecifica,
} from '../api/api'

import {
  useAuth,
} from '../contexts/AuthContext'

import './NecessidadesPage.css'

export default function NecessidadesPage() {
  const {
    capacidades,
  } = useAuth()

  const podeGerenciar =
    Boolean(
      capacidades
        ?.gerenciar_necessidades_especificas,
    )

  const [usuarios, setUsuarios] =
    useState([])

  const [necessidades, setNecessidades] =
    useState([])

  const [loading, setLoading] =
    useState(true)

  const [error, setError] =
    useState('')

  const [success, setSuccess] =
    useState('')

  const [busca, setBusca] =
    useState('')

  const [filtro, setFiltro] =
    useState('ATIVAS')

  const [modal, setModal] =
    useState(null)

  const carregar =
    useCallback(
      async () => {
        setLoading(true)
        setError('')

        try {
          const [
            usuariosResponse,
            necessidadesResponse,
          ] =
            await Promise.all([
              getUsuarios(),
              getNecessidadesEspecificas(),
            ])

          setUsuarios(
            extrairLista(
              usuariosResponse,
              'usuarios',
            ),
          )

          setNecessidades(
            extrairLista(
              necessidadesResponse,
              'necessidades',
            ),
          )
        } catch (err) {
          setError(
            mensagemErro(
              err,
              'Não foi possível carregar as necessidades específicas.',
            ),
          )
        } finally {
          setLoading(false)
        }
      },
      [],
    )

  useEffect(() => {
    if (podeGerenciar) {
      carregar()
    } else {
      setLoading(false)
    }
  }, [
    carregar,
    podeGerenciar,
  ])

  const linhas =
    useMemo(
      () => {
        const mapaNecessidades =
          new Map()

        for (
          const registro
          of necessidades
        ) {
          const normalizado =
            normalizarNecessidade(
              registro,
            )

          mapaNecessidades.set(
            normalizado.usuario_id,
            normalizado,
          )
        }

        return usuarios
          .map(
            normalizarUsuario,
          )
          .filter(
            (usuario) =>
              usuario.status
              === 'ATIVO',
          )
          .map(
            (usuario) => ({
              ...usuario,
              necessidade:
                mapaNecessidades.get(
                  usuario.id,
                )
                ?? null,
            }),
          )
      },
      [
        usuarios,
        necessidades,
      ],
    )

  const filtradas =
    useMemo(
      () => {
        const termo =
          busca
            .trim()
            .toLocaleLowerCase(
              'pt-BR',
            )

        return linhas
          .filter(
            (item) => {
              const necessidade =
                item.necessidade

              if (
                filtro === 'ATIVAS'
                && !necessidade?.ativo
              ) {
                return false
              }

              if (
                filtro === 'SEM_REGISTRO'
                && necessidade !== null
              ) {
                return false
              }

              if (
                filtro === 'INATIVAS'
                && !(
                  necessidade
                  && !necessidade.ativo
                )
              ) {
                return false
              }

              if (!termo) {
                return true
              }

              return [
                item.nome,
                item.email,
              ]
                .filter(Boolean)
                .join(' ')
                .toLocaleLowerCase(
                  'pt-BR',
                )
                .includes(
                  termo,
                )
            },
          )
          .sort(
            (a, b) =>
              a.nome.localeCompare(
                b.nome,
                'pt-BR',
              ),
          )
      },
      [
        linhas,
        busca,
        filtro,
      ],
    )

  const resumo =
    useMemo(
      () => ({
        pessoas:
          linhas.length,

        ativas:
          linhas.filter(
            (item) =>
              item
                .necessidade
                ?.ativo,
          ).length,

        inativas:
          linhas.filter(
            (item) =>
              item.necessidade
              && !item
                .necessidade
                .ativo,
          ).length,

        semRegistro:
          linhas.filter(
            (item) =>
              item.necessidade
              === null,
          ).length,
      }),
      [linhas],
    )

  async function abrirRegistro(
    usuario,
  ) {
    setError('')
    setSuccess('')

    /**
     * Se a listagem já contém um registro,
     * usamos esses metadados apenas para saber
     * que ele existe. A observação completa é
     * consultada somente quando o gestor abre
     * explicitamente a pessoa.
     */
    if (usuario.necessidade) {
      try {
        const response =
          await getNecessidadeEspecificaUsuario(
            usuario.id,
          )

        setModal({
          usuario,
          registro:
            normalizarNecessidade(
              response?.dados
              ?? {},
            ),
        })

        return
      } catch (err) {
        setError(
          mensagemErro(
            err,
            'Não foi possível abrir o registro.',
          ),
        )
        return
      }
    }

    setModal({
      usuario,
      registro: null,
    })
  }

  async function desativar(
    usuario,
  ) {
    if (
      !usuario
        .necessidade
        ?.ativo
    ) {
      return
    }

    const confirmou =
      window.confirm(
        `Desativar o registro de necessidade específica de ${usuario.nome}? O conteúdo permanecerá preservado no histórico do banco.`,
      )

    if (!confirmou) {
      return
    }

    setError('')
    setSuccess('')

    try {
      await desativarNecessidadeEspecifica(
        usuario.id,
      )

      setSuccess(
        `O registro de ${usuario.nome} foi desativado.`,
      )

      await carregar()
    } catch (err) {
      setError(
        mensagemErro(
          err,
          'Não foi possível desativar o registro.',
        ),
      )
    }
  }

  if (!podeGerenciar) {
    return (
      <section className="panel">
        <span className="eyebrow">
          Informação restrita
        </span>

        <h1>
          Necessidades Específicas
        </h1>

        <p className="empty-state">
          Seu usuário não possui a permissão
          especial necessária para consultar
          esta área.
        </p>
      </section>
    )
  }

  return (
    <div className="needs-page">
      <section className="needs-hero">
        <div>
          <span className="eyebrow">
            Acesso restrito
          </span>

          <h1>
            Necessidades Específicas
          </h1>

          <p>
            Registre apenas informações necessárias
            para organização e cuidado adequado
            durante as atividades da igreja.
          </p>
        </div>

        <span className="needs-sensitive-badge">
          Informação sensível
        </span>
      </section>

      <section className="needs-privacy-note">
        <strong>
          Uso responsável
        </strong>

        <p>
          Evite diagnósticos, julgamentos ou detalhes
          desnecessários. Registre de forma objetiva
          aquilo que a equipe autorizada realmente
          precisa saber para prestar suporte.
        </p>
      </section>

      <section className="needs-summary-grid">
        <Metric
          label="Pessoas ativas"
          value={resumo.pessoas}
        />

        <Metric
          label="Registros ativos"
          value={resumo.ativas}
        />

        <Metric
          label="Sem registro"
          value={resumo.semRegistro}
        />

        <Metric
          label="Registros inativos"
          value={resumo.inativas}
        />
      </section>

      <section className="needs-toolbar">
        <label>
          <span>
            Buscar pessoa
          </span>

          <input
            type="search"
            placeholder="Nome ou e-mail..."
            value={busca}
            onChange={(event) =>
              setBusca(
                event.target.value,
              )
            }
          />
        </label>

        <label>
          <span>
            Exibir
          </span>

          <select
            value={filtro}
            onChange={(event) =>
              setFiltro(
                event.target.value,
              )
            }
          >
            <option value="ATIVAS">
              Registros ativos
            </option>

            <option value="SEM_REGISTRO">
              Sem registro
            </option>

            <option value="INATIVAS">
              Registros inativos
            </option>

            <option value="TODOS">
              Todas as pessoas
            </option>
          </select>
        </label>
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

      {loading ? (
        <div className="loading-card">
          Carregando registros...
        </div>
      ) : filtradas.length === 0 ? (
        <section className="panel">
          <p className="empty-state">
            Nenhuma pessoa encontrada
            para este filtro.
          </p>
        </section>
      ) : (
        <section className="needs-list">
          {filtradas.map(
            (usuario) => (
              <NecessidadePessoaCard
                key={usuario.id}
                usuario={usuario}
                onOpen={() =>
                  abrirRegistro(
                    usuario,
                  )
                }
                onDeactivate={() =>
                  desativar(
                    usuario,
                  )
                }
              />
            ),
          )}
        </section>
      )}

      {modal && (
        <NecessidadeModal
          usuario={modal.usuario}
          registro={modal.registro}
          onClose={() =>
            setModal(null)
          }
          onSaved={async (
            mensagem,
          ) => {
            setModal(null)
            setSuccess(mensagem)
            await carregar()
          }}
        />
      )}
    </div>
  )
}

function NecessidadePessoaCard({
  usuario,
  onOpen,
  onDeactivate,
}) {
  const registro =
    usuario.necessidade

  let estado =
    'Sem registro'

  let classe =
    'none'

  if (registro?.ativo) {
    estado =
      'Registro ativo'
    classe =
      'active'
  } else if (registro) {
    estado =
      'Registro inativo'
    classe =
      'inactive'
  }

  return (
    <article className="needs-person-card">
      <div className="needs-avatar">
        {iniciais(
          usuario.nome,
        )}
      </div>

      <div className="needs-person-main">
        <div>
          <strong>
            {usuario.nome}
          </strong>

          <span>
            {usuario.email}
          </span>
        </div>

        <div className="needs-person-meta">
          <span
            className={
              `needs-record-state ${classe}`
            }
          >
            {estado}
          </span>

          {registro?.atualizado_em && (
            <span>
              Atualizado em{' '}
              {formatarData(
                registro
                  .atualizado_em,
              )}
            </span>
          )}
        </div>
      </div>

      <div className="needs-person-actions">
        <button
          type="button"
          className={
            registro
              ? 'small-secondary-button'
              : 'small-primary-button'
          }
          onClick={onOpen}
        >
          {registro
            ? registro.ativo
              ? 'Abrir registro'
              : 'Reativar / editar'
            : '+ Registrar'}
        </button>

        {registro?.ativo && (
          <button
            type="button"
            className="small-danger-button"
            onClick={
              onDeactivate
            }
          >
            Desativar
          </button>
        )}
      </div>
    </article>
  )
}

function NecessidadeModal({
  usuario,
  registro,
  onClose,
  onSaved,
}) {
  const [observacao, setObservacao] =
    useState(
      registro?.observacao
      ?? '',
    )

  const [saving, setSaving] =
    useState(false)

  const [error, setError] =
    useState('')

  const caracteres =
    observacao.length

  async function salvar(
    event,
  ) {
    event.preventDefault()
    setSaving(true)
    setError('')

    try {
      await salvarNecessidadeEspecifica(
        usuario.id,
        observacao,
      )

      await onSaved(
        registro
          ? registro.ativo
            ? 'Registro atualizado com sucesso.'
            : 'Registro reativado e atualizado com sucesso.'
          : 'Necessidade específica registrada com sucesso.',
      )
    } catch (err) {
      setError(
        mensagemErro(
          err,
          'Não foi possível salvar o registro.',
        ),
      )
    } finally {
      setSaving(false)
    }
  }

  return (
    <div
      className="modal-backdrop"
      role="presentation"
      onMouseDown={(event) => {
        if (
          event.target
          === event.currentTarget
        ) {
          onClose()
        }
      }}
    >
      <section
        className="modal-card needs-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="needs-modal-title"
      >
        <header className="modal-header">
          <div>
            <span className="eyebrow">
              Registro restrito
            </span>

            <h2 id="needs-modal-title">
              {usuario.nome}
            </h2>
          </div>

          <button
            type="button"
            className="modal-close"
            onClick={onClose}
            aria-label="Fechar"
          >
            ×
          </button>
        </header>

        <div className="needs-modal-user">
          <div className="needs-avatar">
            {iniciais(
              usuario.nome,
            )}
          </div>

          <div>
            <strong>
              {usuario.nome}
            </strong>

            <span>
              {usuario.email}
            </span>
          </div>
        </div>

        <form
          className="needs-form"
          onSubmit={salvar}
        >
          <label>
            <span>
              Observação necessária para suporte *
            </span>

            <textarea
              required
              maxLength={2000}
              rows={9}
              value={observacao}
              placeholder="Ex.: necessita de acesso sem escadas; evitar alimentos com...; precisa permanecer acompanhado por..."
              onChange={(event) =>
                setObservacao(
                  event.target.value,
                )
              }
            />
          </label>

          <div className="needs-writing-help">
            <span>
              Seja objetivo, respeitoso
              e registre somente o necessário.
            </span>

            <strong>
              {caracteres}/2000
            </strong>
          </div>

          {registro && (
            <div className="needs-history-meta">
              <span>
                Criado:
                {' '}
                {formatarDataHora(
                  registro.criado_em,
                )}
              </span>

              <span>
                Última atualização:
                {' '}
                {formatarDataHora(
                  registro.atualizado_em,
                )}
              </span>
            </div>
          )}

          {error && (
            <div className="error-message">
              {error}
            </div>
          )}

          <footer className="modal-actions">
            <button
              type="button"
              className="button-secondary"
              onClick={onClose}
            >
              Cancelar
            </button>

            <button
              type="submit"
              className="button-primary"
              disabled={
                saving
                || observacao
                  .trim()
                  .length === 0
              }
            >
              {saving
                ? 'Salvando...'
                : registro?.ativo
                  ? 'Salvar alterações'
                  : registro
                    ? 'Reativar e salvar'
                    : 'Registrar'}
            </button>
          </footer>
        </form>
      </section>
    </div>
  )
}

function Metric({
  label,
  value,
}) {
  return (
    <article className="program-metric-card">
      <span>{label}</span>
      <strong>{value}</strong>
    </article>
  )
}

function extrairLista(
  response,
  chave,
) {
  const lista =
    response?.dados?.[chave]
    ?? response?.dados
    ?? response?.[chave]
    ?? []

  return Array.isArray(lista)
    ? lista
    : []
}

function normalizarUsuario(
  item,
) {
  return {
    id:
      Number(item?.id)
      || 0,

    nome:
      item?.nome
      || 'Usuário',

    email:
      item?.email
      || '',

    status:
      String(
        item?.status
        || 'ATIVO',
      ).toUpperCase(),
  }
}

function normalizarNecessidade(
  item,
) {
  const usuario =
    item?.usuario
    ?? {}

  return {
    id:
      Number(item?.id)
      || 0,

    usuario_id:
      Number(
        item?.usuario_id
        ?? usuario?.id
        ?? 0,
      ),

    observacao:
      item?.observacao
      || '',

    ativo:
      Boolean(
        Number(item?.ativo)
        || item?.ativo === true,
      ),

    criado_em:
      item?.criado_em
      ?? null,

    atualizado_em:
      item?.atualizado_em
      ?? null,
  }
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

function formatarData(
  valor,
) {
  if (!valor) {
    return '—'
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

  return data.toLocaleDateString(
    'pt-BR',
  )
}

function formatarDataHora(
  valor,
) {
  if (!valor) {
    return '—'
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
