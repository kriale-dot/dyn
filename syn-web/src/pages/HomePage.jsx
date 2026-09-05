import {
  useCallback,
  useEffect,
  useMemo,
  useState,
} from 'react'

import {
  useNavigate,
} from 'react-router-dom'

import {
  confirmarParticipacao,
  getDashboard,
  getEscalasSemana,
} from '../api/api'

import {
  useAuth,
} from '../contexts/AuthContext'

import './HomePageEtapa47.css'
import './HomePageEtapa57.css'
import './HomePageEtapa62.css'
import './HomePageMobileEtapa103.css'

/**
 * Endereço da API.
 *
 * Se VITE_API_URL não estiver configurado, usa automaticamente
 * o mesmo host pelo qual o frontend foi aberto.
 *
 * Exemplo no celular:
 * frontend http://192.168.15.8:5173
 * API      http://192.168.15.8:8282
 */
const API_URL =
  String(
    import.meta.env.VITE_API_URL
    || '',
  ).trim()
  || (
    typeof window !== 'undefined'
      ? `${window.location.protocol}//${window.location.hostname}:8282`
      : 'http://localhost:8282'
  )

export default function HomePage() {
  const navigate =
    useNavigate()

  const {
    usuario,
    capacidades,
  } = useAuth()

  const podeGerenciarEscalas =
    Boolean(
      capacidades
        ?.gerenciar_escalas,
    )

  const [dashboard, setDashboard] =
    useState(null)

  const [
    escalasSemana,
    setEscalasSemana,
  ] =
    useState(null)

  const [
    escalasSemanaError,
    setEscalasSemanaError,
  ] =
    useState('')

  const [loading, setLoading] =
    useState(true)

  const [busy, setBusy] =
    useState('')

  const [error, setError] =
    useState('')

  const [success, setSuccess] =
    useState('')

  const carregar =
    useCallback(
      async () => {
        setLoading(true)
        setError('')

        try {
          const response =
            await getDashboard()

          const dadosDashboard =
            response?.dados
            ?? null

          setDashboard(
            dadosDashboard,
          )

          /**
           * A Home é útil para todos os membros.
           * Por isso uma falha na projeção administrativa
           * NÃO derruba a página inicial inteira.
           */
          if (podeGerenciarEscalas) {
            setEscalasSemanaError('')

            try {
              const responseEscalas =
                await getEscalasSemana(
                  dadosDashboard
                    ?.semana
                    ?.inicio
                  ?? null,
                )

              setEscalasSemana(
                responseEscalas
                  ?.dados
                ?? null,
              )
            } catch (errEscalas) {
              setEscalasSemana(null)

              setEscalasSemanaError(
                errEscalas?.message
                || 'Não foi possível carregar o resumo das escalas.',
              )
            }
          } else {
            setEscalasSemana(null)
            setEscalasSemanaError('')
          }
        } catch (err) {
          setError(
            err?.message
            || 'Não foi possível carregar a página inicial.',
          )
        } finally {
          setLoading(false)
        }
      },
      [podeGerenciarEscalas],
    )

  useEffect(() => {
    carregar()
  }, [carregar])

  const compromissos =
    useMemo(
      () =>
        Array.isArray(
          dashboard
            ?.meus_compromissos,
        )
          ? dashboard
              .meus_compromissos
              .slice()
              .sort(
                (a, b) =>
                  compararDataHora(
                    a
                      ?.programacao
                      ?.inicio_em,
                    b
                      ?.programacao
                      ?.inicio_em,
                  ),
              )
          : [],
      [dashboard],
    )

  const programacoes =
    useMemo(
      () =>
        Array.isArray(
          dashboard
            ?.programacoes_da_semana,
        )
          ? dashboard
              .programacoes_da_semana
              .slice()
              .sort(
                (a, b) =>
                  compararDataHora(
                    a?.inicio_em,
                    b?.inicio_em,
                  ),
              )
          : [],
      [dashboard],
    )

  const aniversariantes =
    useMemo(
      () =>
        Array.isArray(
          dashboard
            ?.aniversariantes_da_semana,
        )
          ? dashboard
              .aniversariantes_da_semana
          : [],
      [dashboard],
    )

  const proximoCompromisso =
    useMemo(
      () => {
        const agora =
          new Date()

        return (
          compromissos.find(
            (item) => {
              const fim =
                parseDataHora(
                  item
                    ?.programacao
                    ?.fim_em,
                )

              return (
                fim
                && fim >= agora
              )
            },
          )
          ?? null
        )
      },
      [compromissos],
    )

  const pendentes =
    useMemo(
      () =>
        compromissos.filter(
          (item) =>
            item
              ?.participacao_status
              === 'ESCALADO',
        ),
      [compromissos],
    )

  const dias =
    useMemo(
      () =>
        montarDiasSemana(
          dashboard?.semana,
          programacoes,
          compromissos,
        ),
      [
        dashboard,
        programacoes,
        compromissos,
      ],
    )

  async function confirmar(
    participacaoId,
  ) {
    const chave =
      `confirm:${participacaoId}`

    setBusy(chave)
    setError('')
    setSuccess('')

    try {
      await confirmarParticipacao(
        participacaoId,
      )

      setSuccess(
        'Participação confirmada.',
      )

      await carregar()
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível confirmar sua participação.',
      )
    } finally {
      setBusy('')
    }
  }

  if (loading) {
    return (
      <div className="loading-card">
        Organizando sua semana...
      </div>
    )
  }

  return (
    <div className="syn-home-page">
      <section className="syn-home-intro">
        <div>
          <span className="eyebrow">
            {saudacao()}
          </span>

          <h1>
            {primeiroNome(
              usuario?.nome,
            )},
            {' '}
            aqui está sua semana.
          </h1>

          <p>
            Seus compromissos aparecem primeiro.
            Depois, você vê o que acontece na
            igreja sem precisar procurar em uma agenda.
          </p>
        </div>

        <div className="syn-home-week-label syn-home-week-label-57">
          <span>
            Semana do ano
          </span>

          <strong className="syn-home-week-number-57">
            {dashboard?.semana?.inicio
              ? `Semana ${obterNumeroSemanaISO(
                  dashboard.semana.inicio,
                )}`
              : 'Semana atual'}
          </strong>

          <small>
            {formatarPeriodo(
              dashboard?.semana,
            )}
          </small>
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

      <section className="syn-home-priority-grid">
        <ProximoCompromisso
          item={proximoCompromisso}
          busy={busy}
          onConfirm={confirmar}
          onOpen={(id) =>
            navigate(
              `/programacoes/${id}`,
            )
          }
          onWeek={() =>
            navigate('/semana')
          }
        />

        <PendenciasCard
          itens={pendentes}
          busy={busy}
          onConfirm={confirmar}
          onOpen={(id) =>
            navigate(
              `/programacoes/${id}`,
            )
          }
        />
      </section>

      {podeGerenciarEscalas && (
        <GestaoSemanaCard
          dados={escalasSemana}
          error={escalasSemanaError}
          onOpen={() =>
            navigate(
              `/gestao/escalas-semana?data_referencia=${
                dashboard
                  ?.semana
                  ?.inicio
                || hojeISO()
              }`,
            )
          }
        />
      )}

      <section className="syn-week-map-card">
        <header className="syn-section-heading">
          <div>
            <span className="eyebrow">
              Visão da semana
            </span>

            <h2>
              Onde estão os pontos importantes
            </h2>
          </div>

          <button
            type="button"
            className="syn-text-button"
            onClick={() =>
              navigate('/semana')
            }
          >
            Abrir Minha Semana →
          </button>
        </header>

        <div className="syn-week-strip">
          {dias.map(
            (dia) => (
              <button
                type="button"
                key={dia.data}
                className={[
                  'syn-week-day',
                  dia.hoje
                    ? 'today'
                    : '',
                  dia.meus > 0
                    ? 'personal'
                    : '',
                ]
                  .filter(Boolean)
                  .join(' ')}
                onClick={() =>
                  navigate(
                    `/semana?data_referencia=${dia.data}`,
                  )
                }
              >
                <span className="syn-week-day-name">
                  {dia.nome}
                </span>

                <strong>
                  {dia.numero}
                </strong>

                <span className="syn-week-day-month">
                  {dia.mes}
                </span>

                <span className="syn-week-dots">
                  {dia.meus > 0 && (
                    <i
                      className="personal"
                      title={`${dia.meus} compromisso(s) seu(s)`}
                    />
                  )}

                  {dia.programacoes > 0 && (
                    <i
                      className="general"
                      title={`${dia.programacoes} programação(ões)`}
                    />
                  )}
                </span>

                <small>
                  {textoDia(
                    dia,
                  )}
                </small>
              </button>
            ),
          )}
        </div>
      </section>

      <section className="syn-home-secondary-grid">
        <section className="syn-home-panel">
          <header className="syn-section-heading">
            <div>
              <span className="eyebrow">
                Igreja
              </span>

              <h2>
                Acontece nesta semana
              </h2>
            </div>

            <button
              type="button"
              className="syn-text-button"
              onClick={() =>
                navigate(
                  '/programacoes',
                )
              }
            >
              Ver todas →
            </button>
          </header>

          {programacoes.length === 0 ? (
            <p className="empty-state">
              Nenhuma programação cadastrada
              para esta semana.
            </p>
          ) : (
            <div className="syn-home-program-list">
              {programacoes
                .slice(0, 5)
                .map(
                  (item) => (
                    <button
                      type="button"
                      key={item.id}
                      className="syn-home-program-row"
                      onClick={() =>
                        navigate(
                          `/programacoes/${item.id}`,
                        )
                      }
                    >
                      <span className="syn-home-program-date">
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
                      </span>

                      <span className="syn-home-program-copy">
                        <strong>
                          {item.titulo}
                        </strong>

                        <span>
                          {formatarHora(
                            item.inicio_em,
                          )}
                          {' · '}
                          {item.local
                            || 'Local não informado'}
                        </span>
                      </span>

                      <span className="syn-home-program-arrow">
                        →
                      </span>
                    </button>
                  ),
                )}
            </div>
          )}
        </section>

        <section className="syn-home-panel">
          <header className="syn-section-heading">
            <div>
              <span className="eyebrow">
                Pessoas
              </span>

              <h2>
                Aniversários da semana
              </h2>
            </div>
          </header>

          {aniversariantes.length === 0 ? (
            <p className="empty-state">
              Nenhum aniversário nesta semana.
            </p>
          ) : (
            <div className="syn-birthday-list">
              {aniversariantes.map(
                (pessoa) => (
                  <article
                    key={
                      `${pessoa.usuario_id}-${pessoa.data}`
                    }
                    className="syn-birthday-row"
                  >
                    <div className="syn-birthday-avatar">
                      {pessoa.foto ? (
                        <img
                          src={
                            resolverArquivoApi(
                              pessoa.foto,
                            )
                          }
                          alt=""
                        />
                      ) : (
                        <span>
                          {iniciais(
                            pessoa.nome,
                          )}
                        </span>
                      )}
                    </div>

                    <div>
                      <strong>
                        {pessoa.nome}
                      </strong>

                      <span>
                        {formatarDataAniversario(
                          pessoa.data,
                        )}
                      </span>
                    </div>
                  </article>
                ),
              )}
            </div>
          )}
        </section>
      </section>
    </div>
  )
}

function GestaoSemanaCard({
  dados,
  error,
  onOpen,
}) {
  if (error) {
    return (
      <section className="syn-management-week-card error-state">
        <div>
          <span className="eyebrow">
            Gestão da semana
          </span>

          <h2>
            O resumo das escalas não pôde ser carregado.
          </h2>

          <p>
            A Home continua disponível normalmente.
            Você ainda pode abrir a tela completa de escalas.
          </p>
        </div>

        <button
          type="button"
          className="button-secondary"
          onClick={onOpen}
        >
          Abrir Escalas da Semana
        </button>
      </section>
    )
  }

  if (!dados) {
    return null
  }

  const resumo =
    dados?.resumo
    ?? {}

  const semEscala =
    resumo
      .programacoes_sem_escala
    ?? 0

  const aguardando =
    resumo
      .pendentes_confirmacao
    ?? 0

  const funcoesSemParticipante =
    resumo
      .funcoes_sem_participante
    ?? 0

  const totalAlertas =
    semEscala
    + aguardando
    + funcoesSemParticipante

  const tudoResolvido =
    totalAlertas === 0

  return (
    <section
      className={
        tudoResolvido
          ? 'syn-management-week-card complete'
          : 'syn-management-week-card attention'
      }
    >
      <div className="syn-management-week-copy">
        <span className="eyebrow">
          Gestão da Semana {
            dados
              ?.semana
              ?.numero_iso
            ?? ''
          }
        </span>

        <h2>
          {tudoResolvido
            ? 'As escalas estão em ordem.'
            : 'Há pontos que precisam de atenção.'}
        </h2>

        <p>
          {tudoResolvido
            ? 'Nenhuma pendência operacional foi encontrada nas programações desta semana.'
            : 'Veja rapidamente onde ainda falta montar escala, receber confirmações ou revisar funções habilitadas.'}
        </p>
      </div>

      <div className="syn-management-week-metrics">
        <GestaoMetric
          value={
            resumo
              .programacoes
            ?? 0
          }
          label="programações"
        />

        <GestaoMetric
          value={semEscala}
          label="sem escala"
          attention={
            semEscala > 0
          }
        />

        <GestaoMetric
          value={aguardando}
          label="aguardando"
          attention={
            aguardando > 0
          }
        />

        <GestaoMetric
          value={
            funcoesSemParticipante
          }
          label="funções sem participante"
          attention={
            funcoesSemParticipante > 0
          }
        />
      </div>

      <button
        type="button"
        className={
          tudoResolvido
            ? 'button-secondary'
            : 'button-primary'
        }
        onClick={onOpen}
      >
        Abrir Escalas da Semana
      </button>
    </section>
  )
}

function GestaoMetric({
  value,
  label,
  attention = false,
}) {
  return (
    <div
      className={
        attention
          ? 'syn-management-metric attention'
          : 'syn-management-metric'
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

function ProximoCompromisso({
  item,
  busy,
  onConfirm,
  onOpen,
  onWeek,
}) {
  if (!item) {
    return (
      <section className="syn-next-card empty">
        <span className="eyebrow">
          Próximo compromisso
        </span>

        <h2>
          Sua semana está livre.
        </h2>

        <p>
          Você não possui compromissos
          ativos no restante desta semana.
        </p>

        <button
          type="button"
          className="button-secondary"
          onClick={onWeek}
        >
          Ver mapa da semana
        </button>
      </section>
    )
  }

  const programacao =
    item.programacao

  const pendente =
    item
      .participacao_status
      === 'ESCALADO'

  const chave =
    `confirm:${item.participacao_id}`

  return (
    <section className="syn-next-card">
      <div className="syn-next-topline">
        <span className="eyebrow">
          Próximo compromisso
        </span>

        <span
          className={
            pendente
              ? 'syn-next-status pending'
              : 'syn-next-status confirmed'
          }
        >
          {pendente
            ? 'Aguardando confirmação'
            : 'Confirmado'}
        </span>
      </div>

      <div className="syn-next-date">
        <strong>
          {formatarDiaSemanaLongo(
            programacao?.inicio_em,
          )}
        </strong>

        <span>
          {formatarDataCompleta(
            programacao?.inicio_em,
          )}
          {' · '}
          {formatarHora(
            programacao?.inicio_em,
          )}
        </span>
      </div>

      <h2>
        {programacao?.titulo}
      </h2>

      <div className="syn-next-details">
        <span>
          <b>Função</b>
          {item.funcao
            || 'Não informada'}
        </span>

        <span>
          <b>Local</b>
          {programacao?.local
            || 'Local não informado'}
        </span>
      </div>

      <div className="syn-next-actions">
        {pendente && (
          <button
            type="button"
            className="button-primary"
            disabled={
              busy === chave
            }
            onClick={() =>
              onConfirm(
                item.participacao_id,
              )
            }
          >
            {busy === chave
              ? 'Confirmando...'
              : 'Confirmar participação'}
          </button>
        )}

        <button
          type="button"
          className="button-secondary"
          onClick={() =>
            onOpen(
              programacao?.id,
            )
          }
        >
          Ver detalhes
        </button>
      </div>
    </section>
  )
}

function PendenciasCard({
  itens,
  busy,
  onConfirm,
  onOpen,
}) {
  return (
    <section className="syn-pending-card">
      <header>
        <div>
          <span className="eyebrow">
            Precisa de você
          </span>

          <h2>
            {itens.length === 0
              ? 'Nenhuma pendência'
              : `${itens.length} ${
                  itens.length === 1
                    ? 'confirmação'
                    : 'confirmações'
                }`}
          </h2>
        </div>

        <span
          className={
            itens.length > 0
              ? 'syn-pending-count attention'
              : 'syn-pending-count'
          }
        >
          {itens.length}
        </span>
      </header>

      {itens.length === 0 ? (
        <div className="syn-pending-empty">
          <span className="syn-check-mark">
            ✓
          </span>

          <p>
            Você respondeu todas as
            solicitações de escala desta semana.
          </p>
        </div>
      ) : (
        <div className="syn-pending-list">
          {itens
            .slice(0, 3)
            .map(
              (item) => {
                const programacao =
                  item.programacao

                const chave =
                  `confirm:${item.participacao_id}`

                return (
                  <article
                    key={
                      item.participacao_id
                    }
                    className="syn-pending-item"
                  >
                    <button
                      type="button"
                      className="syn-pending-copy"
                      onClick={() =>
                        onOpen(
                          programacao?.id,
                        )
                      }
                    >
                      <strong>
                        {programacao?.titulo}
                      </strong>

                      <span>
                        {formatarDiaMes(
                          programacao
                            ?.inicio_em,
                        )}
                        {' · '}
                        {formatarHora(
                          programacao
                            ?.inicio_em,
                        )}
                        {' · '}
                        {item.funcao}
                      </span>
                    </button>

                    <button
                      type="button"
                      className="syn-mini-confirm"
                      disabled={
                        busy === chave
                      }
                      onClick={() =>
                        onConfirm(
                          item
                            .participacao_id,
                        )
                      }
                    >
                      {busy === chave
                        ? '...'
                        : 'Confirmar'}
                    </button>
                  </article>
                )
              },
            )}
        </div>
      )}
    </section>
  )
}

function montarDiasSemana(
  semana,
  programacoes,
  compromissos,
) {
  const inicio =
    parseDataSomente(
      semana?.inicio,
    )

  if (!inicio) {
    return []
  }

  const hoje =
    dataChave(
      new Date(),
    )

  return Array.from(
    {
      length: 7,
    },
    (_, indice) => {
      const data =
        new Date(
          inicio.getFullYear(),
          inicio.getMonth(),
          inicio.getDate()
            + indice,
        )

      const chave =
        dataChave(data)

      const qtdProgramacoes =
        programacoes.filter(
          (item) =>
            inicioDataHoraChave(
              item.inicio_em,
            ) === chave,
        ).length

      const qtdMeus =
        compromissos.filter(
          (item) =>
            inicioDataHoraChave(
              item
                ?.programacao
                ?.inicio_em,
            ) === chave,
        ).length

      return {
        data: chave,
        nome:
          data
            .toLocaleDateString(
              'pt-BR',
              {
                weekday: 'short',
              },
            )
            .replace('.', '')
            .toUpperCase(),
        numero:
          String(
            data.getDate(),
          ).padStart(
            2,
            '0',
          ),
        mes:
          data
            .toLocaleDateString(
              'pt-BR',
              {
                month: 'short',
              },
            )
            .replace('.', '')
            .toUpperCase(),
        hoje:
          chave === hoje,
        programacoes:
          qtdProgramacoes,
        meus:
          qtdMeus,
      }
    },
  )
}

function textoDia(
  dia,
) {
  if (
    dia.meus > 0
  ) {
    return dia.meus === 1
      ? '1 meu compromisso'
      : `${dia.meus} meus compromissos`
  }

  if (
    dia.programacoes > 0
  ) {
    return dia.programacoes === 1
      ? '1 programação'
      : `${dia.programacoes} programações`
  }

  return 'Livre'
}

function primeiroNome(
  nome,
) {
  return (
    nome
      ?.trim()
      ?.split(/\s+/)
      ?.[0]
    || 'Olá'
  )
}

function saudacao() {
  const hora =
    new Date().getHours()

  if (hora < 12) {
    return 'Bom dia'
  }

  if (hora < 18) {
    return 'Boa tarde'
  }

  return 'Boa noite'
}

function parseDataSomente(
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

  return new Date(
    ano,
    mes - 1,
    dia,
  )
}

function parseDataHora(
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
    parseDataHora(a)

  const db =
    parseDataHora(b)

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

function dataChave(
  data,
) {
  return [
    data.getFullYear(),
    String(
      data.getMonth() + 1,
    ).padStart(2, '0'),
    String(
      data.getDate(),
    ).padStart(2, '0'),
  ].join('-')
}

function inicioDataHoraChave(
  valor,
) {
  if (!valor) {
    return ''
  }

  return String(valor)
    .slice(0, 10)
}

function hojeISO() {
  const data =
    new Date()

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

function obterNumeroSemanaISO(
  iso,
) {
  const data =
    parseDataSomente(
      iso,
    )

  if (!data) {
    return null
  }

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

function formatarPeriodo(
  semana,
) {
  const inicio =
    parseDataSomente(
      semana?.inicio,
    )

  const fim =
    parseDataSomente(
      semana?.fim,
    )

  if (!inicio || !fim) {
    return 'Semana atual'
  }

  return `${inicio.toLocaleDateString(
    'pt-BR',
    {
      day: '2-digit',
      month: 'short',
    },
  )} — ${fim.toLocaleDateString(
    'pt-BR',
    {
      day: '2-digit',
      month: 'short',
    },
  )}`
}

function formatarHora(
  valor,
) {
  return valor
    ? String(valor)
        .slice(11, 16)
    : '--:--'
}

function formatarDia(
  valor,
) {
  const data =
    parseDataHora(valor)

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

function formatarDiaSemanaLongo(
  valor,
) {
  const data =
    parseDataHora(valor)

  if (!data) {
    return 'Data não informada'
  }

  const texto =
    data.toLocaleDateString(
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

function formatarDataCompleta(
  valor,
) {
  const data =
    parseDataHora(valor)

  if (!data) {
    return ''
  }

  return data.toLocaleDateString(
    'pt-BR',
    {
      day: '2-digit',
      month: 'long',
    },
  )
}

function formatarDiaMes(
  valor,
) {
  const data =
    parseDataHora(valor)

  if (!data) {
    return ''
  }

  return data.toLocaleDateString(
    'pt-BR',
    {
      day: '2-digit',
      month: 'short',
    },
  )
}

function formatarDataAniversario(
  valor,
) {
  const data =
    parseDataSomente(valor)

  if (!data) {
    return ''
  }

  const hoje =
    dataChave(
      new Date(),
    )

  if (
    dataChave(data)
    === hoje
  ) {
    return 'Hoje 🎉'
  }

  return data.toLocaleDateString(
    'pt-BR',
    {
      weekday: 'long',
      day: '2-digit',
      month: 'long',
    },
  )
}

function resolverArquivoApi(
  caminho,
) {
  if (!caminho) {
    return null
  }

  if (
    /^https?:\/\//i.test(
      caminho,
    )
  ) {
    return caminho
  }

  return `${
    API_URL.replace(
      /\/+$/,
      '',
    )
  }/${
    String(caminho).replace(
      /^\/+/,
      '',
    )
  }`
}

function iniciais(
  nome,
) {
  if (!nome) {
    return '?'
  }

  const partes =
    nome
      .trim()
      .split(/\s+/)

  const primeira =
    partes[0]?.[0]
    ?? ''

  const ultima =
    partes.length > 1
      ? partes[
          partes.length - 1
        ]?.[0]
      : ''

  return (
    primeira + ultima
  ).toUpperCase()
}
