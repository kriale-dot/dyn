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
  atualizarDepartamento,
  atualizarFuncao,
  atualizarLocal,
  atualizarTipoProgramacao,
  criarDepartamento,
  criarFuncao,
  criarLocal,
  criarTipoProgramacao,
  desativarDepartamento,
  desativarFuncao,
  desativarLocal,
  desativarTipoProgramacao,
  getDepartamentos,
  getFuncoes,
  getLocais,
  getTiposProgramacao,
} from '../api/api'

import { useAuth } from '../contexts/AuthContext'

const ABAS = [
  {
    codigo: 'DEPARTAMENTOS',
    titulo: 'Departamentos',
    descricao: 'Áreas organizacionais da igreja.',
  },
  {
    codigo: 'FUNCOES',
    titulo: 'Funções',
    descricao: 'Habilitações usadas nas escalas.',
  },
  {
    codigo: 'TIPOS',
    titulo: 'Tipos de programação',
    descricao: 'Modelos das atividades da igreja.',
  },
  {
    codigo: 'LOCAIS',
    titulo: 'Locais',
    descricao: 'Espaços disponíveis para programações.',
  },
]

export default function EstruturaPage() {
  const navigate =
    useNavigate()

  const { capacidades } = useAuth()

  const [aba, setAba] = useState('DEPARTAMENTOS')
  const [departamentos, setDepartamentos] = useState([])
  const [funcoes, setFuncoes] = useState([])
  const [tipos, setTipos] = useState([])
  const [locais, setLocais] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [success, setSuccess] = useState('')
  const [busca, setBusca] = useState('')
  const [mostrarInativos, setMostrarInativos] = useState(false)
  const [modal, setModal] = useState(null)

  const podeAdministrar = Boolean(
    capacidades?.gerenciar_departamentos
      || capacidades?.gerenciar_funcoes
      || capacidades?.gerenciar_tipos_programacao
      || capacidades?.gerenciar_locais,
  )

  const carregar = useCallback(async () => {
    setLoading(true)
    setError('')

    try {
      const [
        departamentosResponse,
        funcoesResponse,
        tiposResponse,
        locaisResponse,
      ] = await Promise.all([
        getDepartamentos(),
        getFuncoes(),
        getTiposProgramacao(),
        getLocais(),
      ])

      setDepartamentos(
        extrairLista(departamentosResponse, 'departamentos'),
      )

      setFuncoes(
        extrairLista(funcoesResponse, 'funcoes'),
      )

      setTipos(
        extrairLista(tiposResponse, 'tipos_programacao'),
      )

      setLocais(
        extrairLista(locaisResponse, 'locais'),
      )
    } catch (err) {
      setError(
        err?.message
          || 'Não foi possível carregar a estrutura da igreja.',
      )
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    if (podeAdministrar) {
      carregar()
    } else {
      setLoading(false)
    }
  }, [carregar, podeAdministrar])

  const dadosAtuais = useMemo(() => {
    const termo = busca
      .trim()
      .toLocaleLowerCase('pt-BR')

    let lista = []

    if (aba === 'DEPARTAMENTOS') {
      lista = departamentos.map(normalizarDepartamento)
    }

    if (aba === 'FUNCOES') {
      lista = funcoes.map(normalizarFuncao)
    }

    if (aba === 'TIPOS') {
      lista = tipos.map(normalizarTipo)
    }

    if (aba === 'LOCAIS') {
      lista = locais.map(normalizarLocal)
    }

    return lista
      .filter((item) => mostrarInativos || item.ativo)
      .filter((item) => {
        if (!termo) {
          return true
        }

        return [
          item.nome,
          item.descricao,
          item.departamento_nome,
          item.capacidade,
        ]
          .filter(
            (valor) => valor !== null
              && valor !== undefined
              && valor !== '',
          )
          .join(' ')
          .toLocaleLowerCase('pt-BR')
          .includes(termo)
      })
      .sort((a, b) => a.nome.localeCompare(b.nome, 'pt-BR'))
  }, [
    aba,
    departamentos,
    funcoes,
    tipos,
    locais,
    busca,
    mostrarInativos,
  ])

  const abaAtual = ABAS.find(
    (item) => item.codigo === aba,
  )

  function abrirNovo() {
    setModal({
      modo: 'CRIAR',
      tipo: aba,
      item: null,
    })
  }

  function abrirEdicao(item) {
    setModal({
      modo: 'EDITAR',
      tipo: aba,
      item,
    })
  }

  async function desativar(item) {
    if (!item.ativo) {
      return
    }

    const confirmou = window.confirm(
      `Desativar "${item.nome}"? O histórico associado será preservado.`,
    )

    if (!confirmou) {
      return
    }

    setError('')
    setSuccess('')

    try {
      if (aba === 'DEPARTAMENTOS') {
        await desativarDepartamento(item.id)
      }

      if (aba === 'FUNCOES') {
        await desativarFuncao(item.id)
      }

      if (aba === 'TIPOS') {
        await desativarTipoProgramacao(item.id)
      }

      if (aba === 'LOCAIS') {
        await desativarLocal(item.id)
      }

      setSuccess(`"${item.nome}" foi desativado.`)
      await carregar()
    } catch (err) {
      setError(
        err?.message
          || 'Não foi possível desativar o item.',
      )
    }
  }

  if (!podeAdministrar) {
    return (
      <section className="panel">
        <span className="eyebrow">Acesso restrito</span>
        <h1>Estrutura da igreja</h1>
        <p className="empty-state">
          Seu usuário não possui permissão administrativa para esta área.
        </p>
      </section>
    )
  }

  return (
    <div className="structure-page">
      <section className="structure-hero">
        <div>
          <span className="eyebrow">Administração</span>
          <h1>Estrutura da igreja</h1>
          <p>
            Organize departamentos, funções, tipos de programação
            e locais sem perder o histórico do que já aconteceu.
          </p>
        </div>

        <button
          type="button"
          className="button-primary"
          onClick={abrirNovo}
        >
          + Novo
        </button>
      </section>

      <nav
        className="structure-tabs"
        aria-label="Estrutura da igreja"
      >
        {ABAS.map((item) => (
          <button
            type="button"
            key={item.codigo}
            className={
              aba === item.codigo
                ? 'structure-tab active'
                : 'structure-tab'
            }
            onClick={() => {
              setAba(item.codigo)
              setBusca('')
              setSuccess('')
              setError('')
            }}
          >
            <strong>{item.titulo}</strong>
            <span>{item.descricao}</span>
          </button>
        ))}
      </nav>

      <section className="structure-toolbar">
        <div className="structure-section-title">
          <span className="eyebrow">{abaAtual?.titulo}</span>
          <h2>{abaAtual?.descricao}</h2>
        </div>

        <div className="structure-tools">
          <label className="structure-search">
            <span>Buscar</span>
            <input
              type="search"
              placeholder="Nome ou descrição..."
              value={busca}
              onChange={(event) => setBusca(event.target.value)}
            />
          </label>

          <label className="toggle-field">
            <input
              type="checkbox"
              checked={mostrarInativos}
              onChange={(event) => setMostrarInativos(event.target.checked)}
            />
            <span>Mostrar inativos</span>
          </label>
        </div>
      </section>

      {error && (
        <div className="error-message" role="alert">
          {error}
        </div>
      )}

      {success && (
        <div className="success-message" role="status">
          {success}
        </div>
      )}

      {loading ? (
        <div className="loading-card">Carregando estrutura...</div>
      ) : dadosAtuais.length === 0 ? (
        <section className="panel">
          <p className="empty-state">Nenhum item encontrado.</p>
        </section>
      ) : (
        <section className="structure-grid">
          {dadosAtuais.map((item) => (
            <StructureCard
              key={item.id}
              tipo={aba}
              item={item}
              onEdit={() => abrirEdicao(item)}
              onDeactivate={() => desativar(item)}
            />
          ))}
        </section>
      )}

      {modal && (
        <StructureModal
          modal={modal}
          departamentos={
            departamentos
              .map(normalizarDepartamento)
              .filter((item) => item.ativo)
          }
          onClose={() => setModal(null)}
          onSaved={async (mensagem) => {
            setModal(null)
            setSuccess(mensagem)
            await carregar()
          }}
        />
      )}
    </div>
  )
}

function StructureCard({
  tipo,
  item,
  onEdit,
  onDeactivate,
  onConfigureFunctions,
}) {
  return (
    <article
      className={
        item.ativo
          ? 'structure-card'
          : 'structure-card inactive'
      }
    >
      <header className="structure-card-header">
        <div>
          <span className="structure-kind">
            {rotuloTipo(tipo)}
          </span>
          <h3>{item.nome}</h3>
        </div>

        <span
          className={
            item.ativo
              ? 'user-status active'
              : 'user-status inactive'
          }
        >
          {item.ativo ? 'Ativo' : 'Inativo'}
        </span>
      </header>

      {item.descricao ? (
        <p>{item.descricao}</p>
      ) : (
        <p className="structure-muted">Sem descrição.</p>
      )}

      {tipo === 'FUNCOES' && (
        <div className="structure-detail">
          <span>Departamento</span>
          <strong>
            {item.departamento_nome || 'Sem departamento'}
          </strong>
        </div>
      )}

      {tipo === 'LOCAIS' && (
        <div className="structure-detail">
          <span>Capacidade</span>
          <strong>
            {item.capacidade ?? 'Não informada'}
          </strong>
        </div>
      )}

      <footer className="structure-card-actions">
        {onConfigureFunctions && (
          <button
            type="button"
            className="small-primary-button"
            onClick={onConfigureFunctions}
          >
            Funções autorizadas
          </button>
        )}

        <button
          type="button"
          className="small-secondary-button"
          onClick={onEdit}
        >
          Editar
        </button>

        {item.ativo && (
          <button
            type="button"
            className="small-danger-button"
            onClick={onDeactivate}
          >
            Desativar
          </button>
        )}
      </footer>
    </article>
  )
}

function StructureModal({
  modal,
  departamentos,
  onClose,
  onSaved,
}) {
  const editando = modal.modo === 'EDITAR'
  const item = modal.item

  const [form, setForm] = useState({
    nome: item?.nome || '',
    descricao: item?.descricao || '',
    departamento_id: item?.departamento_id ?? '',
    capacidade: item?.capacidade ?? '',
  })

  const [saving, setSaving] = useState(false)
  const [error, setError] = useState('')

  function alterar(campo, valor) {
    setForm((atual) => ({
      ...atual,
      [campo]: valor,
    }))
  }

  async function salvar(event) {
    event.preventDefault()
    setSaving(true)
    setError('')

    try {
      const comum = {
        nome: form.nome.trim(),
        descricao: form.descricao.trim() || null,
      }

      if (modal.tipo === 'DEPARTAMENTOS') {
        if (editando) {
          await atualizarDepartamento(item.id, comum)
        } else {
          await criarDepartamento(comum)
        }
      }

      if (modal.tipo === 'FUNCOES') {
        const dados = {
          ...comum,
          departamento_id:
            form.departamento_id === ''
              ? null
              : Number(form.departamento_id),
        }

        if (editando) {
          await atualizarFuncao(item.id, dados)
        } else {
          await criarFuncao(dados)
        }
      }

      if (modal.tipo === 'TIPOS') {
        if (editando) {
          await atualizarTipoProgramacao(item.id, comum)
        } else {
          await criarTipoProgramacao(comum)
        }
      }

      if (modal.tipo === 'LOCAIS') {
        const dados = {
          ...comum,
          capacidade:
            form.capacidade === ''
              ? null
              : Number(form.capacidade),
        }

        if (editando) {
          await atualizarLocal(item.id, dados)
        } else {
          await criarLocal(dados)
        }
      }

      await onSaved(
        editando
          ? 'Cadastro atualizado com sucesso.'
          : 'Cadastro criado com sucesso.',
      )
    } catch (err) {
      setError(
        err?.message || 'Não foi possível salvar.',
      )
    } finally {
      setSaving(false)
    }
  }

  return (
    <div
      className="modal-backdrop"
      role="presentation"
      onMouseDown={(event) => {
        if (event.target === event.currentTarget) {
          onClose()
        }
      }}
    >
      <section
        className="modal-card"
        role="dialog"
        aria-modal="true"
      >
        <header className="modal-header">
          <div>
            <span className="eyebrow">
              {editando ? 'Edição' : 'Novo cadastro'}
            </span>
            <h2>
              {editando
                ? `Editar ${rotuloTipoSingular(modal.tipo)}`
                : `Novo ${rotuloTipoSingular(modal.tipo)}`}
            </h2>
          </div>

          <button
            type="button"
            className="modal-close"
            onClick={onClose}
            aria-label="Fechar"
          >
            ×
          </button>
        </header>

        <form className="user-form" onSubmit={salvar}>
          <label>
            <span>Nome *</span>
            <input
              type="text"
              required
              maxLength={120}
              value={form.nome}
              onChange={(event) => alterar('nome', event.target.value)}
            />
          </label>

          {modal.tipo === 'FUNCOES' && (
            <label>
              <span>Departamento</span>
              <select
                value={form.departamento_id}
                onChange={(event) => alterar(
                  'departamento_id',
                  event.target.value,
                )}
              >
                <option value="">Sem departamento</option>
                {departamentos.map((departamento) => (
                  <option
                    key={departamento.id}
                    value={departamento.id}
                  >
                    {departamento.nome}
                  </option>
                ))}
              </select>
            </label>
          )}

          {modal.tipo === 'LOCAIS' && (
            <label>
              <span>Capacidade</span>
              <input
                type="number"
                min="1"
                max="65535"
                value={form.capacidade}
                onChange={(event) => alterar(
                  'capacidade',
                  event.target.value,
                )}
              />
            </label>
          )}

          <label>
            <span>Descrição</span>
            <textarea
              className="structure-textarea"
              maxLength={500}
              rows={4}
              value={form.descricao}
              onChange={(event) => alterar(
                'descricao',
                event.target.value,
              )}
            />
          </label>

          {error && (
            <div className="error-message">{error}</div>
          )}

          <footer className="modal-actions">
            <button
              type="button"
              className="button-secondary"
              onClick={onClose}
            >
              Cancelar
            </button>

            <button
              type="submit"
              className="button-primary"
              disabled={saving}
            >
              {saving ? 'Salvando...' : 'Salvar'}
            </button>
          </footer>
        </form>
      </section>
    </div>
  )
}

function extrairLista(response, chave) {
  const lista =
    response?.dados?.[chave]
      ?? response?.dados
      ?? response?.[chave]
      ?? []

  return Array.isArray(lista) ? lista : []
}

function ativoBoolean(valor) {
  if (
    valor === false
    || valor === 0
    || valor === '0'
  ) {
    return false
  }

  return true
}

function normalizarDepartamento(item) {
  return {
    id: Number(item?.id) || 0,
    nome: item?.nome || 'Departamento',
    descricao: item?.descricao || '',
    ativo: ativoBoolean(item?.ativo),
  }
}

function normalizarFuncao(item) {
  return {
    id: Number(item?.id) || 0,
    nome: item?.nome || 'Função',
    descricao: item?.descricao || '',
    ativo: ativoBoolean(item?.ativo),
    departamento_id:
      item?.departamento_id === null
      || item?.departamento_id === undefined
        ? null
        : Number(item.departamento_id),
    departamento_nome:
      item?.departamento_nome
        ?? item?.departamento?.nome
        ?? null,
  }
}

function normalizarTipo(item) {
  return {
    id: Number(item?.id) || 0,
    nome: item?.nome || 'Tipo de programação',
    descricao: item?.descricao || '',
    ativo: ativoBoolean(item?.ativo),
  }
}

function normalizarLocal(item) {
  return {
    id: Number(item?.id) || 0,
    nome: item?.nome || 'Local',
    descricao: item?.descricao || '',
    capacidade:
      item?.capacidade === null
      || item?.capacidade === undefined
        ? null
        : Number(item.capacidade),
    ativo: ativoBoolean(item?.ativo),
  }
}

function rotuloTipo(tipo) {
  const mapa = {
    DEPARTAMENTOS: 'Departamento',
    FUNCOES: 'Função',
    TIPOS: 'Tipo de programação',
    LOCAIS: 'Local',
  }

  return mapa[tipo] || tipo
}

function rotuloTipoSingular(tipo) {
  return rotuloTipo(tipo).toLocaleLowerCase('pt-BR')
}
