import {
  useCallback,
  useEffect,
  useMemo,
  useState,
} from 'react'

import './UsuariosPageFoto.css'
import './UsuariosPageEtapa116.css'

import UsuarioPermissoesModal
  from '../components/UsuarioPermissoesModal'

import {
  atribuirFuncaoUsuario,
  atualizarUsuario,
  criarUsuario,
  desativarUsuario,
  getFuncoes,
  getUsuario,
  getUsuarios,
  enviarFotoUsuario,
  removerFotoUsuario,
  removerFuncaoUsuario,
} from '../api/api'

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

const MAX_FOTO_BYTES =
  5 * 1024 * 1024

const MIME_FOTO_PERMITIDOS =
  new Set([
    'image/jpeg',
    'image/png',
    'image/webp',
  ])

const FOTO_MAX_DIMENSAO =
  2000

const FOTO_QUALIDADE_JPEG =
  0.88

function carregarImagemLocal(
  arquivo,
) {
  return new Promise(
    (resolve, reject) => {
      const url =
        URL.createObjectURL(
          arquivo,
        )

      const imagem =
        new Image()

      imagem.onload =
        () => {
          URL.revokeObjectURL(
            url,
          )
          resolve(imagem)
        }

      imagem.onerror =
        () => {
          URL.revokeObjectURL(
            url,
          )
          reject(
            new Error(
              'Não foi possível processar a foto selecionada.',
            ),
          )
        }

      imagem.src =
        url
    },
  )
}

async function prepararFotoUsuario(
  arquivo,
) {
  if (
    !MIME_FOTO_PERMITIDOS.has(
      arquivo.type,
    )
  ) {
    throw new Error(
      'Formato não permitido. Use JPEG, PNG ou WEBP.',
    )
  }

  if (
    arquivo.size
    > MAX_FOTO_BYTES
  ) {
    throw new Error(
      'A foto deve possuir no máximo 5 MB.',
    )
  }

  const imagem =
    await carregarImagemLocal(
      arquivo,
    )

  const largura =
    Number(
      imagem.naturalWidth
      || imagem.width
      || 0,
    )

  const altura =
    Number(
      imagem.naturalHeight
      || imagem.height
      || 0,
    )

  if (
    largura <= 0
    || altura <= 0
  ) {
    throw new Error(
      'Não foi possível identificar as dimensões da foto.',
    )
  }

  if (
    largura <= FOTO_MAX_DIMENSAO
    && altura <= FOTO_MAX_DIMENSAO
  ) {
    return arquivo
  }

  const escala =
    Math.min(
      FOTO_MAX_DIMENSAO / largura,
      FOTO_MAX_DIMENSAO / altura,
    )

  const novaLargura =
    Math.max(
      1,
      Math.round(
        largura * escala,
      ),
    )

  const novaAltura =
    Math.max(
      1,
      Math.round(
        altura * escala,
      ),
    )

  const canvas =
    document.createElement(
      'canvas',
    )

  canvas.width =
    novaLargura

  canvas.height =
    novaAltura

  const contexto =
    canvas.getContext(
      '2d',
    )

  if (!contexto) {
    throw new Error(
      'Seu navegador não conseguiu preparar a foto para envio.',
    )
  }

  contexto.fillStyle =
    '#ffffff'

  contexto.fillRect(
    0,
    0,
    novaLargura,
    novaAltura,
  )

  contexto.drawImage(
    imagem,
    0,
    0,
    novaLargura,
    novaAltura,
  )

  const blob =
    await new Promise(
      (resolve, reject) => {
        canvas.toBlob(
          (resultado) => {
            if (!resultado) {
              reject(
                new Error(
                  'Não foi possível reduzir a resolução da foto.',
                ),
              )
              return
            }

            resolve(resultado)
          },
          'image/jpeg',
          FOTO_QUALIDADE_JPEG,
        )
      },
    )

  const nomeOriginal =
    String(
      arquivo.name
      || 'foto',
    )

  const nomeSemExtensao =
    nomeOriginal
      .replace(
        /\.[^.]+$/,
        '',
      )
      .trim()
      || 'foto'

  return new File(
    [blob],
    `${nomeSemExtensao}.jpg`,
    {
      type:
        'image/jpeg',
      lastModified:
        Date.now(),
    },
  )
}

const PAPEIS = [
  {
    id: 1,
    codigo: 'ADMINISTRADOR',
    nome: 'Administrador',
  },
  {
    id: 2,
    codigo: 'ORGANIZADOR',
    nome: 'Organizador',
  },
  {
    id: 3,
    codigo: 'MEMBRO',
    nome: 'Membro',
  },
]

export default function UsuariosPage() {
  const [usuarios, setUsuarios] =
    useState([])

  const [funcoes, setFuncoes] =
    useState([])

  const [loading, setLoading] =
    useState(true)

  const [error, setError] =
    useState('')

  const [success, setSuccess] =
    useState('')

  const [busca, setBusca] =
    useState('')

  const [filtroStatus, setFiltroStatus] =
    useState('TODOS')

  const [modal, setModal] =
    useState(null)

  const carregar =
    useCallback(
      async () => {
        setLoading(true)
        setError('')

        try {
          const [
            usuariosResponse,
            funcoesResponse,
          ] =
            await Promise.all([
              getUsuarios(),
              getFuncoes(),
            ])

          setUsuarios(
            extrairLista(
              usuariosResponse,
              'usuarios',
            ),
          )

          setFuncoes(
            extrairLista(
              funcoesResponse,
              'funcoes',
            ),
          )
        } catch (err) {
          setError(
            err?.message
            || 'Não foi possível carregar os usuários.',
          )
        } finally {
          setLoading(false)
        }
      },
      [],
    )

  useEffect(() => {
    carregar()
  }, [carregar])

  const usuariosNormalizados =
    useMemo(
      () =>
        usuarios.map(
          normalizarUsuario,
        ),
      [usuarios],
    )

  const filtrados =
    useMemo(
      () => {
        const termo =
          busca
            .trim()
            .toLocaleLowerCase(
              'pt-BR',
            )

        return usuariosNormalizados
          .filter(
            (usuario) => {
              if (
                filtroStatus !== 'TODOS'
                && usuario.status
                  !== filtroStatus
              ) {
                return false
              }

              if (!termo) {
                return true
              }

              const texto =
                [
                  usuario.nome,
                  usuario.email,
                  usuario.telefone,
                  usuario.papel_nome,
                ]
                  .filter(Boolean)
                  .join(' ')
                  .toLocaleLowerCase(
                    'pt-BR',
                  )

              return texto.includes(
                termo,
              )
            },
          )
          .sort(
            (a, b) =>
              a.nome.localeCompare(
                b.nome,
                'pt-BR',
              ),
          )
      },
      [
        usuariosNormalizados,
        busca,
        filtroStatus,
      ],
    )

  const resumo =
    useMemo(
      () => ({
        total:
          usuariosNormalizados.length,

        ativos:
          usuariosNormalizados
            .filter(
              (item) =>
                item.status === 'ATIVO',
            )
            .length,

        organizadores:
          usuariosNormalizados
            .filter(
              (item) =>
                item.papel_codigo
                  === 'ORGANIZADOR',
            )
            .length,

        membros:
          usuariosNormalizados
            .filter(
              (item) =>
                item.papel_codigo
                  === 'MEMBRO',
            )
            .length,
      }),
      [usuariosNormalizados],
    )

  function abrirNovoUsuario() {
    setModal({
      tipo: 'FORMULARIO',
      usuario: null,
    })
  }

  async function abrirEdicao(
    usuario,
  ) {
    setError('')
    setSuccess('')

    try {
      const response =
        await getUsuario(
          usuario.id,
        )

      const detalhe =
        extrairObjeto(
          response,
          'usuario',
        )

      setModal({
        tipo: 'FORMULARIO',
        usuario:
          normalizarUsuario(
            detalhe,
          ),
      })
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível abrir o usuário.',
      )
    }
  }

  async function abrirFuncoes(
    usuario,
  ) {
    setError('')
    setSuccess('')

    try {
      const response =
        await getUsuario(
          usuario.id,
        )

      const detalhe =
        extrairObjeto(
          response,
          'usuario',
        )

      setModal({
        tipo: 'FUNCOES',
        usuario:
          normalizarUsuario(
            detalhe,
          ),
      })
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível carregar as funções do usuário.',
      )
    }
  }

  function abrirPermissoes(
    usuario,
  ) {
    setError('')
    setSuccess('')

    setModal({
      tipo: 'PERMISSOES',
      usuario,
    })
  }

  async function abrirFoto(
    usuario,
  ) {
    setError('')
    setSuccess('')

    try {
      const response =
        await getUsuario(
          usuario.id,
        )

      const detalhe =
        extrairObjeto(
          response,
          'usuario',
        )

      setModal({
        tipo: 'FOTO',
        usuario:
          normalizarUsuario(
            detalhe,
          ),
      })
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível abrir a foto do usuário.',
      )
    }
  }

  async function confirmarDesativacao(
    usuario,
  ) {
    if (
      usuario.status
      !== 'ATIVO'
    ) {
      return
    }

    const confirmou =
      window.confirm(
        `Desativar ${usuario.nome}? O histórico será preservado, mas o usuário não poderá receber novas escalas.`,
      )

    if (!confirmou) {
      return
    }

    setError('')
    setSuccess('')

    try {
      await desativarUsuario(
        usuario.id,
      )

      setSuccess(
        `${usuario.nome} foi desativado.`,
      )

      await carregar()
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível desativar o usuário.',
      )
    }
  }

  return (
    <div className="users-page">
      <section className="users-hero">
        <div>
          <span className="eyebrow">
            Administração
          </span>

          <h1>
            Usuários
          </h1>

          <p>
            Cadastre membros, defina o papel
            institucional e mantenha as funções
            atuais de cada pessoa.
          </p>
        </div>

        <button
          type="button"
          className="button-primary"
          onClick={abrirNovoUsuario}
        >
          + Novo usuário
        </button>
      </section>

      <section className="users-summary-grid">
        <Metric
          label="Total"
          value={resumo.total}
        />

        <Metric
          label="Ativos"
          value={resumo.ativos}
        />

        <Metric
          label="Organizadores"
          value={resumo.organizadores}
        />

        <Metric
          label="Membros"
          value={resumo.membros}
        />
      </section>

      <section className="users-toolbar">
        <label>
          <span>
            Buscar usuário
          </span>

          <input
            type="search"
            placeholder="Nome, e-mail, telefone..."
            value={busca}
            onChange={(event) =>
              setBusca(
                event.target.value,
              )
            }
          />
        </label>

        <label>
          <span>Status</span>

          <select
            value={filtroStatus}
            onChange={(event) =>
              setFiltroStatus(
                event.target.value,
              )
            }
          >
            <option value="TODOS">
              Todos
            </option>

            <option value="ATIVO">
              Ativos
            </option>

            <option value="INATIVO">
              Inativos
            </option>
          </select>
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

      {loading ? (
        <div className="loading-card">
          Carregando usuários...
        </div>
      ) : filtrados.length === 0 ? (
        <section className="panel">
          <p className="empty-state">
            Nenhum usuário encontrado.
          </p>
        </section>
      ) : (
        <section className="users-list">
          {filtrados.map(
            (usuario) => (
              <UsuarioCard
                key={usuario.id}
                usuario={usuario}
                onEdit={() =>
                  abrirEdicao(
                    usuario,
                  )
                }
                onFunctions={() =>
                  abrirFuncoes(
                    usuario,
                  )
                }
                onPermissions={() =>
                  abrirPermissoes(
                    usuario,
                  )
                }
                onPhoto={() =>
                  abrirFoto(
                    usuario,
                  )
                }
                onDeactivate={() =>
                  confirmarDesativacao(
                    usuario,
                  )
                }
              />
            ),
          )}
        </section>
      )}

      {modal?.tipo === 'FORMULARIO' && (
        <UsuarioFormModal
          usuario={modal.usuario}
          onClose={() =>
            setModal(null)
          }
          onSaved={async (
            mensagem,
          ) => {
            setModal(null)
            setSuccess(mensagem)
            await carregar()
          }}
        />
      )}

      {modal?.tipo === 'PERMISSOES' && (
        <UsuarioPermissoesModal
          usuario={modal.usuario}
          onClose={() =>
            setModal(null)
          }
        />
      )}

      {modal?.tipo === 'FOTO' && (
        <UsuarioFotoModal
          usuario={modal.usuario}
          onClose={() =>
            setModal(null)
          }
          onUpdated={async (
            mensagem,
          ) => {
            setSuccess(mensagem)

            const response =
              await getUsuario(
                modal.usuario.id,
              )

            const detalhe =
              extrairObjeto(
                response,
                'usuario',
              )

            setModal({
              tipo: 'FOTO',
              usuario:
                normalizarUsuario(
                  detalhe,
                ),
            })

            await carregar()
          }}
        />
      )}

      {modal?.tipo === 'FUNCOES' && (
        <UsuarioFuncoesModal
          usuario={modal.usuario}
          funcoes={funcoes}
          onClose={() =>
            setModal(null)
          }
          onUpdated={async (
            mensagem,
          ) => {
            setSuccess(mensagem)

            const response =
              await getUsuario(
                modal.usuario.id,
              )

            const detalhe =
              extrairObjeto(
                response,
                'usuario',
              )

            setModal({
              tipo: 'FUNCOES',
              usuario:
                normalizarUsuario(
                  detalhe,
                ),
            })

            await carregar()
          }}
        />
      )}
    </div>
  )
}

function UsuarioCard({
  usuario,
  onEdit,
  onFunctions,
  onPermissions,
  onPhoto,
  onDeactivate,
}) {
  return (
    <article className="user-card">
      <div className="user-avatar-large">
        {usuario.foto ? (
          <img
            src={resolverArquivoApi(
              usuario.foto,
            )}
            alt={`Foto de ${usuario.nome}`}
          />
        ) : (
          <span>
            {iniciais(
              usuario.nome,
            )}
          </span>
        )}
      </div>

      <div className="user-card-main">
        <div className="user-card-title">
          <div>
            <strong>
              {usuario.nome}
            </strong>

            <span>
              {usuario.email}
            </span>
          </div>

          <span
            className={
              usuario.status === 'ATIVO'
                ? 'user-status active'
                : 'user-status inactive'
            }
          >
            {usuario.status === 'ATIVO'
              ? 'Ativo'
              : 'Inativo'}
          </span>
        </div>

        <div className="user-card-meta">
          <span>
            {usuario.papel_nome}
          </span>

          {usuario.telefone && (
            <span>
              {usuario.telefone}
            </span>
          )}

          <span>
            {usuario.funcoes.length}
            {' '}
            {usuario.funcoes.length === 1
              ? 'função'
              : 'funções'}
          </span>
        </div>

        {usuario.funcoes.length > 0 && (
          <div className="user-function-tags">
            {usuario.funcoes
              .slice(0, 4)
              .map(
                (funcao) => (
                  <span
                    key={funcao.id}
                  >
                    {funcao.nome}
                  </span>
                ),
              )}

            {usuario.funcoes.length > 4 && (
              <span>
                +{
                  usuario.funcoes.length
                  - 4
                }
              </span>
            )}
          </div>
        )}
      </div>

      <div className="user-card-actions">
        <button
          type="button"
          className="small-secondary-button"
          onClick={onEdit}
        >
          Editar
        </button>

        {usuario.status === 'ATIVO' && (
          <button
            type="button"
            className="small-secondary-button"
            onClick={onPhoto}
          >
            Foto
          </button>
        )}

        <button
          type="button"
          className="small-secondary-button"
          onClick={onFunctions}
        >
          Funções
        </button>

        {usuario.papel_codigo === 'ORGANIZADOR'
          && usuario.status === 'ATIVO' && (
          <button
            type="button"
            className="small-secondary-button"
            onClick={onPermissions}
          >
            Permissões
          </button>
        )}

        {usuario.status === 'ATIVO' && (
          <button
            type="button"
            className="small-danger-button"
            onClick={onDeactivate}
          >
            Desativar
          </button>
        )}
      </div>
    </article>
  )
}

function UsuarioFotoModal({
  usuario,
  onClose,
  onUpdated,
}) {
  const [busy, setBusy] =
    useState(false)

  const [error, setError] =
    useState('')

  async function selecionarFoto(
    event,
  ) {
    const arquivo =
      event.target.files?.[0]

    event.target.value = ''

    if (!arquivo) {
      return
    }

    setBusy(true)
    setError('')

    try {
      const fotoPreparada =
        await prepararFotoUsuario(
          arquivo,
        )

      if (
        fotoPreparada.size
        > MAX_FOTO_BYTES
      ) {
        throw new Error(
          'Mesmo após o ajuste automático, a foto ficou acima de 5 MB.',
        )
      }

      await enviarFotoUsuario(
        usuario.id,
        fotoPreparada,
      )

      await onUpdated(
        fotoPreparada === arquivo
          ? `Foto de ${usuario.nome} atualizada com sucesso.`
          : `Foto de ${usuario.nome} atualizada. A resolução foi ajustada automaticamente.`,
      )
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível atualizar a foto do usuário.',
      )
    } finally {
      setBusy(false)
    }
  }

  async function removerFoto() {
    if (!usuario.foto) {
      return
    }

    const confirmou =
      window.confirm(
        `Remover a foto de ${usuario.nome}?`,
      )

    if (!confirmou) {
      return
    }

    setBusy(true)
    setError('')

    try {
      await removerFotoUsuario(
        usuario.id,
      )

      await onUpdated(
        `Foto de ${usuario.nome} removida com sucesso.`,
      )
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível remover a foto do usuário.',
      )
    } finally {
      setBusy(false)
    }
  }

  return (
    <div
      className="modal-backdrop"
      role="presentation"
      onMouseDown={(event) => {
        if (
          !busy
          && event.target
            === event.currentTarget
        ) {
          onClose()
        }
      }}
    >
      <section
        className="modal-card usuario116-photo-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="usuario116-foto-title"
      >
        <header className="modal-header">
          <div>
            <span className="eyebrow">
              Administração
            </span>

            <h2 id="usuario116-foto-title">
              Foto do usuário
            </h2>
          </div>

          <button
            type="button"
            className="modal-close"
            onClick={onClose}
            disabled={busy}
            aria-label="Fechar"
          >
            ×
          </button>
        </header>

        <div className="usuario116-photo-content">
          <div className="usuario116-photo-preview">
            {usuario.foto ? (
              <img
                src={resolverArquivoApi(
                  usuario.foto,
                )}
                alt={`Foto de ${usuario.nome}`}
              />
            ) : (
              <span>
                {iniciais(
                  usuario.nome,
                )}
              </span>
            )}
          </div>

          <div className="usuario116-photo-info">
            <strong>
              {usuario.nome}
            </strong>

            <span>
              {usuario.email}
            </span>

            <p>
              JPEG, PNG ou WEBP. Máximo de 5 MB.
              Fotos em alta resolução são ajustadas automaticamente.
            </p>
          </div>
        </div>

        {error && (
          <div
            className="error-message"
            role="alert"
          >
            {error}
          </div>
        )}

        <footer className="modal-actions usuario116-photo-actions">
          {usuario.foto && (
            <button
              type="button"
              className="small-danger-button"
              onClick={removerFoto}
              disabled={busy}
            >
              Remover foto
            </button>
          )}

          <label
            className={
              `button-primary usuario116-photo-select ${
                busy
                  ? 'is-disabled'
                  : ''
              }`
            }
          >
            {busy
              ? 'Enviando...'
              : 'Escolher foto'}

            <input
              type="file"
              accept="image/jpeg,image/png,image/webp"
              onChange={selecionarFoto}
              disabled={busy}
            />
          </label>

          <button
            type="button"
            className="button-secondary"
            onClick={onClose}
            disabled={busy}
          >
            Fechar
          </button>
        </footer>
      </section>
    </div>
  )
}

function UsuarioFormModal({
  usuario,
  onClose,
  onSaved,
}) {
  const editando =
    Boolean(usuario)

  const [form, setForm] =
    useState({
      nome:
        usuario?.nome
        || '',
      data_nascimento:
        usuario?.data_nascimento
        || '',
      telefone:
        usuario?.telefone
        || '',
      email:
        usuario?.email
        || '',
      senha: '',
      papel_id:
        usuario?.papel_id
        || 3,
    })

  const [saving, setSaving] =
    useState(false)

  const [error, setError] =
    useState('')

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
  }

  async function salvar(
    event,
  ) {
    event.preventDefault()
    setSaving(true)
    setError('')

    try {
      const dadosBase = {
        nome:
          form.nome.trim(),

        data_nascimento:
          form.data_nascimento
          || null,

        telefone:
          form.telefone.trim()
          || null,

        email:
          form.email
            .trim()
            .toLowerCase(),

        foto:
          usuario?.foto
          ?? null,

        papel_id:
          Number(
            form.papel_id,
          ),
      }

      if (editando) {
        await atualizarUsuario(
          usuario.id,
          dadosBase,
        )

        await onSaved(
          'Usuário atualizado com sucesso.',
        )
      } else {
        await criarUsuario(
          {
            ...dadosBase,
            senha:
              form.senha,
          },
        )

        await onSaved(
          'Usuário criado com sucesso.',
        )
      }
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível salvar o usuário.',
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
        if (
          event.target
          === event.currentTarget
        ) {
          onClose()
        }
      }}
    >
      <section
        className="modal-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="usuario-modal-title"
      >
        <header className="modal-header">
          <div>
            <span className="eyebrow">
              {editando
                ? 'Edição'
                : 'Cadastro'}
            </span>

            <h2 id="usuario-modal-title">
              {editando
                ? 'Editar usuário'
                : 'Novo usuário'}
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

        <form
          className="user-form"
          onSubmit={salvar}
        >
          <div className="form-grid two-columns">
            <label>
              <span>Nome *</span>

              <input
                type="text"
                required
                value={form.nome}
                onChange={(event) =>
                  alterar(
                    'nome',
                    event.target.value,
                  )
                }
              />
            </label>

            <label>
              <span>Papel *</span>

              <select
                value={form.papel_id}
                onChange={(event) =>
                  alterar(
                    'papel_id',
                    event.target.value,
                  )
                }
              >
                {PAPEIS.map(
                  (papel) => (
                    <option
                      key={papel.id}
                      value={papel.id}
                    >
                      {papel.nome}
                    </option>
                  ),
                )}
              </select>
            </label>
          </div>

          <div className="form-grid two-columns">
            <label>
              <span>E-mail *</span>

              <input
                type="email"
                required
                value={form.email}
                onChange={(event) =>
                  alterar(
                    'email',
                    event.target.value,
                  )
                }
              />
            </label>

            <label>
              <span>Telefone</span>

              <input
                type="text"
                value={form.telefone}
                onChange={(event) =>
                  alterar(
                    'telefone',
                    event.target.value,
                  )
                }
              />
            </label>
          </div>

          <div className="form-grid two-columns">
            <label>
              <span>
                Data de nascimento
              </span>

              <input
                type="date"
                value={
                  form.data_nascimento
                }
                onChange={(event) =>
                  alterar(
                    'data_nascimento',
                    event.target.value,
                  )
                }
              />
            </label>

            {!editando && (
              <label>
                <span>
                  Senha inicial *
                </span>

                <input
                  type="password"
                  required
                  minLength={5}
                  value={form.senha}
                  onChange={(event) =>
                    alterar(
                      'senha',
                      event.target.value,
                    )
                  }
                />
              </label>
            )}
          </div>

          {editando && (
            <p className="form-note">
              A senha não é alterada nesta tela.
              A recuperação de senha possui fluxo próprio.
            </p>
          )}

          {error && (
            <div className="error-message">
              {error}
            </div>
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
              {saving
                ? 'Salvando...'
                : 'Salvar'}
            </button>
          </footer>
        </form>
      </section>
    </div>
  )
}

function UsuarioFuncoesModal({
  usuario,
  funcoes,
  onClose,
  onUpdated,
}) {
  const [busy, setBusy] =
    useState('')

  const [error, setError] =
    useState('')

  const funcoesNormalizadas =
    funcoes
      .map(
        normalizarFuncao,
      )
      .filter(
        (funcao) =>
          funcao.ativo,
      )

  const idsAtuais =
    new Set(
      usuario.funcoes.map(
        (funcao) =>
          Number(funcao.id),
      ),
    )

  async function alternar(
    funcao,
  ) {
    const atribuida =
      idsAtuais.has(
        funcao.id,
      )

    const chave =
      `${atribuida ? 'remove' : 'add'}:${funcao.id}`

    setBusy(chave)
    setError('')

    try {
      if (atribuida) {
        await removerFuncaoUsuario(
          usuario.id,
          funcao.id,
        )

        await onUpdated(
          `${funcao.nome} foi removida de ${usuario.nome}.`,
        )
      } else {
        await atribuirFuncaoUsuario(
          usuario.id,
          funcao.id,
        )

        await onUpdated(
          `${funcao.nome} foi atribuída a ${usuario.nome}.`,
        )
      }
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível alterar a função.',
      )
    } finally {
      setBusy('')
    }
  }

  return (
    <div
      className="modal-backdrop"
      role="presentation"
      onMouseDown={(event) => {
        if (
          event.target
          === event.currentTarget
        ) {
          onClose()
        }
      }}
    >
      <section
        className="modal-card functions-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="funcoes-modal-title"
      >
        <header className="modal-header">
          <div>
            <span className="eyebrow">
              Habilitações atuais
            </span>

            <h2 id="funcoes-modal-title">
              Funções de {usuario.nome}
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

        <p className="form-note">
          Remover uma função altera apenas
          a habilitação atual. Participações
          históricas são preservadas.
        </p>

        {error && (
          <div className="error-message">
            {error}
          </div>
        )}

        <div className="functions-picker">
          {funcoesNormalizadas.map(
            (funcao) => {
              const atribuida =
                idsAtuais.has(
                  funcao.id,
                )

              const chave =
                `${atribuida ? 'remove' : 'add'}:${funcao.id}`

              return (
                <article
                  key={funcao.id}
                  className={
                    atribuida
                      ? 'function-choice selected'
                      : 'function-choice'
                  }
                >
                  <div>
                    <strong>
                      {funcao.nome}
                    </strong>

                    <span>
                      {
                        funcao.departamento
                        || 'Sem departamento'
                      }
                    </span>
                  </div>

                  <button
                    type="button"
                    className={
                      atribuida
                        ? 'small-danger-button'
                        : 'small-primary-button'
                    }
                    disabled={
                      busy === chave
                    }
                    onClick={() =>
                      alternar(
                        funcao,
                      )
                    }
                  >
                    {busy === chave
                      ? 'Aguarde...'
                      : atribuida
                        ? 'Remover'
                        : '+ Atribuir'}
                  </button>
                </article>
              )
            },
          )}
        </div>

        <footer className="modal-actions">
          <button
            type="button"
            className="button-secondary"
            onClick={onClose}
          >
            Fechar
          </button>
        </footer>
      </section>
    </div>
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
  chave,
) {
  const objeto =
    response?.dados?.[chave]
    ?? response?.dados
    ?? response?.[chave]
    ?? null

  return objeto
    && !Array.isArray(objeto)
      ? objeto
      : {}
}

function normalizarUsuario(
  item,
) {
  const papelObjeto =
    typeof item?.papel === 'object'
      && item.papel !== null
        ? item.papel
        : null

  const papelId =
    Number(
      item?.papel_id
      ?? papelObjeto?.id
      ?? 3,
    )

  const papelSeed =
    PAPEIS.find(
      (papel) =>
        papel.id === papelId,
    )

  const funcoesBrutas =
    Array.isArray(item?.funcoes)
      ? item.funcoes
      : Array.isArray(
          item?.funcoes_atuais,
        )
        ? item.funcoes_atuais
        : []

  return {
    id:
      Number(item?.id)
      || 0,

    nome:
      item?.nome
      || 'Usuário',

    email:
      item?.email
      || '',

    telefone:
      item?.telefone
      || '',

    foto:
      item?.foto
      ?? null,

    data_nascimento:
      normalizarData(
        item?.data_nascimento,
      ),

    status:
      String(
        item?.status
        || 'ATIVO',
      ).toUpperCase(),

    papel_id:
      papelId,

    papel_codigo:
      item?.papel_codigo
      || papelObjeto?.codigo
      || papelSeed?.codigo
      || 'MEMBRO',

    papel_nome:
      item?.papel_nome
      || papelObjeto?.nome
      || papelSeed?.nome
      || 'Membro',

    funcoes:
      funcoesBrutas.map(
        (funcao) => ({
          id:
            Number(
              funcao?.id
              ?? funcao?.funcao_id
              ?? 0,
            ),

          nome:
            funcao?.nome
            ?? funcao?.funcao_nome
            ?? 'Função',

          departamento:
            funcao?.departamento_nome
            ?? funcao?.departamento?.nome
            ?? null,
        }),
      ),
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

    ativo:
      item?.ativo === undefined
        ? true
        : Boolean(
            Number(item.ativo)
            || item.ativo === true,
          ),

    departamento:
      item?.departamento_nome
      ?? item?.departamento?.nome
      ?? null,
  }
}

function normalizarData(
  valor,
) {
  if (!valor) {
    return ''
  }

  return String(valor)
    .slice(0, 10)
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
