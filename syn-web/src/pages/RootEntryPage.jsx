import {
  Navigate,
} from 'react-router-dom'

import {
  useAuth,
} from '../contexts/AuthContext'

import PublicHomePage
  from './PublicHomePage'

/**
 * Porta de entrada do SYN.
 *
 * A pessoa não precisa decidir antes se é "visitante" ou "membro".
 *
 * - Sem login: entra diretamente na programação pública.
 * - Com login: entra diretamente na área interna.
 */
export default function RootEntryPage() {
  const {
    isAuthenticated,
    loading,
  } = useAuth()

  if (loading) {
    return (
      <main className="screen-center">
        <div className="loading-card">
          <strong>
            Abrindo o SYN...
          </strong>
        </div>
      </main>
    )
  }

  if (isAuthenticated) {
    return (
      <Navigate
        to="/inicio"
        replace
      />
    )
  }

  return (
    <PublicHomePage
      entradaPrincipal
    />
  )
}
