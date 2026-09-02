import {
  useEffect,
  useMemo,
  useState,
} from 'react'

import {
  Link,
  useNavigate,
} from 'react-router-dom'

import {
  getSeriesProgramacao,
} from '../api/api'

import {
  useAuth,
} from '../contexts/AuthContext'

import './SeriesProgramacaoPage.css'

export default function SeriesProgramacaoPage() {
  const navigate = useNavigate()
  const { usuario, bootstrap, capacidades } = useAuth()

  const [series, setSeries] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [busca, setBusca] = useState('')
  const [filtro, setFiltro] = useState('ATIVAS')

  const podeGerenciar =
    Boolean(capacidades?.gerenciar_series)

  const papel =
    usuario?.papel?.codigo

  const idsPermitidos = useMemo(
    () => new Set(
      (
        bootstrap
          ?.escopo_organizador
          ?.tipos_programacao
        ?? []
      ).map(
        (item) => Number(item.id),
      ),
    ),
    [bootstrap],
  )

  useEffect(() => {
    if (!podeGerenciar) {
      setLoading(false)
      return
    }

    let ativo = true

    async function carregar() {
      setLoading(true)
      setError('')

      try {
        const response =
          await getSeriesProgramacao()

        if (ativo) {
          setSeries(
            Array.isArray(response?.dados)
              ? response.dados
              : [],
          )
        }
      } catch (err) {
        if (ativo) {
          setError(
            err?.message
            || 'Não foi possível carregar as programações recorrentes.',
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
  }, [podeGerenciar])

  const normalizadas = useMemo(
    () => series
      .map(normalizarSerie)
      .filter(
        (serie) =>
          papel !== 'ORGANIZADOR'
          || idsPermitidos.has(
            serie.tipo_programacao_id,
          ),
      ),
    [series, papel, idsPermitidos],
  )

  const filtradas = useMemo(
    () => {
      const termo =
        busca.trim().toLocaleLowerCase('pt-BR')

      return normalizadas
        .filter(
          (serie) =>
            filtro === 'TODAS'
            || (
              filtro === 'ATIVAS'
                ? serie.ativa
                : !serie.ativa
            ),
        )
        .filter(
          (serie) =>
            !termo
            || [
              serie.titulo,
              serie.descricao,
            ]
              .filter(Boolean)
              .join(' ')
              .toLocaleLowerCase('pt-BR')
              .includes(termo),
        )
        .sort(
          (a, b) =>
            compararDataHora(
              a.inicio_base,
              b.inicio_base,
            ),
        )
    },
    [normalizadas, filtro, busca],
  )

  const resumo = useMemo(
    () => ({
      total:
        normalizadas.length,
      ativas:
        normalizadas.filter(
          (serie) => serie.ativa,
        ).length,
      futuras:
        normalizadas.reduce(
          (soma, serie) =>
            soma
            + serie.total_ocorrencias_futuras,
          0,
        ),
      ocorrencias:
        normalizadas.reduce(
          (soma, serie) =>
            soma
            + serie.total_ocorrencias,
          0,
        ),
    }),
    [normalizadas],
  )

  if (!podeGerenciar) {
    return (
      <section className="panel">
        <span className="eyebrow">
          Acesso restrito
        </span>

        <h1>
          Programações recorrentes
        </h1>

        <p className="empty-state">
          Seu usuário não possui permissão
          para administrar séries recorrentes.
        </p>
      </section>
    )
  }

  return (
    <div className="series-page">
      <Link
        to="/gestao/programacoes"
        className="text-link"
      >
        ← Gerenciar programações
      </Link>

      <section className="series-hero">
        <div>
          <span className="eyebrow">
            Recorrência
          </span>

          <h1>
            Programações recorrentes
          </h1>

          <p>
            Crie uma regra semanal e deixe o
            SYN gerar cada data como uma
            programação independente.
          </p>
        </div>

        <button
          type="button"
          className="button-primary"
          onClick={() =>
            navigate('/gestao/series/nova')
          }
        >
          + Nova série
        </button>
      </section>

      <section className="series-summary-grid">
        <Metric label="Séries" value={resumo.total} />
        <Metric label="Ativas" value={resumo.ativas} />
        <Metric label="Ocorrências futuras" value={resumo.futuras} />
        <Metric label="Ocorrências criadas" value={resumo.ocorrencias} />
      </section>

      <section className="series-toolbar">
        <label>
          <span>Buscar série</span>

          <input
            type="search"
            placeholder="Título ou descrição..."
            value={busca}
            onChange={(event) =>
              setBusca(event.target.value)
            }
          />
        </label>

        <label>
          <span>Status</span>

          <select
            value={filtro}
            onChange={(event) =>
              setFiltro(event.target.value)
            }
          >
            <option value="ATIVAS">Ativas</option>
            <option value="INATIVAS">Inativas</option>
            <option value="TODAS">Todas</option>
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
          Carregando séries...
        </div>
      ) : filtradas.length === 0 ? (
        <section className="panel">
          <p className="empty-state">
            Nenhuma série recorrente encontrada.
          </p>
        </section>
      ) : (
        <section className="series-list">
          {filtradas.map(
            (serie) => (
              <button
                type="button"
                key={serie.id}
                className={
                  serie.ativa
                    ? 'series-card'
                    : 'series-card inactive'
                }
                onClick={() =>
                  navigate(
                    `/gestao/series/${serie.id}`,
                  )
                }
              >
                <span className="series-calendar-mark">
                  <strong>
                    {formatarDia(serie.inicio_base)}
                  </strong>

                  <small>
                    {formatarMes(serie.inicio_base)}
                  </small>
                </span>

                <span className="series-card-copy">
                  <span className="series-card-topline">
                    <strong>
                      {serie.titulo}
                    </strong>

                    <span
                      className={
                        serie.ativa
                          ? 'series-status active'
                          : 'series-status inactive'
                      }
                    >
                      {serie.ativa ? 'Ativa' : 'Inativa'}
                    </span>
                  </span>

                  <span className="series-rule">
                    {textoRecorrencia(serie)}
                  </span>

                  <span className="series-card-meta">
                    <span>
                      {serie.total_ocorrencias}
                      {' '}ocorrência(s)
                    </span>

                    <span>
                      {serie.total_ocorrencias_futuras}
                      {' '}futura(s)
                    </span>

                    <span>
                      até {formatarData(serie.data_limite)}
                    </span>
                  </span>
                </span>

                <span className="series-card-arrow">
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

function Metric({ label, value }) {
  return (
    <article className="program-metric-card">
      <span>{label}</span>
      <strong>{value}</strong>
    </article>
  )
}

function normalizarSerie(item) {
  return {
    id: Number(item?.id) || 0,
    titulo: item?.titulo || 'Série recorrente',
    descricao: item?.descricao || '',
    inicio_base: item?.inicio_base || '',
    fim_base: item?.fim_base || '',
    data_limite: item?.data_limite || '',
    ativa:
      item?.ativa === true
      || item?.ativa === 1
      || item?.ativa === '1',
    regra_recorrencia:
      item?.regra_recorrencia ?? {},
    tipo_programacao_id:
      Number(item?.tipo_programacao_id) || 0,
    total_ocorrencias:
      Number(item?.total_ocorrencias) || 0,
    total_ocorrencias_futuras:
      Number(item?.total_ocorrencias_futuras) || 0,
  }
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
  if (!valor) {
    return null
  }

  const data =
    new Date(
      String(valor).replace(' ', 'T'),
    )

  return Number.isNaN(data.getTime())
    ? null
    : data
}

function compararDataHora(a, b) {
  const da = parseDataHora(a)
  const db = parseDataHora(b)

  if (!da && !db) return 0
  if (!da) return 1
  if (!db) return -1

  return da.getTime() - db.getTime()
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
