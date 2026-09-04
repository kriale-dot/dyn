import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useRef,
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

const SESSION_EXPIRED_MESSAGE =
  'Sua sessão expirou. Entre novamente para continuar.'

export function AuthProvider({
  children,
}) {
  const [bootstrap, setBootstrap] =
    useState(null)

  const [loading, setLoading] =
    useState(true)

  const [authError, setAuthError] =
    useState(null)

  const expirationTimerRef =
    useRef(null)

  /**
   * Limpa qualquer timer que tenha sido criado para o JWT
   * anterior. Isso é importante ao fazer login novamente ou
   * ao sair manualmente.
   */
  const limparTimerExpiracao =
    useCallback(
      () => {
        if (
          expirationTimerRef.current
        ) {
          window.clearTimeout(
            expirationTimerRef.current,
          )

          expirationTimerRef.current =
            null
        }
      },
      [],
    )

  /**
   * Encerra apenas o estado local da sessão.
   *
   * Não há endpoint de logout no backend atual porque o JWT
   * é stateless. Remover o token do navegador é suficiente
   * para impedir novas requisições autenticadas desse cliente.
   */
  const encerrarSessaoLocal =
    useCallback(
      (
        mensagem = null,
      ) => {
        limparTimerExpiracao()
        clearToken()
        setBootstrap(null)
        setAuthError(
          mensagem,
        )
      },
      [limparTimerExpiracao],
    )

  /**
   * O campo "exp" do JWT é usado SOMENTE para agendar a
   * experiência de logout no frontend.
   *
   * Ele não é usado para autorizar nada. A API continua sendo
   * a autoridade real e valida assinatura, expiração, usuário
   * ativo e permissões.
   */
  const agendarExpiracao =
    useCallback(
      (token) => {
        limparTimerExpiracao()

        const exp =
          obterExpiracaoJwt(
            token,
          )

        if (!exp) {
          return
        }

        const agoraEmMs =
          Date.now()

        const expiracaoEmMs =
          exp * 1000

        const restante =
          expiracaoEmMs
          - agoraEmMs

        if (restante <= 0) {
          encerrarSessaoLocal(
            SESSION_EXPIRED_MESSAGE,
          )
          return
        }

        /**
         * setTimeout em navegadores possui limites grandes,
         * mas o JWT atual é curto (1h). Mantemos um teto de
         * 24 dias apenas para tornar a função defensiva.
         */
        const MAX_TIMEOUT =
          24
          * 24
          * 60
          * 60
          * 1000

        expirationTimerRef.current =
          window.setTimeout(
            () => {
              encerrarSessaoLocal(
                SESSION_EXPIRED_MESSAGE,
              )
            },
            Math.min(
              restante,
              MAX_TIMEOUT,
            ),
          )
      },
      [
        limparTimerExpiracao,
        encerrarSessaoLocal,
      ],
    )

  /**
   * Recarrega identidade, igreja, capacidades e navegação
   * diretamente da API.
   *
   * É usado também depois de alterações do próprio perfil ou
   * da igreja para atualizar o cabeçalho sem novo login.
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

  /**
   * Escuta 401 de qualquer requisição autenticada.
   *
   * Antes desta etapa, cada página acabava recebendo o erro e
   * o JWT inválido permanecia no localStorage. Agora existe
   * um único ponto de encerramento da sessão.
   */
  useEffect(() => {
    function onUnauthorized(
      event,
    ) {
      encerrarSessaoLocal(
        event
          ?.detail
          ?.message
        || SESSION_EXPIRED_MESSAGE,
      )
    }

    window.addEventListener(
      'syn:unauthorized',
      onUnauthorized,
    )

    return () => {
      window.removeEventListener(
        'syn:unauthorized',
        onUnauthorized,
      )
    }
  }, [encerrarSessaoLocal])

  /**
   * Restaura uma sessão após atualizar o navegador.
   */
  useEffect(() => {
    let ativo = true

    async function restoreSession() {
      const token =
        getToken()

      if (!token) {
        if (ativo) {
          setLoading(false)
        }
        return
      }

      const exp =
        obterExpiracaoJwt(
          token,
        )

      if (
        exp
        && exp * 1000
          <= Date.now()
      ) {
        encerrarSessaoLocal(
          SESSION_EXPIRED_MESSAGE,
        )

        if (ativo) {
          setLoading(false)
        }

        return
      }

      agendarExpiracao(
        token,
      )

      try {
        await refreshBootstrap()
      } catch (error) {
        /**
         * Em 401 o próprio apiRequest também emite
         * syn:unauthorized. Mesmo assim mantemos este fallback
         * para que a restauração seja segura caso a origem do
         * erro mude no futuro.
         */
        if (
          error?.status === 401
        ) {
          encerrarSessaoLocal(
            SESSION_EXPIRED_MESSAGE,
          )
        } else {
          encerrarSessaoLocal()
          setAuthError(
            error?.message
            || 'Não foi possível restaurar sua sessão.',
          )
        }
      } finally {
        if (ativo) {
          setLoading(false)
        }
      }
    }

    restoreSession()

    return () => {
      ativo = false
    }
  }, [
    agendarExpiracao,
    encerrarSessaoLocal,
    refreshBootstrap,
  ])

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
    agendarExpiracao(token)

    try {
      await refreshBootstrap()
    } catch (error) {
      encerrarSessaoLocal()
      throw error
    }
  }

  function signOut() {
    encerrarSessaoLocal()
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

/**
 * Lê apenas o payload JSON do JWT para obter "exp".
 *
 * Isso NÃO valida o token e nunca deve ser usado como decisão
 * de autorização. A validação real permanece no backend.
 */
function obterExpiracaoJwt(
  token,
) {
  try {
    const partes =
      String(token)
        .split('.')

    if (partes.length !== 3) {
      return null
    }

    const base64Url =
      partes[1]

    const base64 =
      base64Url
        .replace(/-/g, '+')
        .replace(/_/g, '/')
        .padEnd(
          Math.ceil(
            base64Url.length / 4,
          ) * 4,
          '=',
        )

    const json =
      decodeURIComponent(
        Array.from(
          atob(base64),
        )
          .map(
            (char) =>
              `%${char
                .charCodeAt(0)
                .toString(16)
                .padStart(2, '0')}`,
          )
          .join(''),
      )

    const payload =
      JSON.parse(json)

    const exp =
      Number(
        payload?.exp,
      )

    return Number.isFinite(exp)
      ? exp
      : null
  } catch {
    return null
  }
}
