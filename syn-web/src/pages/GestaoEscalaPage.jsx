import {
  useCallback,
  useEffect,
  useState,
} from 'react'

import {
  Link,
  useParams,
} from 'react-router-dom'

import {
  adicionarParticipacao,
  cancelarParticipacao,
  getGestaoEscala,
} from '../api/api'

export default function GestaoEscalaPage() {
  const {
    id,
  } = useParams()

  const [dados, setDados] =
    useState(null)

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
            await getGestaoEscala(
              id,
            )

          setDados(
            response?.dados
            ?? null,
          )
        } catch (err) {
          setError(
            err?.message
            || 'Não foi possível carregar a gestão da escala.',
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

  async function adicionar(
    funcao,
    candidato,
  ) {
    const chave =
      `add:${funcao.id}:${candidato.usuario_id}`

    setBusy(chave)
    setError('')
    setSuccess('')

    try {
      await adicionarParticipacao(
        id,
        candidato.usuario_id,
        funcao.id,
        false,
      )

      setSuccess(
        `${candidato.nome} foi adicionado à escala.`,
      )

      await carregar()
    } catch (err) {
      if (err?.status === 409) {
        const confirmar =
          window.confirm(
            'Foi encontrado um conflito de horário para esta pessoa. Deseja adicionar mesmo assim?',
          )

        if (confirmar) {
          try {
            await adicionarParticipacao(
              id,
              candidato.usuario_id,
              funcao.id,
              true,
            )

            setSuccess(
              `${candidato.nome} foi adicionado com o conflito confirmado.`,
            )

            await carregar()
            return
          } catch (retryError) {
            setError(
              retryError?.message
              || 'Não foi possível confirmar o conflito.',
            )
          }
        }
      } else {
        setError(
          err?.message
          || 'Não foi possível adicionar a pessoa.',
        )
      }
    } finally {
      setBusy('')
    }
  }

  async function cancelar(
    participacao,
  ) {
    const confirmar =
      window.confirm(
        `Cancelar a participação de ${participacao.usuario.nome}?`,
      )

    if (!confirmar) {
      return
    }

    const chave =
      `cancel:${participacao.participacao_id}`

    setBusy(chave)
    setError('')
    setSuccess('')

    try {
      await cancelarParticipacao(
        participacao.participacao_id,
      )

      setSuccess(
        'Participação cancelada com sucesso.',
      )

      await carregar()
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível cancelar a participação.',
      )
    } finally {
      setBusy('')
    }
  }

  if (loading) {
    return (
      <div className="loading-card">
        Carregando gestão da escala...
      </div>
    )
  }

  const programacao =
    dados?.programacao

  if (!programacao) {
    return (
      <section className="panel">
        <h1>
          Gestão indisponível
        </h1>

        {error && (
          <div className="error-message">
            {error}
          </div>
        )}

        <Link
          to="/gestao/programacoes"
          className="text-link"
        >
          Voltar
        </Link>
      </section>
    )
  }

  return (
    <div className="scale-management-page">
      <Link
        to="/gestao/programacoes"
        className="text-link"
      >
        ← Gerenciar programações
      </Link>

      <section className="scale-management-hero">
        <div>
          <span className="eyebrow">
            Gestão de escala
          </span>

          <h1>
            {programacao.titulo}
          </h1>

          <p>
            {programacao?.tipo?.nome}
            {' • '}
            {programacao?.local?.nome
              || 'Local não informado'}
          </p>
        </div>

        <span className="program-status status-agendada">
          {programacao.status}
        </span>
      </section>

      <section className="scale-management-summary">
        <Metric
          label="Funções permitidas"
          value={
            dados?.resumo
              ?.funcoes_permitidas
            ?? 0
          }
        />

        <Metric
          label="Participações"
          value={
            dados?.resumo
              ?.participacoes
            ?? 0
          }
        />

        <Metric
          label="Ativas"
          value={
            dados?.resumo
              ?.participacoes_ativas
            ?? 0
          }
        />

        <Metric
          label="Candidatos"
          value={
            dados?.resumo
              ?.candidatos_elegiveis
            ?? 0
          }
        />
      </section>

      {error && (
        <div className="error-message">
          {error}
        </div>
      )}

      {success && (
        <div className="success-message">
          {success}
        </div>
      )}

      <section className="scale-management-grid">
        <div className="scale-management-column">
          <div className="section-heading">
            <span className="eyebrow">
              Montagem
            </span>

            <h2>
              Candidatos por função
            </h2>
          </div>

          {(dados?.funcoes ?? []).map(
            (funcao) => (
              <FuncaoCandidatos
                key={funcao.id}
                funcao={funcao}
                busy={busy}
                onAdd={adicionar}
              />
            ),
          )}
        </div>

        <div className="scale-management-column">
          <div className="section-heading">
            <span className="eyebrow">
              Escala atual
            </span>

            <h2>
              Participantes
            </h2>
          </div>

          {(dados?.escala ?? []).length === 0 ? (
            <div className="panel">
              <p className="empty-state">
                A escala ainda está vazia.
              </p>
            </div>
          ) : (
            <div className="current-scale-list">
              {(dados?.escala ?? []).map(
                (participacao) => (
                  <article
                    key={
                      participacao
                        .participacao_id
                    }
                    className="current-scale-card"
                  >
                    <div>
                      <strong>
                        {
                          participacao
                            .usuario
                            .nome
                        }
                      </strong>

                      <span>
                        {
                          participacao
                            .funcao
                            .nome
                        }
                      </span>
                    </div>

                    <div className="current-scale-actions">
                      <span
                        className={
                          participacao
                            .status
                            === 'ESCALADO'
                            ? 'status-pill pending'
                            : 'status-pill'
                        }
                      >
                        {
                          participacao
                            .status
                        }
                      </span>

                      {participacao
                        .ativo_na_escala && (
                        <button
                          type="button"
                          className="small-danger-button"
                          disabled={
                            busy
                            === `cancel:${participacao.participacao_id}`
                          }
                          onClick={() =>
                            cancelar(
                              participacao,
                            )
                          }
                        >
                          Cancelar
                        </button>
                      )}
                    </div>
                  </article>
                ),
              )}
            </div>
          )}
        </div>
      </section>
    </div>
  )
}

function FuncaoCandidatos({
  funcao,
  busy,
  onAdd,
}) {
  return (
    <section className="candidate-function-card">
      <header>
        <div>
          <span className="muted">
            {
              funcao
                ?.departamento
                ?.nome
              || 'Sem departamento'
            }
          </span>

          <h3>
            {funcao.nome}
          </h3>
        </div>

        <span className="candidate-count">
          {
            funcao
              ?.candidatos
              ?.length
            ?? 0
          }
        </span>
      </header>

      {(funcao?.candidatos ?? []).length === 0 ? (
        <p className="empty-state">
          Nenhum candidato elegível.
        </p>
      ) : (
        <div className="candidate-list">
          {funcao.candidatos.map(
            (candidato) => {
              const chave =
                `add:${funcao.id}:${candidato.usuario_id}`

              const ativo =
                candidato.ja_na_escala
                && ['ESCALADO', 'CONFIRMADO']
                  .includes(
                    candidato.status_na_escala,
                  )

              return (
                <div
                  key={candidato.usuario_id}
                  className="candidate-row"
                >
                  <div className="candidate-avatar">
                    {iniciais(
                      candidato.nome,
                    )}
                  </div>

                  <div className="candidate-main">
                    <strong>
                      {candidato.nome}
                    </strong>

                    <span>
                      {
                        candidato.email
                      }
                    </span>
                  </div>

                  {ativo ? (
                    <span className="status-pill">
                      Na escala
                    </span>
                  ) : (
                    <button
                      type="button"
                      className="small-primary-button"
                      disabled={
                        busy === chave
                      }
                      onClick={() =>
                        onAdd(
                          funcao,
                          candidato,
                        )
                      }
                    >
                      {busy === chave
                        ? 'Adicionando...'
                        : '+ Adicionar'}
                    </button>
                  )}
                </div>
              )
            },
          )}
        </div>
      )}
    </section>
  )
}

function Metric({
  label,
  value,
}) {
  return (
    <article className="program-metric-card">
      <span>{label}</span>
      <strong>{value}</strong>
    </article>
  )
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
