const API_URL =
  import.meta.env.VITE_API_URL
  || 'http://localhost:8282'

const TOKEN_KEY = 'syn_token'

export function getToken() {
  return localStorage.getItem(TOKEN_KEY)
}

export function setToken(token) {
  localStorage.setItem(TOKEN_KEY, token)
}

export function clearToken() {
  localStorage.removeItem(TOKEN_KEY)
}

export async function apiRequest(
  path,
  options = {},
) {
  const token = getToken()

  const headers = new Headers(
    options.headers || {},
  )

  headers.set(
    'Accept',
    'application/json',
  )

  const isFormData =
    options.body instanceof FormData

  if (
    options.body
    && !isFormData
    && !headers.has('Content-Type')
  ) {
    headers.set(
      'Content-Type',
      'application/json',
    )
  }

  if (token) {
    headers.set(
      'Authorization',
      `Bearer ${token}`,
    )
  }

  const response = await fetch(
    `${API_URL}${path}`,
    {
      ...options,
      headers,
    },
  )

  let payload = null

  const contentType =
    response.headers.get('content-type')
    || ''

  if (
    contentType.includes(
      'application/json',
    )
  ) {
    payload = await response.json()
  }

  if (!response.ok) {
    const message =
      payload?.mensagem
      || `Erro HTTP ${response.status}`

    const error =
      new Error(message)

    error.status =
      response.status

    error.payload =
      payload

    throw error
  }

  return payload
}

export async function login(
  email,
  senha,
) {
  return apiRequest(
    '/auth/login',
    {
      method: 'POST',
      body: JSON.stringify({
        email,
        senha,
      }),
    },
  )
}

export async function getBootstrap() {
  return apiRequest(
    '/app-bootstrap',
  )
}

export async function getDashboard() {
  return apiRequest(
    '/dashboard',
  )
}

export async function getMapaSemana(
  dataReferencia = null,
) {
  const query =
    dataReferencia
      ? `?data_referencia=${encodeURIComponent(
          dataReferencia,
        )}`
      : ''

  return apiRequest(
    `/mapa-semana${query}`,
  )
}

export async function getDetalheProgramacao(
  programacaoId,
) {
  return apiRequest(
    `/programacoes/${programacaoId}/detalhes`,
  )
}

export async function getProgramacoes() {
  return apiRequest(
    '/programacoes',
  )
}

export async function getGestaoEscala(
  programacaoId,
) {
  return apiRequest(
    `/programacoes/${programacaoId}/gestao-escala`,
  )
}

/**
 * Adiciona alguém à escala.
 *
 * confirmarConflitoPessoa é usado somente quando a API
 * devolve 409 e o gestor decide conscientemente prosseguir.
 */
export async function adicionarParticipacao(
  programacaoId,
  usuarioId,
  funcaoId,
  confirmarConflitoPessoa = false,
) {
  return apiRequest(
    `/programacoes/${programacaoId}/participacoes`,
    {
      method: 'POST',
      body: JSON.stringify({
        usuario_id: usuarioId,
        funcao_id: funcaoId,
        confirmar_conflito_pessoa:
          confirmarConflitoPessoa,
      }),
    },
  )
}

export async function cancelarParticipacao(
  participacaoId,
) {
  return apiRequest(
    `/participacoes/${participacaoId}/cancelar`,
    {
      method: 'PATCH',
    },
  )
}

export async function confirmarParticipacao(
  participacaoId,
) {
  return apiRequest(
    `/participacoes/${participacaoId}/confirmar`,
    {
      method: 'PATCH',
    },
  )
}

export async function informarIndisponibilidade(
  participacaoId,
) {
  return apiRequest(
    `/participacoes/${participacaoId}/indisponivel`,
    {
      method: 'PATCH',
    },
  )
}

export async function recusarParticipacao(
  participacaoId,
) {
  return apiRequest(
    `/participacoes/${participacaoId}/recusar`,
    {
      method: 'PATCH',
    },
  )
}

/* ==========================================================
   ETAPA 41 — USUÁRIOS
   ========================================================== */

export async function getUsuarios() {
  return apiRequest(
    '/usuarios',
  )
}

export async function getUsuario(
  usuarioId,
) {
  return apiRequest(
    `/usuarios/${usuarioId}`,
  )
}

export async function criarUsuario(
  dados,
) {
  return apiRequest(
    '/usuarios',
    {
      method: 'POST',
      body: JSON.stringify(
        dados,
      ),
    },
  )
}

export async function atualizarUsuario(
  usuarioId,
  dados,
) {
  return apiRequest(
    `/usuarios/${usuarioId}`,
    {
      method: 'PUT',
      body: JSON.stringify(
        dados,
      ),
    },
  )
}

export async function desativarUsuario(
  usuarioId,
) {
  return apiRequest(
    `/usuarios/${usuarioId}/desativar`,
    {
      method: 'PATCH',
    },
  )
}

export async function getFuncoes() {
  return apiRequest(
    '/funcoes',
  )
}

export async function atribuirFuncaoUsuario(
  usuarioId,
  funcaoId,
) {
  return apiRequest(
    `/usuarios/${usuarioId}/funcoes/${funcaoId}`,
    {
      method: 'POST',
    },
  )
}

export async function removerFuncaoUsuario(
  usuarioId,
  funcaoId,
) {
  return apiRequest(
    `/usuarios/${usuarioId}/funcoes/${funcaoId}`,
    {
      method: 'DELETE',
    },
  )
}

/* ==========================================================
   ETAPA 42 — ESTRUTURA DA IGREJA
   ========================================================== */

export async function getDepartamentos() {
  return apiRequest('/departamentos')
}

export async function criarDepartamento(dados) {
  return apiRequest('/departamentos', {
    method: 'POST',
    body: JSON.stringify(dados),
  })
}

export async function atualizarDepartamento(id, dados) {
  return apiRequest(`/departamentos/${id}`, {
    method: 'PUT',
    body: JSON.stringify(dados),
  })
}

export async function desativarDepartamento(id) {
  return apiRequest(`/departamentos/${id}/desativar`, {
    method: 'PATCH',
  })
}

export async function criarFuncao(dados) {
  return apiRequest('/funcoes', {
    method: 'POST',
    body: JSON.stringify(dados),
  })
}

export async function atualizarFuncao(id, dados) {
  return apiRequest(`/funcoes/${id}`, {
    method: 'PUT',
    body: JSON.stringify(dados),
  })
}

export async function desativarFuncao(id) {
  return apiRequest(`/funcoes/${id}/desativar`, {
    method: 'PATCH',
  })
}

export async function getTiposProgramacao() {
  return apiRequest('/tipos-programacao')
}

export async function criarTipoProgramacao(dados) {
  return apiRequest('/tipos-programacao', {
    method: 'POST',
    body: JSON.stringify(dados),
  })
}

export async function atualizarTipoProgramacao(id, dados) {
  return apiRequest(`/tipos-programacao/${id}`, {
    method: 'PUT',
    body: JSON.stringify(dados),
  })
}

export async function desativarTipoProgramacao(id) {
  return apiRequest(`/tipos-programacao/${id}/desativar`, {
    method: 'PATCH',
  })
}

export async function getLocais() {
  return apiRequest('/locais')
}

export async function criarLocal(dados) {
  return apiRequest('/locais', {
    method: 'POST',
    body: JSON.stringify(dados),
  })
}

export async function atualizarLocal(id, dados) {
  return apiRequest(`/locais/${id}`, {
    method: 'PUT',
    body: JSON.stringify(dados),
  })
}

export async function desativarLocal(id) {
  return apiRequest(`/locais/${id}/desativar`, {
    method: 'PATCH',
  })
}


/* ==========================================================
   ETAPA 43 — FUNÇÃO × TIPO DE PROGRAMAÇÃO
   ========================================================== */

export async function getTipoProgramacaoDetalhe(
  tipoProgramacaoId,
) {
  return apiRequest(
    `/tipos-programacao/${tipoProgramacaoId}`,
  )
}

export async function autorizarFuncaoTipoProgramacao(
  tipoProgramacaoId,
  funcaoId,
) {
  return apiRequest(
    `/tipos-programacao/${tipoProgramacaoId}/funcoes/${funcaoId}`,
    {
      method: 'POST',
    },
  )
}

export async function removerFuncaoTipoProgramacao(
  tipoProgramacaoId,
  funcaoId,
) {
  return apiRequest(
    `/tipos-programacao/${tipoProgramacaoId}/funcoes/${funcaoId}`,
    {
      method: 'DELETE',
    },
  )
}


/* ==========================================================
   ETAPA 44 — MEU PERFIL
   ========================================================== */

export async function getMeuPerfil() {
  return apiRequest(
    '/meu-perfil',
  )
}

export async function atualizarMeuPerfil(
  dados,
) {
  return apiRequest(
    '/meu-perfil',
    {
      method: 'PUT',
      body: JSON.stringify(
        dados,
      ),
    },
  )
}

export async function enviarFotoPerfil(
  arquivo,
) {
  const formData =
    new FormData()

  formData.append(
    'foto',
    arquivo,
  )

  return apiRequest(
    '/meu-perfil/foto',
    {
      method: 'POST',
      body: formData,
    },
  )
}

export async function removerFotoPerfil() {
  return apiRequest(
    '/meu-perfil/foto',
    {
      method: 'DELETE',
    },
  )
}


/* ==========================================================
   ETAPA 45 — NECESSIDADES ESPECÍFICAS
   ========================================================== */

export async function getNecessidadesEspecificas() {
  return apiRequest(
    '/necessidades-especificas',
  )
}

export async function getNecessidadeEspecificaUsuario(
  usuarioId,
) {
  return apiRequest(
    `/usuarios/${usuarioId}/necessidade-especifica`,
  )
}

export async function salvarNecessidadeEspecifica(
  usuarioId,
  observacao,
) {
  return apiRequest(
    `/usuarios/${usuarioId}/necessidade-especifica`,
    {
      method: 'PUT',
      body: JSON.stringify({
        observacao,
      }),
    },
  )
}

export async function desativarNecessidadeEspecifica(
  usuarioId,
) {
  return apiRequest(
    `/usuarios/${usuarioId}/necessidade-especifica/desativar`,
    {
      method: 'PATCH',
    },
  )
}


/* ==========================================================
   ETAPA 46 — CENTRAL DE NOTIFICAÇÕES
   ========================================================== */

export async function getNotificacoes() {
  return apiRequest(
    '/notificacoes',
  )
}

export async function getResumoNotificacoes() {
  return apiRequest(
    '/notificacoes/resumo',
  )
}

export async function marcarNotificacaoComoLida(
  notificacaoId,
) {
  return apiRequest(
    `/notificacoes/${notificacaoId}/lida`,
    {
      method: 'PATCH',
    },
  )
}

export async function marcarTodasNotificacoesComoLidas() {
  return apiRequest(
    '/notificacoes/marcar-todas-lidas',
    {
      method: 'PATCH',
    },
  )
}
