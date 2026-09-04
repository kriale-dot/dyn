import {
  useCallback,
  useEffect,
  useMemo,
  useState,
} from 'react'

import {
  useNavigate,
  useSearchParams,
} from 'react-router-dom'

import {
  getIgrejaPublica,
  getMapaSemanaPublico,
  getProgramacoesPublicas,
  resolveApiAssetUrl,
} from '../api/api'

import {
  useAuth,
} from '../contexts/AuthContext'

import PublicChurchInfo
  from '../components/PublicChurchInfo'

import './PublicHomePage.css'
import './PublicHomePageEtapa75.css'
import './PublicHomePageEtapa76.css'
import './PublicHomePageEtapa78.css'
import './PublicHomePageEtapa79.css'
import './PublicHomePageEtapa81.css'

export default function PublicHomePage({
  entradaPrincipal = false,
}) {
  const navigate =
    useNavigate()

  const {
    isAuthenticated,
  } = useAuth()

  const [
    searchParams,
    setSearchParams,
  ] =
    useSearchParams()

  const referenciaUrl =
    dataISOValida(
      searchParams.get(
        'data_referencia',
      ),
    )

  const [
    dataReferencia,
    setDataReferencia,
  ] =
    useState(
      () =>
        referenciaUrl
        || hojeISO(),
    )

  const [igreja, setIgreja] =
    useState(null)

  const [mapa, setMapa] =
    useState(null)

  const [
    proximasProgramacoes,
    setProximasProgramacoes,
  ] =
    useState([])

  const [
    diaSelecionado,
    setDiaSelecionado,
  ] =
    useState(null)

  const [loading, setLoading] =
    useState(true)

  const [error, setError] =
    useState('')

  const [
    copiadoSemana,
    setCopiadoSemana,
  ] =
    useState(false)

  const carregar =
    useCallback(
      async () => {
        setLoading(true)
        setError('')

        try {
          const [
            igrejaResponse,
            mapaResponse,
          ] =
            await Promise.all([
              getIgrejaPublica(),
              getMapaSemanaPublico(
                dataReferencia,
              ),
            ])

          setIgreja(
            igrejaResponse?.dados
            ?? null,
          )

          setMapa(
            mapaResponse?.dados
            ?? null,
          )
        } catch (err) {
          setError(
            err?.message
            || 'Não foi possível carregar a programação pública.',
          )
        } finally {
          setLoading(false)
        }
      },
      [dataReferencia],
    )

  useEffect(() => {
    carregar()
  }, [carregar])

  useEffect(() => {
    let ativo = true

    async function carregarProximas() {
      try {
        const response =
          await getProgramacoesPublicas()

        if (!ativo) {
          return
        }

        const lista =
          response
            ?.dados
            ?.programacoes

        setProximasProgramacoes(
          Array.isArray(lista)
            ? lista
            : [],
        )
      } catch {
        /**
         * A área "Próxima programação" é um aprimoramento.
         * Se ela falhar, o mapa semanal continua funcionando.
         */
        if (ativo) {
          setProximasProgramacoes([])
        }
      }
    }

    carregarProximas()

    return () => {
      ativo = false
    }
  }, [])

  useEffect(() => {
    const referencia =
      dataISOValida(
        searchParams.get(
          'data_referencia',
        ),
      )

    if (
      referencia
      && referencia
        !== dataReferencia
    ) {
      setDataReferencia(
        referencia,
      )
    }
  }, [
    searchParams,
    dataReferencia,
  ])

  useEffect(() => {
    const dias =
      mapa?.dias
      ?? []

    if (dias.length === 0) {
      setDiaSelecionado(null)
      return
    }

    const referenciaExplicita =
      dataISOValida(
        searchParams.get(
          'data_referencia',
        ),
      )

    const diaDaUrl =
      referenciaExplicita
        ? dias.find(
            (dia) =>
              dia.data
              === referenciaExplicita,
          )
        : null

    if (diaDaUrl) {
      setDiaSelecionado(
        diaDaUrl.data,
      )
      return
    }

    const hoje =
      hojeISO()

    const diaHoje =
      dias.find(
        (dia) =>
          dia.data === hoje,
      )

    const primeiroComProgramacao =
      dias.find(
        (dia) =>
          dia.tem_programacao,
      )

    setDiaSelecionado(
      diaHoje?.data
      ?? primeiroComProgramacao?.data
      ?? dias[0]?.data
      ?? null,
    )
  }, [
    mapa,
    searchParams,
  ])

  const diaAtual =
    useMemo(
      () =>
        (mapa?.dias ?? [])
          .find(
            (dia) =>
              dia.data
              === diaSelecionado,
          )
        ?? null,
      [
        mapa,
        diaSelecionado,
      ],
    )

  const programacoesHoje =
    useMemo(
      () => {
        const hoje =
          hojeISO()

        return proximasProgramacoes
          .filter(
            (item) =>
              String(
                item
                  ?.quando
                  ?.inicio_em
                || '',
              ).slice(
                0,
                10,
              ) === hoje,
          )
          .sort(
            (a, b) =>
              String(
                a
                  ?.quando
                  ?.inicio_em
                || '',
              ).localeCompare(
                String(
                  b
                    ?.quando
                    ?.inicio_em
                  || '',
                ),
              ),
          )
      },
      [
        proximasProgramacoes,
      ],
    )

  const programacaoAgora =
    useMemo(
      () => {
        const agora =
          new Date()

        return programacoesHoje
          .find(
            (item) => {
              if (
                item?.status
                  !== 'AGENDADA'
              ) {
                return false
              }

              const inicio =
                novaDataHoraLocal(
                  item
                    ?.quando
                    ?.inicio_em,
                )

              const fim =
                novaDataHoraLocal(
                  item
                    ?.quando
                    ?.fim_em,
                )

              if (
                Number.isNaN(
                  inicio.getTime(),
                )
                || Number.isNaN(
                  fim.getTime(),
                )
              ) {
                return false
              }

              return (
                inicio <= agora
                && fim > agora
              )
            },
          )
        ?? null
      },
      [
        programacoesHoje,
      ],
    )

  const proximaProgramacao =
    useMemo(
      () => {
        const agora =
          new Date()

        return proximasProgramacoes
          .filter(
            (item) =>
              item?.status
                === 'AGENDADA'
              && dataHoraValida(
                item
                  ?.quando
                  ?.inicio_em,
              )
              && novaDataHoraLocal(
                item
                  .quando
                  .inicio_em,
              ) >= agora,
          )
          .sort(
            (a, b) =>
              novaDataHoraLocal(
                a
                  .quando
                  .inicio_em,
              )
              - novaDataHoraLocal(
                  b
                    .quando
                    .inicio_em,
                ),
          )[0]
          ?? null
      },
      [
        proximasProgramacoes,
      ],
    )

  const periodo =
    useMemo(
      () => {
        const inicio =
          mapa?.semana?.inicio

        const fim =
          mapa?.semana?.fim

        if (!inicio || !fim) {
          return ''
        }

        return `${
          formatarDataCurta(inicio)
        } — ${
          formatarDataCurta(fim)
        }`
      },
      [mapa],
    )

  const numeroSemana =
    mapa?.semana?.numero_iso
    ?? (
      mapa?.semana?.inicio
        ? obterNumeroSemanaISO(
            mapa.semana.inicio,
          )
        : null
    )

  const logo =
    resolveApiAssetUrl(
      igreja?.logotipo,
    )

  function mudarSemana(
    quantidade,
  ) {
    const proxima =
      adicionarDiasISO(
        dataReferencia,
        quantidade * 7,
      )

    setDataReferencia(
      proxima,
    )

    setDiaSelecionado(null)

    setSearchParams({
      data_referencia:
        proxima,
    })
  }

  function voltarHoje() {
    const hoje =
      hojeISO()

    setDataReferencia(
      hoje,
    )

    setDiaSelecionado(null)

    setSearchParams({
      data_referencia:
        hoje,
    })
  }

  async function compartilharSemana() {
    const inicio =
      mapa?.semana?.inicio

    const url =
      inicio
        ? `${window.location.origin}/publico?data_referencia=${inicio}`
        : `${window.location.origin}/publico`

    const titulo =
      `Programação da ${
        igreja?.nome
        || 'igreja'
      }`

    const texto =
      numeroSemana
        ? `${titulo} — Semana ${numeroSemana}`
        : titulo

    if (navigator.share) {
      try {
        await navigator.share({
          title: titulo,
          text: texto,
          url,
        })

        return
      } catch (err) {
        if (
          err?.name
          === 'AbortError'
        ) {
          return
        }
      }
    }

    await copiarSemana(
      url,
    )
  }

  async function copiarSemana(
    url = null,
  ) {
    const inicio =
      mapa?.semana?.inicio

    const destino =
      url
      || (
        inicio
          ? `${window.location.origin}/publico?data_referencia=${inicio}`
          : `${window.location.origin}/publico`
      )

    try {
      await navigator
        .clipboard
        .writeText(
          destino,
        )

      setCopiadoSemana(
        true,
      )

      window.setTimeout(
        () =>
          setCopiadoSemana(
            false,
          ),
        1800,
      )
    } catch {
      window.prompt(
        'Copie o link da semana:',
        destino,
      )
    }
  }

  function selecionarDia(
    data,
  ) {
    setDiaSelecionado(
      data,
    )

    setSearchParams({
      data_referencia:
        data,
    })
  }

  return (
    <main className="public74-page">
      <header className="public74-topbar">
        <div className="public74-brand">
          {logo ? (
            <img
              src={logo}
              alt={
                igreja?.nome
                  ? `Logotipo ${igreja.nome}`
                  : 'Logotipo da igreja'
              }
            />
          ) : (
            <div className="public74-logo-placeholder">
              SYN
            </div>
          )}

          <div>
            <span>
              Programação pública
            </span>

            <strong>
              {igreja?.nome
                || 'Igreja'}
            </strong>
          </div>
        </div>

        <div className="public75-top-actions">
          {!isAuthenticated && (
            <button
              type="button"
              className="public81-register-button"
              onClick={() =>
                navigate(
                  '/cadastro',
                )
              }
            >
              Solicitar cadastro
            </button>
          )}

          <button
            type="button"
            className="public75-upcoming-button"
            onClick={() =>
              navigate(
                '/publico/programacoes',
              )
            }
          >
            Próximas programações
          </button>

          <button
            type="button"
            className="public74-member-button"
            onClick={() =>
              navigate(
                isAuthenticated
                  ? '/inicio'
                  : '/login',
              )
            }
          >
            {isAuthenticated
              ? 'Voltar à área de membros'
              : 'Entrar na área de membros'}
          </button>
        </div>
      </header>

      <section className="public74-hero">
        <div>
          <span className="public74-eyebrow">
            Mapa público da semana
          </span>

          <h1>
            O que está acontecendo
            nesta semana?
          </h1>

          <p>
            Consulte os cultos, encontros e demais
            programações que a igreja disponibilizou
            publicamente. Não é necessário fazer login.
          </p>
        </div>

        <div className="public74-week-controls">
          <button
            type="button"
            onClick={() =>
              mudarSemana(-1)
            }
            aria-label="Semana anterior"
          >
            ←
          </button>

          <button
            type="button"
            className="today"
            onClick={voltarHoje}
          >
            Hoje
          </button>

          <button
            type="button"
            onClick={() =>
              mudarSemana(1)
            }
            aria-label="Próxima semana"
          >
            →
          </button>
        </div>
      </section>

      {proximaProgramacao && (
        <section className="public78-next">
          <div className="public78-next-marker">
            <span>
              Próxima
            </span>

            <strong>
              {formatarDiaNumero(
                proximaProgramacao
                  ?.quando
                  ?.inicio_em,
              )}
            </strong>

            <small>
              {formatarMesDataHora(
                proximaProgramacao
                  ?.quando
                  ?.inicio_em,
              )}
            </small>
          </div>

          <div className="public78-next-main">
            <span className="public78-next-eyebrow">
              Próxima programação pública
            </span>

            <h2>
              {proximaProgramacao
                ?.o_que
                ?.titulo
                || 'Programação'}
            </h2>

            <div className="public78-next-meta">
              <span>
                <strong>
                  {formatarDiaSemanaDataHora(
                    proximaProgramacao
                      ?.quando
                      ?.inicio_em,
                  )}
                </strong>

                {' · '}

                {formatarHora(
                  proximaProgramacao
                    ?.quando
                    ?.inicio_em,
                )}
              </span>

              <span>
                {proximaProgramacao
                  ?.onde
                  ?.local
                  || 'Local não informado'}
              </span>

              <span>
                {proximaProgramacao
                  ?.o_que
                  ?.tipo
                  || 'Programação'}
              </span>
            </div>
          </div>

          <div className="public78-next-actions">
            <button
              type="button"
              className="primary"
              onClick={() =>
                navigate(
                  `/publico/programacoes/${
                    proximaProgramacao.id
                  }`,
                )
              }
            >
              Ver detalhes
            </button>

            <button
              type="button"
              onClick={() =>
                navigate(
                  `/publico?data_referencia=${
                    String(
                      proximaProgramacao
                        ?.quando
                        ?.inicio_em
                      || '',
                    ).slice(0, 10)
                  }`,
                )
              }
            >
              Ver no mapa
            </button>
          </div>
        </section>
      )}

      <section className="public79-today">
        <header className="public79-today-heading">
          <div>
            <span className="public79-eyebrow">
              Hoje na igreja
            </span>

            <h2>
              {formatarDataHojeCompleta()}
            </h2>

            <p>
              Veja rapidamente o que está acontecendo
              hoje na programação pública.
            </p>
          </div>

          <div className="public79-today-count">
            <strong>
              {programacoesHoje.length}
            </strong>

            <span>
              {programacoesHoje.length === 1
                ? 'programação'
                : 'programações'}
            </span>
          </div>
        </header>

        {programacaoAgora && (
          <article className="public79-now-card">
            <div className="public79-now-indicator">
              <span />
              Acontecendo agora
            </div>

            <div className="public79-now-main">
              <span>
                {programacaoAgora
                  ?.o_que
                  ?.tipo
                  || 'Programação'}
              </span>

              <strong>
                {programacaoAgora
                  ?.o_que
                  ?.titulo
                  || 'Programação'}
              </strong>

              <small>
                {formatarHora(
                  programacaoAgora
                    ?.quando
                    ?.inicio_em,
                )}
                {' — '}
                {formatarHora(
                  programacaoAgora
                    ?.quando
                    ?.fim_em,
                )}
                {' · '}
                {programacaoAgora
                  ?.onde
                  ?.local
                  || 'Local não informado'}
              </small>
            </div>

            <button
              type="button"
              onClick={() =>
                navigate(
                  `/publico/programacoes/${
                    programacaoAgora.id
                  }`,
                )
              }
            >
              Ver detalhes
            </button>
          </article>
        )}

        {programacoesHoje.length === 0 ? (
          <div className="public79-empty">
            <span>
              Hoje não há programação pública cadastrada.
            </span>

            <button
              type="button"
              onClick={() =>
                navigate(
                  '/publico/programacoes',
                )
              }
            >
              Ver próximas programações
            </button>
          </div>
        ) : (
          <div className="public79-timeline">
            {programacoesHoje.map(
              (item) => (
                <TodayItem
                  key={item.id}
                  item={item}
                  active={
                    Number(
                      programacaoAgora
                        ?.id,
                    )
                    === Number(
                      item.id,
                    )
                  }
                  onOpen={() =>
                    navigate(
                      `/publico/programacoes/${item.id}`,
                    )
                  }
                />
              ),
            )}
          </div>
        )}
      </section>

      <section className="public74-period">
        <div>
          <span>
            Semana do ano
          </span>

          <strong>
            {numeroSemana
              ? `Semana ${numeroSemana}`
              : '—'}
          </strong>

          <small>
            {periodo || '—'}
          </small>
        </div>

        <div className="public76-period-actions">
          <div className="public74-summary">
            <strong>
              {mapa
                ?.resumo
                ?.programacoes
                ?? 0}
            </strong>

            <span>
              programações públicas
            </span>
          </div>

          <button
            type="button"
            onClick={
              compartilharSemana
            }
          >
            Compartilhar semana
          </button>

          <button
            type="button"
            onClick={() =>
              copiarSemana()
            }
          >
            {copiadoSemana
              ? 'Link copiado'
              : 'Copiar link'}
          </button>

          <button
            type="button"
            onClick={() =>
              navigate(
                `/publico/divulgar?data_referencia=${
                  mapa?.semana?.inicio
                  || dataReferencia
                }`,
              )
            }
          >
            QR Code
          </button>
        </div>
      </section>

      <section className="public75-discovery-strip">
        <div>
          <span className="public74-eyebrow">
            Além desta semana
          </span>

          <strong>
            Quer ver o que vem pela frente?
          </strong>

          <small>
            Consulte as próximas programações públicas
            já cadastradas pela igreja.
          </small>
        </div>

        <button
          type="button"
          onClick={() =>
            navigate(
              '/publico/programacoes',
            )
          }
        >
          Ver próximas programações →
        </button>
      </section>

      {error && (
        <div
          className="public74-error"
          role="alert"
        >
          {error}
        </div>
      )}

      {loading ? (
        <section className="public74-loading">
          Carregando programação...
        </section>
      ) : (
        <>
          <section className="public74-map-card">
            <header>
              <div>
                <span className="public74-eyebrow">
                  Sete dias
                </span>

                <h2>
                  Escolha um dia
                </h2>
              </div>

              <span className="public74-public-note">
                Somente atividades publicadas
              </span>
            </header>

            <div className="public74-week-map">
              {(mapa?.dias ?? [])
                .map(
                  (dia) => (
                    <PublicDayNode
                      key={dia.data}
                      dia={dia}
                      selected={
                        dia.data
                        === diaSelecionado
                      }
                      onClick={() =>
                        selecionarDia(
                          dia.data,
                        )
                      }
                    />
                  ),
                )}
            </div>
          </section>

          <section className="public74-day-panel">
            {diaAtual ? (
              <>
                <header className="public74-day-heading">
                  <div className="public74-day-date">
                    <strong>
                      {String(
                        novaDataLocal(
                          diaAtual.data,
                        ).getDate(),
                      ).padStart(
                        2,
                        '0',
                      )}
                    </strong>

                    <span>
                      {formatarMesCurto(
                        diaAtual.data,
                      )}
                    </span>
                  </div>

                  <div>
                    <span className="public74-eyebrow">
                      {formatarDiaSemana(
                        diaAtual.data,
                      )}
                    </span>

                    <h2>
                      Programação do dia
                    </h2>

                    <p>
                      {(diaAtual
                        ?.programacoes
                        ?.length
                        ?? 0) > 0
                        ? 'Veja horários, locais e informações disponíveis ao público.'
                        : 'Nenhuma programação pública está prevista para este dia.'}
                    </p>
                  </div>
                </header>

                {(diaAtual
                  ?.programacoes
                  ?.length
                  ?? 0) === 0 ? (
                  <div className="public74-empty-day">
                    <span>
                      ✓
                    </span>

                    <div>
                      <strong>
                        Sem programação pública
                      </strong>

                      <p>
                        Pode haver atividades internas,
                        mas elas não são exibidas nesta área.
                      </p>
                    </div>
                  </div>
                ) : (
                  <div className="public74-events">
                    {diaAtual
                      .programacoes
                      .map(
                        (item) => (
                          <PublicProgramCard
                            key={item.id}
                            item={item}
                            onOpen={() =>
                              navigate(
                                `/publico/programacoes/${item.id}`,
                              )
                            }
                          />
                        ),
                      )}
                  </div>
                )}
              </>
            ) : (
              <p className="public74-empty-copy">
                Escolha um dia da semana.
              </p>
            )}
          </section>
        </>
      )}

      <section className="public76-info-wrap">
        <PublicChurchInfo
          igreja={igreja}
        />
      </section>

      <footer className="public74-footer">
        <div>
          <strong>
            {igreja?.nome
              || 'Igreja'}
          </strong>

          <span>
            Programação pública disponibilizada pelo SYN.
          </span>
        </div>

        {!entradaPrincipal && (
          <button
            type="button"
            onClick={() =>
              navigate('/')
            }
          >
            Página inicial
          </button>
        )}
      </footer>
    </main>
  )
}

function TodayItem({
  item,
  active,
  onOpen,
}) {
  const situacao =
    obterSituacaoHoje(
      item,
      active,
    )

  return (
    <article
      className={[
        'public79-timeline-item',
        active
          ? 'active'
          : '',
        item?.status
          === 'CANCELADA'
          ? 'cancelled'
          : '',
      ]
        .filter(Boolean)
        .join(' ')}
    >
      <div className="public79-time">
        <strong>
          {formatarHora(
            item
              ?.quando
              ?.inicio_em,
          )}
        </strong>

        <span>
          {formatarHora(
            item
              ?.quando
              ?.fim_em,
          )}
        </span>
      </div>

      <div className="public79-item-main">
        <span>
          {item
            ?.o_que
            ?.tipo
            || 'Programação'}
        </span>

        <strong>
          {item
            ?.o_que
            ?.titulo
            || 'Programação'}
        </strong>

        <small>
          {item
            ?.onde
            ?.local
            || 'Local não informado'}
        </small>
      </div>

      <span
        className={
          `public79-situation ${situacao.classe}`
        }
      >
        {situacao.texto}
      </span>

      <button
        type="button"
        onClick={onOpen}
      >
        Detalhes
      </button>
    </article>
  )
}

function obterSituacaoHoje(
  item,
  active,
) {
  if (
    item?.status
      === 'CANCELADA'
  ) {
    return {
      texto:
        'Cancelada',
      classe:
        'cancelled',
    }
  }

  if (
    item?.status
      === 'REALIZADA'
  ) {
    return {
      texto:
        'Realizada',
      classe:
        'done',
    }
  }

  if (active) {
    return {
      texto:
        'Agora',
      classe:
        'now',
    }
  }

  const agora =
    new Date()

  const inicio =
    novaDataHoraLocal(
      item
        ?.quando
        ?.inicio_em,
    )

  const fim =
    novaDataHoraLocal(
      item
        ?.quando
        ?.fim_em,
    )

  if (
    !Number.isNaN(
      fim.getTime(),
    )
    && fim <= agora
  ) {
    return {
      texto:
        'Encerrada',
      classe:
        'done',
    }
  }

  if (
    !Number.isNaN(
      inicio.getTime(),
    )
    && inicio > agora
  ) {
    const minutos =
      Math.round(
        (
          inicio.getTime()
          - agora.getTime()
        )
        / 60000,
      )

    if (
      minutos > 0
      && minutos <= 90
    ) {
      return {
        texto:
          `Em ${minutos} min`,
        classe:
          'soon',
      }
    }

    return {
      texto:
        'Mais tarde',
      classe:
        'later',
    }
  }

  return {
    texto:
      'Programada',
    classe:
      'later',
  }
}

function PublicDayNode({
  dia,
  selected,
  onClick,
}) {
  const total =
    dia?.programacoes?.length
    ?? 0

  const canceladas =
    (dia?.programacoes ?? [])
      .filter(
        (item) =>
          item.status
          === 'CANCELADA',
      )
      .length

  const hoje =
    dia.data
    === hojeISO()

  return (
    <button
      type="button"
      className={[
        'public74-day-node',
        selected
          ? 'selected'
          : '',
        total > 0
          ? 'has-events'
          : '',
        hoje
          ? 'today'
          : '',
      ]
        .filter(Boolean)
        .join(' ')}
      onClick={onClick}
    >
      <span>
        {abreviarDiaSemana(
          dia.dia_semana,
        )}
      </span>

      <strong>
        {String(
          novaDataLocal(
            dia.data,
          ).getDate(),
        ).padStart(
          2,
          '0',
        )}
      </strong>

      <small>
        {formatarMesCurto(
          dia.data,
        )}
      </small>

      <div className="public74-node-count">
        {total > 0
          ? `${total} ${
              total === 1
                ? 'programação'
                : 'programações'
            }`
          : 'Livre'}
      </div>

      {canceladas > 0 && (
        <em>
          {canceladas}
          {' '}
          {canceladas === 1
            ? 'cancelada'
            : 'canceladas'}
        </em>
      )}

      {hoje && (
        <b>
          Hoje
        </b>
      )}
    </button>
  )
}

function PublicProgramCard({
  item,
  onOpen,
}) {
  const cancelada =
    item?.status
    === 'CANCELADA'

  const realizada =
    item?.status
    === 'REALIZADA'

  return (
    <article
      className={[
        'public74-event',
        cancelada
          ? 'cancelled'
          : '',
        realizada
          ? 'done'
          : '',
      ]
        .filter(Boolean)
        .join(' ')}
    >
      <div className="public74-event-time">
        <strong>
          {formatarHora(
            item
              ?.quando
              ?.inicio_em,
          )}
        </strong>

        <span>
          até{' '}
          {formatarHora(
            item
              ?.quando
              ?.fim_em,
          )}
        </span>
      </div>

      <div className="public74-event-main">
        <div className="public74-event-topline">
          <div>
            <span>
              {item
                ?.o_que
                ?.tipo
                || 'Programação'}
            </span>

            <h3>
              {item
                ?.o_que
                ?.titulo
                || 'Programação'}
            </h3>
          </div>

          <PublicStatus
            status={item?.status}
          />
        </div>

        <div className="public74-event-place">
          <strong>
            Local
          </strong>

          <span>
            {item
              ?.onde
              ?.local
              || 'Local não informado'}
          </span>
        </div>

        {item
          ?.o_que
          ?.descricao && (
          <p>
            {item
              .o_que
              .descricao}
          </p>
        )}

        <button
          type="button"
          onClick={onOpen}
        >
          Ver programação →
        </button>
      </div>
    </article>
  )
}

function PublicStatus({
  status,
}) {
  if (status === 'CANCELADA') {
    return (
      <span className="public74-status cancelled">
        Cancelada
      </span>
    )
  }

  if (status === 'REALIZADA') {
    return (
      <span className="public74-status done">
        Realizada
      </span>
    )
  }

  return (
    <span className="public74-status active">
      Programada
    </span>
  )
}

function formatarDataHojeCompleta() {
  const hoje =
    new Date()

  const texto =
    hoje
      .toLocaleDateString(
        'pt-BR',
        {
          weekday:
            'long',
          day:
            '2-digit',
          month:
            'long',
        },
      )

  return texto
    .charAt(0)
    .toUpperCase()
    + texto.slice(1)
}

function novaDataHoraLocal(
  valor,
) {
  if (!valor) {
    return new Date(
      Number.NaN,
    )
  }

  return new Date(
    String(valor)
      .replace(
        ' ',
        'T',
      ),
  )
}

function dataHoraValida(
  valor,
) {
  const data =
    novaDataHoraLocal(
      valor,
    )

  return !Number.isNaN(
    data.getTime(),
  )
}

function formatarDiaNumero(
  valor,
) {
  const data =
    novaDataHoraLocal(
      valor,
    )

  return dataHoraValida(valor)
    ? String(
        data.getDate(),
      ).padStart(
        2,
        '0',
      )
    : '--'
}

function formatarMesDataHora(
  valor,
) {
  const data =
    novaDataHoraLocal(
      valor,
    )

  return dataHoraValida(valor)
    ? data
        .toLocaleDateString(
          'pt-BR',
          {
            month:
              'short',
          },
        )
        .replace(
          '.',
          '',
        )
        .toUpperCase()
    : '---'
}

function formatarDiaSemanaDataHora(
  valor,
) {
  const data =
    novaDataHoraLocal(
      valor,
    )

  if (!dataHoraValida(valor)) {
    return 'Data'
  }

  const texto =
    data
      .toLocaleDateString(
        'pt-BR',
        {
          weekday:
            'long',
        },
      )

  return texto
    .charAt(0)
    .toUpperCase()
    + texto.slice(1)
}

function dataISOValida(
  valor,
) {
  if (
    !valor
    || !/^\d{4}-\d{2}-\d{2}$/.test(
      String(valor),
    )
  ) {
    return null
  }

  const [
    ano,
    mes,
    dia,
  ] =
    String(valor)
      .split('-')
      .map(Number)

  const data =
    new Date(
      ano,
      mes - 1,
      dia,
      12,
      0,
      0,
    )

  if (
    data.getFullYear() !== ano
    || data.getMonth()
      !== mes - 1
    || data.getDate()
      !== dia
  ) {
    return null
  }

  return String(valor)
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
    novaDataLocal(
      iso,
    )

  data.setDate(
    data.getDate()
    + dias,
  )

  return formatarISO(
    data,
  )
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

function formatarDataCurta(
  iso,
) {
  return novaDataLocal(
    iso,
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

function formatarMesCurto(
  iso,
) {
  return novaDataLocal(
    iso,
  )
    .toLocaleDateString(
      'pt-BR',
      {
        month: 'short',
      },
    )
    .replace('.', '')
    .toUpperCase()
}

function formatarDiaSemana(
  iso,
) {
  const texto =
    novaDataLocal(
      iso,
    )
      .toLocaleDateString(
        'pt-BR',
        {
          weekday: 'long',
        },
      )

  return texto
    .charAt(0)
    .toUpperCase()
    + texto.slice(1)
}

function abreviarDiaSemana(
  nome,
) {
  if (!nome) {
    return 'DIA'
  }

  return nome
    .split('-')[0]
    .slice(0, 3)
    .toUpperCase()
}

function formatarHora(
  valor,
) {
  if (!valor) {
    return '--:--'
  }

  return String(
    valor,
  ).slice(11, 16)
  || '--:--'
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
