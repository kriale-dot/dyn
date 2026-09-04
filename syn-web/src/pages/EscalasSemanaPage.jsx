import {
  useCallback,
  useEffect,
  useMemo,
  useState,
} from 'react'

import {
  Link,
  useNavigate,
  useSearchParams,
} from 'react-router-dom'

import {
  getEscalasSemana,
} from '../api/api'

import {
  useAuth,
} from '../contexts/AuthContext'

import './EscalasSemanaPage.css'

export default function EscalasSemanaPage() {
  const navigate =
    useNavigate()

  const [
    searchParams,
    setSearchParams,
  ] =
    useSearchParams()

  const {
    usuario,
  } = useAuth()

  const papel =
    usuario?.papel?.codigo

  const podeGerenciar =
    [
      'ADMINISTRADOR',
      'ORGANIZADOR',
    ].includes(papel)

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

  const [dados, setDados] =
    useState(null)

  const [diaSelecionado, setDiaSelecionado] =
    useState(null)

  const [loading, setLoading] =
    useState(true)

  const [error, setError] =
    useState('')

  const [
    modoVisualizacao,
    setModoVisualizacao,
  ] =
    useState('PENDENCIAS')

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

  const carregar =
    useCallback(
      async () => {
        if (!podeGerenciar) {
          setLoading(false)
          return
        }

        setLoading(true)
        setError('')

        try {
          const response =
            await getEscalasSemana(
              dataReferencia,
            )

          setDados(
            response?.dados
            ?? null,
          )
        } catch (err) {
          setError(
            err?.message
            || 'Não foi possível carregar as escalas da semana.',
          )
        } finally {
          setLoading(false)
        }
      },
      [
        dataReferencia,
        podeGerenciar,
      ],
    )

  useEffect(() => {
    carregar()
  }, [carregar])

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
      dados?.dias
      ?? []

    if (dias.length === 0) {
      setDiaSelecionado(null)
      return
    }

    const referencia =
      dataISOValida(
        searchParams.get(
          'data_referencia',
        ),
      )

    const explicito =
      referencia
        ? dias.find(
            (dia) =>
              dia.data
              === referencia,
          )
        : null

    if (explicito) {
      setDiaSelecionado(
        explicito.data,
      )
      return
    }

    const diaComPendencia =
      dias.find(
        (dia) =>
          dia.programacoes.some(
            (item) =>
              item.status === 'AGENDADA'
              && (
                item.situacao_escala
                  === 'PENDENTE_CONFIRMACAO'
                || item.situacao_escala
                  === 'SEM_ESCALA'
                || item.situacao_escala
                  === 'SEM_PARTICIPANTES_ATIVOS'
                || (
                  item
                    ?.cobertura_funcoes
                    ?.sem_participante_ativo
                  ?? 0
                ) > 0
              ),
          ),
      )

    setDiaSelecionado(
      diaComPendencia?.data
      ?? dias[0]?.data
      ?? null,
    )
  }, [
    dados,
    searchParams,
  ])

  useEffect(() => {
    const inicio =
      dados
        ?.semana
        ?.inicio

    if (!inicio) {
      return
    }

    const data =
      dataLocal(
        inicio,
      )

    setAnoSemanaSelecionado(
      obterAnoSemanaISO(
        data,
      ),
    )

    setNumeroSemanaSelecionado(
      obterNumeroSemanaISO(
        data,
      ),
    )
  }, [dados])

  const totalSemanasAnoSelecionado =
    useMemo(
      () =>
        obterTotalSemanasISO(
          anoSemanaSelecionado,
        ),
      [anoSemanaSelecionado],
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

  const diaAtual =
    useMemo(
      () =>
        (dados?.dias ?? [])
          .find(
            (dia) =>
              dia.data
              === diaSelecionado,
          )
        ?? null,
      [
        dados,
        diaSelecionado,
      ],
    )

  const programacoesDia =
    useMemo(
      () => {
        const lista =
          diaAtual?.programacoes
          ?? []

        if (
          modoVisualizacao
          === 'TODAS'
        ) {
          return lista
        }

        if (
          modoVisualizacao
          === 'CONFIRMADAS'
        ) {
          return lista.filter(
            (item) =>
              item.situacao_escala
              === 'CONFIRMADA',
          )
        }

        if (
          modoVisualizacao
          === 'SEM_ESCALA'
        ) {
          return lista.filter(
            (item) =>
              item.status === 'AGENDADA'
              && (
                item.situacao_escala
                  === 'SEM_ESCALA'
                || item.situacao_escala
                  === 'SEM_PARTICIPANTES_ATIVOS'
              ),
          )
        }

        /**
         * PENDENCIAS
         *
         * Consideramos pendência:
         * - programação agendada sem escala;
         * - participação aguardando confirmação;
         * - função habilitada sem participante ativo.
         */
        return lista.filter(
          (item) =>
            item.status === 'AGENDADA'
            && (
              item.situacao_escala
                === 'SEM_ESCALA'
              || item.situacao_escala
                === 'SEM_PARTICIPANTES_ATIVOS'
              || item.situacao_escala
                === 'PENDENTE_CONFIRMACAO'
              || (
                item
                  ?.cobertura_funcoes
                  ?.sem_participante_ativo
                ?? 0
              ) > 0
            ),
        )
      },
      [
        diaAtual,
        modoVisualizacao,
      ],
    )

  const resumoDia =
    useMemo(
      () => {
        const lista =
          diaAtual?.programacoes
          ?? []

        return {
          total:
            lista.length,

          pendencias:
            lista.filter(
              (item) =>
                item.status
                  === 'AGENDADA'
                && (
                  item.situacao_escala
                    === 'SEM_ESCALA'
                  || item.situacao_escala
                    === 'SEM_PARTICIPANTES_ATIVOS'
                  || item.situacao_escala
                    === 'PENDENTE_CONFIRMACAO'
                  || (
                    item
                      ?.cobertura_funcoes
                      ?.sem_participante_ativo
                    ?? 0
                  ) > 0
                ),
            ).length,

          semEscala:
            lista.filter(
              (item) =>
                item.status
                  === 'AGENDADA'
                && (
                  item.situacao_escala
                    === 'SEM_ESCALA'
                  || item.situacao_escala
                    === 'SEM_PARTICIPANTES_ATIVOS'
                ),
            ).length,

          confirmadas:
            lista.filter(
              (item) =>
                item.situacao_escala
                === 'CONFIRMADA',
            ).length,
        }
      },
      [diaAtual],
    )

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

    setDataReferencia(
      iso,
    )

    setDiaSelecionado(
      null,
    )

    setSearchParams({
      data_referencia:
        iso,
    })
  }

  function mudarSemana(
    quantidade,
  ) {
    const nova =
      adicionarDiasISO(
        dataReferencia,
        quantidade * 7,
      )

    setDataReferencia(nova)
    setDiaSelecionado(null)

    setSearchParams({
      data_referencia:
        nova,
    })
  }

  function irHoje() {
    const hoje =
      hojeISO()

    setDataReferencia(hoje)
    setDiaSelecionado(null)

    setSearchParams({
      data_referencia:
        hoje,
    })
  }

  function selecionarDia(
    data,
  ) {
    setDiaSelecionado(data)

    setSearchParams({
      data_referencia:
        data,
    })
  }

  if (!podeGerenciar) {
    return (
      <section className="panel">
        <span className="eyebrow">
          Acesso restrito
        </span>

        <h1>
          Escalas da semana
        </h1>

        <p className="empty-state">
          Somente Administradores e Organizadores
          podem consultar esta visão.
        </p>
      </section>
    )
  }

  return (
    <div className="scale-week-page">
      <Link
        to="/gestao/programacoes"
        className="text-link"
      >
        ← Gerenciar programações
      </Link>

      <section className="scale-week-hero">
        <div>
          <span className="eyebrow">
            Gestão semanal
          </span>

          <h1>
            Escalas da semana
          </h1>

          <p>
            Veja rapidamente quais programações
            ainda precisam de escala ou de confirmação.
          </p>
        </div>

        <div className="scale-week-controls">
          <button
            type="button"
            className="scale-week-nav"
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
            onClick={irHoje}
          >
            Hoje
          </button>

          <button
            type="button"
            className="scale-week-nav"
            onClick={() =>
              mudarSemana(1)
            }
            aria-label="Próxima semana"
          >
            →
          </button>
        </div>
      </section>

      <section className="scale-week-reference">
        <div>
          <span>
            Semana do ano
          </span>

          <strong>
            Semana {
              dados
                ?.semana
                ?.numero_iso
              ?? '—'
            }
          </strong>

          <small>
            {formatarDataCurta(
              dados
                ?.semana
                ?.inicio,
            )}
            {' — '}
            {formatarDataCurta(
              dados
                ?.semana
                ?.fim,
            )}
          </small>
        </div>

        <div className="scale-week-summary">
          <Metric
            label="Programações"
            value={
              dados
                ?.resumo
                ?.programacoes
              ?? 0
            }
          />

          <Metric
            label="Sem escala"
            value={
              dados
                ?.resumo
                ?.programacoes_sem_escala
              ?? 0
            }
            attention={
              (
                dados
                  ?.resumo
                  ?.programacoes_sem_escala
                ?? 0
              ) > 0
            }
          />

          <Metric
            label="Aguardando"
            value={
              dados
                ?.resumo
                ?.pendentes_confirmacao
              ?? 0
            }
            attention={
              (
                dados
                  ?.resumo
                  ?.pendentes_confirmacao
                ?? 0
              ) > 0
            }
          />

          <Metric
            label="Confirmados"
            value={
              dados
                ?.resumo
                ?.confirmadas
              ?? 0
            }
            positive
          />

          <Metric
            label="Funções sem participante"
            value={
              dados
                ?.resumo
                ?.funcoes_sem_participante
              ?? 0
            }
            attention={
              (
                dados
                  ?.resumo
                  ?.funcoes_sem_participante
                ?? 0
              ) > 0
            }
          />
        </div>
      </section>

      <section className="scale-week65-jump">
        <div className="scale-week65-jump-copy">
          <span className="eyebrow">
            Navegação rápida
          </span>

          <strong>
            Ir direto para uma semana
          </strong>

          <small>
            Escolha a semana do ano que deseja
            administrar sem avançar uma a uma.
          </small>
        </div>

        <div className="scale-week65-jump-controls">
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
        <div className="error-message">
          {error}
        </div>
      )}

      {loading ? (
        <div className="loading-card">
          Carregando escalas da semana...
        </div>
      ) : (
        <>
          <section className="scale-week-map">
            {(dados?.dias ?? []).map(
              (dia) => (
                <DayScaleNode
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
          </section>

          <section className="scale-week-operational-filter">
            <div>
              <span className="eyebrow">
                Foco operacional
              </span>

              <h2>
                O que você quer enxergar?
              </h2>

              <p>
                O modo Pendências deixa visível apenas
                o que ainda exige ação do gestor.
              </p>
            </div>

            <div className="scale-week-filter-buttons">
              <button
                type="button"
                className={
                  modoVisualizacao === 'PENDENCIAS'
                    ? 'active'
                    : ''
                }
                onClick={() =>
                  setModoVisualizacao(
                    'PENDENCIAS',
                  )
                }
              >
                Pendências
                <strong>
                  {resumoDia.pendencias}
                </strong>
              </button>

              <button
                type="button"
                className={
                  modoVisualizacao === 'SEM_ESCALA'
                    ? 'active'
                    : ''
                }
                onClick={() =>
                  setModoVisualizacao(
                    'SEM_ESCALA',
                  )
                }
              >
                Sem escala
                <strong>
                  {resumoDia.semEscala}
                </strong>
              </button>

              <button
                type="button"
                className={
                  modoVisualizacao === 'CONFIRMADAS'
                    ? 'active'
                    : ''
                }
                onClick={() =>
                  setModoVisualizacao(
                    'CONFIRMADAS',
                  )
                }
              >
                Confirmadas
                <strong>
                  {resumoDia.confirmadas}
                </strong>
              </button>

              <button
                type="button"
                className={
                  modoVisualizacao === 'TODAS'
                    ? 'active'
                    : ''
                }
                onClick={() =>
                  setModoVisualizacao(
                    'TODAS',
                  )
                }
              >
                Todas
                <strong>
                  {resumoDia.total}
                </strong>
              </button>
            </div>
          </section>

          <section className="scale-week-day">
            <header className="scale-week-day-heading">
              <div>
                <span className="eyebrow">
                  Dia selecionado
                </span>

                <h2>
                  {diaAtual?.dia_semana
                    || 'Dia'}
                </h2>

                <p>
                  {formatarDataCompleta(
                    diaAtual?.data,
                  )}
                </p>
              </div>

              <button
                type="button"
                className="button-secondary"
                onClick={() =>
                  navigate(
                    `/semana?data_referencia=${diaAtual?.data || dataReferencia}`,
                  )
                }
              >
                Abrir no mapa da semana
              </button>
            </header>

            {(
              diaAtual
                ?.programacoes
                ?.length
              ?? 0
            ) === 0 ? (
              <div className="scale-week-empty">
                <strong>
                  Nenhuma programação neste dia.
                </strong>

                <span>
                  Não há escala para administrar.
                </span>
              </div>
            ) : programacoesDia.length === 0 ? (
              <div className="scale-week-empty scale-week-empty-filter">
                <strong>
                  Nada neste filtro.
                </strong>

                <span>
                  {modoVisualizacao === 'PENDENCIAS'
                    ? 'Este dia não possui pendências operacionais.'
                    : modoVisualizacao === 'SEM_ESCALA'
                      ? 'Nenhuma programação agendada está sem escala.'
                      : modoVisualizacao === 'CONFIRMADAS'
                        ? 'Nenhuma escala confirmada neste dia.'
                        : 'Nenhuma programação encontrada.'}
                </span>

                {modoVisualizacao !== 'TODAS' && (
                  <button
                    type="button"
                    className="button-secondary"
                    onClick={() =>
                      setModoVisualizacao(
                        'TODAS',
                      )
                    }
                  >
                    Mostrar todas
                  </button>
                )}
              </div>
            ) : (
              <div className="scale-week-programs">
                {programacoesDia.map(
                  (item) => (
                    <ScaleProgramCard
                      key={item.id}
                      item={item}
                      onManage={() =>
                        navigate(
                          `/gestao/programacoes/${item.id}/escala`,
                        )
                      }
                      onOpen={() =>
                        navigate(
                          `/programacoes/${item.id}`,
                        )
                      }
                    />
                  ),
                )}
              </div>
            )}
          </section>
        </>
      )}
    </div>
  )
}

function DayScaleNode({
  dia,
  selected,
  onClick,
}) {
  const programacoes =
    dia?.programacoes
    ?? []

  const pendencias =
    programacoes.filter(
      (item) =>
        item.status === 'AGENDADA'
        && (
          item.situacao_escala
            === 'PENDENTE_CONFIRMACAO'
          || item.situacao_escala
            === 'SEM_ESCALA'
          || item.situacao_escala
            === 'SEM_PARTICIPANTES_ATIVOS'
          || (
            item
              ?.cobertura_funcoes
              ?.sem_participante_ativo
            ?? 0
          ) > 0
        ),
    ).length

  const hoje =
    dia.data
    === hojeISO()

  return (
    <button
      type="button"
      className={[
        'scale-day-node',
        selected
          ? 'selected'
          : '',
        pendencias > 0
          ? 'attention'
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
        {abreviarDia(
          dia.dia_semana,
        )}
      </span>

      <strong>
        {diaDoMes(
          dia.data,
        )}
      </strong>

      <small>
        {programacoes.length === 0
          ? 'Livre'
          : `${programacoes.length} ${
              programacoes.length === 1
                ? 'programação'
                : 'programações'
            }`}
      </small>

      <i
        className={
          pendencias > 0
            ? 'scale-day-status attention'
            : programacoes.length > 0
              ? 'scale-day-status ok'
              : 'scale-day-status empty'
        }
      />

      {pendencias > 0 && (
        <em>
          {pendencias}
          {' '}
          {pendencias === 1
            ? 'pendência'
            : 'pendências'}
        </em>
      )}
    </button>
  )
}

function ScaleProgramCard({
  item,
  onManage,
  onOpen,
}) {
  const resumo =
    item?.resumo_escala
    ?? {}

  const escala =
    (item?.escala ?? [])
      .filter(
        (participacao) =>
          [
            'ESCALADO',
            'CONFIRMADO',
          ].includes(
            participacao.status,
          ),
      )

  return (
    <article
      className={
        `scale-program-card state-${String(
          item.situacao_escala
          || '',
        ).toLowerCase()}`
      }
    >
      <div className="scale-program-time">
        <strong>
          {hora(
            item
              ?.quando
              ?.inicio_em,
          )}
        </strong>

        <span>
          até{' '}
          {hora(
            item
              ?.quando
              ?.fim_em,
          )}
        </span>
      </div>

      <div className="scale-program-main">
        <div className="scale-program-top">
          <div>
            <span className="program-type">
              {item
                ?.tipo
                ?.nome_historico
                || 'Programação'}
            </span>

            <h3>
              {item.titulo}
            </h3>
          </div>

          <ScaleState
            state={
              item.situacao_escala
            }
          />
        </div>

        <div className="scale-program-context">
          <span>
            <b>Local</b>
            {item
              ?.local
              ?.nome_historico
              || 'Local não informado'}
          </span>

          <span>
            <b>Escala ativa</b>
            {resumo.ativas ?? 0}
          </span>

          <span>
            <b>Confirmados</b>
            {resumo.confirmados ?? 0}
          </span>

          <span>
            <b>Aguardando</b>
            {resumo.escalados ?? 0}
          </span>
        </div>


        <PendenciasProgramacao
          item={item}
        />

        <div className="scale-confirm-progress">
          <div>
            <span>
              Confirmação
            </span>

            <strong>
              {resumo
                .percentual_confirmacao
                ?? 0}%
            </strong>
          </div>

          <div className="scale-progress-track">
            <i
              style={{
                width:
                  `${Math.max(
                    0,
                    Math.min(
                      100,
                      resumo
                        .percentual_confirmacao
                      ?? 0,
                    ),
                  )}%`,
              }}
            />
          </div>
        </div>


        <CoberturaFuncoes
          cobertura={
            item.cobertura_funcoes
          }
          encerrada={
            item.situacao_escala
            === 'ENCERRADA'
          }
        />

        {escala.length > 0 && (
          <div className="scale-people">
            {escala
              .slice(0, 8)
              .map(
                (participacao) => (
                  <span
                    key={
                      participacao
                        .participacao_id
                    }
                    className="scale-person-chip"
                    title={
                      `${
                        participacao
                          ?.usuario
                          ?.nome_historico
                      } — ${
                        participacao
                          ?.funcao
                          ?.nome_historico
                      }`
                    }
                  >
                    <i>
                      {iniciais(
                        participacao
                          ?.usuario
                          ?.nome_historico,
                      )}
                    </i>

                    <span>
                      {
                        participacao
                          ?.usuario
                          ?.nome_historico
                      }
                    </span>

                    <b
                      className={
                        participacao.status
                          === 'CONFIRMADO'
                          ? 'confirmed'
                          : 'pending'
                      }
                    >
                      {participacao.status
                        === 'CONFIRMADO'
                          ? '✓'
                          : '?'}
                    </b>
                  </span>
                ),
              )}

            {escala.length > 8 && (
              <span className="scale-more-chip">
                +{escala.length - 8}
              </span>
            )}
          </div>
        )}

        <footer className="scale-program-actions">
          <button
            type="button"
            className="small-secondary-button"
            onClick={onOpen}
          >
            Detalhes
          </button>

          <button
            type="button"
            className="small-primary-button"
            disabled={
              item.status
              !== 'AGENDADA'
            }
            onClick={onManage}
          >
            {item.status === 'AGENDADA'
              ? 'Gerenciar escala'
              : 'Consultar escala'}
          </button>
        </footer>
      </div>
    </article>
  )
}

function PendenciasProgramacao({
  item,
}) {
  if (
    item.status
    !== 'AGENDADA'
  ) {
    return null
  }

  const itens = []

  if (
    item.situacao_escala
      === 'SEM_ESCALA'
  ) {
    itens.push(
      'Nenhuma pessoa foi adicionada à escala.',
    )
  }

  if (
    item.situacao_escala
      === 'SEM_PARTICIPANTES_ATIVOS'
  ) {
    itens.push(
      'A escala não possui participantes ativos.',
    )
  }

  const aguardando =
    item
      ?.resumo_escala
      ?.escalados
    ?? 0

  if (aguardando > 0) {
    itens.push(
      `${aguardando} ${
        aguardando === 1
          ? 'pessoa aguarda confirmação'
          : 'pessoas aguardam confirmação'
      }.`,
    )
  }

  const funcoes =
    item
      ?.cobertura_funcoes
      ?.sem_participante_ativo
    ?? 0

  if (funcoes > 0) {
    itens.push(
      `${funcoes} ${
        funcoes === 1
          ? 'função habilitada está sem participante'
          : 'funções habilitadas estão sem participante'
      }.`,
    )
  }

  if (itens.length === 0) {
    return (
      <div className="scale-operational-status ok">
        <strong>
          Sem pendências operacionais
        </strong>

        <span>
          Esta escala não exige ação imediata.
        </span>
      </div>
    )
  }

  return (
    <div className="scale-operational-status attention">
      <strong>
        Precisa de atenção
      </strong>

      <ul>
        {itens.map(
          (texto) => (
            <li key={texto}>
              {texto}
            </li>
          ),
        )}
      </ul>
    </div>
  )
}

function CoberturaFuncoes({
  cobertura,
  encerrada = false,
}) {
  const total =
    cobertura
      ?.habilitadas_total
    ?? 0

  const cobertas =
    cobertura
      ?.com_participante_ativo
    ?? 0

  const semParticipante =
    cobertura
      ?.funcoes_sem_participante
    ?? []

  if (total === 0) {
    return (
      <div className="scale-function-coverage neutral">
        <div>
          <span>
            Funções habilitadas
          </span>

          <strong>
            Nenhuma função configurada para este tipo
          </strong>
        </div>
      </div>
    )
  }

  return (
    <section
      className={
        encerrada
          ? 'scale-function-coverage closed'
          : semParticipante.length > 0
            ? 'scale-function-coverage attention'
            : 'scale-function-coverage complete'
      }
    >
      <header>
        <div>
          <span>
            Cobertura das funções habilitadas
          </span>

          <strong>
            {cobertas} de {total}
            {' '}
            com participante ativo
          </strong>
        </div>

        <b>
          {
            cobertura
              ?.percentual_cobertura
            ?? 0
          }%
        </b>
      </header>

      <div className="scale-function-track">
        <i
          style={{
            width:
              `${Math.max(
                0,
                Math.min(
                  100,
                  cobertura
                    ?.percentual_cobertura
                  ?? 0,
                ),
              )}%`,
          }}
        />
      </div>

      {!encerrada
        && semParticipante.length > 0 && (
        <div className="scale-function-gaps">
          <span>
            Habilitadas sem participante:
          </span>

          <div>
            {semParticipante.map(
              (funcao) => (
                <em
                  key={funcao.id}
                >
                  {funcao
                    ?.departamento
                    ?.nome
                    ? `${funcao.departamento.nome} · `
                    : ''}
                  {funcao.nome}
                </em>
              ),
            )}
          </div>

          <small>
            Isso é um alerta de cobertura.
            A função habilitada não é necessariamente
            obrigatória nesta ocorrência.
          </small>
        </div>
      )}
    </section>
  )
}

function ScaleState({
  state,
}) {
  const mapa = {
    SEM_ESCALA: {
      texto:
        'Sem escala',
      classe:
        'missing',
    },

    SEM_PARTICIPANTES_ATIVOS: {
      texto:
        'Sem participantes ativos',
      classe:
        'missing',
    },

    PENDENTE_CONFIRMACAO: {
      texto:
        'Aguardando confirmações',
      classe:
        'pending',
    },

    CONFIRMADA: {
      texto:
        'Escala confirmada',
      classe:
        'confirmed',
    },

    ENCERRADA: {
      texto:
        'Programação encerrada',
      classe:
        'closed',
    },
  }

  const item =
    mapa[state]
    ?? {
      texto:
        state || 'Escala',
      classe:
        '',
    }

  return (
    <span
      className={
        `scale-state ${item.classe}`
      }
    >
      {item.texto}
    </span>
  )
}

function Metric({
  label,
  value,
  attention = false,
  positive = false,
}) {
  return (
    <div
      className={[
        'scale-week-metric',
        attention
          ? 'attention'
          : '',
        positive
          ? 'positive'
          : '',
      ]
        .filter(Boolean)
        .join(' ')}
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
      : dataLocal(
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
      : dataLocal(
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
  const data =
    new Date()

  return formatarISO(
    data,
  )
}

function adicionarDiasISO(
  iso,
  dias,
) {
  const data =
    dataLocal(
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

  const data =
    dataLocal(
      valor,
    )

  return formatarISO(data)
    === valor
      ? valor
      : null
}

function dataLocal(
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
  if (!iso) {
    return '—'
  }

  return dataLocal(iso)
    .toLocaleDateString(
      'pt-BR',
      {
        day: '2-digit',
        month: 'short',
      },
    )
    .replace('.', '')
}

function formatarDataCompleta(
  iso,
) {
  if (!iso) {
    return ''
  }

  return dataLocal(iso)
    .toLocaleDateString(
      'pt-BR',
      {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
      },
    )
}

function diaDoMes(
  iso,
) {
  return String(
    dataLocal(iso)
      .getDate(),
  ).padStart(
    2,
    '0',
  )
}

function abreviarDia(
  nome,
) {
  return String(
    nome
    || 'Dia',
  )
    .split('-')[0]
    .slice(0, 3)
    .toUpperCase()
}

function hora(
  valor,
) {
  return valor
    ? String(valor)
        .slice(11, 16)
    : '--:--'
}

function iniciais(
  nome,
) {
  const partes =
    String(
      nome
      || '?',
    )
      .trim()
      .split(/\s+/)
      .filter(Boolean)

  return partes
    .slice(0, 2)
    .map(
      (item) =>
        item[0]
          ?.toUpperCase(),
    )
    .join('')
    || '?'
}
