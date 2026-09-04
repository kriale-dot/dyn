import {
  useCallback,
  useEffect,
  useMemo,
  useRef,
  useState,
} from 'react'

import {
  useNavigate,
} from 'react-router-dom'

import {
  getNotificacoes,
  getResumoNotificacoes,
  marcarNotificacaoComoLida,
  marcarTodasNotificacoesComoLidas,
} from '../api/api'

import './NotificacoesPopover.css'

const INTERVALO_ATUALIZACAO_MS =
  60 * 1000

export default function NotificacoesPopover() {
  const navigate =
    useNavigate()

  const containerRef =
    useRef(null)

  const [aberto, setAberto] =
    useState(false)

  const [notificacoes, setNotificacoes] =
    useState([])

  const [naoLidas, setNaoLidas] =
    useState(0)

  const [loading, setLoading] =
    useState(false)

  const [busy, setBusy] =
    useState('')

  const [error, setError] =
    useState('')

  const carregarResumo =
    useCallback(
      async () => {
        try {
          const response =
            await getResumoNotificacoes()

          setNaoLidas(
            extrairQuantidadeNaoLidas(
              response,
            ),
          )
        } catch {
          /**
           * O sino não deve quebrar o restante do sistema.
           * Uma falha temporária no resumo fica silenciosa.
           */
        }
      },
      [],
    )

  const carregarLista =
    useCallback(
      async () => {
        setLoading(true)
        setError('')

        try {
          const response =
            await getNotificacoes()

          const lista =
            extrairListaNotificacoes(
              response,
            )

          setNotificacoes(
            lista.map(
              normalizarNotificacao,
            ),
          )

          setNaoLidas(
            lista.filter(
              (item) =>
                !normalizarLida(
                  item,
                ),
            ).length,
          )
        } catch (err) {
          setError(
            err?.message
            || 'Não foi possível carregar as notificações.',
          )
        } finally {
          setLoading(false)
        }
      },
      [],
    )

  useEffect(() => {
    carregarResumo()

    const intervalo =
      window.setInterval(
        carregarResumo,
        INTERVALO_ATUALIZACAO_MS,
      )

    return () => {
      window.clearInterval(
        intervalo,
      )
    }
  }, [carregarResumo])

  useEffect(() => {
    if (!aberto) {
      return undefined
    }

    carregarLista()

    function fecharAoClicarFora(
      event,
    ) {
      if (
        containerRef.current
        && !containerRef
          .current
          .contains(
            event.target,
          )
      ) {
        setAberto(false)
      }
    }

    function fecharComEscape(
      event,
    ) {
      if (
        event.key
        === 'Escape'
      ) {
        setAberto(false)
      }
    }

    document.addEventListener(
      'mousedown',
      fecharAoClicarFora,
    )

    document.addEventListener(
      'keydown',
      fecharComEscape,
    )

    return () => {
      document.removeEventListener(
        'mousedown',
        fecharAoClicarFora,
      )

      document.removeEventListener(
        'keydown',
        fecharComEscape,
      )
    }
  }, [
    aberto,
    carregarLista,
  ])

  const visiveis =
    useMemo(
      () =>
        notificacoes.slice(
          0,
          20,
        ),
      [notificacoes],
    )

  async function abrirOuFechar() {
    setAberto(
      (valor) =>
        !valor,
    )
  }

  async function abrirNotificacao(
    notificacao,
  ) {
    setError('')

    if (!notificacao.lida) {
      const chave =
        `read:${notificacao.id}`

      setBusy(chave)

      try {
        await marcarNotificacaoComoLida(
          notificacao.id,
        )

        setNotificacoes(
          (atuais) =>
            atuais.map(
              (item) =>
                item.id
                  === notificacao.id
                    ? {
                        ...item,
                        lida: true,
                      }
                    : item,
            ),
        )

        setNaoLidas(
          (atual) =>
            Math.max(
              0,
              atual - 1,
            ),
        )
      } catch (err) {
        setError(
          err?.message
          || 'Não foi possível marcar a notificação como lida.',
        )
      } finally {
        setBusy('')
      }
    }

    if (
      notificacao.url_acao
    ) {
      setAberto(false)
      navegarParaAcao(
        notificacao.url_acao,
        navigate,
      )
    }
  }

  async function marcarTodas() {
    if (
      naoLidas === 0
      || busy
    ) {
      return
    }

    setBusy(
      'read-all',
    )
    setError('')

    try {
      await marcarTodasNotificacoesComoLidas()

      setNotificacoes(
        (atuais) =>
          atuais.map(
            (item) => ({
              ...item,
              lida: true,
            }),
          ),
      )

      setNaoLidas(0)
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível marcar todas como lidas.',
      )
    } finally {
      setBusy('')
    }
  }

  return (
    <div
      className="notifications-root"
      ref={containerRef}
    >
      <button
        type="button"
        className={
          aberto
            ? 'notification-bell active'
            : 'notification-bell'
        }
        aria-label={
          naoLidas > 0
            ? `${naoLidas} notificações não lidas`
            : 'Notificações'
        }
        aria-expanded={aberto}
        onClick={abrirOuFechar}
      >
        <span
          className="notification-bell-icon"
          aria-hidden="true"
        >
          ♢
        </span>

        {naoLidas > 0 && (
          <span className="notification-count">
            {naoLidas > 99
              ? '99+'
              : naoLidas}
          </span>
        )}
      </button>

      {aberto && (
        <section
          className="notifications-popover"
          aria-label="Central de notificações"
        >
          <header className="notifications-header">
            <div>
              <span className="eyebrow">
                Central
              </span>

              <h2>
                Notificações
              </h2>
            </div>

            <button
              type="button"
              className="notifications-read-all"
              disabled={
                naoLidas === 0
                || busy === 'read-all'
              }
              onClick={marcarTodas}
            >
              {busy === 'read-all'
                ? 'Aguarde...'
                : 'Marcar todas como lidas'}
            </button>
          </header>

          {error && (
            <div className="notifications-error">
              {error}
            </div>
          )}

          <div className="notifications-body">
            {loading ? (
              <div className="notifications-empty">
                Carregando notificações...
              </div>
            ) : visiveis.length === 0 ? (
              <div className="notifications-empty">
                <strong>
                  Tudo em dia.
                </strong>

                <span>
                  Nenhuma notificação encontrada.
                </span>
              </div>
            ) : (
              visiveis.map(
                (notificacao) => (
                  <button
                    type="button"
                    key={notificacao.id}
                    className={
                      notificacao.lida
                        ? 'notification-item read'
                        : 'notification-item unread'
                    }
                    disabled={
                      busy
                      === `read:${notificacao.id}`
                    }
                    onClick={() =>
                      abrirNotificacao(
                        notificacao,
                      )
                    }
                  >
                    <span
                      className={
                        `notification-type-icon ${classeTipo(
                          notificacao.tipo,
                        )}`
                      }
                      aria-hidden="true"
                    >
                      {iconeTipo(
                        notificacao.tipo,
                      )}
                    </span>

                    <span className="notification-content">
                      <span className="notification-title-row">
                        <strong>
                          {notificacao.titulo}
                        </strong>

                        {!notificacao.lida && (
                          <span
                            className="notification-unread-dot"
                            title="Não lida"
                          />
                        )}
                      </span>

                      <span className="notification-message">
                        {notificacao.mensagem}
                      </span>

                      <span className="notification-time">
                        {formatarMomento(
                          notificacao.criado_em,
                        )}
                      </span>
                    </span>
                  </button>
                ),
              )
            )}
          </div>

          <footer className="notifications-footer">
            <span>
              Exibindo até 20 notificações recentes
            </span>

            <button
              type="button"
              onClick={
                carregarLista
              }
            >
              Atualizar
            </button>
          </footer>
        </section>
      )}
    </div>
  )
}

function extrairListaNotificacoes(
  response,
) {
  const lista =
    response?.dados?.notificacoes
    ?? response?.dados
    ?? response?.notificacoes
    ?? []

  return Array.isArray(lista)
    ? lista
    : []
}

function extrairQuantidadeNaoLidas(
  response,
) {
  const candidatos = [
    response
      ?.dados
      ?.nao_lidas,

    response
      ?.dados
      ?.total_nao_lidas,

    response
      ?.dados
      ?.naoLidas,

    response
      ?.nao_lidas,

    response
      ?.total_nao_lidas,
  ]

  for (
    const valor
    of candidatos
  ) {
    const numero =
      Number(valor)

    if (
      Number.isFinite(numero)
      && numero >= 0
    ) {
      return numero
    }
  }

  return 0
}

function normalizarNotificacao(
  item,
) {
  return {
    id:
      Number(item?.id)
      || 0,

    tipo:
      item?.tipo
      || 'GERAL',

    titulo:
      item?.titulo
      || 'Notificação',

    mensagem:
      item?.mensagem
      || '',

    url_acao:
      item?.url_acao
      || null,

    lida:
      normalizarLida(
        item,
      ),

    criado_em:
      item?.criado_em
      ?? item?.data
      ?? null,
  }
}

function normalizarLida(
  item,
) {
  if (
    item?.lida === true
    || item?.lida === 1
    || item?.lida === '1'
  ) {
    return true
  }

  return false
}

function navegarParaAcao(
  url,
  navigate,
) {
  if (!url) {
    return
  }

  if (
    /^https?:\/\//i.test(
      url,
    )
  ) {
    window.location.href =
      url
    return
  }

  navigate(url)
}

function classeTipo(
  tipo,
) {
  const valor =
    String(tipo || '')
      .toUpperCase()

  if (
    valor.includes(
      'CANCEL',
    )
  ) {
    return 'danger'
  }

  if (
    valor.includes(
      'CADASTRO',
    )
  ) {
    return 'registration'
  }

  if (
    valor.includes(
      'ALTER',
    )
  ) {
    return 'warning'
  }

  if (
    valor.includes(
      'COMPROMISSO',
    )
    || valor.includes(
      'ESCALA',
    )
  ) {
    return 'personal'
  }

  return 'general'
}

function iconeTipo(
  tipo,
) {
  const classe =
    classeTipo(tipo)

  const mapa = {
    danger: '×',
    warning: '↻',
    registration: '+',
    personal: '✓',
    general: '•',
  }

  return mapa[classe]
}

function formatarMomento(
  valor,
) {
  if (!valor) {
    return ''
  }

  const data =
    new Date(
      String(valor)
        .replace(' ', 'T'),
    )

  if (
    Number.isNaN(
      data.getTime(),
    )
  ) {
    return String(valor)
  }

  const agora =
    new Date()

  const diferencaMs =
    agora.getTime()
    - data.getTime()

  const minutos =
    Math.floor(
      diferencaMs
      / 60000,
    )

  if (
    minutos >= 0
    && minutos < 1
  ) {
    return 'Agora'
  }

  if (
    minutos >= 1
    && minutos < 60
  ) {
    return `Há ${minutos} min`
  }

  const horas =
    Math.floor(
      minutos / 60,
    )

  if (
    horas >= 1
    && horas < 24
  ) {
    return `Há ${horas} h`
  }

  return data.toLocaleString(
    'pt-BR',
    {
      dateStyle: 'short',
      timeStyle: 'short',
    },
  )
}
