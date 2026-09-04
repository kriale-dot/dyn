import {
  useEffect,
  useMemo,
  useState,
} from 'react'

import {
  useNavigate,
} from 'react-router-dom'

import {
  getIgrejaPublica,
  getProgramacoesPublicas,
  resolveApiAssetUrl,
} from '../api/api'

import {
  useAuth,
} from '../contexts/AuthContext'

import PublicChurchInfo
  from '../components/PublicChurchInfo'

import './PublicProgramacoesPage.css'
import './PublicProgramacoesPageEtapa76.css'

export default function PublicProgramacoesPage() {
  const navigate = useNavigate()
  const { isAuthenticated } = useAuth()

  const [igreja, setIgreja] = useState(null)
  const [programacoes, setProgramacoes] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [busca, setBusca] = useState('')
  const [copiadoId, setCopiadoId] = useState(null)

  useEffect(() => {
    let ativo = true

    async function carregar() {
      setLoading(true)
      setError('')

      try {
        const [
          igrejaResponse,
          programacoesResponse,
        ] = await Promise.all([
          getIgrejaPublica(),
          getProgramacoesPublicas(),
        ])

        if (!ativo) return

        setIgreja(
          igrejaResponse?.dados ?? null,
        )

        const lista =
          programacoesResponse
            ?.dados
            ?.programacoes

        setProgramacoes(
          Array.isArray(lista)
            ? lista
            : [],
        )
      } catch (err) {
        if (ativo) {
          setError(
            err?.message
            || 'Não foi possível carregar as próximas programações.',
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

  const filtradas = useMemo(
    () => {
      const termo =
        busca.trim()
          .toLocaleLowerCase('pt-BR')

      if (!termo) {
        return programacoes
      }

      return programacoes.filter(
        (item) => {
          const texto = [
            item?.o_que?.titulo,
            item?.o_que?.tipo,
            item?.o_que?.descricao,
            item?.onde?.local,
          ]
            .filter(Boolean)
            .join(' ')
            .toLocaleLowerCase('pt-BR')

          return texto.includes(termo)
        },
      )
    },
    [programacoes, busca],
  )

  const grupos = useMemo(
    () => agruparPorSemana(filtradas),
    [filtradas],
  )

  const logo =
    resolveApiAssetUrl(
      igreja?.logotipo,
    )

  async function compartilhar(item) {
    const url =
      `${window.location.origin}/publico/programacoes/${item.id}`

    const titulo =
      item?.o_que?.titulo
      || 'Programação da igreja'

    const texto =
      `${titulo} — ${formatarDataHoraCurta(
        item?.quando?.inicio_em,
      )}`

    if (navigator.share) {
      try {
        await navigator.share({
          title: titulo,
          text: texto,
          url,
        })
        return
      } catch (err) {
        if (err?.name === 'AbortError') {
          return
        }
      }
    }

    await copiarLink(item.id, url)
  }

  async function copiarLink(
    id,
    url = null,
  ) {
    const destino =
      url
      || `${window.location.origin}/publico/programacoes/${id}`

    try {
      await navigator.clipboard.writeText(destino)

      setCopiadoId(Number(id))

      window.setTimeout(
        () => setCopiadoId(null),
        1800,
      )
    } catch {
      window.prompt(
        'Copie o link da programação:',
        destino,
      )
    }
  }

  return (
    <main className="public75-page">
      <header className="public75-header">
        <div className="public75-brand">
          {logo ? (
            <img
              src={logo}
              alt=""
            />
          ) : (
            <span>SYN</span>
          )}

          <div>
            <small>
              Programação pública
            </small>

            <strong>
              {igreja?.nome || 'Igreja'}
            </strong>
          </div>
        </div>

        <div className="public75-header-actions">
          <button
            type="button"
            onClick={() =>
              navigate('/publico')
            }
          >
            Mapa da semana
          </button>

          <button
            type="button"
            className="member"
            onClick={() =>
              navigate(
                isAuthenticated
                  ? '/inicio'
                  : '/login',
              )
            }
          >
            {isAuthenticated
              ? 'Área de membros'
              : 'Entrar'}
          </button>
        </div>
      </header>

      <section className="public75-hero">
        <span>
          Próximas programações
        </span>

        <h1>
          O que vem pela frente
        </h1>

        <p>
          Veja as programações que a igreja
          disponibilizou publicamente para os
          próximos dias e semanas.
        </p>
      </section>

      <section className="public75-toolbar">
        <label>
          <span>Buscar</span>

          <input
            type="search"
            placeholder="Culto, encontro, local..."
            value={busca}
            onChange={(event) =>
              setBusca(event.target.value)
            }
          />
        </label>

        <div className="public75-total">
          <strong>
            {filtradas.length}
          </strong>

          <span>
            {filtradas.length === 1
              ? 'programação encontrada'
              : 'programações encontradas'}
          </span>
        </div>
      </section>

      {error && (
        <div className="public75-error">
          {error}
        </div>
      )}

      {loading ? (
        <section className="public75-loading">
          Carregando próximas programações...
        </section>
      ) : grupos.length === 0 ? (
        <section className="public75-empty">
          <strong>
            Nenhuma programação pública encontrada.
          </strong>

          <p>
            Tente outro termo de busca ou volte ao
            Mapa da Semana.
          </p>

          <button
            type="button"
            onClick={() =>
              navigate('/publico')
            }
          >
            Voltar ao mapa
          </button>
        </section>
      ) : (
        <section className="public75-groups">
          {grupos.map(
            (grupo) => (
              <section
                key={grupo.chave}
                className="public75-week-group"
              >
                <header>
                  <div className="public75-week-number">
                    <span>Semana</span>
                    <strong>{grupo.numero}</strong>
                  </div>

                  <div>
                    <strong>
                      Semana {grupo.numero}
                    </strong>

                    <span>
                      {formatarDataCurta(
                        grupo.inicio,
                      )}
                      {' — '}
                      {formatarDataCurta(
                        grupo.fim,
                      )}
                    </span>
                  </div>

                  <button
                    type="button"
                    onClick={() =>
                      navigate(
                        `/publico?data_referencia=${grupo.inicio}`,
                      )
                    }
                  >
                    Ver no mapa
                  </button>
                </header>

                <div className="public75-list">
                  {grupo.itens.map(
                    (item) => (
                      <article
                        key={item.id}
                        className={
                          item.status === 'CANCELADA'
                            ? 'public75-card cancelled'
                            : 'public75-card'
                        }
                      >
                        <div className="public75-card-date">
                          <strong>
                            {formatarDia(
                              item?.quando?.inicio_em,
                            )}
                          </strong>

                          <span>
                            {formatarMes(
                              item?.quando?.inicio_em,
                            )}
                          </span>

                          <small>
                            {formatarDiaSemanaCurto(
                              item?.quando?.inicio_em,
                            )}
                          </small>
                        </div>

                        <div className="public75-card-main">
                          <div className="public75-card-topline">
                            <div>
                              <span>
                                {item?.o_que?.tipo
                                  || 'Programação'}
                              </span>

                              <h2>
                                {item?.o_que?.titulo
                                  || 'Programação'}
                              </h2>
                            </div>

                            <Status
                              status={item.status}
                            />
                          </div>

                          <div className="public75-card-meta">
                            <span>
                              <strong>Horário</strong>
                              {' '}
                              {formatarHora(
                                item?.quando?.inicio_em,
                              )}
                            </span>

                            <span>
                              <strong>Local</strong>
                              {' '}
                              {item?.onde?.local
                                || 'Não informado'}
                            </span>
                          </div>

                          {item?.o_que?.descricao && (
                            <p>
                              {item.o_que.descricao}
                            </p>
                          )}

                          <footer>
                            <button
                              type="button"
                              onClick={() =>
                                navigate(
                                  `/publico/programacoes/${item.id}`,
                                )
                              }
                            >
                              Ver detalhes
                            </button>

                            <button
                              type="button"
                              onClick={() =>
                                compartilhar(item)
                              }
                            >
                              Compartilhar
                            </button>

                            <button
                              type="button"
                              onClick={() =>
                                copiarLink(item.id)
                              }
                            >
                              {copiadoId === Number(item.id)
                                ? 'Link copiado'
                                : 'Copiar link'}
                            </button>
                          </footer>
                        </div>
                      </article>
                    ),
                  )}
                </div>
              </section>
            ),
          )}
        </section>
      )}
      <section className="public76-list-info">
        <PublicChurchInfo
          igreja={igreja}
          compact
        />
      </section>
    </main>
  )
}

function Status({ status }) {
  const mapa = {
    AGENDADA: ['Programada', 'active'],
    REALIZADA: ['Realizada', 'done'],
    CANCELADA: ['Cancelada', 'cancelled'],
  }

  const [
    texto,
    classe,
  ] = mapa[status]
    || [
      status || 'Programação',
      'active',
    ]

  return (
    <span
      className={
        `public75-status ${classe}`
      }
    >
      {texto}
    </span>
  )
}

function agruparPorSemana(itens) {
  const mapa = new Map()

  for (const item of itens) {
    const iso =
      String(
        item?.quando?.inicio_em || '',
      ).slice(0, 10)

    if (!iso) continue

    const data = novaDataLocal(iso)
    const inicio = inicioSemanaISO(data)
    const fim = new Date(inicio)

    fim.setDate(
      fim.getDate() + 6,
    )

    const numero =
      obterNumeroSemanaISO(
        formatarISO(inicio),
      )

    const ano =
      obterAnoSemanaISO(inicio)

    const chave =
      `${ano}-W${String(numero).padStart(2, '0')}`

    if (!mapa.has(chave)) {
      mapa.set(
        chave,
        {
          chave,
          numero,
          ano,
          inicio: formatarISO(inicio),
          fim: formatarISO(fim),
          timestamp: inicio.getTime(),
          itens: [],
        },
      )
    }

    mapa.get(chave).itens.push(item)
  }

  return Array.from(
    mapa.values(),
  ).sort(
    (a, b) =>
      a.timestamp - b.timestamp,
  )
}

function inicioSemanaISO(dataOriginal) {
  const data =
    new Date(
      dataOriginal.getFullYear(),
      dataOriginal.getMonth(),
      dataOriginal.getDate(),
      12,
      0,
      0,
    )

  const dia = data.getDay()
  const deslocamento =
    dia === 0
      ? -6
      : 1 - dia

  data.setDate(
    data.getDate() + deslocamento,
  )

  return data
}

function obterNumeroSemanaISO(iso) {
  const data = novaDataLocal(iso)

  const utc =
    new Date(
      Date.UTC(
        data.getFullYear(),
        data.getMonth(),
        data.getDate(),
      ),
    )

  const diaSemana =
    utc.getUTCDay() || 7

  utc.setUTCDate(
    utc.getUTCDate()
    + 4
    - diaSemana,
  )

  const primeiroDiaAno =
    new Date(
      Date.UTC(
        utc.getUTCFullYear(),
        0,
        1,
      ),
    )

  return Math.ceil(
    (
      (
        utc
        - primeiroDiaAno
      )
      / 86400000
      + 1
    )
    / 7,
  )
}

function obterAnoSemanaISO(dataOriginal) {
  const utc =
    new Date(
      Date.UTC(
        dataOriginal.getFullYear(),
        dataOriginal.getMonth(),
        dataOriginal.getDate(),
      ),
    )

  const diaSemana =
    utc.getUTCDay() || 7

  utc.setUTCDate(
    utc.getUTCDate()
    + 4
    - diaSemana,
  )

  return utc.getUTCFullYear()
}

function novaDataLocal(iso) {
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
}

function formatarISO(data) {
  const ano = data.getFullYear()
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

function formatarDataCurta(iso) {
  return novaDataLocal(iso)
    .toLocaleDateString(
      'pt-BR',
      {
        day: '2-digit',
        month: 'short',
      },
    )
    .replace('.', '')
}

function parseDataHora(valor) {
  if (!valor) return null

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

function formatarDia(valor) {
  const data =
    parseDataHora(valor)

  return data
    ? String(data.getDate())
        .padStart(2, '0')
    : '--'
}

function formatarMes(valor) {
  const data =
    parseDataHora(valor)

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

function formatarHora(valor) {
  return valor
    ? String(valor).slice(11, 16)
    : '--:--'
}

function formatarDiaSemanaCurto(valor) {
  const data =
    parseDataHora(valor)

  return data
    ? data
        .toLocaleDateString(
          'pt-BR',
          {
            weekday: 'short',
          },
        )
        .replace('.', '')
        .toUpperCase()
    : ''
}

function formatarDataHoraCurta(valor) {
  const data =
    parseDataHora(valor)

  if (!data) {
    return 'data a confirmar'
  }

  return `${
    data
      .toLocaleDateString(
        'pt-BR',
        {
          day: '2-digit',
          month: 'short',
        },
      )
      .replace('.', '')
  }, ${formatarHora(valor)}`
}
