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

import {
  useAuth,
} from '../contexts/AuthContext'

import './GestaoProgramacoesEtapa48.css'
import './GestaoProgramacoesEtapa49.css'
import './GestaoProgramacoesEtapa59.css'

export default function GestaoProgramacoesPage() {
  const navigate =
    useNavigate()

  const {
    usuario,
    bootstrap,
  } = useAuth()

  const [programacoes, setProgramacoes] =
    useState([])

  const [loading, setLoading] =
    useState(true)

  const [error, setError] =
    useState('')

  const [busca, setBusca] =
    useState('')

  const [statusFiltro, setStatusFiltro] =
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
          response?.dados
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

  const papel =
    usuario?.papel?.codigo

  const idsPermitidos =
    useMemo(
      () =>
        new Set(
          (
            bootstrap
              ?.escopo_organizador
              ?.tipos_programacao
            ?? []
          ).map(
            (item) =>
              Number(item.id),
          ),
        ),
      [bootstrap],
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

        return programacoes
          .map(
            normalizarProgramacao,
          )
          .filter(
            (item) => {
              if (
                papel
                  === 'ORGANIZADOR'
                && !idsPermitidos.has(
                  item
                    .tipo_programacao_id,
                )
              ) {
                return false
              }

              if (
                statusFiltro !== 'TODAS'
                && item.status
                  !== statusFiltro
              ) {
                return false
              }

              if (!termo) {
                return true
              }

              return [
                item.titulo,
                item.tipo,
                item.local,
              ]
                .filter(Boolean)
                .join(' ')
                .toLocaleLowerCase(
                  'pt-BR',
                )
                .includes(termo)
            },
          )
          .sort(
            (a, b) =>
              compararDataHora(
                a.inicio_em,
                b.inicio_em,
              ),
          )
      },
      [
        programacoes,
        papel,
        idsPermitidos,
        busca,
        statusFiltro,
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

  return (
    <div className="management-page">
      <section className="management-hero management-hero-actions">
        <div>
          <span className="eyebrow">
            Gestão
          </span>

          <h1>
            Gerenciar programações
          </h1>

          <p>
            Crie uma atividade, ajuste seus
            dados ou abra a escala.
          </p>
        </div>

        <div className="management-hero-button-group">
          <button
            type="button"
            className="button-secondary"
            onClick={() =>
              navigate(
                '/gestao/escalas-semana',
              )
            }
          >
            Escalas da semana
          </button>

          <button
            type="button"
            className="button-secondary"
            onClick={() =>
              navigate('/gestao/series')
            }
          >
            Programações recorrentes
          </button>

          <button
            type="button"
            className="button-primary"
            onClick={() =>
              navigate('/gestao/programacoes/nova')
            }
          >
            + Nova programação
          </button>
        </div>
      </section>

      <section className="management-week-reference">
        <div>
          <span className="eyebrow">
            Semana atual
          </span>

          <strong>
            Semana {semanaAtual.numero}
          </strong>

          <small>
            {formatarDataCurtaISO(
              semanaAtual.inicio,
            )}
            {' — '}
            {formatarDataCurtaISO(
              semanaAtual.fim,
            )}
          </small>
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
          Ver Semana {semanaAtual.numero} no mapa
        </button>
      </section>

      <section className="management-toolbar management-toolbar-59">
        <label>
          <span>
            Buscar programação
          </span>

          <input
            type="search"
            placeholder="Título, tipo ou local..."
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
            Status
          </span>

          <select
            value={statusFiltro}
            onChange={(event) =>
              setStatusFiltro(
                event.target.value,
              )
            }
          >
            <option value="TODAS">
              Todas
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
      </section>

      {error && (
        <div className="error-message">
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
            Nenhuma programação disponível
            para sua gestão.
          </p>
        </section>
      ) : (
        <section className="management-week-groups">
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
                      ? 'management-week-group current'
                      : 'management-week-group'
                  }
                >
                  <header className="management-week-heading">
                    <div className="management-week-number">
                      <span>
                        Semana
                      </span>

                      <strong>
                        {grupo.numero}
                      </strong>
                    </div>

                    <div className="management-week-copy">
                      <div>
                        <strong>
                          Semana {grupo.numero}
                        </strong>

                        {atual && (
                          <span className="management-current-week-badge">
                            Atual
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
                      className="management-week-map-button"
                      onClick={() =>
                        navigate(
                          `/semana?data_referencia=${grupo.inicio}`,
                        )
                      }
                    >
                      Ver no mapa
                    </button>
                  </header>

                  <div className="management-list management-list-59">
                    {grupo.itens.map(
                      (item) => (
                        <article
                          key={item.id}
                          className="management-program-card management-program-article"
                        >
                          <div className="management-date management-date-59">
                            <strong>
                              {formatarDia(
                                item.inicio_em,
                              )}
                            </strong>

                            <span>
                              {formatarMes(
                                item.inicio_em,
                              )}
                            </span>

                            <small>
                              {formatarDiaSemanaCurto(
                                item.inicio_em,
                              )}
                            </small>
                          </div>

                          <button
                            type="button"
                            className="management-program-copy-button"
                            onClick={() =>
                              navigate(
                                `/programacoes/${item.id}`,
                              )
                            }
                          >
                            <span className="program-type">
                              {item.tipo}
                            </span>

                            <strong className="management-program-title">
                              {item.titulo}
                            </strong>

                            <span className="management-meta">
                              <span>
                                <b>Horário</b>
                                {formatarHora(
                                  item.inicio_em,
                                )}
                                {' — '}
                                {formatarHora(
                                  item.fim_em,
                                )}
                              </span>

                              <span>
                                <b>Local</b>
                                {item.local
                                  || 'Local não informado'}
                              </span>
                            </span>
                          </button>

                          <div className="management-program-action">
                            <span
                              className={
                                `program-status status-${String(
                                  item.status
                                  || '',
                                ).toLowerCase()}`
                              }
                            >
                              {traduzirStatus(
                                item.status,
                              )}
                            </span>

                            <div className="management-action-buttons">
                              <button
                                type="button"
                                className="small-secondary-button"
                                onClick={() =>
                                  navigate(
                                    `/gestao/programacoes/${item.id}/editar`,
                                  )
                                }
                              >
                                {item.status
                                  === 'AGENDADA'
                                    ? 'Editar'
                                    : 'Consultar'}
                              </button>

                              <button
                                type="button"
                                className="small-primary-button"
                                onClick={() =>
                                  navigate(
                                    `/gestao/programacoes/${item.id}/escala`,
                                  )
                                }
                              >
                                Gerenciar escala
                              </button>
                            </div>
                          </div>
                        </article>
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

function normalizarProgramacao(
  item,
) {
  return {
    id:
      Number(item?.id)
      || 0,

    titulo:
      item?.titulo
      || 'Programação',

    inicio_em:
      item?.inicio_em
      || '',

    fim_em:
      item?.fim_em
      || '',

    status:
      item?.status
      || 'AGENDADA',

    tipo_programacao_id:
      Number(
        item
          ?.tipo_programacao
          ?.id
        ?? item
          ?.tipo_programacao_id
        ?? 0,
      ),

    tipo:
      item
        ?.tipo_programacao
        ?.nome_historico
      ?? item
        ?.tipo_programacao_nome_historico
      ?? 'Programação',

    local:
      item
        ?.local
        ?.nome_historico
      ?? item
        ?.local_nome_historico
      ?? 'Local não informado',
  }
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

function compararDataHora(
  a,
  b,
) {
  const da =
    parseDataHoraLocal(a)

  const db =
    parseDataHoraLocal(b)

  if (!da && !db) {
    return 0
  }

  if (!da) {
    return 1
  }

  if (!db) {
    return -1
  }

  return da.getTime()
    - db.getTime()
}

function formatarDia(
  valor,
) {
  const data =
    parseDataHoraLocal(valor)

  return data
    ? String(
        data.getDate(),
      ).padStart(2, '0')
    : '--'
}

function formatarMes(
  valor,
) {
  const data =
    parseDataHoraLocal(valor)

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
  valor,
) {
  return valor
    ? String(valor)
        .slice(11, 16)
    : '--:--'
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
  ).sort(
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
  valor,
) {
  const data =
    parseDataHoraLocal(
      valor,
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
