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

export default function GestaoProgramacoesPage() {
  const navigate = useNavigate()

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

  const papel =
    usuario?.papel?.codigo

  const idsPermitidos =
    useMemo(
      () => new Set(
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
          .map(normalizarProgramacao)
          .filter((item) => {
            if (
              papel === 'ORGANIZADOR'
              && !idsPermitidos.has(
                item.tipo_programacao_id,
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
        programacoes,
        papel,
        idsPermitidos,
        busca,
      ],
    )

  return (
    <div className="management-page">
      <section className="management-hero">
        <span className="eyebrow">
          Gestão
        </span>

        <h1>
          Gerenciar programações
        </h1>

        <p>
          Escolha uma atividade para montar,
          revisar ou acompanhar sua escala.
        </p>
      </section>

      <section className="management-toolbar">
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
        <section className="management-list">
          {filtradas.map(
            (item) => (
              <button
                type="button"
                key={item.id}
                className="management-program-card"
                onClick={() =>
                  navigate(
                    `/gestao/programacoes/${item.id}/escala`,
                  )
                }
              >
                <div className="management-date">
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
                </div>

                <div className="management-program-main">
                  <span className="program-type">
                    {item.tipo}
                  </span>

                  <h2>
                    {item.titulo}
                  </h2>

                  <div className="management-meta">
                    <span>
                      {formatarHora(
                        item.inicio_em,
                      )}
                      {' — '}
                      {formatarHora(
                        item.fim_em,
                      )}
                    </span>

                    <span>
                      {
                        item.local
                        || 'Local não informado'
                      }
                    </span>
                  </div>
                </div>

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

                  <span className="management-open-label">
                    Gerenciar escala →
                  </span>
                </div>
              </button>
            ),
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
      || item?.quando?.inicio_em
      || '',

    fim_em:
      item?.fim_em
      || item?.quando?.fim_em
      || '',

    status:
      item?.status
      || 'AGENDADA',

    tipo_programacao_id:
      Number(
        item?.tipo_programacao_id
        ?? item?.tipo?.id
        ?? 0,
      ),

    tipo:
      item
        ?.tipo_programacao_nome_historico
      || item?.tipo?.nome
      || item?.tipo
      || 'Programação',

    local:
      item
        ?.local_nome_historico
      || item?.local?.nome
      || (
        typeof item?.local
          === 'string'
          ? item.local
          : ''
      ),
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
  if (!valor) {
    return '--:--'
  }

  return String(valor)
    .slice(11, 16)
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
