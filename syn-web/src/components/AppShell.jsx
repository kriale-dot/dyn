import {
  NavLink,
  Outlet,
  useNavigate,
} from 'react-router-dom'

import {
  useAuth,
} from '../contexts/AuthContext'

import NotificacoesPopover
  from './NotificacoesPopover'

import './AppShellEtapa44.css'
import './AppShellMobileEtapa103.css'

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

export default function AppShell() {
  const navigate =
    useNavigate()

  const {
    usuario,
    igreja,
    navegacao,
    signOut,
  } = useAuth()

  const fotoUsuario =
    resolverArquivoApi(
      usuario?.foto,
    )

  /**
   * Logout manual:
   *
   * 1. encerra a sessão;
   * 2. leva explicitamente para a raiz pública.
   *
   * Sem este navigate(), o usuário permanece em uma rota
   * protegida (por exemplo /inicio). Ao perder a sessão,
   * ProtectedRoute detecta que não há autenticação e envia
   * para /login.
   *
   * Depois da Etapa 74 a raiz "/" é o Mapa Público para
   * visitantes, portanto esse é o destino correto do logout.
   */
  function sair() {
    signOut()

    navigate(
      '/',
      {
        replace: true,
      },
    )
  }

  return (
    <div className="app-shell">
      <header className="topbar">
        <div className="brand">
          {igreja?.logotipo ? (
            <img
              src={
                resolverArquivoApi(
                  igreja.logotipo,
                )
              }
              alt=""
              className="brand-logo"
            />
          ) : (
            <div className="brand-mark">
              S
            </div>
          )}

          <div>
            <strong>SYN</strong>
            <span>
              {igreja?.nome
                || 'Organização da Igreja'}
            </span>
          </div>
        </div>

        <div className="user-area">
          <div className="shell-user-avatar">
            {fotoUsuario ? (
              <img
                src={fotoUsuario}
                alt=""
              />
            ) : (
              <span>
                {iniciais(
                  usuario?.nome,
                )}
              </span>
            )}
          </div>

          <div className="user-name">
            <strong>
              {usuario?.nome}
            </strong>

            <span>
              {usuario?.papel?.nome}
            </span>
          </div>

          <NotificacoesPopover />

          <button
            type="button"
            className="button-secondary"
            onClick={sair}
          >
            Sair
          </button>
        </div>
      </header>

      <div className="app-layout">
        <aside className="sidebar">
          <nav>
            {navegacao.map(
              (item) => (
                <NavLink
                  key={item.codigo}
                  to={item.rota}
                  className={({
                    isActive,
                  }) =>
                    isActive
                      ? 'nav-link active'
                      : 'nav-link'
                  }
                >
                  {item.rotulo}
                </NavLink>
              ),
            )}
          </nav>
        </aside>

        <main className="content">
          <Outlet />
        </main>
      </div>
    </div>
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
