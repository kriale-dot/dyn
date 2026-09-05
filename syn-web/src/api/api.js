/**
 * URL base da API.
 *
 * Regra da Etapa 100:
 * - se VITE_API_URL estiver configurado, ele continua tendo prioridade;
 * - sem configuração, usamos automaticamente o mesmo host pelo qual
 *   o frontend foi aberto, na porta 8282.
 *
 * Exemplos:
 *   frontend: http://localhost:5173
 *   API:      http://localhost:8282
 *
 *   frontend: http://192.168.15.8:5173
 *   API:      http://192.168.15.8:8282
 *
 * Isso evita que o celular tente acessar "localhost:8282",
 * que apontaria para o próprio celular.
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

    /**
     * Se uma requisição autenticada recebe 401, a API está
     * informando que o JWT não é mais aceito.
     *
     * O api.js não conhece React e não deve manipular estado
     * de interface. Por isso ele publica apenas um evento
     * global. O AuthContext escuta esse evento e encerra a
     * sessão de forma centralizada.
     *
     * Importante: só emitimos quando já existia JWT local.
     * Assim um 401 de login/recuperação não é confundido com
     * expiração de sessão.
     */
    if (
      response.status === 401
      && token
    ) {
      window.dispatchEvent(
        new CustomEvent(
          'syn:unauthorized',
          {
            detail: {
              message,
            },
          },
        ),
      )
    }

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


/* ==========================================================
   ETAPA 48 — CRIAÇÃO / EDIÇÃO DE PROGRAMAÇÕES
   ========================================================== */

export async function getProgramacao(
  programacaoId,
) {
  return apiRequest(
    `/programacoes/${programacaoId}`,
  )
}

export async function criarProgramacao(
  dados,
) {
  return apiRequest(
    '/programacoes',
    {
      method: 'POST',
      body: JSON.stringify(
        dados,
      ),
    },
  )
}

export async function atualizarProgramacao(
  programacaoId,
  dados,
) {
  return apiRequest(
    `/programacoes/${programacaoId}`,
    {
      method: 'PUT',
      body: JSON.stringify(
        dados,
      ),
    },
  )
}

export async function cancelarProgramacao(
  programacaoId,
  motivo = null,
) {
  return apiRequest(
    `/programacoes/${programacaoId}/cancelar`,
    {
      method: 'PATCH',
      body: JSON.stringify({
        motivo:
          motivo?.trim()
          || null,
      }),
    },
  )
}

export async function realizarProgramacao(
  programacaoId,
) {
  return apiRequest(
    `/programacoes/${programacaoId}/realizar`,
    {
      method: 'PATCH',
    },
  )
}


/* ==========================================================
   ETAPA 49 — PROGRAMAÇÕES RECORRENTES
   ========================================================== */

export async function getSeriesProgramacao() {
  return apiRequest('/series-programacao')
}

export async function getSerieProgramacao(serieId) {
  return apiRequest(`/series-programacao/${serieId}`)
}

export async function criarSerieProgramacao(dados) {
  return apiRequest('/series-programacao', {
    method: 'POST',
    body: JSON.stringify(dados),
  })
}

export async function desativarSerieProgramacao(serieId) {
  return apiRequest(`/series-programacao/${serieId}/desativar`, {
    method: 'PATCH',
  })
}


/* ==========================================================
   ETAPA 50 — RECUPERAÇÃO DE SENHA
   ========================================================== */

export async function solicitarRecuperacaoSenha(
  email,
) {
  return apiRequest(
    '/auth/esqueci-senha',
    {
      method: 'POST',
      body: JSON.stringify({
        email,
      }),
    },
  )
}

export async function redefinirSenha(
  token,
  novaSenha,
  confirmarSenha,
) {
  return apiRequest(
    '/auth/redefinir-senha',
    {
      method: 'POST',
      body: JSON.stringify({
        token,
        nova_senha:
          novaSenha,
        confirmar_senha:
          confirmarSenha,
      }),
    },
  )
}


/* ==========================================================
   ETAPA 51 — PERMISSÕES DO ORGANIZADOR
   ========================================================== */

export async function getPermissoesOrganizador(
  usuarioId,
) {
  return apiRequest(
    `/organizadores/${usuarioId}/tipos-programacao`,
  )
}

export async function concederPermissaoOrganizador(
  usuarioId,
  tipoProgramacaoId,
) {
  return apiRequest(
    `/organizadores/${usuarioId}/tipos-programacao/${tipoProgramacaoId}`,
    {
      method: 'POST',
    },
  )
}

export async function revogarPermissaoOrganizador(
  usuarioId,
  tipoProgramacaoId,
) {
  return apiRequest(
    `/organizadores/${usuarioId}/tipos-programacao/${tipoProgramacaoId}`,
    {
      method: 'DELETE',
    },
  )
}

export async function getCatalogoPermissoesEspeciais() {
  return apiRequest(
    '/permissoes-especiais',
  )
}

export async function getPermissoesEspeciaisUsuario(
  usuarioId,
) {
  return apiRequest(
    `/usuarios/${usuarioId}/permissoes-especiais`,
  )
}

export async function concederPermissaoEspecial(
  usuarioId,
  permissaoId,
) {
  return apiRequest(
    `/usuarios/${usuarioId}/permissoes-especiais/${permissaoId}`,
    {
      method: 'POST',
    },
  )
}

export async function revogarPermissaoEspecial(
  usuarioId,
  permissaoId,
) {
  return apiRequest(
    `/usuarios/${usuarioId}/permissoes-especiais/${permissaoId}`,
    {
      method: 'DELETE',
    },
  )
}


/* ==========================================================
   ETAPA 52 — DADOS INSTITUCIONAIS DA IGREJA
   ========================================================== */

export async function getIgreja() {
  return apiRequest(
    '/igreja',
  )
}

export async function atualizarIgreja(
  dados,
) {
  return apiRequest(
    '/igreja',
    {
      method: 'PUT',
      body: JSON.stringify(
        dados,
      ),
    },
  )
}

export async function enviarLogotipoIgreja(
  arquivo,
) {
  const formData =
    new FormData()

  formData.append(
    'logotipo',
    arquivo,
  )

  return apiRequest(
    '/igreja/logotipo',
    {
      method: 'POST',
      body: formData,
    },
  )
}

export async function removerLogotipoIgreja() {
  return apiRequest(
    '/igreja/logotipo',
    {
      method: 'DELETE',
    },
  )
}


/* ==========================================================
   ETAPA 53 — HISTÓRICO DE ALTERAÇÕES DA PROGRAMAÇÃO
   ========================================================== */

export async function getHistoricoAlteracoesProgramacao(
  programacaoId,
) {
  return apiRequest(
    `/programacoes/${programacaoId}/historico-alteracoes`,
  )
}


/* ==========================================================
   ETAPA 54 — AUDITORIA ADMINISTRATIVA
   ========================================================== */

export async function getAuditoria(
  filtros = {},
) {
  const params =
    new URLSearchParams()

  if (filtros.pagina) {
    params.set(
      'pagina',
      String(filtros.pagina),
    )
  }

  if (filtros.limite) {
    params.set(
      'limite',
      String(filtros.limite),
    )
  }

  if (filtros.usuario_id) {
    params.set(
      'usuario_id',
      String(filtros.usuario_id),
    )
  }

  if (filtros.metodo) {
    params.set(
      'metodo',
      filtros.metodo,
    )
  }

  if (filtros.recurso) {
    params.set(
      'recurso',
      filtros.recurso,
    )
  }

  if (
    filtros.somente_erros
    === true
  ) {
    params.set(
      'somente_erros',
      'true',
    )
  }

  const query =
    params.toString()

  return apiRequest(
    `/auditoria${
      query
        ? `?${query}`
        : ''
    }`,
  )
}

export async function getAuditoriaRegistro(
  auditoriaId,
) {
  return apiRequest(
    `/auditoria/${auditoriaId}`,
  )
}


/* ==========================================================
   ETAPA 60 — ESCALAS DA SEMANA
   ========================================================== */

export async function getEscalasSemana(
  dataReferencia = null,
) {
  const query =
    dataReferencia
      ? `?data_referencia=${encodeURIComponent(
          dataReferencia,
        )}`
      : ''

  return apiRequest(
    `/gestao/escalas-semana${query}`,
  )
}


/* ==========================================================
   ETAPA 74 — ÁREA PÚBLICA
   ========================================================== */

/**
 * As rotas abaixo não exigem JWT.
 *
 * Usamos o mesmo apiRequest porque ele já centraliza tratamento
 * HTTP/JSON. Quando não existe sessão, nenhum Authorization é
 * enviado. Se existir sessão, as rotas públicas continuam
 * respondendo normalmente porque não possuem middleware de auth.
 */

export async function getIgrejaPublica() {
  return apiRequest(
    '/publico/igreja',
  )
}

export async function getMapaSemanaPublico(
  dataReferencia = null,
) {
  const query =
    dataReferencia
      ? `?data_referencia=${encodeURIComponent(
          dataReferencia,
        )}`
      : ''

  return apiRequest(
    `/publico/mapa-semana${query}`,
  )
}

export async function getProgramacoesPublicas(
  dataInicial = null,
  dataFinal = null,
) {
  const params =
    new URLSearchParams()

  if (dataInicial) {
    params.set(
      'data_inicial',
      dataInicial,
    )
  }

  if (dataFinal) {
    params.set(
      'data_final',
      dataFinal,
    )
  }

  const query =
    params.toString()

  return apiRequest(
    `/publico/programacoes${
      query
        ? `?${query}`
        : ''
    }`,
  )
}

export async function getProgramacaoPublica(
  programacaoId,
) {
  return apiRequest(
    `/publico/programacoes/${programacaoId}`,
  )
}

/**
 * Resolve caminhos de imagens servidas pela própria API.
 *
 * Se a API já devolver URL absoluta, ela é preservada.
 * Se devolver um caminho como /uploads/igreja/logo.webp,
 * acrescentamos a origem configurada em VITE_API_URL.
 */
export function resolveApiAssetUrl(
  caminho,
) {
  if (!caminho) {
    return null
  }

  const valor =
    String(caminho)

  if (
    /^https?:\/\//i.test(
      valor,
    )
  ) {
    return valor
  }

  return `${
    API_URL.replace(
      /\/+$/,
      '',
    )
  }/${
    valor.replace(
      /^\/+/,
      '',
    )
  }`
}


/* ==========================================================
   ETAPA 81 — CADASTRO PÚBLICO COM APROVAÇÃO
   ========================================================== */

/**
 * Cria somente uma solicitação.
 *
 * Não exige JWT e não cria usuário diretamente.
 */
export async function solicitarCadastroPublico(
  dados,
) {
  return apiRequest(
    '/publico/cadastros',
    {
      method: 'POST',
      body: JSON.stringify(
        dados,
      ),
    },
  )
}

/**
 * Fila de cadastro para Administrador ou Organizador autorizado.
 */
export async function getCadastros(
  status = 'PENDENTE',
) {
  return apiRequest(
    `/gestao/cadastros?status=${encodeURIComponent(
      status,
    )}`,
  )
}

export async function getCadastro(
  cadastroId,
) {
  return apiRequest(
    `/gestao/cadastros/${cadastroId}`,
  )
}

export async function aprovarCadastro(
  cadastroId,
) {
  return apiRequest(
    `/gestao/cadastros/${cadastroId}/aprovar`,
    {
      method: 'PATCH',
      body: JSON.stringify({}),
    },
  )
}

export async function rejeitarCadastro(
  cadastroId,
  motivo = null,
) {
  return apiRequest(
    `/gestao/cadastros/${cadastroId}/rejeitar`,
    {
      method: 'PATCH',
      body: JSON.stringify({
        motivo:
          motivo
          || null,
      }),
    },
  )
}

/* ==========================================================
   ETAPA 84 — CONFIRMAÇÃO DE E-MAIL DO CADASTRO
   ========================================================== */

export async function confirmarEmailCadastro(
  token,
) {
  return apiRequest(
    '/publico/cadastros/confirmar-email',
    {
      method: 'POST',
      body: JSON.stringify({
        token,
      }),
    },
  )
}

export async function reenviarConfirmacaoCadastro(
  email,
) {
  return apiRequest(
    '/publico/cadastros/reenviar-confirmacao',
    {
      method: 'POST',
      body: JSON.stringify({
        email,
      }),
    },
  )
}

