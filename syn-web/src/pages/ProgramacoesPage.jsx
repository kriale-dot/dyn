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

import './ProgramacoesPageEtapa58.css'

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

  const gruposPorSemana =
    useMemo(
      () =>
        agruparPorSemana(
          filtradas,
        ),
      [filtradas],
    )

  const semanaAtual =
    useMemo(
      () => {
        const hoje =
          new Date()

        const inicio =
          inicioSemanaISO(
            hoje,
          )

        const fim =
          new Date(inicio)

        fim.setDate(
          fim.getDate() + 6,
        )

        return {
          numero:
            obterNumeroSemanaISO(
              inicio,
            ),

          ano:
            obterAnoSemanaISO(
              inicio,
            ),

          inicio:
            formatarISO(
              inicio,
            ),

          fim:
            formatarISO(
              fim,
            ),
        }
      },
      [],
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

      <section className="programs-week-focus">
        <div className="programs-week-focus-main">
          <span className="eyebrow">
            Referência temporal
          </span>

          <div className="programs-week-focus-title">
            <strong>
              Semana {semanaAtual.numero}
            </strong>

            <span>
              {formatarDataCurtaISO(
                semanaAtual.inicio,
              )}
              {' — '}
              {formatarDataCurtaISO(
                semanaAtual.fim,
              )}
            </span>
          </div>

          <p>
            Esta é a semana atual no mapa do SYN.
            A listagem abaixo também passa a ser
            organizada por número da semana.
          </p>
        </div>

        <button
          type="button"
          className="button-secondary"
          onClick={() =>
            navigate(
              `/semana?data_referencia=${semanaAtual.inicio}`,
            )
          }
        >
          Abrir Semana {semanaAtual.numero} no mapa
        </button>
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
        <section className="program-week-groups">
          {gruposPorSemana.map(
            (grupo) => {
              const atual =
                grupo.numero
                  === semanaAtual.numero
                && grupo.ano
                  === semanaAtual.ano

              return (
                <section
                  key={grupo.chave}
                  className={
                    atual
                      ? 'program-week-group current'
                      : 'program-week-group'
                  }
                >
                  <header className="program-week-heading">
                    <div className="program-week-number">
                      <span>
                        Semana
                      </span>

                      <strong>
                        {grupo.numero}
                      </strong>
                    </div>

                    <div className="program-week-copy">
                      <div>
                        <strong>
                          Semana {grupo.numero}
                        </strong>

                        {atual && (
                          <span className="program-current-week-badge">
                            Semana atual
                          </span>
                        )}
                      </div>

                      <span>
                        {formatarDataCurtaISO(
                          grupo.inicio,
                        )}
                        {' — '}
                        {formatarDataCurtaISO(
                          grupo.fim,
                        )}
                        {' · '}
                        {grupo.itens.length}
                        {' '}
                        {grupo.itens.length === 1
                          ? 'programação'
                          : 'programações'}
                      </span>
                    </div>

                    <button
                      type="button"
                      className="program-week-map-button"
                      onClick={() =>
                        navigate(
                          `/semana?data_referencia=${grupo.inicio}`,
                        )
                      }
                    >
                      Ver no mapa
                    </button>
                  </header>

                  <div className="programs-list">
                    {grupo.itens.map(
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

                            <small>
                              {formatarDiaSemanaCurto(
                                programacao.inicio_em,
                              )}
                            </small>
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
                                <b>Horário</b>
                                {formatarHora(
                                  programacao.inicio_em,
                                )}
                                {' – '}
                                {formatarHora(
                                  programacao.fim_em,
                                )}
                              </span>

                              <span>
                                <b>Local</b>
                                {
                                  programacao.local
                                  || 'Local não informado'
                                }
                              </span>

                              {programacao.organizador && (
                                <span>
                                  <b>Organizador</b>
                                  {programacao.organizador}
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
                  </div>
                </section>
              )
            },
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

function agruparPorSemana(
  itens,
) {
  const grupos =
    new Map()

  for (const item of itens) {
    const data =
      parseDataHoraLocal(
        item.inicio_em,
      )

    if (!data) {
      continue
    }

    const inicio =
      inicioSemanaISO(
        data,
      )

    const fim =
      new Date(inicio)

    fim.setDate(
      fim.getDate() + 6,
    )

    const numero =
      obterNumeroSemanaISO(
        inicio,
      )

    const ano =
      obterAnoSemanaISO(
        inicio,
      )

    const chave =
      `${ano}-W${String(
        numero,
      ).padStart(2, '0')}`

    if (!grupos.has(chave)) {
      grupos.set(
        chave,
        {
          chave,
          numero,
          ano,
          inicio:
            formatarISO(
              inicio,
            ),
          fim:
            formatarISO(
              fim,
            ),
          timestamp:
            inicio.getTime(),
          itens: [],
        },
      )
    }

    grupos
      .get(chave)
      .itens
      .push(item)
  }

  return Array.from(
    grupos.values(),
  )
    .sort(
      (a, b) =>
        a.timestamp
        - b.timestamp,
    )
}

function inicioSemanaISO(
  dataOriginal,
) {
  const data =
    new Date(
      dataOriginal.getFullYear(),
      dataOriginal.getMonth(),
      dataOriginal.getDate(),
      12,
      0,
      0,
    )

  const dia =
    data.getDay()

  const deslocamento =
    dia === 0
      ? -6
      : 1 - dia

  data.setDate(
    data.getDate()
    + deslocamento,
  )

  return data
}

function obterNumeroSemanaISO(
  dataOriginal,
) {
  const data =
    new Date(
      Date.UTC(
        dataOriginal.getFullYear(),
        dataOriginal.getMonth(),
        dataOriginal.getDate(),
      ),
    )

  const diaSemana =
    data.getUTCDay()
    || 7

  data.setUTCDate(
    data.getUTCDate()
    + 4
    - diaSemana,
  )

  const primeiroDiaAno =
    new Date(
      Date.UTC(
        data.getUTCFullYear(),
        0,
        1,
      ),
    )

  return Math.ceil(
    (
      (
        data
        - primeiroDiaAno
      )
      / 86400000
      + 1
    )
    / 7,
  )
}

function obterAnoSemanaISO(
  dataOriginal,
) {
  const data =
    new Date(
      Date.UTC(
        dataOriginal.getFullYear(),
        dataOriginal.getMonth(),
        dataOriginal.getDate(),
      ),
    )

  const diaSemana =
    data.getUTCDay()
    || 7

  data.setUTCDate(
    data.getUTCDate()
    + 4
    - diaSemana,
  )

  return data.getUTCFullYear()
}

function formatarISO(
  data,
) {
  const ano =
    data.getFullYear()

  const mes =
    String(
      data.getMonth() + 1,
    ).padStart(2, '0')

  const dia =
    String(
      data.getDate(),
    ).padStart(2, '0')

  return `${ano}-${mes}-${dia}`
}

function formatarDataCurtaISO(
  iso,
) {
  if (!iso) {
    return '—'
  }

  const [
    ano,
    mes,
    dia,
  ] =
    String(iso)
      .slice(0, 10)
      .split('-')
      .map(Number)

  return new Date(
    ano,
    mes - 1,
    dia,
    12,
    0,
    0,
  )
    .toLocaleDateString(
      'pt-BR',
      {
        day: '2-digit',
        month: 'short',
      },
    )
    .replace('.', '')
}

function formatarDiaSemanaCurto(
  dataHora,
) {
  const data =
    parseDataHoraLocal(
      dataHora,
    )

  if (!data) {
    return ''
  }

  return data
    .toLocaleDateString(
      'pt-BR',
      {
        weekday: 'short',
      },
    )
    .replace('.', '')
    .toUpperCase()
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
