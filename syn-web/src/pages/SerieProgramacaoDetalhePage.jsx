import {
  useCallback,
  useEffect,
  useState,
} from 'react'

import {
  Link,
  useLocation,
  useNavigate,
  useParams,
} from 'react-router-dom'

import {
  desativarSerieProgramacao,
  getSerieProgramacao,
} from '../api/api'

import {
  useAuth,
} from '../contexts/AuthContext'

import './SerieProgramacaoDetalhePage.css'
import './SerieProgramacaoDetalheEtapa68.css'

export default function SerieProgramacaoDetalhePage() {
  const { id } = useParams()
  const navigate = useNavigate()
  const location = useLocation()
  const { capacidades } = useAuth()

  const [serie, setSerie] = useState(null)
  const [loading, setLoading] = useState(true)
  const [busy, setBusy] = useState(false)
  const [error, setError] = useState('')
  const [success, setSuccess] =
    useState(
      location?.state?.mensagem || '',
    )

  const podeGerenciar =
    Boolean(capacidades?.gerenciar_series)

  const carregar = useCallback(
    async () => {
      setLoading(true)
      setError('')

      try {
        const response =
          await getSerieProgramacao(id)

        setSerie(
          response?.dados ?? null,
        )
      } catch (err) {
        setError(
          err?.message
          || 'Não foi possível carregar a série.',
        )
      } finally {
        setLoading(false)
      }
    },
    [id],
  )

  useEffect(() => {
    if (podeGerenciar) {
      carregar()
    } else {
      setLoading(false)
    }
  }, [carregar, podeGerenciar])

  async function desativar() {
    if (!serie?.ativa || busy) {
      return
    }

    const confirmou =
      window.confirm(
        'Desativar esta série? As ocorrências já criadas permanecerão no sistema e não serão canceladas automaticamente.',
      )

    if (!confirmou) return

    setBusy(true)
    setError('')
    setSuccess('')

    try {
      const response =
        await desativarSerieProgramacao(
          serie.id,
        )

      setSuccess(
        response?.mensagem
        || 'Série desativada com sucesso.',
      )

      await carregar()
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível desativar a série.',
      )
    } finally {
      setBusy(false)
    }
  }

  if (!podeGerenciar) {
    return (
      <section className="panel">
        <h1>
          Programação recorrente
        </h1>

        <p className="empty-state">
          Seu usuário não possui permissão
          para consultar esta série.
        </p>
      </section>
    )
  }

  if (loading) {
    return (
      <div className="loading-card">
        Carregando série...
      </div>
    )
  }

  if (!serie) {
    return (
      <section className="panel">
        <h1>Série não encontrada</h1>

        {error && (
          <div className="error-message">
            {error}
          </div>
        )}

        <Link
          to="/gestao/series"
          className="text-link"
        >
          Voltar
        </Link>
      </section>
    )
  }

  const ocorrencias =
    Array.isArray(serie.ocorrencias)
      ? serie.ocorrencias
      : []

  const gruposPorSemana =
    agruparOcorrenciasPorSemana(
      ocorrencias,
    )

  const primeiraSemana =
    gruposPorSemana[0]
    ?? null

  const ultimaSemana =
    gruposPorSemana[
      gruposPorSemana.length - 1
    ]
    ?? null

  return (
    <div className="serie-detail-page">
      <Link
        to="/gestao/series"
        className="text-link"
      >
        ← Programações recorrentes
      </Link>

      <section className="serie-detail-hero">
        <div>
          <span className="eyebrow">
            Série semanal
          </span>

          <h1>{serie.titulo}</h1>

          <p>
            {serie.descricao || 'Sem descrição.'}
          </p>
        </div>

        <span
          className={
            serie.ativa
              ? 'series-status active'
              : 'series-status inactive'
          }
        >
          {serie.ativa ? 'Ativa' : 'Inativa'}
        </span>
      </section>

      {error && (
        <div className="error-message">
          {error}
        </div>
      )}

      {success && (
        <div className="success-message">
          {success}
        </div>
      )}

      <section className="serie-detail-summary">
        <Info
          label="Regra"
          value={textoRecorrencia(serie)}
        />

        <Info
          label="Data limite"
          value={formatarData(serie.data_limite)}
        />

        <Info
          label="Ocorrências"
          value={String(
            serie.total_ocorrencias
            ?? ocorrencias.length,
          )}
        />

        <Info
          label="Futuras"
          value={String(
            serie.total_ocorrencias_futuras
            ?? 0,
          )}
        />

        <Info
          label="Primeira semana"
          value={
            primeiraSemana
              ? `Semana ${primeiraSemana.numero}`
              : '—'
          }
        />

        <Info
          label="Última semana"
          value={
            ultimaSemana
              ? `Semana ${ultimaSemana.numero}`
              : '—'
          }
        />
      </section>

      <section className="serie-history-note">
        <strong>
          Cada ocorrência é independente.
        </strong>

        <p>
          Alterar ou cancelar uma ocorrência
          não modifica automaticamente as demais
          datas desta série.
        </p>
      </section>

      <section className="serie-occurrences-card">
        <header className="syn-section-heading">
          <div>
            <span className="eyebrow">
              Ocorrências
            </span>

            <h2>
              Datas materializadas
            </h2>
          </div>

          <span className="serie-occurrence-total">
            {ocorrencias.length}
          </span>
        </header>

        {ocorrencias.length === 0 ? (
          <p className="empty-state">
            Nenhuma ocorrência encontrada.
          </p>
        ) : (
          <div className="serie68-week-groups">
            {gruposPorSemana.map(
              (grupo) => (
                <section
                  key={grupo.chave}
                  className="serie68-week-group"
                >
                  <header className="serie68-week-heading">
                    <div className="serie68-week-number">
                      <span>
                        Semana
                      </span>

                      <strong>
                        {grupo.numero}
                      </strong>
                    </div>

                    <div className="serie68-week-copy">
                      <strong>
                        Semana {grupo.numero}
                      </strong>

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
                          ? 'ocorrência'
                          : 'ocorrências'}
                      </span>
                    </div>

                    <button
                      type="button"
                      className="serie68-map-button"
                      onClick={() =>
                        navigate(
                          `/semana?data_referencia=${grupo.inicio}`,
                        )
                      }
                    >
                      Ver no mapa
                    </button>
                  </header>

                  <div className="serie-occurrence-list serie68-occurrence-list">
                    {grupo.itens.map(
                      (item) => (
                        <article
                          key={item.id}
                          className="serie-occurrence-row serie68-occurrence-row"
                        >
                          <span className="serie-occurrence-date serie68-occurrence-date">
                            <strong>
                              {formatarDia(
                                item.inicio_em,
                              )}
                            </strong>

                            <small>
                              {formatarMes(
                                item.inicio_em,
                              )}
                            </small>

                            <em>
                              {formatarDiaSemanaCurto(
                                item.inicio_em,
                              )}
                            </em>
                          </span>

                          <span className="serie-occurrence-copy">
                            <strong>
                              {item.titulo}
                            </strong>

                            <span>
                              {formatarHora(item.inicio_em)}
                              {' — '}
                              {formatarHora(item.fim_em)}
                              {' · '}
                              {item.local
                                || 'Local não informado'}
                            </span>
                          </span>

                          <span
                            className={
                              `program-status status-${String(
                                item.status || '',
                              ).toLowerCase()}`
                            }
                          >
                            {traduzirStatus(item.status)}
                          </span>

                          <span className="serie-occurrence-actions serie68-actions">
                            <button
                              type="button"
                              className="small-secondary-button"
                              onClick={() =>
                                navigate(
                                  `/semana?data_referencia=${String(
                                    item.inicio_em,
                                  ).slice(0, 10)}`,
                                )
                              }
                            >
                              Mapa
                            </button>

                            <button
                              type="button"
                              className="small-secondary-button"
                              onClick={() =>
                                navigate(
                                  `/programacoes/${item.id}`,
                                )
                              }
                            >
                              Detalhes
                            </button>

                            <button
                              type="button"
                              className="small-primary-button"
                              onClick={() =>
                                navigate(
                                  `/gestao/programacoes/${item.id}/editar`,
                                )
                              }
                            >
                              {item.status === 'AGENDADA'
                                ? 'Editar'
                                : 'Consultar'}
                            </button>
                          </span>
                        </article>
                      ),
                    )}
                  </div>
                </section>
              ),
            )}
          </div>
        )}
      </section>

      <section className="serie-detail-actions">
        <div>
          <span className="eyebrow">
            Série
          </span>

          <h2>
            Gestão da recorrência
          </h2>

          <p>
            A regra da série não é editada na V1.
            Desativá-la não cancela as ocorrências
            que já foram materializadas.
          </p>
        </div>

        {serie.ativa && (
          <button
            type="button"
            className="program-cancel-button"
            disabled={busy}
            onClick={desativar}
          >
            {busy
              ? 'Desativando...'
              : 'Desativar série'}
          </button>
        )}
      </section>
    </div>
  )
}

function Info({ label, value }) {
  return (
    <article className="serie-info-card">
      <span>{label}</span>
      <strong>{value}</strong>
    </article>
  )
}

function textoRecorrencia(serie) {
  const intervalo =
    Number(
      serie
        ?.regra_recorrencia
        ?.intervalo_semanas,
    ) || 1

  const dia =
    formatarDiaSemana(serie.inicio_base)

  const hora =
    formatarHora(serie.inicio_base)

  return intervalo === 1
    ? `Toda ${dia}, às ${hora}`
    : `A cada ${intervalo} semanas, ${dia}, às ${hora}`
}

function parseDataHora(valor) {
  if (!valor) return null

  const data =
    new Date(
      String(valor).replace(' ', 'T'),
    )

  return Number.isNaN(data.getTime())
    ? null
    : data
}

function formatarDia(valor) {
  const data = parseDataHora(valor)

  return data
    ? String(data.getDate()).padStart(2, '0')
    : '--'
}

function formatarMes(valor) {
  const data = parseDataHora(valor)

  return data
    ? data
        .toLocaleDateString(
          'pt-BR',
          { month: 'short' },
        )
        .replace('.', '')
        .toUpperCase()
    : '---'
}

function formatarHora(valor) {
  return valor
    ? String(valor).slice(11, 16)
    : '--:--'
}

function formatarDiaSemana(valor) {
  const data = parseDataHora(valor)

  return data
    ? data.toLocaleDateString(
        'pt-BR',
        { weekday: 'long' },
      )
    : 'semana'
}

function formatarData(valor) {
  if (!valor) return '—'

  const [ano, mes, dia] =
    String(valor)
      .slice(0, 10)
      .split('-')
      .map(Number)

  return new Date(
    ano,
    mes - 1,
    dia,
  ).toLocaleDateString('pt-BR')
}

function agruparOcorrenciasPorSemana(
  itens,
) {
  const grupos =
    new Map()

  for (const item of itens) {
    const data =
      parseDataHora(
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
      new Date(
        inicio,
      )

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
    parseDataHora(
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

function traduzirStatus(status) {
  const mapa = {
    AGENDADA: 'Agendada',
    REALIZADA: 'Realizada',
    CANCELADA: 'Cancelada',
  }

  return mapa[status] || status
}
