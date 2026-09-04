import {
  useCallback,
  useEffect,
  useMemo,
  useState,
} from 'react'

import {
  Link,
} from 'react-router-dom'

import {
  getAuditoria,
  getAuditoriaRegistro,
  getUsuarios,
} from '../api/api'

import {
  useAuth,
} from '../contexts/AuthContext'

import './AuditoriaPage.css'

const LIMITE =
  25

const FILTRO_INICIAL = {
  usuario_id: '',
  metodo: '',
  recurso: '',
  somente_erros: false,
}

export default function AuditoriaPage() {
  const {
    usuario,
  } = useAuth()

  const [filtros, setFiltros] =
    useState(FILTRO_INICIAL)

  const [filtrosAplicados, setFiltrosAplicados] =
    useState(FILTRO_INICIAL)

  const [pagina, setPagina] =
    useState(1)

  const [dados, setDados] =
    useState(null)

  const [usuarios, setUsuarios] =
    useState([])

  const [loading, setLoading] =
    useState(true)

  const [error, setError] =
    useState('')

  const [detalheId, setDetalheId] =
    useState(null)

  const [detalhe, setDetalhe] =
    useState(null)

  const [detalheLoading, setDetalheLoading] =
    useState(false)

  const [detalheError, setDetalheError] =
    useState('')

  const administrador =
    usuario?.papel?.codigo
    === 'ADMINISTRADOR'

  const carregar =
    useCallback(
      async () => {
        if (!administrador) {
          setLoading(false)
          return
        }

        setLoading(true)
        setError('')

        try {
          const response =
            await getAuditoria({
              pagina,
              limite:
                LIMITE,

              usuario_id:
                filtrosAplicados
                  .usuario_id
                || null,

              metodo:
                filtrosAplicados
                  .metodo
                || null,

              recurso:
                filtrosAplicados
                  .recurso
                  .trim()
                || null,

              somente_erros:
                filtrosAplicados
                  .somente_erros,
            })

          setDados(
            response?.dados
            ?? null,
          )
        } catch (err) {
          setError(
            mensagemErro(
              err,
              'Não foi possível carregar a auditoria.',
            ),
          )
        } finally {
          setLoading(false)
        }
      },
      [
        administrador,
        pagina,
        filtrosAplicados,
      ],
    )

  useEffect(() => {
    if (!administrador) {
      return
    }

    let ativo = true

    async function carregarUsuarios() {
      try {
        const response =
          await getUsuarios()

        const lista =
          response?.dados
          ?? response?.usuarios
          ?? []

        if (ativo) {
          setUsuarios(
            Array.isArray(lista)
              ? lista
              : [],
          )
        }
      } catch {
        /**
         * O filtro por usuário é apenas uma conveniência.
         * Falhar ao carregar a lista não impede a auditoria.
         */
      }
    }

    carregarUsuarios()

    return () => {
      ativo = false
    }
  }, [administrador])

  useEffect(() => {
    carregar()
  }, [carregar])

  const operacoes =
    useMemo(
      () =>
        Array.isArray(
          dados?.operacoes,
        )
          ? dados.operacoes
          : [],
      [dados],
    )

  const resumo =
    useMemo(
      () => {
        const erros =
          operacoes.filter(
            (item) =>
              !item
                ?.resultado
                ?.sucesso,
          ).length

        const usuariosUnicos =
          new Set(
            operacoes
              .map(
                (item) =>
                  item
                    ?.usuario
                    ?.id,
              )
              .filter(Boolean),
          ).size

        return {
          exibidas:
            operacoes.length,

          erros,

          sucessos:
            operacoes.length
            - erros,

          usuarios:
            usuariosUnicos,
        }
      },
      [operacoes],
    )

  const temProxima =
    Number(
      dados?.quantidade,
    ) === LIMITE

  function alterarFiltro(
    campo,
    valor,
  ) {
    setFiltros(
      (atual) => ({
        ...atual,
        [campo]: valor,
      }),
    )
  }

  function aplicarFiltros(
    event,
  ) {
    event.preventDefault()

    setPagina(1)

    setFiltrosAplicados({
      ...filtros,
      recurso:
        filtros.recurso.trim(),
    })
  }

  function limparFiltros() {
    setFiltros(
      FILTRO_INICIAL,
    )

    setFiltrosAplicados(
      FILTRO_INICIAL,
    )

    setPagina(1)
  }

  async function abrirDetalhe(
    id,
  ) {
    setDetalheId(id)
    setDetalhe(null)
    setDetalheError('')
    setDetalheLoading(true)

    try {
      const response =
        await getAuditoriaRegistro(
          id,
        )

      setDetalhe(
        response?.dados
        ?? null,
      )
    } catch (err) {
      setDetalheError(
        mensagemErro(
          err,
          'Não foi possível carregar este registro.',
        ),
      )
    } finally {
      setDetalheLoading(false)
    }
  }

  function fecharDetalhe() {
    setDetalheId(null)
    setDetalhe(null)
    setDetalheError('')
  }

  if (!administrador) {
    return (
      <section className="panel">
        <span className="eyebrow">
          Acesso restrito
        </span>

        <h1>
          Auditoria
        </h1>

        <p className="empty-state">
          Somente Administradores podem
          consultar a auditoria do sistema.
        </p>
      </section>
    )
  }

  return (
    <div className="audit-page">
      <Link
        to="/admin/estrutura"
        className="text-link"
      >
        ← Estrutura da igreja
      </Link>

      <section className="audit-hero">
        <div>
          <span className="eyebrow">
            Segurança e rastreabilidade
          </span>

          <h1>
            Auditoria administrativa
          </h1>

          <p>
            Consulte quem executou operações
            de escrita, em qual recurso e qual
            foi o resultado HTTP.
          </p>
        </div>

        <div className="audit-privacy-badge">
          <strong>
            Metadados apenas
          </strong>

          <span>
            Sem senha, JWT ou corpo completo
            da requisição.
          </span>
        </div>
      </section>

      <section className="audit-summary-grid">
        <Metric
          label="Nesta página"
          value={
            resumo.exibidas
          }
        />

        <Metric
          label="Sucessos"
          value={
            resumo.sucessos
          }
          tone="success"
        />

        <Metric
          label="Falhas"
          value={
            resumo.erros
          }
          tone={
            resumo.erros > 0
              ? 'danger'
              : ''
          }
        />

        <Metric
          label="Usuários envolvidos"
          value={
            resumo.usuarios
          }
        />
      </section>

      <form
        className="audit-filters"
        onSubmit={aplicarFiltros}
      >
        <label>
          <span>
            Usuário
          </span>

          <select
            value={
              filtros.usuario_id
            }
            onChange={(event) =>
              alterarFiltro(
                'usuario_id',
                event.target.value,
              )
            }
          >
            <option value="">
              Todos
            </option>

            {usuarios
              .map(
                normalizarUsuario,
              )
              .sort(
                (a, b) =>
                  a.nome.localeCompare(
                    b.nome,
                    'pt-BR',
                  ),
              )
              .map(
                (item) => (
                  <option
                    key={item.id}
                    value={item.id}
                  >
                    {item.nome}
                  </option>
                ),
              )}
          </select>
        </label>

        <label>
          <span>
            Método
          </span>

          <select
            value={
              filtros.metodo
            }
            onChange={(event) =>
              alterarFiltro(
                'metodo',
                event.target.value,
              )
            }
          >
            <option value="">
              Todos
            </option>

            <option value="POST">
              POST
            </option>

            <option value="PUT">
              PUT
            </option>

            <option value="PATCH">
              PATCH
            </option>

            <option value="DELETE">
              DELETE
            </option>
          </select>
        </label>

        <label>
          <span>
            Recurso
          </span>

          <input
            type="text"
            placeholder="programacoes"
            value={
              filtros.recurso
            }
            onChange={(event) =>
              alterarFiltro(
                'recurso',
                event.target.value,
              )
            }
          />
        </label>

        <label className="audit-error-filter">
          <input
            type="checkbox"
            checked={
              filtros
                .somente_erros
            }
            onChange={(event) =>
              alterarFiltro(
                'somente_erros',
                event.target.checked,
              )
            }
          />

          <span>
            Mostrar somente falhas
          </span>
        </label>

        <div className="audit-filter-actions">
          <button
            type="button"
            className="button-secondary"
            onClick={
              limparFiltros
            }
          >
            Limpar
          </button>

          <button
            type="submit"
            className="button-primary"
          >
            Aplicar filtros
          </button>
        </div>
      </form>

      {error && (
        <div
          className="error-message"
          role="alert"
        >
          {error}
        </div>
      )}

      <section className="audit-list-shell">
        <header className="audit-list-heading">
          <div>
            <span className="eyebrow">
              Operações
            </span>

            <h2>
              Registro administrativo
            </h2>
          </div>

          <span>
            Página
            {' '}
            {dados?.pagina ?? pagina}
          </span>
        </header>

        {loading ? (
          <div className="loading-card">
            Carregando auditoria...
          </div>
        ) : operacoes.length === 0 ? (
          <p className="empty-state">
            Nenhuma operação encontrada
            com os filtros atuais.
          </p>
        ) : (
          <div className="audit-table-wrap">
            <table className="audit-table">
              <thead>
                <tr>
                  <th>
                    Quando
                  </th>

                  <th>
                    Usuário
                  </th>

                  <th>
                    Operação
                  </th>

                  <th>
                    Recurso
                  </th>

                  <th>
                    Resultado
                  </th>

                  <th>
                    Detalhes
                  </th>
                </tr>
              </thead>

              <tbody>
                {operacoes.map(
                  (item) => (
                    <AuditRow
                      key={item.id}
                      item={item}
                      onOpen={() =>
                        abrirDetalhe(
                          item.id,
                        )
                      }
                    />
                  ),
                )}
              </tbody>
            </table>
          </div>
        )}

        <footer className="audit-pagination">
          <button
            type="button"
            className="button-secondary"
            disabled={
              loading
              || pagina <= 1
            }
            onClick={() =>
              setPagina(
                (atual) =>
                  Math.max(
                    1,
                    atual - 1,
                  ),
              )
            }
          >
            ← Anterior
          </button>

          <span>
            Página {pagina}
          </span>

          <button
            type="button"
            className="button-secondary"
            disabled={
              loading
              || !temProxima
            }
            onClick={() =>
              setPagina(
                (atual) =>
                  atual + 1,
              )
            }
          >
            Próxima →
          </button>
        </footer>
      </section>

      {detalheId && (
        <AuditoriaDetalheModal
          detalhe={detalhe}
          loading={
            detalheLoading
          }
          error={
            detalheError
          }
          onClose={
            fecharDetalhe
          }
        />
      )}
    </div>
  )
}

function AuditRow({
  item,
  onOpen,
}) {
  const sucesso =
    Boolean(
      item
        ?.resultado
        ?.sucesso,
    )

  return (
    <tr>
      <td>
        <span className="audit-date">
          {formatarDataHora(
            item.criado_em,
          )}
        </span>
      </td>

      <td>
        <span className="audit-user">
          <strong>
            {item
              ?.usuario
              ?.nome_historico
              || 'Sistema'}
          </strong>

          <small>
            {traduzirPapel(
              item
                ?.usuario
                ?.papel_historico,
            )}
          </small>
        </span>
      </td>

      <td>
        <span
          className={
            `audit-method method-${String(
              item
                ?.operacao
                ?.metodo
                || '',
            ).toLowerCase()}`
          }
        >
          {item
            ?.operacao
            ?.metodo}
        </span>
      </td>

      <td>
        <span className="audit-resource">
          <strong>
            {item
              ?.operacao
              ?.recurso
              || '—'}
          </strong>

          <small>
            {item
              ?.operacao
              ?.entidade_id
              ? `ID ${item.operacao.entidade_id}`
              : item
                ?.operacao
                ?.caminho}
          </small>
        </span>
      </td>

      <td>
        <span
          className={
            sucesso
              ? 'audit-result success'
              : 'audit-result error'
          }
        >
          <strong>
            {item
              ?.resultado
              ?.http_status}
          </strong>

          <small>
            {sucesso
              ? 'Sucesso'
              : 'Falha'}
          </small>
        </span>
      </td>

      <td>
        <button
          type="button"
          className="small-secondary-button"
          onClick={onOpen}
        >
          Ver
        </button>
      </td>
    </tr>
  )
}

function AuditoriaDetalheModal({
  detalhe,
  loading,
  error,
  onClose,
}) {
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
        className="modal-card audit-detail-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="audit-detail-title"
      >
        <header className="modal-header">
          <div>
            <span className="eyebrow">
              Auditoria
            </span>

            <h2 id="audit-detail-title">
              Detalhes da operação
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

        {loading ? (
          <div className="loading-card">
            Carregando registro...
          </div>
        ) : error ? (
          <div className="error-message">
            {error}
          </div>
        ) : detalhe ? (
          <div className="audit-detail-content">
            <section className="audit-detail-status">
              <span
                className={
                  detalhe
                    ?.resultado
                    ?.sucesso
                    ? 'audit-detail-icon success'
                    : 'audit-detail-icon error'
                }
              >
                {detalhe
                  ?.resultado
                  ?.sucesso
                  ? '✓'
                  : '!'}
              </span>

              <div>
                <strong>
                  HTTP
                  {' '}
                  {detalhe
                    ?.resultado
                    ?.http_status}
                </strong>

                <span>
                  {detalhe
                    ?.resultado
                    ?.sucesso
                    ? 'Operação concluída'
                    : 'Operação rejeitada ou falhou'}
                </span>
              </div>
            </section>

            <div className="audit-detail-grid">
              <Detail
                label="Data e hora"
                value={
                  formatarDataHora(
                    detalhe.criado_em,
                  )
                }
              />

              <Detail
                label="Usuário"
                value={
                  detalhe
                    ?.usuario
                    ?.nome_historico
                  || 'Sistema'
                }
              />

              <Detail
                label="Papel histórico"
                value={
                  traduzirPapel(
                    detalhe
                      ?.usuario
                      ?.papel_historico,
                  )
                }
              />

              <Detail
                label="Método"
                value={
                  detalhe
                    ?.operacao
                    ?.metodo
                }
              />

              <Detail
                label="Recurso"
                value={
                  detalhe
                    ?.operacao
                    ?.recurso
                  || '—'
                }
              />

              <Detail
                label="Entidade"
                value={
                  detalhe
                    ?.operacao
                    ?.entidade_id
                    ? `ID ${detalhe.operacao.entidade_id}`
                    : 'Não identificada'
                }
              />

              <Detail
                label="Caminho"
                value={
                  detalhe
                    ?.operacao
                    ?.caminho
                }
                full
              />

              <Detail
                label="Mensagem"
                value={
                  detalhe
                    ?.resultado
                    ?.mensagem
                  || 'Sem mensagem pública.'
                }
                full
              />

              <Detail
                label="IP"
                value={
                  detalhe
                    ?.origem
                    ?.ip
                  || 'Não informado'
                }
              />

              <Detail
                label="Request ID"
                value={
                  detalhe
                    ?.request_id
                  || '—'
                }
              />

              <Detail
                label="User-Agent"
                value={
                  detalhe
                    ?.origem
                    ?.user_agent
                  || 'Não informado'
                }
                full
              />
            </div>

            <section className="audit-security-note">
              <strong>
                Conteúdo sensível não é armazenado.
              </strong>

              <p>
                Este registro contém metadados da operação,
                não senha, JWT nem corpo completo da requisição.
              </p>
            </section>
          </div>
        ) : null}

        <footer className="modal-actions">
          <button
            type="button"
            className="button-secondary"
            onClick={onClose}
          >
            Fechar
          </button>
        </footer>
      </section>
    </div>
  )
}

function Detail({
  label,
  value,
  full = false,
}) {
  return (
    <article
      className={
        full
          ? 'audit-detail-item full'
          : 'audit-detail-item'
      }
    >
      <span>
        {label}
      </span>

      <strong>
        {value ?? '—'}
      </strong>
    </article>
  )
}

function Metric({
  label,
  value,
  tone = '',
}) {
  return (
    <article
      className={
        tone
          ? `audit-metric ${tone}`
          : 'audit-metric'
      }
    >
      <span>
        {label}
      </span>

      <strong>
        {value}
      </strong>
    </article>
  )
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
  }
}

function traduzirPapel(
  codigo,
) {
  const mapa = {
    ADMINISTRADOR:
      'Administrador',

    ORGANIZADOR:
      'Organizador',

    MEMBRO:
      'Membro',
  }

  return mapa[codigo]
    || codigo
    || 'Não informado'
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
