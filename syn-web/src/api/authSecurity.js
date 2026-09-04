import {
  apiRequest,
} from './api'

/**
 * Invalida no backend TODOS os JWT emitidos anteriormente para o
 * usuário autenticado, inclusive o token do navegador atual.
 */
export async function encerrarTodasSessoes() {
  return apiRequest(
    '/auth/encerrar-todas-sessoes',
    {
      method: 'POST',
      body: JSON.stringify({}),
    },
  )
}


/**
 * Altera a senha do usuário autenticado.
 *
 * Em caso de sucesso o backend também revoga todos os JWT existentes.
 */
export async function alterarMinhaSenha(
  dados,
) {
  return apiRequest(
    '/auth/alterar-senha',
    {
      method: 'POST',
      body: JSON.stringify(
        dados,
      ),
    },
  )
}


/**
 * Solicita a alteração do e-mail de login.
 * O endereço atual só muda depois da confirmação recebida no novo e-mail.
 */
export async function solicitarAlteracaoEmail(
  dados,
) {
  return apiRequest(
    '/meu-perfil/alterar-email',
    {
      method: 'POST',
      body: JSON.stringify(
        dados,
      ),
    },
  )
}

export async function confirmarAlteracaoEmail(
  token,
) {
  return apiRequest(
    '/publico/conta/confirmar-email',
    {
      method: 'POST',
      body: JSON.stringify({
        token,
      }),
    },
  )
}


/**
 * Lista eventos recentes de segurança do próprio usuário.
 */
export async function getMinhaAtividadeSeguranca(
  limite = 20,
) {
  return apiRequest(
    `/meu-perfil/atividade-seguranca?limite=${encodeURIComponent(
      limite,
    )}`,
  )
}
