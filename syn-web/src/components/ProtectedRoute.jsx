import {
  Navigate,
} from 'react-router-dom'

import {
  useAuth,
} from '../contexts/AuthContext'

export default function ProtectedRoute({
  children,
}) {
  const {
    isAuthenticated,
    loading,
  } = useAuth()

  if (loading) {
    return (
      <main className="screen-center">
        <div className="loading-card">
          <strong>Carregando SYN...</strong>
        </div>
      </main>
    )
  }

  if (!isAuthenticated) {
    return (
      <Navigate
        to="/login"
        replace
      />
    )
  }

  return children
}
