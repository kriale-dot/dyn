import {
  useCallback,
  useEffect,
  useMemo,
  useState,
} from 'react'

import {
  Link,
  useParams,
} from 'react-router-dom'

import {
  confirmarParticipacao,
  getDetalheProgramacao,
  getHistoricoAlteracoesProgramacao,
  informarIndisponibilidade,
  recusarParticipacao,
} from '../api/api'

import {
  useAuth,
} from '../contexts/AuthContext'

import './ProgramacaoDetalheEtapa53.css'

export default function ProgramacaoDetalhePage() {
  const {
    id,
  } = useParams()

  const {
    usuario,
  } = useAuth()

  const papel =
    usuario?.papel?.codigo

  const podeVerHistorico =
    [
      'ADMINISTRADOR',
      'ORGANIZADOR',
    ].includes(papel)

  const [detalhe, setDetalhe] =
    useState(null)

  const [loading, setLoading] =
    useState(true)

  const [actionLoading, setActionLoading] =
    useState(null)

  const [error, setError] =
    useState('')

  const [success, setSuccess] =
    useState('')

  const [historicoAberto, setHistoricoAberto] =
    useState(false)

  const [historicoLoading, setHistoricoLoading] =
    useState(false)

  const [historicoCarregado, setHistoricoCarregado] =
    useState(false)

  const [historico, setHistorico] =
    useState(null)

  const [historicoError, setHistoricoError] =
    useState('')

  const carregar =
    useCallback(
      async () => {
        setLoading(true)
        setError('')

        try {
          const response =
            await getDetalheProgramacao(
              id,
            )

          setDetalhe(
            response?.dados
            ?? null,
          )
        } catch (err) {
          setError(
            err?.message
            || 'Não foi possível carregar a programação.',
          )
        } finally {
          setLoading(false)
        }
      },
      [id],
    )

  useEffect(() => {
    carregar()
  }, [carregar])

  const programacao =
    detalhe?.programacao
    ?? null

  const minhasParticipacoes =
    detalhe?.minhas_participacoes
    ?? []

  const escala =
    detalhe?.escala
    ?? []

  const gruposEscala =
    useMemo(
      () => agruparEscala(
        escala,
      ),
      [escala],
    )

  async function executarAcao(
    participacao,
    acao,
  ) {
    const chave =
      `${participacao.id}:${acao}`

    setActionLoading(chave)
    setError('')
    setSuccess('')

    try {
      if (acao === 'CONFIRMAR') {
        await confirmarParticipacao(
          participacao.id,
        )

        setSuccess(
          'Participação confirmada com sucesso.',
        )
      }

      if (acao === 'INDISPONIVEL') {
        await informarIndisponibilidade(
          participacao.id,
        )

        setSuccess(
          'Indisponibilidade registrada com sucesso.',
        )
      }

      if (acao === 'RECUSAR') {
        await recusarParticipacao(
          participacao.id,
        )

        setSuccess(
          'Participação recusada.',
        )
      }

      await carregar()
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível concluir a ação.',
      )
    } finally {
      setActionLoading(null)
    }
  }

  async function alternarHistorico() {
    const novoEstado =
      !historicoAberto

    setHistoricoAberto(
      novoEstado,
    )

    if (
      !novoEstado
      || historicoCarregado
      || historicoLoading
    ) {
      return
    }

    setHistoricoLoading(true)
    setHistoricoError('')

    try {
      const response =
        await getHistoricoAlteracoesProgramacao(
          id,
        )

      setHistorico(
        response?.dados
        ?? null,
      )

      setHistoricoCarregado(true)
    } catch (err) {
      setHistoricoError(
        err?.message
        || 'Não foi possível carregar o histórico.',
      )
    } finally {
      setHistoricoLoading(false)
    }
  }

  if (loading) {
    return (
      <div className="loading-card">
        Carregando programação...
      </div>
    )
  }

  if (!programacao) {
    return (
      <section className="panel">
        <h1>
          Programação não encontrada
        </h1>

        {error && (
          <div className="error-message">
            {error}
          </div>
        )}

        <Link
          to="/semana"
          className="text-link"
        >
          Voltar para Minha Semana
        </Link>
      </section>
    )
  }

  return (
    <div className="program-detail-page">
      <div className="detail-back-row">
        <Link
          to="/semana"
          className="text-link"
        >
          ← Minha Semana
        </Link>
      </div>

      <section className="program-detail-hero">
        <div>
          <span className="eyebrow">
            {programacao?.tipo?.nome}
          </span>

          <h1>
            {programacao?.titulo}
          </h1>

          {programacao?.descricao && (
            <p>
              {programacao.descricao}
            </p>
          )}
        </div>

        <span
          className={
            `program-status status-${String(
              programacao?.status
              || '',
            ).toLowerCase()}`
          }
        >
          {programacao?.status}
        </span>
      </section>

      <section className="detail-info-grid">
        <InfoCard
          label="Quando"
          value={
            formatarQuando(
              programacao?.quando,
            )
          }
        />

        <InfoCard
          label="Onde"
          value={
            programacao
              ?.local
              ?.nome
            || 'Local não informado'
          }
        />

        <InfoCard
          label="Organizador"
          value={
            programacao
              ?.organizador
              ?.nome
            || 'Não informado'
          }
        />
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

      {minhasParticipacoes.length > 0 && (
        <section className="panel">
          <div className="panel-heading detail-panel-heading">
            <div>
              <span className="eyebrow">
                Sua participação
              </span>

              <h2>
                O que você vai fazer
              </h2>
            </div>
          </div>

          <div className="my-participations-grid">
            {minhasParticipacoes.map(
              (participacao) => (
                <MinhaParticipacaoCard
                  key={participacao.id}
                  participacao={
                    participacao
                  }
                  actionLoading={
                    actionLoading
                  }
                  onAction={
                    executarAcao
                  }
                />
              ),
            )}
          </div>
        </section>
      )}

      <section className="panel">
        <div className="panel-heading detail-panel-heading">
          <div>
            <span className="eyebrow">
              Escala
            </span>

            <h2>
              Quem participa
            </h2>
          </div>

          <div className="scale-summary">
            <span>
              {
                detalhe
                  ?.resumo_escala
                  ?.confirmados
                ?? 0
              } confirmados
            </span>

            <span>
              {
                detalhe
                  ?.resumo_escala
                  ?.escalados
                ?? 0
              } aguardando
            </span>
          </div>
        </div>

        {escala.length === 0 ? (
          <p className="empty-state">
            Nenhuma pessoa foi adicionada
            à escala desta programação.
          </p>
        ) : (
          <div className="scale-groups">
            {Object.entries(
              gruposEscala,
            ).map(
              ([
                departamento,
                itens,
              ]) => (
                <ScaleGroup
                  key={departamento}
                  departamento={
                    departamento
                  }
                  itens={itens}
                />
              ),
            )}
          </div>
        )}
      </section>

      {podeVerHistorico && (
        <section className="program-history-shell">
          <header className="program-history-heading">
            <div>
              <span className="eyebrow">
                Administração
              </span>

              <h2>
                Histórico de alterações
              </h2>

              <p>
                Consulte mudanças importantes sem alterar
                o estado atual da programação.
              </p>
            </div>

            <button
              type="button"
              className="button-secondary"
              onClick={alternarHistorico}
            >
              {historicoAberto
                ? 'Ocultar histórico'
                : 'Ver histórico'}
            </button>
          </header>

          {historicoAberto && (
            <div className="program-history-content">
              {historicoLoading ? (
                <div className="program-history-loading">
                  Carregando histórico...
                </div>
              ) : historicoError ? (
                <div className="error-message">
                  {historicoError}
                </div>
              ) : (
                <HistoricoProgramacao
                  historico={historico}
                />
              )}
            </div>
          )}
        </section>
      )}
    </div>
  )
}

function HistoricoProgramacao({
  historico,
}) {
  const eventos =
    historico?.eventos
    ?? []

  if (eventos.length === 0) {
    return (
      <div className="program-history-empty">
        <strong>
          Nenhuma alteração registrada.
        </strong>

        <span>
          O histórico começará a aparecer quando
          título, descrição, horário, local ou status
          forem alterados.
        </span>
      </div>
    )
  }

  return (
    <div className="program-history-timeline">
      {eventos.map(
        (evento) => (
          <article
            key={evento.id}
            className="program-history-event"
          >
            <div className="program-history-marker">
              <span
                className={
                  evento.tipo
                    === 'PROGRAMACAO_CANCELADA'
                    ? 'program-history-dot cancelled'
                    : 'program-history-dot'
                }
              />

              <span className="program-history-line" />
            </div>

            <div className="program-history-event-body">
              <header>
                <div>
                  <strong>
                    {traduzirTipoEvento(
                      evento.tipo,
                    )}
                  </strong>

                  <span>
                    {formatarDataHoraHistorico(
                      evento.criada_em,
                    )}
                  </span>
                </div>

                <span className="program-history-change-count">
                  {
                    evento
                      ?.alteracoes
                      ?.length
                    ?? 0
                  }
                  {' '}
                  alteração(ões)
                </span>
              </header>

              <div className="program-history-changes">
                {(evento.alteracoes ?? []).map(
                  (
                    alteracao,
                    indice,
                  ) => (
                    <AlteracaoHistorico
                      key={
                        `${evento.id}-${alteracao.campo}-${indice}`
                      }
                      alteracao={alteracao}
                    />
                  ),
                )}
              </div>
            </div>
          </article>
        ),
      )}
    </div>
  )
}

function AlteracaoHistorico({
  alteracao,
}) {
  const campo =
    alteracao?.campo
    ?? ''

  return (
    <div className="program-history-change">
      <span className="program-history-field">
        {traduzirCampoHistorico(
          campo,
        )}
      </span>

      <div className="program-history-values">
        <span className="program-history-old">
          {formatarValorHistorico(
            campo,
            alteracao?.anterior,
          )}
        </span>

        <span className="program-history-arrow">
          →
        </span>

        <strong>
          {formatarValorHistorico(
            campo,
            alteracao?.novo,
          )}
        </strong>
      </div>
    </div>
  )
}

function MinhaParticipacaoCard({
  participacao,
  actionLoading,
  onAction,
}) {
  const acoes =
    participacao
      ?.acoes_disponiveis
    ?? []

  return (
    <article className="my-participation-card">
      <div className="my-participation-top">
        <div>
          <span className="muted">
            Função
          </span>

          <strong>
            {participacao
              ?.funcao
              ?.nome}
          </strong>
        </div>

        <span
          className={
            participacao.status
              === 'ESCALADO'
              ? 'status-pill pending'
              : 'status-pill'
          }
        >
          {traduzirStatus(
            participacao.status,
          )}
        </span>
      </div>

      {acoes.length > 0 && (
        <div className="participation-actions">
          {acoes.includes(
            'CONFIRMAR',
          ) && (
            <ActionButton
              className="action-confirm"
              loading={
                actionLoading
                === `${participacao.id}:CONFIRMAR`
              }
              onClick={() =>
                onAction(
                  participacao,
                  'CONFIRMAR',
                )
              }
            >
              Confirmar
            </ActionButton>
          )}

          {acoes.includes(
            'INDISPONIVEL',
          ) && (
            <ActionButton
              className="action-secondary"
              loading={
                actionLoading
                === `${participacao.id}:INDISPONIVEL`
              }
              onClick={() =>
                onAction(
                  participacao,
                  'INDISPONIVEL',
                )
              }
            >
              Estou indisponível
            </ActionButton>
          )}

          {acoes.includes(
            'RECUSAR',
          ) && (
            <ActionButton
              className="action-danger"
              loading={
                actionLoading
                === `${participacao.id}:RECUSAR`
              }
              onClick={() =>
                onAction(
                  participacao,
                  'RECUSAR',
                )
              }
            >
              Recusar
            </ActionButton>
          )}
        </div>
      )}
    </article>
  )
}

function ActionButton({
  children,
  loading,
  className,
  onClick,
}) {
  return (
    <button
      type="button"
      className={
        `participation-action ${className}`
      }
      disabled={loading}
      onClick={onClick}
    >
      {loading
        ? 'Aguarde...'
        : children}
    </button>
  )
}

function ScaleGroup({
  departamento,
  itens,
}) {
  return (
    <section className="scale-group">
      <h3>
        {departamento}
      </h3>

      <div className="scale-list">
        {itens.map(
          (item) => (
            <article
              key={
                item
                  .participacao_id
              }
              className="scale-person"
            >
              <div className="scale-avatar">
                {obterIniciais(
                  item
                    ?.usuario
                    ?.nome,
                )}
              </div>

              <div className="scale-person-main">
                <strong>
                  {item
                    ?.usuario
                    ?.nome}
                </strong>

                <span>
                  {item
                    ?.funcao
                    ?.nome}
                </span>
              </div>

              <span
                className={
                  item.status
                    === 'ESCALADO'
                    ? 'status-pill pending'
                    : 'status-pill'
                }
              >
                {traduzirStatus(
                  item.status,
                )}
              </span>
            </article>
          ),
        )}
      </div>
    </section>
  )
}

function InfoCard({
  label,
  value,
}) {
  return (
    <article className="detail-info-card">
      <span>{label}</span>
      <strong>{value}</strong>
    </article>
  )
}

function agruparEscala(
  escala,
) {
  return escala.reduce(
    (
      grupos,
      item,
    ) => {
      const departamento =
        item
          ?.funcao
          ?.departamento
        || 'Sem departamento'

      if (!grupos[departamento]) {
        grupos[departamento] = []
      }

      grupos[departamento].push(
        item,
      )

      return grupos
    },
    {},
  )
}

function formatarQuando(
  quando,
) {
  const inicio =
    quando?.inicio_em

  const fim =
    quando?.fim_em

  if (!inicio) {
    return 'Data não informada'
  }

  const data =
    novaDataLocal(
      inicio.slice(0, 10),
    )
      .toLocaleDateString(
        'pt-BR',
        {
          weekday: 'long',
          day: '2-digit',
          month: 'long',
        },
      )

  const horaInicio =
    inicio.slice(11, 16)

  const horaFim =
    fim
      ? fim.slice(11, 16)
      : null

  return horaFim
    ? `${capitalizar(data)}, ${horaInicio}–${horaFim}`
    : `${capitalizar(data)}, ${horaInicio}`
}

function novaDataLocal(iso) {
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

function capitalizar(texto) {
  if (!texto) {
    return ''
  }

  return (
    texto.charAt(0).toUpperCase()
    + texto.slice(1)
  )
}

function traduzirStatus(
  status,
) {
  const mapa = {
    ESCALADO: 'A confirmar',
    CONFIRMADO: 'Confirmado',
    INDISPONIVEL: 'Indisponível',
    RECUSADO: 'Recusado',
    CANCELADO: 'Cancelado',
  }

  return mapa[status]
    || status
}

function obterIniciais(
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
    || ''

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

function traduzirTipoEvento(
  tipo,
) {
  const mapa = {
    PROGRAMACAO_ALTERADA:
      'Programação alterada',

    PROGRAMACAO_CANCELADA:
      'Programação cancelada',
  }

  return mapa[tipo]
    || 'Alteração registrada'
}

function traduzirCampoHistorico(
  campo,
) {
  const mapa = {
    titulo: 'Título',
    descricao: 'Descrição',
    inicio_em: 'Início',
    fim_em: 'Término',
    local: 'Local',
    status: 'Status',
  }

  return mapa[campo]
    || campo
}

function formatarValorHistorico(
  campo,
  valor,
) {
  if (
    valor === null
    || valor === undefined
    || valor === ''
  ) {
    return 'Não informado'
  }

  if (
    campo === 'inicio_em'
    || campo === 'fim_em'
  ) {
    return formatarDataHoraHistorico(
      valor,
    )
  }

  if (campo === 'status') {
    return traduzirStatus(
      String(valor),
    )
  }

  return String(valor)
}

function formatarDataHoraHistorico(
  valor,
) {
  if (!valor) {
    return 'Data não informada'
  }

  const data =
    new Date(
      String(valor)
        .replace(' ', 'T'),
    )

  if (
    Number.isNaN(
      data.getTime(),
    )
  ) {
    return String(valor)
  }

  return data.toLocaleString(
    'pt-BR',
    {
      dateStyle: 'short',
      timeStyle: 'short',
    },
  )
}

