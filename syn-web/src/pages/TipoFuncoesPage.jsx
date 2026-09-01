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
  autorizarFuncaoTipoProgramacao,
  getFuncoes,
  getTipoProgramacaoDetalhe,
  removerFuncaoTipoProgramacao,
} from '../api/api'

import {
  useAuth,
} from '../contexts/AuthContext'

import './TipoFuncoesPage.css'

export default function TipoFuncoesPage() {
  const {
    id,
  } = useParams()

  const {
    capacidades,
  } = useAuth()

  const [tipo, setTipo] =
    useState(null)

  const [funcoes, setFuncoes] =
    useState([])

  const [loading, setLoading] =
    useState(true)

  const [busy, setBusy] =
    useState('')

  const [error, setError] =
    useState('')

  const [success, setSuccess] =
    useState('')

  const [busca, setBusca] =
    useState('')

  const podeGerenciar =
    Boolean(
      capacidades
        ?.gerenciar_tipos_programacao,
    )

  const carregar =
    useCallback(
      async () => {
        setLoading(true)
        setError('')

        try {
          const [
            tipoResponse,
            funcoesResponse,
          ] =
            await Promise.all([
              getTipoProgramacaoDetalhe(
                id,
              ),
              getFuncoes(),
            ])

          const tipoDados =
            extrairObjeto(
              tipoResponse,
            )

          const listaFuncoes =
            extrairLista(
              funcoesResponse,
              'funcoes',
            )

          setTipo(
            normalizarTipo(
              tipoDados,
            ),
          )

          setFuncoes(
            listaFuncoes.map(
              normalizarFuncao,
            ),
          )
        } catch (err) {
          setError(
            err?.message
            || 'Não foi possível carregar as funções autorizadas.',
          )
        } finally {
          setLoading(false)
        }
      },
      [id],
    )

  useEffect(() => {
    if (podeGerenciar) {
      carregar()
    } else {
      setLoading(false)
    }
  }, [
    carregar,
    podeGerenciar,
  ])

  const idsAutorizados =
    useMemo(
      () =>
        new Set(
          (
            tipo
              ?.funcoes_autorizadas
            ?? []
          ).map(
            (funcao) =>
              Number(funcao.id),
          ),
        ),
      [tipo],
    )

  const funcoesFiltradas =
    useMemo(
      () => {
        const termo =
          busca
            .trim()
            .toLocaleLowerCase(
              'pt-BR',
            )

        return funcoes
          .filter(
            (funcao) =>
              funcao.ativo,
          )
          .filter(
            (funcao) => {
              if (!termo) {
                return true
              }

              return [
                funcao.nome,
                funcao.departamento_nome,
                funcao.descricao,
              ]
                .filter(Boolean)
                .join(' ')
                .toLocaleLowerCase(
                  'pt-BR',
                )
                .includes(
                  termo,
                )
            },
          )
          .sort(
            (a, b) => {
              const departamentoA =
                a.departamento_nome
                || ''

              const departamentoB =
                b.departamento_nome
                || ''

              const porDepartamento =
                departamentoA.localeCompare(
                  departamentoB,
                  'pt-BR',
                )

              if (porDepartamento !== 0) {
                return porDepartamento
              }

              return a.nome.localeCompare(
                b.nome,
                'pt-BR',
              )
            },
          )
      },
      [
        funcoes,
        busca,
      ],
    )

  const resumo =
    useMemo(
      () => ({
        autorizadas:
          idsAutorizados.size,

        disponiveis:
          funcoes.filter(
            (funcao) =>
              funcao.ativo,
          ).length,
      }),
      [
        idsAutorizados,
        funcoes,
      ],
    )

  async function alternar(
    funcao,
  ) {
    const autorizada =
      idsAutorizados.has(
        funcao.id,
      )

    const chave =
      `${autorizada ? 'remove' : 'add'}:${funcao.id}`

    setBusy(chave)
    setError('')
    setSuccess('')

    try {
      if (autorizada) {
        await removerFuncaoTipoProgramacao(
          id,
          funcao.id,
        )

        setSuccess(
          `${funcao.nome} deixou de ser autorizada para novas escalas deste tipo.`,
        )
      } else {
        await autorizarFuncaoTipoProgramacao(
          id,
          funcao.id,
        )

        setSuccess(
          `${funcao.nome} agora pode ser usada em novas escalas deste tipo.`,
        )
      }

      await carregar()
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível alterar a autorização da função.',
      )
    } finally {
      setBusy('')
    }
  }

  if (!podeGerenciar) {
    return (
      <section className="panel">
        <span className="eyebrow">
          Acesso restrito
        </span>

        <h1>
          Funções autorizadas
        </h1>

        <p className="empty-state">
          Seu usuário não possui permissão
          para configurar tipos de programação.
        </p>
      </section>
    )
  }

  if (loading) {
    return (
      <div className="loading-card">
        Carregando configuração...
      </div>
    )
  }

  if (!tipo) {
    return (
      <section className="panel">
        <h1>
          Tipo de programação não encontrado
        </h1>

        {error && (
          <div className="error-message">
            {error}
          </div>
        )}

        <Link
          to="/admin/estrutura"
          className="text-link"
        >
          Voltar para Estrutura
        </Link>
      </section>
    )
  }

  return (
    <div className="type-functions-page">
      <Link
        to="/admin/estrutura"
        className="text-link"
      >
        ← Estrutura da igreja
      </Link>

      <section className="type-functions-hero">
        <div>
          <span className="eyebrow">
            Elegibilidade da escala
          </span>

          <h1>
            {tipo.nome}
          </h1>

          <p>
            Defina quais funções podem ser
            usadas quando uma nova escala
            deste tipo de programação for montada.
          </p>
        </div>

        <div className="type-functions-counter">
          <strong>
            {resumo.autorizadas}
          </strong>

          <span>
            de {resumo.disponiveis}
            {' '}
            funções autorizadas
          </span>
        </div>
      </section>

      <section className="type-functions-explanation">
        <div className="eligibility-flow">
          <span>Usuário ativo</span>
          <b>→</b>
          <span>possui função atual</span>
          <b>→</b>
          <span>função autorizada aqui</span>
          <b>→</b>
          <strong>candidato elegível</strong>
        </div>
      </section>

      <section className="type-functions-toolbar">
        <label>
          <span>
            Buscar função
          </span>

          <input
            type="search"
            placeholder="Função ou departamento..."
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

      {funcoesFiltradas.length === 0 ? (
        <section className="panel">
          <p className="empty-state">
            Nenhuma função ativa encontrada.
          </p>
        </section>
      ) : (
        <section className="type-functions-groups">
          {agruparPorDepartamento(
            funcoesFiltradas,
          ).map(
            (grupo) => (
              <section
                key={grupo.nome}
                className="type-function-group"
              >
                <header>
                  <div>
                    <span className="eyebrow">
                      Departamento
                    </span>

                    <h2>
                      {grupo.nome}
                    </h2>
                  </div>

                  <span className="type-group-count">
                    {
                      grupo.funcoes.filter(
                        (funcao) =>
                          idsAutorizados.has(
                            funcao.id,
                          ),
                      ).length
                    }
                    {' / '}
                    {grupo.funcoes.length}
                  </span>
                </header>

                <div className="type-functions-list">
                  {grupo.funcoes.map(
                    (funcao) => {
                      const autorizada =
                        idsAutorizados.has(
                          funcao.id,
                        )

                      const chave =
                        `${autorizada ? 'remove' : 'add'}:${funcao.id}`

                      return (
                        <article
                          key={funcao.id}
                          className={
                            autorizada
                              ? 'type-function-row selected'
                              : 'type-function-row'
                          }
                        >
                          <button
                            type="button"
                            className="type-function-toggle"
                            disabled={
                              busy === chave
                            }
                            aria-pressed={
                              autorizada
                            }
                            onClick={() =>
                              alternar(
                                funcao,
                              )
                            }
                          >
                            <span
                              className={
                                autorizada
                                  ? 'fake-checkbox checked'
                                  : 'fake-checkbox'
                              }
                              aria-hidden="true"
                            >
                              {autorizada
                                ? '✓'
                                : ''}
                            </span>

                            <span className="type-function-copy">
                              <strong>
                                {funcao.nome}
                              </strong>

                              {funcao.descricao && (
                                <span>
                                  {funcao.descricao}
                                </span>
                              )}
                            </span>

                            <span
                              className={
                                autorizada
                                  ? 'authorization-state active'
                                  : 'authorization-state'
                              }
                            >
                              {busy === chave
                                ? 'Aguarde...'
                                : autorizada
                                  ? 'Autorizada'
                                  : 'Não autorizada'}
                            </span>
                          </button>
                        </article>
                      )
                    },
                  )}
                </div>
              </section>
            ),
          )}
        </section>
      )}
    </div>
  )
}

function extrairLista(
  response,
  chave,
) {
  const lista =
    response?.dados?.[chave]
    ?? response?.dados
    ?? response?.[chave]
    ?? []

  return Array.isArray(lista)
    ? lista
    : []
}

function extrairObjeto(
  response,
) {
  const objeto =
    response?.dados
    ?? response
    ?? null

  return objeto
    && !Array.isArray(objeto)
      ? objeto
      : null
}

function normalizarTipo(
  item,
) {
  return {
    id:
      Number(item?.id)
      || 0,

    nome:
      item?.nome
      || 'Tipo de programação',

    ativo:
      item?.ativo === undefined
        ? true
        : Boolean(item.ativo),

    funcoes_autorizadas:
      Array.isArray(
        item?.funcoes_autorizadas,
      )
        ? item.funcoes_autorizadas
        : [],
  }
}

function normalizarFuncao(
  item,
) {
  return {
    id:
      Number(item?.id)
      || 0,

    nome:
      item?.nome
      || 'Função',

    descricao:
      item?.descricao
      || '',

    ativo:
      item?.ativo === undefined
        ? true
        : Boolean(
            Number(item.ativo)
            || item.ativo === true,
          ),

    departamento_nome:
      item?.departamento_nome
      ?? item?.departamento?.nome
      ?? 'Sem departamento',
  }
}

function agruparPorDepartamento(
  funcoes,
) {
  const mapa =
    new Map()

  for (const funcao of funcoes) {
    const nome =
      funcao.departamento_nome
      || 'Sem departamento'

    if (!mapa.has(nome)) {
      mapa.set(
        nome,
        [],
      )
    }

    mapa.get(nome).push(
      funcao,
    )
  }

  return Array.from(
    mapa.entries(),
  ).map(
    ([
      nome,
      itens,
    ]) => ({
      nome,
      funcoes: itens,
    }),
  )
}
