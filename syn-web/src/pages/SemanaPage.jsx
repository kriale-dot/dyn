import {
  useEffect,
  useMemo,
  useState,
} from 'react'

import {
  useNavigate,
} from 'react-router-dom'

import {
  getMapaSemana,
} from '../api/api'

export default function SemanaPage() {
  const navigate =
    useNavigate()

  const [dataReferencia, setDataReferencia] =
    useState(() => hojeISO())

  const [mapa, setMapa] =
    useState(null)

  const [loading, setLoading] =
    useState(true)

  const [error, setError] =
    useState('')

  useEffect(() => {
    let ativo = true

    async function carregar() {
      setLoading(true)
      setError('')

      try {
        const response =
          await getMapaSemana(
            dataReferencia,
          )

        if (ativo) {
          setMapa(
            response?.dados
            ?? null,
          )
        }
      } catch (err) {
        if (ativo) {
          setError(
            err?.message
            || 'Não foi possível carregar a semana.',
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
  }, [dataReferencia])

  const periodo = useMemo(
    () => {
      const inicio =
        mapa?.semana?.inicio

      const fim =
        mapa?.semana?.fim

      if (!inicio || !fim) {
        return ''
      }

      return `${formatarDataCurta(
        inicio,
      )} — ${formatarDataCurta(fim)}`
    },
    [mapa],
  )

  function mudarSemana(
    quantidadeSemanas,
  ) {
    setDataReferencia(
      adicionarDiasISO(
        dataReferencia,
        quantidadeSemanas * 7,
      ),
    )
  }

  function voltarHoje() {
    setDataReferencia(
      hojeISO(),
    )
  }

  function abrirProgramacao(
    programacaoId,
  ) {
    navigate(
      `/programacoes/${programacaoId}`,
    )
  }

  return (
    <div className="week-page">
      <section className="week-header">
        <div>
          <span className="eyebrow">
            Mapa da semana
          </span>

          <h1>
            O que acontece e onde você participa
          </h1>

          <p>
            Seus compromissos aparecem em destaque,
            sem transformar a rotina da igreja em uma planilha.
          </p>
        </div>

        <div className="week-controls">
          <button
            type="button"
            className="week-nav-button"
            onClick={() =>
              mudarSemana(-1)
            }
            aria-label="Semana anterior"
          >
            ←
          </button>

          <button
            type="button"
            className="button-secondary"
            onClick={voltarHoje}
          >
            Hoje
          </button>

          <button
            type="button"
            className="week-nav-button"
            onClick={() =>
              mudarSemana(1)
            }
            aria-label="Próxima semana"
          >
            →
          </button>
        </div>
      </section>

      <section className="week-period-card">
        <div>
          <span className="eyebrow">
            Período
          </span>

          <strong>
            {periodo || 'Carregando...'}
          </strong>
        </div>

        {mapa?.resumo && (
          <div className="week-summary-inline">
            <WeekMetric
              value={
                mapa.resumo
                  .meus_compromissos
                ?? 0
              }
              label="meus compromissos"
            />

            <WeekMetric
              value={
                mapa.resumo
                  .participacoes_pendentes
                ?? 0
              }
              label="a confirmar"
            />

            <WeekMetric
              value={
                mapa.resumo
                  .programacoes
                ?? 0
              }
              label="programações"
            />
          </div>
        )}
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
          Carregando o mapa da semana...
        </div>
      ) : (
        <section className="week-map">
          {(mapa?.dias ?? []).map(
            (dia) => (
              <DayRow
                key={dia.data}
                dia={dia}
                onOpen={
                  abrirProgramacao
                }
              />
            ),
          )}
        </section>
      )}
    </div>
  )
}

function DayRow({
  dia,
  onOpen,
}) {
  const programacoes =
    dia?.programacoes
    ?? []

  const temMeuCompromisso =
    Boolean(
      dia?.tem_meu_compromisso,
    )

  return (
    <article
      className={
        temMeuCompromisso
          ? 'week-day has-personal'
          : 'week-day'
      }
    >
      <div className="week-day-marker">
        <span className="week-day-dot" />

        <span className="week-day-line" />
      </div>

      <div className="week-day-content">
        <header className="week-day-header">
          <div>
            <strong>
              {dia?.dia_semana}
            </strong>

            <span>
              {formatarDataExtensa(
                dia?.data,
              )}
            </span>
          </div>

          {temMeuCompromisso && (
            <span className="personal-badge">
              Você participa
            </span>
          )}
        </header>

        {programacoes.length === 0 ? (
          <div className="week-empty">
            Nenhuma programação neste dia.
          </div>
        ) : (
          <div className="week-events">
            {programacoes.map(
              (programacao) => (
                <ProgramacaoCard
                  key={programacao.id}
                  programacao={
                    programacao
                  }
                  onOpen={() =>
                    onOpen(
                      programacao.id,
                    )
                  }
                />
              ),
            )}
          </div>
        )}
      </div>
    </article>
  )
}

function ProgramacaoCard({
  programacao,
  onOpen,
}) {
  const quando =
    programacao?.quando
    ?? {}

  const pessoal =
    Boolean(
      programacao
        ?.destaque
        ?.pessoal,
    )

  const proximo =
    Boolean(
      programacao
        ?.destaque
        ?.proximo_compromisso,
    )

  const participacoes =
    programacao
      ?.minhas_participacoes
    ?? []

  return (
    <button
      type="button"
      className={[
        'program-card',
        'program-card-button',
        pessoal
          ? 'personal'
          : '',
        proximo
          ? 'next'
          : '',
      ]
        .filter(Boolean)
        .join(' ')}
      onClick={onOpen}
    >
      <div className="program-time">
        <strong>
          {formatarHora(
            quando.inicio_em,
          )}
        </strong>

        <span>
          até{' '}
          {formatarHora(
            quando.fim_em,
          )}
        </span>
      </div>

      <div className="program-main">
        <div className="program-title-row">
          <div>
            <span className="program-type">
              {
                programacao
                  ?.o_que
                  ?.tipo
              }
            </span>

            <h3>
              {
                programacao
                  ?.o_que
                  ?.titulo
              }
            </h3>
          </div>

          {proximo && (
            <span className="next-badge">
              Próximo compromisso
            </span>
          )}
        </div>

        <div className="program-location">
          <span className="map-pin">
            ●
          </span>

          <strong>
            {
              programacao
                ?.onde
                ?.local
              || 'Local não informado'
            }
          </strong>
        </div>

        {pessoal && (
          <div className="my-role-box">
            <span>
              Sua participação
            </span>

            <div className="my-role-list">
              {participacoes.map(
                (item) => (
                  <div
                    key={item.id}
                    className="my-role-item"
                  >
                    <strong>
                      {item.funcao}
                    </strong>

                    <span
                      className={
                        item
                          .pendente_confirmacao
                          ? 'status-pill pending'
                          : 'status-pill'
                      }
                    >
                      {
                        item
                          .pendente_confirmacao
                          ? 'A confirmar'
                          : item.status
                      }
                    </span>
                  </div>
                ),
              )}
            </div>
          </div>
        )}
      </div>
    </button>
  )
}

function WeekMetric({
  value,
  label,
}) {
  return (
    <div className="week-metric">
      <strong>{value}</strong>
      <span>{label}</span>
    </div>
  )
}

function hojeISO() {
  return formatarISO(
    new Date(),
  )
}

function adicionarDiasISO(
  iso,
  dias,
) {
  const data =
    novaDataLocal(iso)

  data.setDate(
    data.getDate() + dias,
  )

  return formatarISO(data)
}

function formatarISO(data) {
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

function novaDataLocal(iso) {
  if (!iso) {
    return new Date()
  }

  const [
    ano,
    mes,
    dia,
  ] =
    iso
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

function formatarDataCurta(iso) {
  return novaDataLocal(iso)
    .toLocaleDateString(
      'pt-BR',
      {
        day: '2-digit',
        month: 'short',
      },
    )
}

function formatarDataExtensa(iso) {
  return novaDataLocal(iso)
    .toLocaleDateString(
      'pt-BR',
      {
        day: '2-digit',
        month: 'long',
      },
    )
}

function formatarHora(
  dataHora,
) {
  if (!dataHora) {
    return '--:--'
  }

  const hora =
    dataHora
      .slice(11, 16)

  return hora || '--:--'
}
