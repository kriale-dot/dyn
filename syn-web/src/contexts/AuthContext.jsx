import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from 'react'

import {
  clearToken,
  getBootstrap,
  getToken,
  login as loginRequest,
  setToken,
} from '../api/api'

const AuthContext =
  createContext(null)

export function AuthProvider({
  children,
}) {
  const [bootstrap, setBootstrap] =
    useState(null)

  const [loading, setLoading] =
    useState(true)

  const [authError, setAuthError] =
    useState(null)

  /**
   * Recarrega identidade, igreja, capacidades
   * e navegação diretamente da API.
   *
   * É usado também depois de alterações do
   * próprio perfil para o cabeçalho atualizar
   * nome e foto sem exigir novo login.
   */
  const refreshBootstrap =
    useCallback(
      async () => {
        const response =
          await getBootstrap()

        const dados =
          response?.dados
          ?? null

        setBootstrap(
          dados,
        )

        return dados
      },
      [],
    )

  useEffect(() => {
    async function restoreSession() {
      const token =
        getToken()

      if (!token) {
        setLoading(false)
        return
      }

      try {
        await refreshBootstrap()
      } catch (error) {
        clearToken()
        setBootstrap(null)

        if (
          error?.status !== 401
        ) {
          setAuthError(
            error.message,
          )
        }
      } finally {
        setLoading(false)
      }
    }

    restoreSession()
  }, [refreshBootstrap])

  async function signIn(
    email,
    senha,
  ) {
    setAuthError(null)

    const response =
      await loginRequest(
        email,
        senha,
      )

    const token =
      response?.dados?.token
      ?? response?.token
      ?? response?.dados?.access_token
      ?? response?.access_token
      ?? null

    if (!token) {
      throw new Error(
        'A API autenticou, mas não retornou o token esperado.',
      )
    }

    setToken(token)

    try {
      await refreshBootstrap()
    } catch (error) {
      clearToken()
      throw error
    }
  }

  function signOut() {
    clearToken()
    setBootstrap(null)
    setAuthError(null)
  }

  const value =
    useMemo(
      () => ({
        bootstrap,

        usuario:
          bootstrap?.usuario
          ?? null,

        igreja:
          bootstrap?.igreja
          ?? null,

        capacidades:
          bootstrap?.capacidades
          ?? {},

        navegacao:
          bootstrap?.navegacao
          ?? [],

        isAuthenticated:
          Boolean(
            bootstrap?.usuario,
          ),

        loading,
        authError,

        signIn,
        signOut,
        refreshBootstrap,
      }),
      [
        bootstrap,
        loading,
        authError,
        refreshBootstrap,
      ],
    )

  return (
    <AuthContext.Provider
      value={value}
    >
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  const context =
    useContext(AuthContext)

  if (!context) {
    throw new Error(
      'useAuth deve ser usado dentro de AuthProvider.',
    )
  }

  return context
}
