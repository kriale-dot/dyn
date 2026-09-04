import {
  useEffect,
  useState,
} from 'react'

import {
  useNavigate,
  useParams,
} from 'react-router-dom'

import {
  getIgrejaPublica,
  getProgramacaoPublica,
  resolveApiAssetUrl,
} from '../api/api'

import {
  useAuth,
} from '../contexts/AuthContext'

import PublicChurchInfo
  from '../components/PublicChurchInfo'

import './PublicProgramacaoDetalhePage.css'
import './PublicProgramacaoDetalheEtapa75.css'
import './PublicProgramacaoDetalheEtapa76.css'

export default function PublicProgramacaoDetalhePage() {
  const {
    id,
  } = useParams()

  const navigate =
    useNavigate()

  const {
    isAuthenticated,
  } = useAuth()

  const [igreja, setIgreja] =
    useState(null)

  const [
    programacao,
    setProgramacao,
  ] =
    useState(null)

  const [loading, setLoading] =
    useState(true)

  const [error, setError] =
    useState('')

  const [copiado, setCopiado] =
    useState(false)

  useEffect(() => {
    let ativo = true

    async function carregar() {
      setLoading(true)
      setError('')

      try {
        const [
          igrejaResponse,
          programacaoResponse,
        ] =
          await Promise.all([
            getIgrejaPublica(),
            getProgramacaoPublica(
              id,
            ),
          ])

        if (!ativo) {
          return
        }

        setIgreja(
          igrejaResponse?.dados
          ?? null,
        )

        setProgramacao(
          programacaoResponse?.dados
          ?? null,
        )
      } catch (err) {
        if (!ativo) {
          return
        }

        setError(
          err?.status === 404
            ? 'Esta programação não está disponível publicamente.'
            : err?.message
              || 'Não foi possível carregar a programação.',
        )
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
  }, [id])

  async function compartilhar() {
    const url =
      window.location.href

    const titulo =
      programacao
        ?.o_que
        ?.titulo
      || 'Programação da igreja'

    if (navigator.share) {
      try {
        await navigator.share({
          title: titulo,
          text: titulo,
          url,
        })
        return
      } catch (err) {
        if (err?.name === 'AbortError') {
          return
        }
      }
    }

    await copiarLink()
  }

  async function copiarLink() {
    const url =
      window.location.href

    try {
      await navigator.clipboard.writeText(url)

      setCopiado(true)

      window.setTimeout(
        () => setCopiado(false),
        1800,
      )
    } catch {
      window.prompt(
        'Copie o link da programação:',
        url,
      )
    }
  }

  const logo =
    resolveApiAssetUrl(
      igreja?.logotipo,
    )

  if (loading) {
    return (
      <main className="public-detail74-page">
        <div className="public-detail74-loading">
          Carregando programação...
        </div>
      </main>
    )
  }

  return (
    <main className="public-detail74-page">
      <header className="public-detail74-topbar">
        <button
          type="button"
          className="public-detail74-back"
          onClick={() =>
            navigate(-1)
          }
        >
          ← Voltar ao mapa
        </button>

        <div className="public-detail74-brand">
          {logo ? (
            <img
              src={logo}
              alt=""
            />
          ) : (
            <span>
              SYN
            </span>
          )}

          <strong>
            {igreja?.nome
              || 'Igreja'}
          </strong>
        </div>

        <button
          type="button"
          className="public-detail74-member"
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
      </header>

      {error ? (
        <section className="public-detail74-error">
          <span>
            Programação pública
          </span>

          <h1>
            Não disponível
          </h1>

          <p>
            {error}
          </p>

          <button
            type="button"
            onClick={() =>
              navigate('/publico')
            }
          >
            Ver programação pública
          </button>
        </section>
      ) : (
        <article className="public-detail74-card">
          <div className="public-detail74-type">
            {programacao
              ?.o_que
              ?.tipo
              || 'Programação'}
          </div>

          <div className="public-detail74-title-row">
            <div>
              <h1>
                {programacao
                  ?.o_que
                  ?.titulo
                  || 'Programação'}
              </h1>

              <PublicDetailStatus
                status={
                  programacao?.status
                }
              />
            </div>
          </div>

          <section className="public-detail74-info-grid">
            <InfoCard
              label="Data"
              value={
                formatarData(
                  programacao
                    ?.quando
                    ?.inicio_em,
                )
              }
            />

            <InfoCard
              label="Horário"
              value={
                `${formatarHora(
                  programacao
                    ?.quando
                    ?.inicio_em,
                )} — ${formatarHora(
                  programacao
                    ?.quando
                    ?.fim_em,
                )}`
              }
            />

            <InfoCard
              label="Local"
              value={
                programacao
                  ?.onde
                  ?.local
                || 'Local não informado'
              }
            />

            <InfoCard
              label="Semana"
              value={
                programacao
                  ?.quando
                  ?.inicio_em
                ? `Semana ${
                    obterNumeroSemanaISO(
                      String(
                        programacao
                          .quando
                          .inicio_em,
                      ).slice(0, 10),
                    )
                  }`
                : '—'
              }
            />
          </section>

          {programacao
            ?.o_que
            ?.descricao && (
            <section className="public-detail74-description">
              <span>
                Sobre a programação
              </span>

              <p>
                {programacao
                  .o_que
                  .descricao}
              </p>
            </section>
          )}

          <footer className="public-detail74-actions public75-detail-actions">
            <button
              type="button"
              onClick={() =>
                navigate(
                  `/publico?data_referencia=${
                    String(
                      programacao
                        ?.quando
                        ?.inicio_em
                      || '',
                    ).slice(0, 10)
                  }`,
                )
              }
            >
              Ver esta data no mapa
            </button>

            <button
              type="button"
              onClick={compartilhar}
            >
              Compartilhar
            </button>

            <button
              type="button"
              onClick={copiarLink}
            >
              {copiado
                ? 'Link copiado'
                : 'Copiar link'}
            </button>
          </footer>
        </article>
      )}

      <section className="public76-detail-info">
        <PublicChurchInfo
          igreja={igreja}
          compact
        />
      </section>
    </main>
  )
}

function InfoCard({
  label,
  value,
}) {
  return (
    <div className="public-detail74-info">
      <span>
        {label}
      </span>

      <strong>
        {value}
      </strong>
    </div>
  )
}

function PublicDetailStatus({
  status,
}) {
  const texto = {
    AGENDADA:
      'Programada',
    REALIZADA:
      'Realizada',
    CANCELADA:
      'Cancelada',
  }[status]
  || status
  || 'Programação'

  return (
    <span
      className={
        `public-detail74-status status-${
          String(
            status || '',
          ).toLowerCase()
        }`
      }
    >
      {texto}
    </span>
  )
}

function formatarData(
  valor,
) {
  if (!valor) {
    return '—'
  }

  const iso =
    String(valor)
      .slice(0, 10)

  const data =
    novaDataLocal(
      iso,
    )

  const diaSemana =
    data.toLocaleDateString(
      'pt-BR',
      {
        weekday: 'long',
      },
    )

  const dataTexto =
    data.toLocaleDateString(
      'pt-BR',
      {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
      },
    )

  return `${
    diaSemana
      .charAt(0)
      .toUpperCase()
    + diaSemana.slice(1)
  }, ${dataTexto}`
}

function formatarHora(
  valor,
) {
  return valor
    ? String(
        valor,
      ).slice(11, 16)
    : '--:--'
}

function novaDataLocal(
  iso,
) {
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

function obterNumeroSemanaISO(
  iso,
) {
  const data =
    novaDataLocal(
      iso,
    )

  const utc =
    new Date(
      Date.UTC(
        data.getFullYear(),
        data.getMonth(),
        data.getDate(),
      ),
    )

  const diaSemana =
    utc.getUTCDay()
    || 7

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
