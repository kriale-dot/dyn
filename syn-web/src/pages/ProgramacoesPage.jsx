import {
  useEffect,
  useMemo,
  useState,
} from 'react'

import {
  useNavigate,
} from 'react-router-dom'

import {
  getProgramacoes,
} from '../api/api'

export default function ProgramacoesPage() {
  const navigate = useNavigate()

  const [programacoes, setProgramacoes] =
    useState([])

  const [loading, setLoading] =
    useState(true)

  const [error, setError] =
    useState('')

  const [busca, setBusca] =
    useState('')

  const [status, setStatus] =
    useState('TODOS')

  const [periodo, setPeriodo] =
    useState('TODAS')

  useEffect(() => {
    let ativo = true

    async function carregar() {
      setLoading(true)
      setError('')

      try {
        const response =
          await getProgramacoes()

        const lista =
          response?.dados?.programacoes
          ?? response?.dados
          ?? response?.programacoes
          ?? []

        if (ativo) {
          setProgramacoes(
            Array.isArray(lista)
              ? lista
              : [],
          )
        }
      } catch (err) {
        if (ativo) {
          setError(
            err?.message
            || 'Não foi possível carregar as programações.',
          )
        }
      } finally {
        if (ativo) {
          setLoading(false)
        }
      }
    }

    carregar()

    return () => {
      ativo = false
    }
  }, [])

  const normalizadas =
    useMemo(
      () =>
        programacoes.map(
          normalizarProgramacao,
        ),
      [programacoes],
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

        return normalizadas
          .filter((item) => {
            if (
              status !== 'TODOS'
              && item.status !== status
            ) {
              return false
            }

            if (
              !estaNoPeriodo(
                item.inicio_em,
                periodo,
              )
            ) {
              return false
            }

            if (!termo) {
              return true
            }

            const texto =
              [
                item.titulo,
                item.tipo,
                item.local,
                item.organizador,
                item.descricao,
              ]
                .filter(Boolean)
                .join(' ')
                .toLocaleLowerCase(
                  'pt-BR',
                )

            return texto.includes(
              termo,
            )
          })
          .sort(
            (a, b) =>
              compararDataHora(
                a.inicio_em,
                b.inicio_em,
              ),
          )
      },
      [
        normalizadas,
        busca,
        status,
        periodo,
      ],
    )

  const contadores =
    useMemo(
      () => ({
        total:
          normalizadas.length,

        agendadas:
          normalizadas.filter(
            (item) =>
              item.status
              === 'AGENDADA',
          ).length,

        realizadas:
          normalizadas.filter(
            (item) =>
              item.status
              === 'REALIZADA',
          ).length,

        canceladas:
          normalizadas.filter(
            (item) =>
              item.status
              === 'CANCELADA',
          ).length,
      }),
      [normalizadas],
    )

  return (
    <div className="programs-page">
      <section className="programs-header">
        <span className="eyebrow">
          Programação geral
        </span>

        <h1>
          O que acontece na igreja
        </h1>

        <p>
          Consulte atividades, horários,
          locais e responsáveis sem misturar
          essa visão com seus compromissos pessoais.
        </p>
      </section>

      <section className="programs-summary-grid">
        <ProgramMetric
          label="Total"
          value={contadores.total}
        />

        <ProgramMetric
          label="Agendadas"
          value={contadores.agendadas}
        />

        <ProgramMetric
          label="Realizadas"
          value={contadores.realizadas}
        />

        <ProgramMetric
          label="Canceladas"
          value={contadores.canceladas}
        />
      </section>

      <section className="program-filters">
        <label>
          <span>Buscar programação</span>

          <input
            type="search"
            value={busca}
            placeholder="Título, tipo, local..."
            onChange={(event) =>
              setBusca(
                event.target.value,
              )
            }
          />
        </label>

        <label>
          <span>Status</span>

          <select
            value={status}
            onChange={(event) =>
              setStatus(
                event.target.value,
              )
            }
          >
            <option value="TODOS">
              Todos
            </option>
            <option value="AGENDADA">
              Agendadas
            </option>
            <option value="REALIZADA">
              Realizadas
            </option>
            <option value="CANCELADA">
              Canceladas
            </option>
          </select>
        </label>

        <label>
          <span>Período</span>

          <select
            value={periodo}
            onChange={(event) =>
              setPeriodo(
                event.target.value,
              )
            }
          >
            <option value="TODAS">
              Todas
            </option>
            <option value="HOJE">
              Hoje
            </option>
            <option value="SEMANA">
              Esta semana
            </option>
            <option value="MES">
              Este mês
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

      {loading ? (
        <div className="loading-card">
          Carregando programações...
        </div>
      ) : filtradas.length === 0 ? (
        <section className="panel">
          <p className="empty-state">
            Nenhuma programação encontrada
            para os filtros selecionados.
          </p>
        </section>
      ) : (
        <section className="programs-list">
          {filtradas.map(
            (programacao) => (
              <button
                type="button"
                key={programacao.id}
                className="program-list-card"
                onClick={() =>
                  navigate(
                    `/programacoes/${programacao.id}`,
                  )
                }
              >
                <div className="program-list-date">
                  <strong>
                    {formatarDia(
                      programacao.inicio_em,
                    )}
                  </strong>

                  <span>
                    {formatarMes(
                      programacao.inicio_em,
                    )}
                  </span>
                </div>

                <div className="program-list-main">
                  <div className="program-list-title-row">
                    <div>
                      <span className="program-type">
                        {programacao.tipo}
                      </span>

                      <h2>
                        {programacao.titulo}
                      </h2>
                    </div>

                    <StatusBadge
                      status={
                        programacao.status
                      }
                    />
                  </div>

                  <div className="program-list-meta">
                    <span>
                      🕒{' '}
                      {formatarHora(
                        programacao.inicio_em,
                      )}
                      {' – '}
                      {formatarHora(
                        programacao.fim_em,
                      )}
                    </span>

                    <span>
                      📍{' '}
                      {
                        programacao.local
                        || 'Local não informado'
                      }
                    </span>

                    {programacao
                      .organizador && (
                      <span>
                        👤{' '}
                        {
                          programacao
                            .organizador
                        }
                      </span>
                    )}
                  </div>
                </div>

                <span className="program-list-arrow">
                  →
                </span>
              </button>
            ),
          )}
        </section>
      )}
    </div>
  )
}

function ProgramMetric({
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

function StatusBadge({
  status,
}) {
  return (
    <span
      className={
        `program-status status-${String(
          status || '',
        ).toLowerCase()}`
      }
    >
      {traduzirStatus(status)}
    </span>
  )
}

function normalizarProgramacao(
  item,
) {
  return {
    id:
      Number(item?.id)
      || 0,

    titulo:
      item?.titulo
      || 'Programação sem título',

    descricao:
      item?.descricao
      || '',

    inicio_em:
      item?.inicio_em
      || item?.quando?.inicio_em
      || '',

    fim_em:
      item?.fim_em
      || item?.quando?.fim_em
      || '',

    status:
      item?.status
      || 'AGENDADA',

    /**
     * A listagem GET /programacoes devolve os nomes históricos
     * dentro de objetos aninhados:
     *
     * tipo_programacao.nome_historico
     * local.nome_historico
     * organizador.nome_historico
     *
     * Mantemos também os formatos antigos como fallback para
     * tornar o frontend tolerante a pequenas diferenças de payload.
     */
    tipo:
      item
        ?.tipo_programacao
        ?.nome_historico
      || item
        ?.tipo_programacao
        ?.nome
      || item
        ?.tipo_programacao_nome_historico
      || item?.tipo?.nome_historico
      || item?.tipo?.nome
      || (
        typeof item?.tipo
          === 'string'
          ? item.tipo
          : ''
      )
      || 'Programação',

    local:
      item
        ?.local
        ?.nome_historico
      || item?.local?.nome
      || item
        ?.local_nome_historico
      || (
        typeof item?.local
          === 'string'
          ? item.local
          : ''
      ),

    organizador:
      item
        ?.organizador
        ?.nome_historico
      || item?.organizador?.nome
      || item
        ?.organizador_nome_historico
      || (
        typeof item?.organizador
          === 'string'
          ? item.organizador
          : ''
      ),
  }
}

function estaNoPeriodo(
  dataHora,
  periodo,
) {
  if (
    periodo === 'TODAS'
    || !dataHora
  ) {
    return true
  }

  const data =
    parseDataHoraLocal(
      dataHora,
    )

  if (!data) {
    return false
  }

  const agora =
    new Date()

  const hojeInicio =
    new Date(
      agora.getFullYear(),
      agora.getMonth(),
      agora.getDate(),
      0,
      0,
      0,
    )

  if (periodo === 'HOJE') {
    const amanha =
      new Date(hojeInicio)

    amanha.setDate(
      amanha.getDate() + 1,
    )

    return (
      data >= hojeInicio
      && data < amanha
    )
  }

  if (periodo === 'SEMANA') {
    const inicio =
      new Date(hojeInicio)

    const dia =
      inicio.getDay()

    const deslocamento =
      dia === 0
        ? -6
        : 1 - dia

    inicio.setDate(
      inicio.getDate()
      + deslocamento,
    )

    const fim =
      new Date(inicio)

    fim.setDate(
      fim.getDate() + 7,
    )

    return (
      data >= inicio
      && data < fim
    )
  }

  if (periodo === 'MES') {
    return (
      data.getFullYear()
        === agora.getFullYear()
      && data.getMonth()
        === agora.getMonth()
    )
  }

  return true
}

function compararDataHora(
  a,
  b,
) {
  const dataA =
    parseDataHoraLocal(a)

  const dataB =
    parseDataHoraLocal(b)

  if (!dataA && !dataB) {
    return 0
  }

  if (!dataA) {
    return 1
  }

  if (!dataB) {
    return -1
  }

  return (
    dataA.getTime()
    - dataB.getTime()
  )
}

function parseDataHoraLocal(
  valor,
) {
  if (!valor) {
    return null
  }

  const data =
    new Date(
      String(valor)
        .replace(' ', 'T'),
    )

  return Number.isNaN(
    data.getTime(),
  )
    ? null
    : data
}

function formatarDia(
  dataHora,
) {
  const data =
    parseDataHoraLocal(dataHora)

  return data
    ? String(
        data.getDate(),
      ).padStart(2, '0')
    : '--'
}

function formatarMes(
  dataHora,
) {
  const data =
    parseDataHoraLocal(dataHora)

  return data
    ? data
        .toLocaleDateString(
          'pt-BR',
          {
            month: 'short',
          },
        )
        .replace('.', '')
        .toUpperCase()
    : '---'
}

function formatarHora(
  dataHora,
) {
  if (!dataHora) {
    return '--:--'
  }

  const texto =
    String(dataHora)

  if (texto.length >= 16) {
    return texto.slice(11, 16)
  }

  return '--:--'
}

function traduzirStatus(
  status,
) {
  const mapa = {
    AGENDADA: 'Agendada',
    REALIZADA: 'Realizada',
    CANCELADA: 'Cancelada',
  }

  return mapa[status]
    || status
}
