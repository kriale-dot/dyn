import {
  useEffect,
  useMemo,
  useState,
} from 'react'

import {
  Link,
  useNavigate,
} from 'react-router-dom'

import {
  criarSerieProgramacao,
  getLocais,
  getTiposProgramacao,
  getUsuarios,
} from '../api/api'

import {
  useAuth,
} from '../contexts/AuthContext'

import './SerieProgramacaoFormPage.css'
import './SerieProgramacaoFormEtapa67.css'

const FORM_INICIAL = {
  titulo: '',
  descricao: '',
  tipo_programacao_id: '',
  local_id: '',
  organizador_id: '',
  inicio_base: '',
  fim_base: '',
  intervalo_semanas: 1,
  data_limite: '',
  permite_resposta: true,
}

export default function SerieProgramacaoFormPage() {
  const navigate = useNavigate()
  const { usuario, bootstrap, capacidades } = useAuth()

  const [form, setForm] = useState(FORM_INICIAL)
  const [tipos, setTipos] = useState([])
  const [locais, setLocais] = useState([])
  const [usuarios, setUsuarios] = useState([])
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState('')
  const [fieldErrors, setFieldErrors] = useState({})
  const [conflitos, setConflitos] = useState([])

  const papel = usuario?.papel?.codigo
  const podeGerenciar =
    Boolean(capacidades?.gerenciar_series)

  const idsPermitidos = useMemo(
    () => new Set(
      (
        bootstrap
          ?.escopo_organizador
          ?.tipos_programacao
        ?? []
      ).map(
        (item) => Number(item.id),
      ),
    ),
    [bootstrap],
  )

  const tiposDisponiveis = useMemo(
    () => tipos
      .map(normalizarTipo)
      .filter((item) => item.ativo)
      .filter(
        (item) =>
          papel !== 'ORGANIZADOR'
          || idsPermitidos.has(item.id),
      )
      .sort(
        (a, b) =>
          a.nome.localeCompare(b.nome, 'pt-BR'),
      ),
    [tipos, papel, idsPermitidos],
  )

  const locaisDisponiveis = useMemo(
    () => locais
      .map(normalizarLocal)
      .filter((item) => item.ativo)
      .sort(
        (a, b) =>
          a.nome.localeCompare(b.nome, 'pt-BR'),
      ),
    [locais],
  )

  const organizadores = useMemo(
    () => usuarios
      .map(normalizarUsuario)
      .filter(
        (item) => item.status === 'ATIVO',
      )
      .filter(
        (item) =>
          [
            'ADMINISTRADOR',
            'ORGANIZADOR',
          ].includes(item.papel_codigo),
      )
      .sort(
        (a, b) =>
          a.nome.localeCompare(b.nome, 'pt-BR'),
      ),
    [usuarios],
  )

  const totalPrevisto = useMemo(
    () => calcularOcorrencias(
      form.inicio_base,
      form.data_limite,
      Number(form.intervalo_semanas),
    ),
    [
      form.inicio_base,
      form.data_limite,
      form.intervalo_semanas,
    ],
  )

  const previaSemanas =
    useMemo(
      () =>
        montarPreviaSemanas(
          form.inicio_base,
          form.data_limite,
          Number(
            form.intervalo_semanas,
          ),
        ),
      [
        form.inicio_base,
        form.data_limite,
        form.intervalo_semanas,
      ],
    )

  useEffect(() => {
    if (!podeGerenciar) {
      setLoading(false)
      return
    }

    let ativo = true

    async function carregar() {
      setLoading(true)
      setError('')

      try {
        const [
          tiposResponse,
          locaisResponse,
          usuariosResponse,
        ] = await Promise.all([
          getTiposProgramacao(),
          getLocais(),
          getUsuarios(),
        ])

        if (!ativo) return

        setTipos(
          extrairLista(
            tiposResponse,
            'tipos_programacao',
          ),
        )

        setLocais(
          extrairLista(
            locaisResponse,
            'locais',
          ),
        )

        setUsuarios(
          extrairLista(
            usuariosResponse,
            'usuarios',
          ),
        )

        if (
          papel === 'ORGANIZADOR'
          && usuario?.id
        ) {
          setForm(
            (atual) => ({
              ...atual,
              organizador_id:
                String(usuario.id),
            }),
          )
        }
      } catch (err) {
        if (ativo) {
          setError(
            err?.message
            || 'Não foi possível preparar o formulário.',
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
  }, [
    papel,
    podeGerenciar,
    usuario?.id,
  ])

  function alterar(campo, valor) {
    setForm(
      (atual) => ({
        ...atual,
        [campo]: valor,
      }),
    )

    setConflitos([])

    setFieldErrors(
      (atuais) => {
        if (!atuais[campo]) {
          return atuais
        }

        const novos = { ...atuais }
        delete novos[campo]
        return novos
      },
    )
  }

  async function enviar(
    confirmarConflitos = false,
  ) {
    const erros =
      validar(form, totalPrevisto)

    if (
      Object.keys(erros).length > 0
    ) {
      setFieldErrors(erros)
      setError(
        'Revise os campos destacados.',
      )
      return
    }

    setSaving(true)
    setError('')
    setFieldErrors({})

    try {
      const response =
        await criarSerieProgramacao(
          montarPayload(
            form,
            confirmarConflitos,
          ),
        )

      const serie =
        response?.dados?.serie
        ?? {}

      const novoId =
        Number(serie?.id)

      navigate(
        novoId
          ? `/gestao/series/${novoId}`
          : '/gestao/series',
        {
          replace: true,
          state: {
            mensagem:
              response?.mensagem
              || 'Série criada com sucesso.',
          },
        },
      )
    } catch (err) {
      if (
        err?.status === 409
        && Array.isArray(
          err?.payload?.conflitos,
        )
      ) {
        setConflitos(
          err.payload.conflitos,
        )
        setError(
          'Existem conflitos de local. Revise abaixo antes de confirmar a criação.',
        )
        return
      }

      if (
        err?.payload?.erros
        && typeof err.payload.erros
          === 'object'
      ) {
        setFieldErrors(
          err.payload.erros,
        )
      }

      setError(
        mensagemErro(
          err,
          'Não foi possível criar a série recorrente.',
        ),
      )
    } finally {
      setSaving(false)
    }
  }

  async function submit(event) {
    event.preventDefault()
    await enviar(false)
  }

  if (!podeGerenciar) {
    return (
      <section className="panel">
        <span className="eyebrow">
          Acesso restrito
        </span>

        <h1>
          Nova série recorrente
        </h1>

        <p className="empty-state">
          Seu usuário não possui permissão
          para criar programações recorrentes.
        </p>
      </section>
    )
  }

  if (loading) {
    return (
      <div className="loading-card">
        Preparando recorrência...
      </div>
    )
  }

  return (
    <div className="serie-form-page">
      <Link
        to="/gestao/series"
        className="text-link"
      >
        ← Programações recorrentes
      </Link>

      <section className="serie-form-hero">
        <div>
          <span className="eyebrow">
            Nova série
          </span>

          <h1>
            Criar programação recorrente
          </h1>

          <p>
            Na V1 do SYN a recorrência é semanal.
            Cada data gerada será uma programação
            real com seu próprio ID.
          </p>
        </div>

        <div className="serie-preview-count">
          <strong>{totalPrevisto}</strong>
          <span>
            ocorrência(s) prevista(s)
          </span>
        </div>
      </section>

      {error && (
        <div className="error-message">
          {error}
        </div>
      )}

      <form
        className="serie-editor"
        onSubmit={submit}
      >
        <section className="serie-editor-card">
          <header>
            <span className="program-editor-step">
              1
            </span>

            <div>
              <h2>Identificação</h2>
              <p>
                Defina o nome e o contexto
                da série.
              </p>
            </div>
          </header>

          <div className="serie-fields">
            <Field
              label="Título"
              full
              error={fieldErrors.titulo}
            >
              <input
                type="text"
                required
                maxLength={180}
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
                value={
                  form.tipo_programacao_id
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
              label="Local"
              error={fieldErrors.local_id}
            >
              <select
                required
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
              full
              error={fieldErrors.organizador_id}
            >
              <select
                required
                disabled={
                  papel === 'ORGANIZADOR'
                }
                value={form.organizador_id}
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
                  (item) => (
                    <option
                      key={item.id}
                      value={item.id}
                    >
                      {item.nome}
                      {' — '}
                      {item.papel_nome}
                    </option>
                  ),
                )}
              </select>
            </Field>

            <Field
              label="Descrição"
              full
              error={fieldErrors.descricao}
            >
              <textarea
                rows={4}
                value={form.descricao}
                onChange={(event) =>
                  alterar(
                    'descricao',
                    event.target.value,
                  )
                }
                placeholder="Descrição opcional..."
              />
            </Field>
          </div>
        </section>

        <section className="serie-editor-card">
          <header>
            <span className="program-editor-step">
              2
            </span>

            <div>
              <h2>Primeira ocorrência</h2>
              <p>
                O dia e horário escolhidos
                formam a base das repetições.
              </p>
            </div>
          </header>

          <div className="serie-fields">
            <Field
              label="Início da primeira ocorrência"
              error={fieldErrors.inicio_base}
            >
              <input
                type="datetime-local"
                required
                value={form.inicio_base}
                onChange={(event) =>
                  alterar(
                    'inicio_base',
                    event.target.value,
                  )
                }
              />
            </Field>

            <Field
              label="Término da primeira ocorrência"
              error={fieldErrors.fim_base}
            >
              <input
                type="datetime-local"
                required
                value={form.fim_base}
                onChange={(event) =>
                  alterar(
                    'fim_base',
                    event.target.value,
                  )
                }
              />
            </Field>
          </div>
        </section>

        <section className="serie-editor-card">
          <header>
            <span className="program-editor-step">
              3
            </span>

            <div>
              <h2>Regra semanal</h2>
              <p>
                Defina a distância entre
                repetições e a data final.
              </p>
            </div>
          </header>

          <div className="serie-fields">
            <Field
              label="Repetir a cada"
              error={
                fieldErrors.intervalo_semanas
              }
            >
              <div className="serie-interval-field">
                <input
                  type="number"
                  min="1"
                  max="52"
                  required
                  value={form.intervalo_semanas}
                  onChange={(event) =>
                    alterar(
                      'intervalo_semanas',
                      event.target.value,
                    )
                  }
                />

                <span>semana(s)</span>
              </div>
            </Field>

            <Field
              label="Gerar até"
              error={fieldErrors.data_limite}
            >
              <input
                type="date"
                required
                value={form.data_limite}
                onChange={(event) =>
                  alterar(
                    'data_limite',
                    event.target.value,
                  )
                }
              />
            </Field>

            <label className="program-response-toggle">
              <input
                type="checkbox"
                checked={form.permite_resposta}
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
                  Essa configuração será aplicada
                  a todas as ocorrências criadas.
                </small>
              </span>
            </label>
          </div>
        </section>

        <section className="serie-preview-card serie67-preview-card">
          <div className="serie67-preview-heading">
            <div>
              <span className="eyebrow">
                Prévia por semanas
              </span>

              <h2>
                {textoPrevia(form)}
              </h2>

              <p>
                O SYN criará aproximadamente{' '}
                <strong>{totalPrevisto}</strong>
                {' '}ocorrência(s), limitado a 200.
              </p>
            </div>

            {previaSemanas?.primeira && (
              <div className="serie67-range">
                <span>
                  Primeira
                </span>

                <strong>
                  Semana {
                    previaSemanas
                      .primeira
                      .semana
                  }
                </strong>

                <small>
                  {
                    previaSemanas
                      .primeira
                      .dataFormatada
                  }
                </small>
              </div>
            )}

            {previaSemanas?.ultima && (
              <div className="serie67-range">
                <span>
                  Última
                </span>

                <strong>
                  Semana {
                    previaSemanas
                      .ultima
                      .semana
                  }
                </strong>

                <small>
                  {
                    previaSemanas
                      .ultima
                      .dataFormatada
                  }
                </small>
              </div>
            )}
          </div>

          {previaSemanas?.itens?.length > 0 && (
            <>
              <div className="serie67-week-sequence">
                {previaSemanas.itens.map(
                  (item) => (
                    <button
                      type="button"
                      key={item.chave}
                      className="serie67-week-chip"
                      onClick={() =>
                        navigate(
                          `/semana?data_referencia=${item.dataISO}`,
                        )
                      }
                      title={
                        `Abrir Semana ${item.semana} no mapa`
                      }
                    >
                      <span>
                        Semana
                      </span>

                      <strong>
                        {item.semana}
                      </strong>

                      <small>
                        {item.dataCurta}
                      </small>
                    </button>
                  ),
                )}

                {previaSemanas.restantes > 0 && (
                  <div className="serie67-more-weeks">
                    +{
                      previaSemanas
                        .restantes
                    }
                    {' '}
                    {previaSemanas.restantes === 1
                      ? 'ocorrência'
                      : 'ocorrências'}
                  </div>
                )}
              </div>

              <div className="serie67-preview-note">
                <strong>
                  Referência do mapa
                </strong>

                <span>
                  Cada ocorrência será posicionada
                  na sua própria Semana N. Clique
                  em uma das semanas acima para
                  abrir aquele ponto do mapa.
                </span>
              </div>
            </>
          )}
        </section>

        {conflitos.length > 0 && (
          <ConflitosSerie
            conflitos={conflitos}
            saving={saving}
            onConfirm={() =>
              enviar(true)
            }
            onAdjust={() => {
              setConflitos([])
              setError('')
            }}
          />
        )}

        <footer className="serie-editor-actions">
          <button
            type="button"
            className="button-secondary"
            onClick={() =>
              navigate('/gestao/series')
            }
          >
            Cancelar
          </button>

          <button
            type="submit"
            className="button-primary"
            disabled={
              saving
              || totalPrevisto === 0
              || totalPrevisto > 200
            }
          >
            {saving ? 'Criando...' : 'Criar série'}
          </button>
        </footer>
      </form>
    </div>
  )
}

function ConflitosSerie({
  conflitos,
  saving,
  onConfirm,
  onAdjust,
}) {
  const exibidos = conflitos.slice(0, 8)

  return (
    <section className="serie-conflict-card">
      <span className="eyebrow">
        Conflito de local
      </span>

      <h2>
        {conflitos.length}
        {' '}conflito(s) encontrado(s)
      </h2>

      <p>
        Nada foi gravado ainda. Ajuste a regra
        ou confirme conscientemente a criação.
      </p>

      <div className="serie-conflict-list">
        {exibidos.map(
          (conflito, indice) => (
            <article
              key={`${indice}-${conflito?.programacao_existente?.id ?? 'x'}`}
            >
              <strong>
                Nova ocorrência:{' '}
                {formatarDataHora(
                  conflito
                    ?.ocorrencia_nova
                    ?.inicio_em,
                )}
              </strong>

              <span>
                conflita com{' '}
                <b>
                  {conflito
                    ?.programacao_existente
                    ?.titulo
                    || 'programação existente'}
                </b>
                {' '}(
                {formatarDataHora(
                  conflito
                    ?.programacao_existente
                    ?.inicio_em,
                )}
                )
              </span>
            </article>
          ),
        )}
      </div>

      <footer>
        <button
          type="button"
          className="button-secondary"
          onClick={onAdjust}
        >
          Voltar e ajustar
        </button>

        <button
          type="button"
          className="serie-conflict-confirm"
          disabled={saving}
          onClick={onConfirm}
        >
          {saving
            ? 'Criando...'
            : 'Criar mesmo assim'}
        </button>
      </footer>
    </section>
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
          ? 'serie-field full'
          : 'serie-field'
      }
    >
      <span>{label}</span>
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
  confirmarConflitos,
) {
  return {
    titulo: form.titulo.trim(),
    descricao:
      form.descricao.trim() || null,
    tipo_programacao_id:
      Number(form.tipo_programacao_id),
    local_id:
      Number(form.local_id),
    organizador_id:
      Number(form.organizador_id),
    inicio_base:
      paraApiDataHora(form.inicio_base),
    fim_base:
      paraApiDataHora(form.fim_base),
    intervalo_semanas:
      Number(form.intervalo_semanas),
    data_limite:
      form.data_limite,
    permite_resposta:
      Boolean(form.permite_resposta),
    confirmar_conflitos:
      Boolean(confirmarConflitos),
  }
}

function validar(form, totalPrevisto) {
  const erros = {}

  if (!form.titulo.trim()) {
    erros.titulo = 'Informe o título.'
  }

  if (!Number(form.tipo_programacao_id)) {
    erros.tipo_programacao_id =
      'Selecione o tipo.'
  }

  if (!Number(form.local_id)) {
    erros.local_id =
      'Selecione o local.'
  }

  if (!Number(form.organizador_id)) {
    erros.organizador_id =
      'Selecione o responsável.'
  }

  if (!form.inicio_base) {
    erros.inicio_base =
      'Informe o início.'
  }

  if (!form.fim_base) {
    erros.fim_base =
      'Informe o término.'
  }

  if (
    form.inicio_base
    && form.fim_base
    && form.fim_base <= form.inicio_base
  ) {
    erros.fim_base =
      'O término deve ser posterior ao início.'
  }

  const intervalo =
    Number(form.intervalo_semanas)

  if (
    !Number.isInteger(intervalo)
    || intervalo < 1
    || intervalo > 52
  ) {
    erros.intervalo_semanas =
      'Use um número entre 1 e 52.'
  }

  if (!form.data_limite) {
    erros.data_limite =
      'Informe a data limite.'
  }

  if (totalPrevisto > 200) {
    erros.data_limite =
      'A regra gera mais de 200 ocorrências.'
  }

  return erros
}

function calcularOcorrencias(
  inicioBase,
  dataLimite,
  intervaloSemanas,
) {
  if (
    !inicioBase
    || !dataLimite
    || !Number.isInteger(intervaloSemanas)
    || intervaloSemanas < 1
  ) {
    return 0
  }

  const inicio = new Date(inicioBase)
  const limite = parseDataLimite(dataLimite)

  if (
    Number.isNaN(inicio.getTime())
    || !limite
    || limite < inicio
  ) {
    return 0
  }

  const passo =
    intervaloSemanas
    * 7
    * 24
    * 60
    * 60
    * 1000

  return (
    Math.floor(
      (
        limite.getTime()
        - inicio.getTime()
      ) / passo,
    ) + 1
  )
}

function montarPreviaSemanas(
  inicioBase,
  dataLimite,
  intervaloSemanas,
) {
  if (
    !inicioBase
    || !dataLimite
    || !Number.isInteger(
      intervaloSemanas,
    )
    || intervaloSemanas < 1
  ) {
    return null
  }

  const inicio =
    new Date(
      inicioBase,
    )

  const limite =
    parseDataLimite(
      dataLimite,
    )

  if (
    Number.isNaN(
      inicio.getTime(),
    )
    || !limite
    || limite < inicio
  ) {
    return null
  }

  const passoDias =
    intervaloSemanas * 7

  const ocorrencias = []

  let atual =
    new Date(
      inicio,
    )

  let seguranca = 0

  while (
    atual <= limite
    && seguranca < 200
  ) {
    ocorrencias.push(
      criarReferenciaSemana(
        atual,
      ),
    )

    atual =
      new Date(
        atual,
      )

    atual.setDate(
      atual.getDate()
      + passoDias,
    )

    seguranca++
  }

  if (ocorrencias.length === 0) {
    return null
  }

  const limiteVisual = 8

  return {
    primeira:
      ocorrencias[0],

    ultima:
      ocorrencias[
        ocorrencias.length - 1
      ],

    itens:
      ocorrencias.slice(
        0,
        limiteVisual,
      ),

    restantes:
      Math.max(
        0,
        ocorrencias.length
        - limiteVisual,
      ),

    total:
      ocorrencias.length,
  }
}

function criarReferenciaSemana(
  dataOriginal,
) {
  const data =
    new Date(
      dataOriginal.getFullYear(),
      dataOriginal.getMonth(),
      dataOriginal.getDate(),
      12,
      0,
      0,
    )

  const semana =
    obterNumeroSemanaISO(
      data,
    )

  const ano =
    obterAnoSemanaISO(
      data,
    )

  const dataISO =
    formatarISO(
      data,
    )

  return {
    chave:
      `${ano}-W${String(
        semana,
      ).padStart(2, '0')}-${dataISO}`,

    semana,
    ano,
    dataISO,

    dataCurta:
      formatarDataCurta(
        data,
      ),

    dataFormatada:
      data.toLocaleDateString(
        'pt-BR',
      ),
  }
}

function obterNumeroSemanaISO(
  dataOriginal,
) {
  const data =
    new Date(
      Date.UTC(
        dataOriginal.getFullYear(),
        dataOriginal.getMonth(),
        dataOriginal.getDate(),
      ),
    )

  const diaSemana =
    data.getUTCDay()
    || 7

  data.setUTCDate(
    data.getUTCDate()
    + 4
    - diaSemana,
  )

  const primeiroDiaAno =
    new Date(
      Date.UTC(
        data.getUTCFullYear(),
        0,
        1,
      ),
    )

  return Math.ceil(
    (
      (
        data
        - primeiroDiaAno
      )
      / 86400000
      + 1
    )
    / 7,
  )
}

function obterAnoSemanaISO(
  dataOriginal,
) {
  const data =
    new Date(
      Date.UTC(
        dataOriginal.getFullYear(),
        dataOriginal.getMonth(),
        dataOriginal.getDate(),
      ),
    )

  const diaSemana =
    data.getUTCDay()
    || 7

  data.setUTCDate(
    data.getUTCDate()
    + 4
    - diaSemana,
  )

  return data.getUTCFullYear()
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

function formatarDataCurta(
  data,
) {
  return data
    .toLocaleDateString(
      'pt-BR',
      {
        day: '2-digit',
        month: 'short',
      },
    )
    .replace('.', '')
}

function textoPrevia(form) {
  if (!form.inicio_base) {
    return 'Defina a primeira ocorrência.'
  }

  const inicio = new Date(form.inicio_base)

  if (Number.isNaN(inicio.getTime())) {
    return 'Defina uma data válida.'
  }

  const intervalo =
    Number(form.intervalo_semanas) || 1

  const dia =
    inicio.toLocaleDateString(
      'pt-BR',
      { weekday: 'long' },
    )

  const hora =
    String(form.inicio_base).slice(11, 16)

  return intervalo === 1
    ? `Toda ${dia}, às ${hora}`
    : `A cada ${intervalo} semanas, ${dia}, às ${hora}`
}

function parseDataLimite(valor) {
  if (
    !/^\d{4}-\d{2}-\d{2}$/.test(
      String(valor || ''),
    )
  ) {
    return null
  }

  const [ano, mes, dia] =
    String(valor)
      .split('-')
      .map(Number)

  return new Date(
    ano,
    mes - 1,
    dia,
    23,
    59,
    59,
  )
}

function paraApiDataHora(valor) {
  return valor
    ? `${String(valor)
        .replace('T', ' ')
        .slice(0, 16)}:00`
    : ''
}

function formatarDataHora(valor) {
  if (!valor) return '—'

  const data =
    new Date(
      String(valor).replace(' ', 'T'),
    )

  if (Number.isNaN(data.getTime())) {
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

function normalizarTipo(item) {
  return {
    id: Number(item?.id) || 0,
    nome: item?.nome || 'Tipo',
    ativo: ativoBoolean(item?.ativo),
  }
}

function normalizarLocal(item) {
  return {
    id: Number(item?.id) || 0,
    nome: item?.nome || 'Local',
    ativo: ativoBoolean(item?.ativo),
  }
}

function normalizarUsuario(item) {
  const papel =
    typeof item?.papel === 'object'
      ? item.papel
      : null

  return {
    id: Number(item?.id) || 0,
    nome: item?.nome || 'Usuário',
    status:
      String(
        item?.status || 'ATIVO',
      ).toUpperCase(),
    papel_codigo:
      item?.papel_codigo
      ?? papel?.codigo
      ?? '',
    papel_nome:
      item?.papel_nome
      ?? papel?.nome
      ?? 'Usuário',
  }
}

function extrairLista(response, chave) {
  const lista =
    response?.dados?.[chave]
    ?? response?.dados
    ?? response?.[chave]
    ?? []

  return Array.isArray(lista)
    ? lista
    : []
}

function ativoBoolean(valor) {
  return !(
    valor === false
    || valor === 0
    || valor === '0'
  )
}

function mensagemErro(err, fallback) {
  const erros = err?.payload?.erros

  if (
    erros
    && typeof erros === 'object'
  ) {
    const primeira =
      Object.values(erros)
        .find(
          (valor) =>
            typeof valor === 'string',
        )

    if (primeira) {
      return primeira
    }
  }

  return err?.message || fallback
}
