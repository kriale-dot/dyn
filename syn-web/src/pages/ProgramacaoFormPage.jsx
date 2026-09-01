import {
  useCallback,
  useEffect,
  useMemo,
  useState,
} from 'react'

import {
  Link,
  useNavigate,
  useParams,
} from 'react-router-dom'

import {
  atualizarProgramacao,
  cancelarProgramacao,
  criarProgramacao,
  getLocais,
  getProgramacao,
  getTiposProgramacao,
  getUsuarios,
  realizarProgramacao,
} from '../api/api'

import {
  useAuth,
} from '../contexts/AuthContext'

import './ProgramacaoFormPage.css'

const FORM_VAZIO = {
  titulo: '',
  descricao: '',
  tipo_programacao_id: '',
  local_id: '',
  organizador_id: '',
  inicio_em: '',
  fim_em: '',
  permite_resposta: true,
}

export default function ProgramacaoFormPage() {
  const {
    id,
  } = useParams()

  const navigate =
    useNavigate()

  const {
    usuario,
    bootstrap,
    capacidades,
  } = useAuth()

  const editando =
    Boolean(id)

  const [form, setForm] =
    useState(FORM_VAZIO)

  const [programacao, setProgramacao] =
    useState(null)

  const [tipos, setTipos] =
    useState([])

  const [locais, setLocais] =
    useState([])

  const [usuarios, setUsuarios] =
    useState([])

  const [loading, setLoading] =
    useState(true)

  const [saving, setSaving] =
    useState(false)

  const [actionBusy, setActionBusy] =
    useState('')

  const [error, setError] =
    useState('')

  const [fieldErrors, setFieldErrors] =
    useState({})

  const [success, setSuccess] =
    useState('')

  const papel =
    usuario?.papel?.codigo

  const podeGerenciar =
    Boolean(
      capacidades
        ?.gerenciar_programacoes,
    )

  const idsTiposPermitidos =
    useMemo(
      () =>
        new Set(
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

  const tiposDisponiveis =
    useMemo(
      () =>
        tipos
          .map(
            normalizarTipo,
          )
          .filter(
            (item) =>
              item.ativo,
          )
          .filter(
            (item) =>
              papel !== 'ORGANIZADOR'
              || idsTiposPermitidos.has(
                item.id,
              ),
          )
          .sort(
            (a, b) =>
              a.nome.localeCompare(
                b.nome,
                'pt-BR',
              ),
          ),
      [
        tipos,
        papel,
        idsTiposPermitidos,
      ],
    )

  const locaisDisponiveis =
    useMemo(
      () =>
        locais
          .map(
            normalizarLocal,
          )
          .filter(
            (item) =>
              item.ativo,
          )
          .sort(
            (a, b) =>
              a.nome.localeCompare(
                b.nome,
                'pt-BR',
              ),
          ),
      [locais],
    )

  const organizadores =
    useMemo(
      () =>
        usuarios
          .map(
            normalizarUsuario,
          )
          .filter(
            (item) =>
              item.status === 'ATIVO',
          )
          .filter(
            (item) =>
              [
                'ADMINISTRADOR',
                'ORGANIZADOR',
              ].includes(
                item.papel_codigo,
              ),
          )
          .sort(
            (a, b) =>
              a.nome.localeCompare(
                b.nome,
                'pt-BR',
              ),
          ),
      [usuarios],
    )

  const carregar =
    useCallback(
      async () => {
        setLoading(true)
        setError('')

        try {
          const requisicoes = [
            getTiposProgramacao(),
            getLocais(),
            getUsuarios(),
          ]

          if (editando) {
            requisicoes.push(
              getProgramacao(id),
            )
          }

          const resultados =
            await Promise.all(
              requisicoes,
            )

          setTipos(
            extrairLista(
              resultados[0],
              'tipos_programacao',
            ),
          )

          setLocais(
            extrairLista(
              resultados[1],
              'locais',
            ),
          )

          setUsuarios(
            extrairLista(
              resultados[2],
              'usuarios',
            ),
          )

          if (editando) {
            const item =
              resultados[3]?.dados
              ?? null

            const normalizada =
              normalizarProgramacao(
                item,
              )

            setProgramacao(
              normalizada,
            )

            setForm({
              titulo:
                normalizada.titulo,
              descricao:
                normalizada.descricao
                || '',
              tipo_programacao_id:
                String(
                  normalizada
                    .tipo_programacao_id,
                ),
              local_id:
                String(
                  normalizada.local_id,
                ),
              organizador_id:
                String(
                  normalizada
                    .organizador_id,
                ),
              inicio_em:
                paraDatetimeLocal(
                  normalizada.inicio_em,
                ),
              fim_em:
                paraDatetimeLocal(
                  normalizada.fim_em,
                ),
              permite_resposta:
                normalizada
                  .permite_resposta,
            })
          } else if (
            papel === 'ORGANIZADOR'
            && usuario?.id
          ) {
            setForm(
              (atual) => ({
                ...atual,
                organizador_id:
                  String(
                    usuario.id,
                  ),
              }),
            )
          }
        } catch (err) {
          setError(
            mensagemErro(
              err,
              'Não foi possível preparar o formulário.',
            ),
          )
        } finally {
          setLoading(false)
        }
      },
      [
        editando,
        id,
        papel,
        usuario?.id,
      ],
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

  function alterar(
    campo,
    valor,
  ) {
    setForm(
      (atual) => ({
        ...atual,
        [campo]: valor,
      }),
    )

    setFieldErrors(
      (atuais) => {
        if (!atuais[campo]) {
          return atuais
        }

        const novos = {
          ...atuais,
        }

        delete novos[campo]

        return novos
      },
    )
  }

  async function salvar(
    event,
  ) {
    event.preventDefault()

    const errosLocais =
      validarFormulario(
        form,
      )

    if (
      Object.keys(
        errosLocais,
      ).length > 0
    ) {
      setFieldErrors(
        errosLocais,
      )
      setError(
        'Revise os campos destacados.',
      )
      return
    }

    setSaving(true)
    setError('')
    setSuccess('')
    setFieldErrors({})

    const payload =
      montarPayload(
        form,
        false,
      )

    try {
      let response

      try {
        response =
          editando
            ? await atualizarProgramacao(
                id,
                payload,
              )
            : await criarProgramacao(
                payload,
              )
      } catch (err) {
        if (
          err?.status === 409
          && Array.isArray(
            err
              ?.payload
              ?.conflitos,
          )
        ) {
          const confirmou =
            confirmarConflitoLocal(
              err.payload.conflitos,
            )

          if (!confirmou) {
            setError(
              'A programação não foi salva porque existe conflito de horário no local.',
            )
            return
          }

          const payloadConfirmado =
            montarPayload(
              form,
              true,
            )

          response =
            editando
              ? await atualizarProgramacao(
                  id,
                  payloadConfirmado,
                )
              : await criarProgramacao(
                  payloadConfirmado,
                )
        } else {
          throw err
        }
      }

      const salvo =
        response?.dados
        ?? null

      setSuccess(
        editando
          ? 'Programação atualizada com sucesso.'
          : 'Programação criada com sucesso.',
      )

      const novoId =
        Number(
          salvo?.id
          ?? id,
        )

      if (novoId) {
        navigate(
          `/gestao/programacoes/${novoId}/editar`,
          {
            replace:
              !editando,
          },
        )
      }
    } catch (err) {
      const errosApi =
        err?.payload?.erros

      if (
        errosApi
        && typeof errosApi
          === 'object'
      ) {
        setFieldErrors(
          errosApi,
        )
      }

      setError(
        mensagemErro(
          err,
          'Não foi possível salvar a programação.',
        ),
      )
    } finally {
      setSaving(false)
    }
  }

  async function cancelar() {
    if (
      !programacao
      || programacao.status
        !== 'AGENDADA'
    ) {
      return
    }

    const motivo =
      window.prompt(
        'Motivo do cancelamento (opcional):',
        '',
      )

    if (motivo === null) {
      return
    }

    const confirmou =
      window.confirm(
        'Confirmar o cancelamento desta programação? Ela permanecerá no histórico.',
      )

    if (!confirmou) {
      return
    }

    setActionBusy(
      'cancelar',
    )
    setError('')
    setSuccess('')

    try {
      await cancelarProgramacao(
        programacao.id,
        motivo,
      )

      setSuccess(
        'Programação cancelada. O histórico foi preservado.',
      )

      await carregar()
    } catch (err) {
      setError(
        mensagemErro(
          err,
          'Não foi possível cancelar a programação.',
        ),
      )
    } finally {
      setActionBusy('')
    }
  }

  async function realizar() {
    if (
      !programacao
      || programacao.status
        !== 'AGENDADA'
    ) {
      return
    }

    const confirmou =
      window.confirm(
        'Marcar esta programação como realizada?',
      )

    if (!confirmou) {
      return
    }

    setActionBusy(
      'realizar',
    )
    setError('')
    setSuccess('')

    try {
      await realizarProgramacao(
        programacao.id,
      )

      setSuccess(
        'Programação marcada como realizada.',
      )

      await carregar()
    } catch (err) {
      setError(
        mensagemErro(
          err,
          'Não foi possível realizar a programação.',
        ),
      )
    } finally {
      setActionBusy('')
    }
  }

  if (!podeGerenciar) {
    return (
      <section className="panel">
        <span className="eyebrow">
          Acesso restrito
        </span>

        <h1>
          Programações
        </h1>

        <p className="empty-state">
          Seu usuário não possui permissão
          para administrar programações.
        </p>
      </section>
    )
  }

  if (loading) {
    return (
      <div className="loading-card">
        Preparando programação...
      </div>
    )
  }

  const bloqueada =
    editando
    && programacao
    && programacao.status
      !== 'AGENDADA'

  return (
    <div className="program-form-page">
      <Link
        to="/gestao/programacoes"
        className="text-link"
      >
        ← Gerenciar programações
      </Link>

      <section className="program-form-hero">
        <div>
          <span className="eyebrow">
            {editando
              ? 'Programação'
              : 'Nova programação'}
          </span>

          <h1>
            {editando
              ? programacao?.titulo
                || 'Editar programação'
              : 'Criar programação'}
          </h1>

          <p>
            Defina quando, onde e o que
            acontecerá. A escala é montada
            depois, na área de gestão.
          </p>
        </div>

        {editando && (
          <span
            className={
              `program-status status-${String(
                programacao?.status
                || '',
              ).toLowerCase()}`
            }
          >
            {traduzirStatus(
              programacao?.status,
            )}
          </span>
        )}
      </section>

      {bloqueada && (
        <section className="program-form-lock">
          <strong>
            Programação encerrada
          </strong>

          <p>
            Programações realizadas ou canceladas
            permanecem disponíveis para consulta,
            mas não podem ser reescritas.
          </p>
        </section>
      )}

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

      <form
        className="program-editor"
        onSubmit={salvar}
      >
        <section className="program-editor-section">
          <header>
            <span className="program-editor-step">
              1
            </span>

            <div>
              <h2>
                O que vai acontecer?
              </h2>

              <p>
                Identifique a atividade e seu tipo.
              </p>
            </div>
          </header>

          <div className="program-editor-fields">
            <Field
              label="Título"
              error={
                fieldErrors.titulo
              }
              full
            >
              <input
                type="text"
                required
                disabled={bloqueada}
                value={form.titulo}
                onChange={(event) =>
                  alterar(
                    'titulo',
                    event.target.value,
                  )
                }
              />
            </Field>

            <Field
              label="Tipo de programação"
              error={
                fieldErrors
                  .tipo_programacao_id
              }
            >
              <select
                required
                disabled={bloqueada}
                value={
                  form
                    .tipo_programacao_id
                }
                onChange={(event) =>
                  alterar(
                    'tipo_programacao_id',
                    event.target.value,
                  )
                }
              >
                <option value="">
                  Selecione...
                </option>

                {tiposDisponiveis.map(
                  (tipo) => (
                    <option
                      key={tipo.id}
                      value={tipo.id}
                    >
                      {tipo.nome}
                    </option>
                  ),
                )}
              </select>
            </Field>

            <Field
              label="Descrição"
              error={
                fieldErrors.descricao
              }
              full
            >
              <textarea
                rows={4}
                disabled={bloqueada}
                value={
                  form.descricao
                }
                onChange={(event) =>
                  alterar(
                    'descricao',
                    event.target.value,
                  )
                }
                placeholder="Informação opcional sobre a atividade..."
              />
            </Field>
          </div>
        </section>

        <section className="program-editor-section">
          <header>
            <span className="program-editor-step">
              2
            </span>

            <div>
              <h2>
                Quando?
              </h2>

              <p>
                Informe início e término.
              </p>
            </div>
          </header>

          <div className="program-editor-fields">
            <Field
              label="Início"
              error={
                fieldErrors.inicio_em
              }
            >
              <input
                type="datetime-local"
                required
                disabled={bloqueada}
                value={
                  form.inicio_em
                }
                onChange={(event) =>
                  alterar(
                    'inicio_em',
                    event.target.value,
                  )
                }
              />
            </Field>

            <Field
              label="Término"
              error={
                fieldErrors.fim_em
              }
            >
              <input
                type="datetime-local"
                required
                disabled={bloqueada}
                value={
                  form.fim_em
                }
                onChange={(event) =>
                  alterar(
                    'fim_em',
                    event.target.value,
                  )
                }
              />
            </Field>
          </div>
        </section>

        <section className="program-editor-section">
          <header>
            <span className="program-editor-step">
              3
            </span>

            <div>
              <h2>
                Onde e com quem?
              </h2>

              <p>
                Escolha o local e o responsável.
              </p>
            </div>
          </header>

          <div className="program-editor-fields">
            <Field
              label="Local"
              error={
                fieldErrors.local_id
              }
            >
              <select
                required
                disabled={bloqueada}
                value={form.local_id}
                onChange={(event) =>
                  alterar(
                    'local_id',
                    event.target.value,
                  )
                }
              >
                <option value="">
                  Selecione...
                </option>

                {locaisDisponiveis.map(
                  (local) => (
                    <option
                      key={local.id}
                      value={local.id}
                    >
                      {local.nome}
                    </option>
                  ),
                )}
              </select>
            </Field>

            <Field
              label="Responsável / organizador"
              error={
                fieldErrors
                  .organizador_id
              }
            >
              <select
                required
                disabled={
                  bloqueada
                  || (
                    papel
                    === 'ORGANIZADOR'
                  )
                }
                value={
                  form.organizador_id
                }
                onChange={(event) =>
                  alterar(
                    'organizador_id',
                    event.target.value,
                  )
                }
              >
                <option value="">
                  Selecione...
                </option>

                {organizadores.map(
                  (organizador) => (
                    <option
                      key={
                        organizador.id
                      }
                      value={
                        organizador.id
                      }
                    >
                      {organizador.nome}
                      {' — '}
                      {organizador
                        .papel_nome}
                    </option>
                  ),
                )}
              </select>
            </Field>

            <label className="program-response-toggle">
              <input
                type="checkbox"
                disabled={bloqueada}
                checked={
                  form
                    .permite_resposta
                }
                onChange={(event) =>
                  alterar(
                    'permite_resposta',
                    event.target.checked,
                  )
                }
              />

              <span>
                <strong>
                  Permitir resposta à escala
                </strong>

                <small>
                  Participantes poderão confirmar,
                  informar indisponibilidade ou recusar
                  conforme as regras do sistema.
                </small>
              </span>
            </label>
          </div>
        </section>

        <footer className="program-editor-actions">
          <button
            type="button"
            className="button-secondary"
            onClick={() =>
              navigate(
                '/gestao/programacoes',
              )
            }
          >
            Voltar
          </button>

          {!bloqueada && (
            <button
              type="submit"
              className="button-primary"
              disabled={saving}
            >
              {saving
                ? 'Salvando...'
                : editando
                  ? 'Salvar alterações'
                  : 'Criar programação'}
            </button>
          )}
        </footer>
      </form>

      {editando && (
        <section className="program-management-actions">
          <div>
            <span className="eyebrow">
              Gestão
            </span>

            <h2>
              Ações da programação
            </h2>
          </div>

          <div className="program-management-buttons">
            <button
              type="button"
              className="button-secondary"
              onClick={() =>
                navigate(
                  `/gestao/programacoes/${programacao.id}/escala`,
                )
              }
            >
              Gerenciar escala
            </button>

            {programacao.status
              === 'AGENDADA' && (
              <>
                <button
                  type="button"
                  className="program-realize-button"
                  disabled={
                    actionBusy
                    === 'realizar'
                  }
                  onClick={realizar}
                >
                  {actionBusy
                    === 'realizar'
                      ? 'Aguarde...'
                      : 'Marcar como realizada'}
                </button>

                <button
                  type="button"
                  className="program-cancel-button"
                  disabled={
                    actionBusy
                    === 'cancelar'
                  }
                  onClick={cancelar}
                >
                  {actionBusy
                    === 'cancelar'
                      ? 'Aguarde...'
                      : 'Cancelar programação'}
                </button>
              </>
            )}
          </div>
        </section>
      )}
    </div>
  )
}

function Field({
  label,
  error,
  full = false,
  children,
}) {
  return (
    <label
      className={
        full
          ? 'program-field full'
          : 'program-field'
      }
    >
      <span>
        {label}
      </span>

      {children}

      {error && (
        <small className="program-field-error">
          {error}
        </small>
      )}
    </label>
  )
}

function montarPayload(
  form,
  confirmarConflito,
) {
  return {
    titulo:
      form.titulo.trim(),

    descricao:
      form.descricao.trim()
      || null,

    tipo_programacao_id:
      Number(
        form.tipo_programacao_id,
      ),

    local_id:
      Number(form.local_id),

    organizador_id:
      Number(
        form.organizador_id,
      ),

    inicio_em:
      paraApiDataHora(
        form.inicio_em,
      ),

    fim_em:
      paraApiDataHora(
        form.fim_em,
      ),

    permite_resposta:
      Boolean(
        form.permite_resposta,
      ),

    confirmar_conflito:
      Boolean(
        confirmarConflito,
      ),
  }
}

function validarFormulario(
  form,
) {
  const erros = {}

  if (!form.titulo.trim()) {
    erros.titulo =
      'Informe o título.'
  }

  if (
    !Number(
      form.tipo_programacao_id,
    )
  ) {
    erros.tipo_programacao_id =
      'Selecione o tipo.'
  }

  if (
    !Number(form.local_id)
  ) {
    erros.local_id =
      'Selecione o local.'
  }

  if (
    !Number(
      form.organizador_id,
    )
  ) {
    erros.organizador_id =
      'Selecione o responsável.'
  }

  if (!form.inicio_em) {
    erros.inicio_em =
      'Informe o início.'
  }

  if (!form.fim_em) {
    erros.fim_em =
      'Informe o término.'
  }

  if (
    form.inicio_em
    && form.fim_em
    && form.fim_em
      <= form.inicio_em
  ) {
    erros.fim_em =
      'O término deve ser posterior ao início.'
  }

  return erros
}

function confirmarConflitoLocal(
  conflitos,
) {
  const linhas =
    conflitos
      .slice(0, 5)
      .map(
        (item) =>
          `• ${item.titulo} — ${formatarDataHoraCurta(
            item.inicio_em,
          )} até ${formatarHora(
            item.fim_em,
          )}`,
      )
      .join('\n')

  return window.confirm(
    `Existe conflito de horário neste local:\n\n${linhas}\n\nDeseja salvar mesmo assim?`,
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

    descricao:
      item?.descricao
      || '',

    inicio_em:
      item?.inicio_em
      || '',

    fim_em:
      item?.fim_em
      || '',

    status:
      item?.status
      || 'AGENDADA',

    permite_resposta:
      item?.permite_resposta
      === undefined
        ? true
        : Boolean(
            Number(
              item.permite_resposta,
            )
            || item
              .permite_resposta
              === true,
          ),

    tipo_programacao_id:
      Number(
        item
          ?.tipo_programacao
          ?.id
        ?? item
          ?.tipo_programacao_id
        ?? 0,
      ),

    local_id:
      Number(
        item?.local?.id
        ?? item?.local_id
        ?? 0,
      ),

    organizador_id:
      Number(
        item
          ?.organizador
          ?.id
        ?? item
          ?.organizador_id
        ?? 0,
      ),
  }
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
      || 'Tipo',

    ativo:
      ativoBoolean(
        item?.ativo,
      ),
  }
}

function normalizarLocal(
  item,
) {
  return {
    id:
      Number(item?.id)
      || 0,

    nome:
      item?.nome
      || 'Local',

    ativo:
      ativoBoolean(
        item?.ativo,
      ),
  }
}

function normalizarUsuario(
  item,
) {
  const papelObjeto =
    typeof item?.papel
      === 'object'
      ? item.papel
      : null

  return {
    id:
      Number(item?.id)
      || 0,

    nome:
      item?.nome
      || 'Usuário',

    status:
      String(
        item?.status
        || 'ATIVO',
      ).toUpperCase(),

    papel_codigo:
      item?.papel_codigo
      ?? papelObjeto?.codigo
      ?? '',

    papel_nome:
      item?.papel_nome
      ?? papelObjeto?.nome
      ?? 'Usuário',
  }
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

function ativoBoolean(
  valor,
) {
  if (
    valor === false
    || valor === 0
    || valor === '0'
  ) {
    return false
  }

  return true
}

function paraDatetimeLocal(
  valor,
) {
  if (!valor) {
    return ''
  }

  return String(valor)
    .replace(' ', 'T')
    .slice(0, 16)
}

function paraApiDataHora(
  valor,
) {
  if (!valor) {
    return ''
  }

  return `${String(valor)
    .replace('T', ' ')
    .slice(0, 16)}:00`
}

function formatarDataHoraCurta(
  valor,
) {
  if (!valor) {
    return ''
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

function formatarHora(
  valor,
) {
  return valor
    ? String(valor)
        .slice(11, 16)
    : '--:--'
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

function mensagemErro(
  err,
  fallback,
) {
  const erros =
    err?.payload?.erros

  if (
    erros
    && typeof erros
      === 'object'
  ) {
    const primeira =
      Object.values(erros)
        .find(
          (valor) =>
            typeof valor
            === 'string',
        )

    if (primeira) {
      return primeira
    }
  }

  return (
    err?.message
    || fallback
  )
}
