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
  confirmarParticipacao,
  getMapaSemana,
  informarIndisponibilidade,
  recusarParticipacao,
} from '../api/api'

import './SemanaPageEtapa56.css'
import './SemanaPageEtapa69.css'
import './SemanaPageEtapa70.css'
import './SemanaPageEtapa71.css'

export default function SemanaPage() {
  const navigate =
    useNavigate()

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

  const [dataReferencia, setDataReferencia] =
    useState(
      () =>
        referenciaUrl
        || hojeISO(),
    )

  const [mapa, setMapa] =
    useState(null)

  const [diaSelecionado, setDiaSelecionado] =
    useState(null)

  const [loading, setLoading] =
    useState(true)

  const [actionLoading, setActionLoading] =
    useState('')

  const [
    anoSemanaSelecionado,
    setAnoSemanaSelecionado,
  ] =
    useState(
      () =>
        obterAnoSemanaISO(
          new Date(),
        ),
    )

  const [
    numeroSemanaSelecionado,
    setNumeroSemanaSelecionado,
  ] =
    useState(
      () =>
        obterNumeroSemanaISO(
          new Date(),
        ),
    )

  const [error, setError] =
    useState('')

  const [success, setSuccess] =
    useState('')

  /**
   * A visão pessoal é o ponto de partida do SYN:
   * ao abrir o mapa, o usuário deve entender primeiro
   * quais compromissos pertencem à sua própria semana.
   *
   * A programação geral da igreja permanece disponível
   * com um único clique.
   */
  const [
    modoMapa,
    setModoMapa,
  ] =
    useState('PESSOAL')

  const carregar =
    useCallback(
      async () => {
        setLoading(true)
        setError('')

        try {
          const response =
            await getMapaSemana(
              dataReferencia,
            )

          setMapa(
            response?.dados
            ?? null,
          )
        } catch (err) {
          setError(
            err?.message
            || 'Não foi possível carregar a semana.',
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

  /**
   * Mantém o estado sincronizado com a URL.
   *
   * Isso permite:
   * - abrir um dia diretamente pela Home;
   * - usar voltar/avançar do navegador;
   * - copiar um link para uma semana específica.
   */
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

  /**
   * Ao trocar de semana escolhemos automaticamente:
   *
   * 1. o dia do próximo compromisso pessoal;
   * 2. hoje, se hoje estiver dentro da semana;
   * 3. a segunda-feira.
   *
   * Assim o usuário não cai sempre no primeiro dia por acaso.
   */
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

    const diaReferenciado =
      referenciaExplicita
        ? dias.find(
            (dia) =>
              dia.data
              === referenciaExplicita,
          )
        : null

    if (diaReferenciado) {
      setDiaSelecionado(
        diaReferenciado.data,
      )
      return
    }

    const proximoId =
      mapa
        ?.resumo
        ?.proximo_compromisso_programacao_id

    const diaDoProximo =
      dias.find(
        (dia) =>
          dia
            ?.programacoes
            ?.some(
              (item) =>
                Number(item.id)
                === Number(proximoId),
            ),
      )

    if (diaDoProximo) {
      setDiaSelecionado(
        diaDoProximo.data,
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

    setDiaSelecionado(
      diaHoje?.data
      ?? dias[0]?.data
      ?? null,
    )
  }, [
    mapa,
    searchParams,
  ])

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

        return `${formatarDataCurta(
          inicio,
        )} — ${formatarDataCurta(
          fim,
        )}`
      },
      [mapa],
    )

  const numeroSemana =
    useMemo(
      () =>
        mapa?.semana?.inicio
          ? obterNumeroSemanaISO(
              mapa.semana.inicio,
            )
          : null,
      [mapa],
    )

  useEffect(() => {
    if (!mapa?.semana?.inicio) {
      return
    }

    const inicio =
      novaDataLocal(
        mapa.semana.inicio,
      )

    setAnoSemanaSelecionado(
      obterAnoSemanaISO(
        inicio,
      ),
    )

    setNumeroSemanaSelecionado(
      obterNumeroSemanaISO(
        inicio,
      ),
    )
  }, [mapa])

  const totalSemanasAnoSelecionado =
    useMemo(
      () =>
        obterTotalSemanasISO(
          anoSemanaSelecionado,
        ),
      [anoSemanaSelecionado],
    )

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

  const programacoesDiaVisiveis =
    useMemo(
      () => {
        const lista =
          diaAtual?.programacoes
          ?? []

        if (modoMapa === 'GERAL') {
          return lista
        }

        return lista.filter(
          (item) =>
            Boolean(
              item
                ?.destaque
                ?.pessoal,
            ),
        )
      },
      [
        diaAtual,
        modoMapa,
      ],
    )

  const meusCompromissosNaSemana =
    useMemo(
      () =>
        (mapa?.dias ?? [])
          .reduce(
            (
              total,
              dia,
            ) =>
              total
              + (
                dia
                  ?.programacoes
                  ?? []
              ).filter(
                (item) =>
                  Boolean(
                    item
                      ?.destaque
                      ?.pessoal,
                  ),
              ).length,
            0,
          ),
      [mapa],
    )

  const acoesPendentes =
    useMemo(
      () => {
        const resultado = []

        for (
          const dia
          of mapa?.dias ?? []
        ) {
          for (
            const programacao
            of dia?.programacoes ?? []
          ) {
            const participacoes =
              programacao
                ?.minhas_participacoes
              ?? []

            for (
              const participacao
              of participacoes
            ) {
              if (
                !participacao
                  ?.pendente_confirmacao
              ) {
                continue
              }

              resultado.push({
                participacaoId:
                  participacao.id,

                funcao:
                  participacao.funcao,

                data:
                  dia.data,

                diaSemana:
                  dia.dia_semana,

                programacaoId:
                  programacao.id,

                titulo:
                  programacao
                    ?.o_que
                    ?.titulo
                  || 'Programação',

                tipo:
                  programacao
                    ?.o_que
                    ?.tipo
                  || 'Programação',

                inicioEm:
                  programacao
                    ?.quando
                    ?.inicio_em,

                fimEm:
                  programacao
                    ?.quando
                    ?.fim_em,

                local:
                  programacao
                    ?.onde
                    ?.local
                  || 'Local não informado',

                permiteResposta:
                  Boolean(
                    programacao
                      ?.permite_resposta,
                  ),
              })
            }
          }
        }

        return resultado.sort(
          (a, b) =>
            String(
              a.inicioEm || '',
            ).localeCompare(
              String(
                b.inicioEm || '',
              ),
            ),
        )
      },
      [mapa],
    )

  const proximoCompromisso =
    useMemo(
      () => {
        const proximoId =
          mapa
            ?.resumo
            ?.proximo_compromisso_programacao_id

        if (!proximoId) {
          return null
        }

        for (
          const dia
          of mapa?.dias ?? []
        ) {
          const item =
            dia
              ?.programacoes
              ?.find(
                (programacao) =>
                  Number(
                    programacao.id,
                  )
                  === Number(
                    proximoId,
                  ),
              )

          if (item) {
            return {
              dia,
              programacao:
                item,
            }
          }
        }

        return null
      },
      [mapa],
    )

  useEffect(() => {
    if (
      numeroSemanaSelecionado
      > totalSemanasAnoSelecionado
    ) {
      setNumeroSemanaSelecionado(
        totalSemanasAnoSelecionado,
      )
    }
  }, [
    numeroSemanaSelecionado,
    totalSemanasAnoSelecionado,
  ])

  function irParaSemanaSelecionada() {
    const inicio =
      obterSegundaFeiraSemanaISO(
        anoSemanaSelecionado,
        numeroSemanaSelecionado,
      )

    const iso =
      formatarISO(
        inicio,
      )

    setSuccess('')
    setDiaSelecionado(null)
    setDataReferencia(iso)

    setSearchParams({
      data_referencia:
        iso,
    })
  }

  function mudarSemana(
    quantidadeSemanas,
  ) {
    setSuccess('')
    setDiaSelecionado(null)

    const novaReferencia =
      adicionarDiasISO(
        dataReferencia,
        quantidadeSemanas * 7,
      )

    setDataReferencia(
      novaReferencia,
    )

    setSearchParams({
      data_referencia:
        novaReferencia,
    })
  }

  function voltarHoje() {
    setSuccess('')
    setDiaSelecionado(null)

    const hoje =
      hojeISO()

    setDataReferencia(
      hoje,
    )

    setSearchParams({
      data_referencia:
        hoje,
    })
  }

  async function confirmar(
    participacaoId,
  ) {
    const chave =
      `confirmar:${participacaoId}`

    setActionLoading(chave)
    setError('')
    setSuccess('')

    try {
      await confirmarParticipacao(
        participacaoId,
      )

      setSuccess(
        'Participação confirmada com sucesso.',
      )

      await carregar()
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível confirmar a participação.',
      )
    } finally {
      setActionLoading('')
    }
  }


  async function indisponivel(
    participacaoId,
  ) {
    const confirmou =
      window.confirm(
        'Informar que você está indisponível para esta participação?',
      )

    if (!confirmou) {
      return
    }

    const chave =
      `indisponivel:${participacaoId}`

    setActionLoading(chave)
    setError('')
    setSuccess('')

    try {
      await informarIndisponibilidade(
        participacaoId,
      )

      setSuccess(
        'Indisponibilidade registrada. O responsável poderá reorganizar a escala.',
      )

      await carregar()
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível registrar a indisponibilidade.',
      )
    } finally {
      setActionLoading('')
    }
  }

  async function recusar(
    participacaoId,
  ) {
    const confirmou =
      window.confirm(
        'Recusar esta participação? Use esta opção quando você não aceitar esta escala.',
      )

    if (!confirmou) {
      return
    }

    const chave =
      `recusar:${participacaoId}`

    setActionLoading(chave)
    setError('')
    setSuccess('')

    try {
      await recusarParticipacao(
        participacaoId,
      )

      setSuccess(
        'Participação recusada. O responsável poderá reorganizar a escala.',
      )

      await carregar()
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível recusar a participação.',
      )
    } finally {
      setActionLoading('')
    }
  }

  return (
    <div className="week56-page">
      <section className="week56-hero">
        <div>
          <span className="eyebrow">
            Mapa da semana
          </span>

          <h1>
            Veja sua semana de relance
          </h1>

          <p>
            O SYN mostra primeiro onde você participa
            e depois o restante da programação da igreja.
            Sem grade de agenda e sem planilha.
          </p>
        </div>

        <div className="week56-controls">
          <button
            type="button"
            className="week56-nav-button"
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
            className="week56-nav-button"
            onClick={() =>
              mudarSemana(1)
            }
            aria-label="Próxima semana"
          >
            →
          </button>
        </div>
      </section>

      <section className="week56-period">
        <div className="week56-period-identification">
          <span>
            Semana do ano
          </span>

          <strong className="week56-week-number">
            {numeroSemana
              ? `Semana ${numeroSemana}`
              : 'Carregando...'}
          </strong>

          <small>
            {periodo
              || 'Carregando período...'}
          </small>
        </div>

        <div className="week56-metrics">
          <Metric
            value={
              mapa
                ?.resumo
                ?.meus_compromissos
              ?? 0
            }
            label="meus compromissos"
          />

          <Metric
            value={
              mapa
                ?.resumo
                ?.participacoes_pendentes
              ?? 0
            }
            label="a confirmar"
            attention={
              (
                mapa
                  ?.resumo
                  ?.participacoes_pendentes
                ?? 0
              ) > 0
            }
          />

          <Metric
            value={
              mapa
                ?.resumo
                ?.programacoes
              ?? 0
            }
            label="programações"
          />
        </div>
      </section>

      <section className="week64-jump">
        <div className="week64-jump-copy">
          <span className="eyebrow">
            Navegação rápida
          </span>

          <strong>
            Ir direto para uma semana
          </strong>

          <small>
            Útil para escalas e programações planejadas
            com várias semanas de antecedência.
          </small>
        </div>

        <div className="week64-jump-controls">
          <label>
            <span>
              Ano
            </span>

            <select
              value={
                anoSemanaSelecionado
              }
              onChange={(event) =>
                setAnoSemanaSelecionado(
                  Number(
                    event.target.value,
                  ),
                )
              }
            >
              {gerarAnosSemana(
                anoSemanaSelecionado,
              ).map(
                (ano) => (
                  <option
                    key={ano}
                    value={ano}
                  >
                    {ano}
                  </option>
                ),
              )}
            </select>
          </label>

          <label>
            <span>
              Semana
            </span>

            <select
              value={
                numeroSemanaSelecionado
              }
              onChange={(event) =>
                setNumeroSemanaSelecionado(
                  Number(
                    event.target.value,
                  ),
                )
              }
            >
              {Array.from(
                {
                  length:
                    totalSemanasAnoSelecionado,
                },
                (_, indice) =>
                  indice + 1,
              ).map(
                (numero) => (
                  <option
                    key={numero}
                    value={numero}
                  >
                    Semana {numero}
                  </option>
                ),
              )}
            </select>
          </label>

          <button
            type="button"
            className="button-primary"
            onClick={
              irParaSemanaSelecionada
            }
          >
            Ir para a semana
          </button>
        </div>
      </section>

      {error && (
        <div
          className="error-message"
          role="alert"
        >
          {error}
        </div>
      )}

      {success && (
        <div
          className="success-message"
          role="status"
        >
          {success}
        </div>
      )}

      {!loading
        && acoesPendentes.length > 0 && (
        <section className="week71-actions-center">
          <header className="week71-actions-heading">
            <div>
              <span className="eyebrow">
                Suas ações
              </span>

              <h2>
                Você tem {
                  acoesPendentes.length
                } {
                  acoesPendentes.length === 1
                    ? 'resposta pendente'
                    : 'respostas pendentes'
                }
              </h2>

              <p>
                Resolva suas escalas sem precisar
                procurar compromisso por compromisso.
              </p>
            </div>

            <span className="week71-actions-counter">
              {acoesPendentes.length}
            </span>
          </header>

          <div className="week71-actions-list">
            {acoesPendentes.map(
              (acao) => (
                <article
                  key={
                    `${acao.participacaoId}-${acao.programacaoId}`
                  }
                  className="week71-action-row"
                >
                  <div className="week71-action-date">
                    <strong>
                      {String(
                        novaDataLocal(
                          acao.data,
                        ).getDate(),
                      ).padStart(
                        2,
                        '0',
                      )}
                    </strong>

                    <span>
                      {formatarMesCurto(
                        acao.data,
                      )}
                    </span>
                  </div>

                  <div className="week71-action-main">
                    <span className="week71-action-type">
                      {acao.tipo}
                    </span>

                    <strong>
                      {acao.titulo}
                    </strong>

                    <span>
                      {formatarDiaSemanaCurto(
                        acao.data,
                      )}
                      {' · '}
                      {formatarHora(
                        acao.inicioEm,
                      )}
                      {' · '}
                      {acao.local}
                    </span>

                    <small>
                      Sua função: {
                        acao.funcao
                        || 'Não informada'
                      }
                    </small>
                  </div>

                  <div className="week71-action-buttons">
                    {acao.permiteResposta ? (
                      <>
                        <button
                          type="button"
                          className="week71-action-confirm"
                          disabled={
                            Boolean(
                              actionLoading,
                            )
                          }
                          onClick={() =>
                            confirmar(
                              acao.participacaoId,
                            )
                          }
                        >
                          {actionLoading
                            === `confirmar:${acao.participacaoId}`
                            ? 'Confirmando...'
                            : 'Confirmar'}
                        </button>

                        <button
                          type="button"
                          className="week71-action-unavailable"
                          disabled={
                            Boolean(
                              actionLoading,
                            )
                          }
                          onClick={() =>
                            indisponivel(
                              acao.participacaoId,
                            )
                          }
                        >
                          {actionLoading
                            === `indisponivel:${acao.participacaoId}`
                            ? 'Registrando...'
                            : 'Não posso'}
                        </button>

                        <button
                          type="button"
                          className="week71-action-reject"
                          disabled={
                            Boolean(
                              actionLoading,
                            )
                          }
                          onClick={() =>
                            recusar(
                              acao.participacaoId,
                            )
                          }
                        >
                          {actionLoading
                            === `recusar:${acao.participacaoId}`
                            ? 'Recusando...'
                            : 'Recusar'}
                        </button>
                      </>
                    ) : (
                      <span className="week71-action-disabled">
                        Resposta desabilitada
                      </span>
                    )}

                    <button
                      type="button"
                      className="week71-action-map"
                      onClick={() => {
                        setModoMapa(
                          'PESSOAL',
                        )

                        setDiaSelecionado(
                          acao.data,
                        )

                        setDataReferencia(
                          acao.data,
                        )

                        setSearchParams({
                          data_referencia:
                            acao.data,
                        })
                      }}
                    >
                      Ver no mapa
                    </button>
                  </div>
                </article>
              ),
            )}
          </div>
        </section>
      )}

      {!loading
        && acoesPendentes.length === 0
        && (
        <section className="week71-actions-complete">
          <div className="week71-actions-complete-icon">
            ✓
          </div>

          <div>
            <span className="eyebrow">
              Suas ações
            </span>

            <strong>
              Nenhuma resposta pendente.
            </strong>

            <p>
              Você já respondeu todas as escalas
              disponíveis nesta semana.
            </p>
          </div>
        </section>
      )}

      {loading ? (
        <div className="loading-card">
          Organizando o mapa da semana...
        </div>
      ) : (
        <>
          <section className="week56-map-shell">
            <header className="week56-map-heading">
              <div>
                <span className="eyebrow">
                  Sete dias
                </span>

                <h2>
                  Escolha um ponto da semana
                </h2>
              </div>

              <div className="week69-view-control">
                <button
                  type="button"
                  className={
                    modoMapa === 'PESSOAL'
                      ? 'active'
                      : ''
                  }
                  onClick={() =>
                    setModoMapa(
                      'PESSOAL',
                    )
                  }
                >
                  <span>
                    Meu mapa
                  </span>

                  <strong>
                    {meusCompromissosNaSemana}
                  </strong>
                </button>

                <button
                  type="button"
                  className={
                    modoMapa === 'GERAL'
                      ? 'active'
                      : ''
                  }
                  onClick={() =>
                    setModoMapa(
                      'GERAL',
                    )
                  }
                >
                  <span>
                    Toda a igreja
                  </span>

                  <strong>
                    {mapa
                      ?.resumo
                      ?.programacoes
                      ?? 0}
                  </strong>
                </button>
              </div>
            </header>

            <div className="week69-view-message">
              {modoMapa === 'PESSOAL' ? (
                <>
                  <i className="week56-dot personal" />

                  <span>
                    Mostrando somente os compromissos em que
                    <strong> você participa</strong>.
                  </span>
                </>
              ) : (
                <>
                  <i className="week56-dot general" />

                  <span>
                    Mostrando
                    <strong> toda a programação da igreja</strong>,
                    com seus compromissos pessoais destacados.
                  </span>
                </>
              )}
            </div>

            <div className="week56-map">
              {(mapa?.dias ?? []).map(
                (dia) => (
                  <DayNode
                    key={dia.data}
                    dia={dia}
                    selected={
                      dia.data
                      === diaSelecionado
                    }
                    nextId={
                      mapa
                        ?.resumo
                        ?.proximo_compromisso_programacao_id
                    }
                    modo={modoMapa}
                    onClick={() => {
                      setDiaSelecionado(
                        dia.data,
                      )

                      setSearchParams({
                        data_referencia:
                          dia.data,
                      })
                    }}
                  />
                ),
              )}
            </div>
          </section>

          {proximoCompromisso && (
            <section className="week56-next">
              <div className="week56-next-marker">
                Próximo
              </div>

              <div className="week56-next-copy">
                <span className="eyebrow">
                  Seu próximo compromisso
                </span>

                <strong>
                  {
                    proximoCompromisso
                      .programacao
                      ?.o_que
                      ?.titulo
                  }
                </strong>

                <span>
                  {formatarDiaSemanaCurto(
                    proximoCompromisso
                      .dia
                      .data,
                  )}
                  {', '}
                  {formatarDataCurta(
                    proximoCompromisso
                      .dia
                      .data,
                  )}
                  {' · '}
                  {formatarHora(
                    proximoCompromisso
                      .programacao
                      ?.quando
                      ?.inicio_em,
                  )}
                  {' · '}
                  {
                    proximoCompromisso
                      .programacao
                      ?.onde
                      ?.local
                    || 'Local não informado'
                  }
                </span>
              </div>

              <button
                type="button"
                className="button-secondary"
                onClick={() => {
                  const data =
                    proximoCompromisso
                      .dia
                      .data

                  setDiaSelecionado(
                    data,
                  )

                  setSearchParams({
                    data_referencia:
                      data,
                  })
                }}
              >
                Ver no mapa
              </button>
            </section>
          )}

          <section className="week56-day-panel">
            {diaAtual ? (
              <>
                <header className="week56-day-heading">
                  <div className="week56-day-date">
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
                    <span className="eyebrow">
                      Dia selecionado
                    </span>

                    <h2>
                      {diaAtual.dia_semana}
                    </h2>

                    <p>
                      {modoMapa === 'PESSOAL'
                        ? diaAtual.tem_meu_compromisso
                          ? 'Estes são os seus compromissos neste dia.'
                          : 'Você não possui compromisso pessoal neste dia.'
                        : diaAtual.tem_programacao
                          ? 'Estas são as programações da igreja neste dia.'
                          : 'Nenhuma programação prevista para este dia.'}
                    </p>
                  </div>
                </header>

                {(
                  diaAtual
                    ?.programacoes
                    ?.length
                  ?? 0
                ) === 0 ? (
                  <div className="week56-free-day">
                    <span className="week56-free-icon">
                      ✓
                    </span>

                    <div>
                      <strong>
                        Dia livre no mapa
                      </strong>

                      <p>
                        Não existe programação cadastrada
                        para esta data.
                      </p>
                    </div>
                  </div>
                ) : programacoesDiaVisiveis.length === 0 ? (
                  <div className="week69-personal-empty">
                    <div className="week69-personal-empty-icon">
                      ✓
                    </div>

                    <div className="week69-personal-empty-copy">
                      <span className="eyebrow">
                        Seu mapa
                      </span>

                      <strong>
                        Você não tem compromisso neste dia.
                      </strong>

                      <p>
                        Existem {
                          diaAtual
                            ?.programacoes
                            ?.length
                          ?? 0
                        } {
                          (
                            diaAtual
                              ?.programacoes
                              ?.length
                            ?? 0
                          ) === 1
                            ? 'programação'
                            : 'programações'
                        } da igreja na data, mas nenhuma
                        faz parte da sua escala.
                      </p>
                    </div>

                    <button
                      type="button"
                      className="button-secondary"
                      onClick={() =>
                        setModoMapa(
                          'GERAL',
                        )
                      }
                    >
                      Ver toda a programação
                    </button>
                  </div>
                ) : (
                  <div className="week56-events">
                    {programacoesDiaVisiveis
                      .map(
                        (programacao) => (
                          <ProgramCard
                            key={
                              programacao.id
                            }
                            programacao={
                              programacao
                            }
                            actionLoading={
                              actionLoading
                            }
                            onConfirm={
                              confirmar
                            }
                            onUnavailable={
                              indisponivel
                            }
                            onReject={
                              recusar
                            }
                            onOpen={() =>
                              navigate(
                                `/programacoes/${programacao.id}`,
                              )
                            }
                          />
                        ),
                      )}
                  </div>
                )}
              </>
            ) : (
              <p className="empty-state">
                Escolha um dia para visualizar
                suas programações.
              </p>
            )}
          </section>
        </>
      )}
    </div>
  )
}

function DayNode({
  dia,
  selected,
  nextId,
  modo,
  onClick,
}) {
  const programacoes =
    dia?.programacoes
    ?? []

  const programacoesPessoais =
    programacoes.filter(
      (item) =>
        Boolean(
          item
            ?.destaque
            ?.pessoal,
        ),
    )

  const pessoais =
    programacoesPessoais.length

  const programacoesVisiveis =
    modo === 'PESSOAL'
      ? programacoesPessoais
      : programacoes

  const possuiProximo =
    programacoes.some(
      (item) =>
        Number(item.id)
        === Number(nextId),
    )

  const hoje =
    dia.data
    === hojeISO()

  return (
    <button
      type="button"
      className={[
        'week56-node',
        selected
          ? 'selected'
          : '',
        pessoais > 0
          ? 'personal'
          : '',
        hoje
          ? 'today'
          : '',
      ]
        .filter(Boolean)
        .join(' ')}
      onClick={onClick}
    >
      <span className="week56-node-weekday">
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

      <span className="week56-node-month">
        {formatarMesCurto(
          dia.data,
        )}
      </span>

      <span className="week56-node-points">
        {modo === 'PESSOAL' ? (
          pessoais > 0 && (
            <i className="week56-dot personal" />
          )
        ) : (
          <>
            {pessoais > 0 && (
              <i className="week56-dot personal" />
            )}

            {programacoes.length > 0 && (
              <i className="week56-dot general" />
            )}
          </>
        )}
      </span>

      <small>
        {modo === 'PESSOAL'
          ? pessoais > 0
            ? `${pessoais} ${
                pessoais === 1
                  ? 'compromisso'
                  : 'compromissos'
              }`
            : 'Livre'
          : programacoesVisiveis.length > 0
            ? `${programacoesVisiveis.length} ${
                programacoesVisiveis.length === 1
                  ? 'atividade'
                  : 'atividades'
              }`
            : 'Livre'}
      </small>

      {hoje && (
        <span className="week56-today-label">
          Hoje
        </span>
      )}

      {possuiProximo && (
        <span className="week56-next-label">
          Próximo
        </span>
      )}
    </button>
  )
}

function ProgramCard({
  programacao,
  actionLoading,
  onConfirm,
  onUnavailable,
  onReject,
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
    <article
      className={[
        'week56-event',
        pessoal
          ? 'personal'
          : '',
        proximo
          ? 'next'
          : '',
      ]
        .filter(Boolean)
        .join(' ')}
    >
      <div className="week56-event-time">
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

      <div className="week56-event-main">
        <div className="week56-event-topline">
          <div>
            <span className="program-type">
              {
                programacao
                  ?.o_que
                  ?.tipo
                || 'Programação'
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

          <div className="week56-event-badges">
            {proximo && (
              <span className="week56-badge next">
                Próximo compromisso
              </span>
            )}

            {pessoal && (
              <span className="week56-badge personal">
                Você participa
              </span>
            )}
          </div>
        </div>

        <div className="week56-event-context">
          <span>
            <b>Local</b>
            {
              programacao
                ?.onde
                ?.local
              || 'Local não informado'
            }
          </span>

          <span>
            <b>Organizador</b>
            {
              programacao
                ?.organizador
              || 'Não informado'
            }
          </span>
        </div>

        {programacao
          ?.o_que
          ?.descricao && (
          <p className="week56-event-description">
            {
              programacao
                .o_que
                .descricao
            }
          </p>
        )}

        {pessoal && (
          <div className="week56-my-roles">
            {participacoes.map(
              (item) => {
                const pendente =
                  item
                    .pendente_confirmacao

                const chave =
                  `confirmar:${item.id}`

                return (
                  <div
                    key={item.id}
                    className="week56-role"
                  >
                    <div>
                      <span>
                        Sua função
                      </span>

                      <strong>
                        {item.funcao}
                      </strong>
                    </div>

                    <span
                      className={
                        pendente
                          ? 'status-pill pending'
                          : 'status-pill'
                      }
                    >
                      {pendente
                        ? 'A confirmar'
                        : traduzirStatus(
                            item.status,
                          )}
                    </span>

                    {pendente
                      && programacao
                        ?.permite_resposta && (
                      <div className="week70-response-actions">
                        <button
                          type="button"
                          className="week70-response-button confirm"
                          disabled={
                            Boolean(
                              actionLoading,
                            )
                          }
                          onClick={() =>
                            onConfirm(
                              item.id,
                            )
                          }
                        >
                          {actionLoading
                            === chave
                            ? 'Confirmando...'
                            : 'Confirmar'}
                        </button>

                        <button
                          type="button"
                          className="week70-response-button unavailable"
                          disabled={
                            Boolean(
                              actionLoading,
                            )
                          }
                          onClick={() =>
                            onUnavailable(
                              item.id,
                            )
                          }
                        >
                          {actionLoading
                            === `indisponivel:${item.id}`
                            ? 'Registrando...'
                            : 'Não posso'}
                        </button>

                        <button
                          type="button"
                          className="week70-response-button reject"
                          disabled={
                            Boolean(
                              actionLoading,
                            )
                          }
                          onClick={() =>
                            onReject(
                              item.id,
                            )
                          }
                        >
                          {actionLoading
                            === `recusar:${item.id}`
                            ? 'Recusando...'
                            : 'Recusar'}
                        </button>
                      </div>
                    )}

                    {pendente
                      && !programacao
                        ?.permite_resposta && (
                      <span className="week70-response-disabled">
                        Resposta desabilitada pelo organizador
                      </span>
                    )}
                  </div>
                )
              },
            )}
          </div>
        )}

        {pessoal
          && participacoes.some(
            (item) =>
              item.pendente_confirmacao,
          )
          && programacao
            ?.permite_resposta && (
          <div className="week70-response-help">
            <span>
              <strong>Confirmar</strong>
              {' '}aceita a escala.
            </span>

            <span>
              <strong>Não posso</strong>
              {' '}registra indisponibilidade.
            </span>

            <span>
              <strong>Recusar</strong>
              {' '}informa que você não aceita esta participação.
            </span>
          </div>
        )}

        <button
          type="button"
          className="week56-details-button"
          onClick={onOpen}
        >
          Ver detalhes da programação →
        </button>
      </div>
    </article>
  )
}

function Metric({
  value,
  label,
  attention = false,
}) {
  return (
    <div
      className={
        attention
          ? 'week56-metric attention'
          : 'week56-metric'
      }
    >
      <strong>
        {value}
      </strong>

      <span>
        {label}
      </span>
    </div>
  )
}

/**
 * Retorna o número ISO-8601 da semana.
 *
 * O SYN já considera segunda-feira como início da semana,
 * portanto a numeração ISO é a referência mais coerente:
 *
 * - a semana começa na segunda-feira;
 * - a semana 1 é a semana que contém a primeira quinta-feira
 *   do ano.
 */
function obterNumeroSemanaISO(
  valor,
) {
  const data =
    valor instanceof Date
      ? new Date(
          valor.getFullYear(),
          valor.getMonth(),
          valor.getDate(),
          12,
          0,
          0,
        )
      : novaDataLocal(
          valor,
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
    || data.getDate() !== dia
  ) {
    return null
  }

  return String(valor)
}

function obterAnoSemanaISO(
  valor,
) {
  const data =
    valor instanceof Date
      ? new Date(
          valor.getFullYear(),
          valor.getMonth(),
          valor.getDate(),
          12,
          0,
          0,
        )
      : novaDataLocal(
          valor,
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

  return utc.getUTCFullYear()
}

/**
 * Um ano ISO pode possuir 52 ou 53 semanas.
 * O número da semana de 28 de dezembro sempre corresponde
 * à última semana ISO daquele ano.
 */
function obterTotalSemanasISO(
  ano,
) {
  return obterNumeroSemanaISO(
    new Date(
      ano,
      11,
      28,
      12,
      0,
      0,
    ),
  )
}

/**
 * Converte "ano ISO + número da semana" para a segunda-feira
 * daquela semana.
 *
 * Exemplo:
 * 2026 + semana 36 -> 31/08/2026.
 */
function obterSegundaFeiraSemanaISO(
  ano,
  semana,
) {
  const quatroJaneiro =
    new Date(
      ano,
      0,
      4,
      12,
      0,
      0,
    )

  const diaSemana =
    quatroJaneiro.getDay()
    || 7

  const segundaSemana1 =
    new Date(
      quatroJaneiro,
    )

  segundaSemana1.setDate(
    quatroJaneiro.getDate()
    - diaSemana
    + 1,
  )

  const resultado =
    new Date(
      segundaSemana1,
    )

  resultado.setDate(
    segundaSemana1.getDate()
    + (
      semana - 1
    ) * 7,
  )

  return resultado
}

function gerarAnosSemana(
  centro,
) {
  return [
    centro - 2,
    centro - 1,
    centro,
    centro + 1,
    centro + 2,
  ]
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
  if (!iso) {
    return new Date()
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
}

function formatarDataCurta(
  iso,
) {
  return novaDataLocal(iso)
    .toLocaleDateString(
      'pt-BR',
      {
        day: '2-digit',
        month: 'short',
      },
    )
}

function formatarMesCurto(
  iso,
) {
  return novaDataLocal(iso)
    .toLocaleDateString(
      'pt-BR',
      {
        month: 'short',
      },
    )
    .replace('.', '')
    .toUpperCase()
}

function formatarDiaSemanaCurto(
  iso,
) {
  const texto =
    novaDataLocal(iso)
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
  dataHora,
) {
  if (!dataHora) {
    return '--:--'
  }

  return String(
    dataHora,
  ).slice(11, 16)
  || '--:--'
}

function traduzirStatus(
  status,
) {
  const mapa = {
    ESCALADO:
      'A confirmar',

    CONFIRMADO:
      'Confirmado',

    INDISPONIVEL:
      'Indisponível',

    RECUSADO:
      'Recusado',

    CANCELADO:
      'Cancelado',
  }

  return mapa[status]
    || status
}
