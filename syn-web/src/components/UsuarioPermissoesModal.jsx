import {
  useCallback,
  useEffect,
  useMemo,
  useState,
} from 'react'

import {
  concederPermissaoEspecial,
  concederPermissaoOrganizador,
  getCatalogoPermissoesEspeciais,
  getPermissoesEspeciaisUsuario,
  getPermissoesOrganizador,
  getTiposProgramacao,
  revogarPermissaoEspecial,
  revogarPermissaoOrganizador,
} from '../api/api'

import './UsuarioPermissoesModal.css'

export default function UsuarioPermissoesModal({
  usuario,
  onClose,
}) {
  const [tipos, setTipos] =
    useState([])

  const [tiposPermitidos, setTiposPermitidos] =
    useState([])

  const [catalogoEspeciais, setCatalogoEspeciais] =
    useState([])

  const [especiaisUsuario, setEspeciaisUsuario] =
    useState([])

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
          const [
            tiposResponse,
            organizadorResponse,
            catalogoResponse,
            especiaisResponse,
          ] =
            await Promise.all([
              getTiposProgramacao(),
              getPermissoesOrganizador(
                usuario.id,
              ),
              getCatalogoPermissoesEspeciais(),
              getPermissoesEspeciaisUsuario(
                usuario.id,
              ),
            ])

          setTipos(
            extrairLista(
              tiposResponse,
              'tipos_programacao',
            ),
          )

          setTiposPermitidos(
            Array.isArray(
              organizadorResponse
                ?.dados
                ?.tipos_programacao,
            )
              ? organizadorResponse
                  .dados
                  .tipos_programacao
              : [],
          )

          setCatalogoEspeciais(
            Array.isArray(
              catalogoResponse?.dados,
            )
              ? catalogoResponse.dados
              : [],
          )

          setEspeciaisUsuario(
            Array.isArray(
              especiaisResponse
                ?.dados
                ?.permissoes,
            )
              ? especiaisResponse
                  .dados
                  .permissoes
              : [],
          )
        } catch (err) {
          setError(
            mensagemErro(
              err,
              'Não foi possível carregar as permissões.',
            ),
          )
        } finally {
          setLoading(false)
        }
      },
      [usuario.id],
    )

  useEffect(() => {
    carregar()
  }, [carregar])

  const tiposNormalizados =
    useMemo(
      () =>
        tipos
          .map(normalizarTipo)
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
      [tipos],
    )

  const idsTiposPermitidos =
    useMemo(
      () =>
        new Set(
          tiposPermitidos.map(
            (item) =>
              Number(
                item?.id
                ?? item
                  ?.tipo_programacao_id,
              ),
          ),
        ),
      [tiposPermitidos],
    )

  const especiaisNormalizadas =
    useMemo(
      () =>
        catalogoEspeciais
          .map(
            normalizarPermissaoEspecial,
          )
          .filter(
            (item) =>
              item.ativo,
          ),
      [catalogoEspeciais],
    )

  const idsEspeciais =
    useMemo(
      () =>
        new Set(
          especiaisUsuario.map(
            (item) =>
              Number(
                item?.id
                ?? item?.permissao_id,
              ),
          ),
        ),
      [especiaisUsuario],
    )

  async function alternarTipo(
    tipo,
  ) {
    const possui =
      idsTiposPermitidos.has(
        tipo.id,
      )

    const chave =
      `tipo:${tipo.id}`

    setBusy(chave)
    setError('')
    setSuccess('')

    try {
      if (possui) {
        await revogarPermissaoOrganizador(
          usuario.id,
          tipo.id,
        )

        setSuccess(
          `${usuario.nome} não administrará mais "${tipo.nome}".`,
        )
      } else {
        await concederPermissaoOrganizador(
          usuario.id,
          tipo.id,
        )

        setSuccess(
          `${usuario.nome} agora pode administrar "${tipo.nome}".`,
        )
      }

      await carregar()
    } catch (err) {
      setError(
        mensagemErro(
          err,
          'Não foi possível alterar a permissão.',
        ),
      )
    } finally {
      setBusy('')
    }
  }

  async function alternarEspecial(
    permissao,
  ) {
    const possui =
      idsEspeciais.has(
        permissao.id,
      )

    const chave =
      `especial:${permissao.id}`

    setBusy(chave)
    setError('')
    setSuccess('')

    try {
      if (possui) {
        await revogarPermissaoEspecial(
          usuario.id,
          permissao.id,
        )

        setSuccess(
          `Permissão especial "${permissao.nome}" revogada.`,
        )
      } else {
        await concederPermissaoEspecial(
          usuario.id,
          permissao.id,
        )

        setSuccess(
          `Permissão especial "${permissao.nome}" concedida.`,
        )
      }

      await carregar()
    } catch (err) {
      setError(
        mensagemErro(
          err,
          'Não foi possível alterar a permissão especial.',
        ),
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
        className="modal-card organizer-permissions-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="permissions-modal-title"
      >
        <header className="modal-header">
          <div>
            <span className="eyebrow">
              Organizador
            </span>

            <h2 id="permissions-modal-title">
              Permissões de {usuario.nome}
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

        <section className="organizer-permissions-intro">
          <strong>
            O papel Organizador não significa acesso total.
          </strong>

          <p>
            Escolha quais tipos de programação
            essa pessoa poderá administrar e,
            separadamente, quais informações
            especiais poderá acessar.
          </p>
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
            Carregando permissões...
          </div>
        ) : (
          <div className="organizer-permissions-sections">
            <section className="organizer-permission-section">
              <header>
                <div>
                  <span className="eyebrow">
                    Escopo
                  </span>

                  <h3>
                    Tipos de programação
                  </h3>

                  <p>
                    O Organizador poderá criar,
                    editar, escalar e administrar
                    apenas os tipos selecionados.
                  </p>
                </div>

                <span className="permission-section-count">
                  {idsTiposPermitidos.size}
                  {' / '}
                  {tiposNormalizados.length}
                </span>
              </header>

              {tiposNormalizados.length === 0 ? (
                <p className="empty-state">
                  Nenhum tipo de programação ativo.
                </p>
              ) : (
                <div className="permission-choice-list">
                  {tiposNormalizados.map(
                    (tipo) => {
                      const selecionado =
                        idsTiposPermitidos.has(
                          tipo.id,
                        )

                      const chave =
                        `tipo:${tipo.id}`

                      return (
                        <PermissionChoice
                          key={tipo.id}
                          titulo={tipo.nome}
                          descricao={
                            tipo.descricao
                            || 'Tipo de programação'
                          }
                          selected={
                            selecionado
                          }
                          disabled={
                            busy === chave
                          }
                          onClick={() =>
                            alternarTipo(
                              tipo,
                            )
                          }
                        />
                      )
                    },
                  )}
                </div>
              )}
            </section>

            <section className="organizer-permission-section special">
              <header>
                <div>
                  <span className="eyebrow">
                    Acesso especial
                  </span>

                  <h3>
                    Permissões sensíveis
                  </h3>

                  <p>
                    Essas permissões não são
                    concedidas automaticamente
                    pelo papel Organizador.
                  </p>
                </div>

                <span className="permission-section-count">
                  {idsEspeciais.size}
                  {' / '}
                  {especiaisNormalizadas.length}
                </span>
              </header>

              {especiaisNormalizadas.length === 0 ? (
                <p className="empty-state">
                  Nenhuma permissão especial cadastrada.
                </p>
              ) : (
                <div className="permission-choice-list">
                  {especiaisNormalizadas.map(
                    (permissao) => {
                      const selecionado =
                        idsEspeciais.has(
                          permissao.id,
                        )

                      const chave =
                        `especial:${permissao.id}`

                      return (
                        <PermissionChoice
                          key={
                            permissao.id
                          }
                          titulo={
                            permissao.nome
                          }
                          descricao={
                            permissao.descricao
                            || permissao.codigo
                          }
                          selected={
                            selecionado
                          }
                          sensitive
                          disabled={
                            busy === chave
                          }
                          onClick={() =>
                            alternarEspecial(
                              permissao,
                            )
                          }
                        />
                      )
                    },
                  )}
                </div>
              )}
            </section>
          </div>
        )}

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

function PermissionChoice({
  titulo,
  descricao,
  selected,
  sensitive = false,
  disabled,
  onClick,
}) {
  return (
    <button
      type="button"
      className={[
        'permission-choice',
        selected
          ? 'selected'
          : '',
        sensitive
          ? 'sensitive'
          : '',
      ]
        .filter(Boolean)
        .join(' ')}
      aria-pressed={selected}
      disabled={disabled}
      onClick={onClick}
    >
      <span
        className={
          selected
            ? 'permission-check checked'
            : 'permission-check'
        }
        aria-hidden="true"
      >
        {selected
          ? '✓'
          : ''}
      </span>

      <span className="permission-choice-copy">
        <strong>
          {titulo}
        </strong>

        <span>
          {descricao}
        </span>
      </span>

      <span
        className={
          selected
            ? 'permission-state active'
            : 'permission-state'
        }
      >
        {disabled
          ? 'Aguarde...'
          : selected
            ? 'Permitido'
            : 'Sem acesso'}
      </span>
    </button>
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
  }
}

function normalizarPermissaoEspecial(
  item,
) {
  return {
    id:
      Number(item?.id)
      || 0,

    codigo:
      item?.codigo
      || '',

    nome:
      item?.nome
      || item?.codigo
      || 'Permissão especial',

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
  }
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
