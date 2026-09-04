import {
  useCallback,
  useEffect,
  useMemo,
  useState,
} from 'react'

import {
  aprovarCadastro,
  getCadastros,
  rejeitarCadastro,
} from '../api/api'

import {
  useAuth,
} from '../contexts/AuthContext'

import './CadastrosPendentesPage.css'

const FILTROS = [
  {
    codigo:
      'AGUARDANDO_EMAIL',
    rotulo:
      'Aguardando e-mail',
  },
  {
    codigo:
      'PENDENTE',
    rotulo:
      'Pendentes',
  },
  {
    codigo:
      'APROVADO',
    rotulo:
      'Aprovados',
  },
  {
    codigo:
      'REJEITADO',
    rotulo:
      'Rejeitados',
  },
  {
    codigo:
      'EXPIRADO',
    rotulo:
      'Expirados',
  },
]

export default function CadastrosPendentesPage() {
  const {
    bootstrap,
  } = useAuth()

  const [
    filtro,
    setFiltro,
  ] =
    useState(
      'PENDENTE',
    )

  const [
    cadastros,
    setCadastros,
  ] =
    useState([])

  const [loading, setLoading] =
    useState(true)

  const [busy, setBusy] =
    useState('')

  const [error, setError] =
    useState('')

  const [success, setSuccess] =
    useState('')

  const podeAprovar =
    Boolean(
      bootstrap
        ?.capacidades
        ?.aprovar_cadastros,
    )

  const carregar =
    useCallback(
      async () => {
        setLoading(true)
        setError('')

        try {
          const response =
            await getCadastros(
              filtro,
            )

          const lista =
            response
              ?.dados
              ?.cadastros

          setCadastros(
            Array.isArray(lista)
              ? lista
              : [],
          )
        } catch (err) {
          setError(
            err?.message
            || 'Não foi possível carregar as solicitações.',
          )
        } finally {
          setLoading(false)
        }
      },
      [filtro],
    )

  useEffect(() => {
    if (podeAprovar) {
      carregar()
    } else {
      setLoading(false)
    }
  }, [
    carregar,
    podeAprovar,
  ])

  const resumo =
    useMemo(
      () => ({
        total:
          cadastros.length,
        aguardandoMaisTempo:
          filtro
            === 'PENDENTE'
          && cadastros.length > 0
            ? Math.max(
                ...cadastros.map(
                  (item) =>
                    Number(
                      item
                        ?.dias_aguardando
                      ?? 0,
                    ),
                ),
              )
            : 0,
      }),
      [
        cadastros,
        filtro,
      ],
    )

  async function aprovar(
    item,
  ) {
    const confirmou =
      window.confirm(
        `Aprovar o cadastro de ${item.nome}? O SYN criará um usuário com papel MEMBRO e liberará o login.`,
      )

    if (!confirmou) {
      return
    }

    const chave =
      `aprovar:${item.id}`

    setBusy(chave)
    setError('')
    setSuccess('')

    try {
      const response =
        await aprovarCadastro(
          item.id,
        )

      setSuccess(
        response
          ?.dados
          ?.mensagem
        || `Cadastro de ${item.nome} aprovado.`,
      )

      await carregar()
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível aprovar o cadastro.',
      )
    } finally {
      setBusy('')
    }
  }

  async function rejeitar(
    item,
  ) {
    const motivo =
      window.prompt(
        `Motivo da rejeição de ${item.nome} (opcional):`,
        '',
      )

    if (motivo === null) {
      return
    }

    const confirmou =
      window.confirm(
        'Confirmar a rejeição desta solicitação?',
      )

    if (!confirmou) {
      return
    }

    const chave =
      `rejeitar:${item.id}`

    setBusy(chave)
    setError('')
    setSuccess('')

    try {
      const response =
        await rejeitarCadastro(
          item.id,
          motivo.trim()
          || null,
        )

      setSuccess(
        response
          ?.dados
          ?.mensagem
        || `Cadastro de ${item.nome} rejeitado.`,
      )

      await carregar()
    } catch (err) {
      setError(
        err?.message
        || 'Não foi possível rejeitar o cadastro.',
      )
    } finally {
      setBusy('')
    }
  }

  if (!podeAprovar) {
    return (
      <section className="cadastros81-page">
        <div className="cadastros81-no-access">
          <span>
            Cadastros
          </span>

          <h1>
            Acesso não autorizado
          </h1>

          <p>
            Esta área é exclusiva de Administradores
            e Organizadores que receberam a permissão
            especial “Aprovar cadastros”.
          </p>
        </div>
      </section>
    )
  }

  return (
    <section className="cadastros81-page">
      <header className="cadastros81-heading">
        <div>
          <span className="eyebrow">
            Gestão de acesso
          </span>

          <h1>
            Cadastros
          </h1>

          <p>
            Analise quem solicitou acesso ao SYN antes
            que a pessoa seja criada como Membro.
          </p>
        </div>

        <div className="cadastros81-summary">
          <strong>
            {resumo.total}
          </strong>

          <span>
            {filtro === 'AGUARDANDO_EMAIL'
              ? 'aguardando confirmação'
              : filtro === 'PENDENTE'
                ? 'aguardando análise'
                : filtro === 'APROVADO'
                  ? 'aprovados'
                  : filtro === 'REJEITADO'
                    ? 'rejeitados'
                    : 'expirados'}
          </span>
        </div>
      </header>

      <nav className="cadastros81-filters">
        {FILTROS.map(
          (item) => (
            <button
              key={item.codigo}
              type="button"
              className={
                filtro
                  === item.codigo
                  ? 'active'
                  : ''
              }
              onClick={() => {
                setFiltro(
                  item.codigo,
                )
                setSuccess('')
              }}
            >
              {item.rotulo}
            </button>
          ),
        )}
      </nav>

      {filtro === 'PENDENTE'
        && resumo
          .aguardandoMaisTempo > 0 && (
        <div className="cadastros81-attention">
          A solicitação mais antiga está aguardando há {
            resumo
              .aguardandoMaisTempo
          } {
            resumo
              .aguardandoMaisTempo === 1
              ? 'dia'
              : 'dias'
          }.
        </div>
      )}

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
        <div className="cadastros81-loading">
          Carregando cadastros...
        </div>
      ) : cadastros.length === 0 ? (
        <div className="cadastros81-empty">
          <strong>
            Nenhum cadastro nesta situação.
          </strong>

          <span>
            {filtro === 'AGUARDANDO_EMAIL'
              ? 'Não existem solicitações aguardando confirmação do e-mail.'
              : filtro === 'PENDENTE'
                ? 'Não existem solicitações aguardando aprovação.'
                : filtro === 'EXPIRADO'
                  ? 'Nenhuma solicitação de confirmação expirada.'
                  : 'Nenhum registro encontrado para este filtro.'}
          </span>
        </div>
      ) : (
        <div className="cadastros81-list">
          {cadastros.map(
            (item) => (
              <CadastroCard
                key={item.id}
                item={item}
                filtro={filtro}
                busy={busy}
                onApprove={() =>
                  aprovar(item)
                }
                onReject={() =>
                  rejeitar(item)
                }
              />
            ),
          )}
        </div>
      )}
    </section>
  )
}

function CadastroCard({
  item,
  filtro,
  busy,
  onApprove,
  onReject,
}) {
  const isBusy =
    Boolean(
      busy,
    )

  return (
    <article className="cadastros81-card">
      <div className="cadastros81-person">
        <div className="cadastros81-avatar">
          {iniciais(
            item.nome,
          )}
        </div>

        <div>
          <span>
            Solicitação #{item.id}
          </span>

          <h2>
            {item.nome}
          </h2>

          <p>
            {item.email}
          </p>
        </div>
      </div>

      <div className="cadastros81-details">
        <Detail
          label="Telefone"
          value={
            item.telefone
            || 'Não informado'
          }
        />

        <Detail
          label="Nascimento"
          value={
            formatarData(
              item.data_nascimento,
            )
          }
        />

        <Detail
          label="Solicitado"
          value={
            formatarDataHora(
              item.solicitado_em,
            )
          }
        />

        <Detail
          label="Tentativa"
          value={
            String(
              item.tentativas
              || 1,
            )
          }
        />

        <Detail
          label="E-mail"
          value={
            item.email_confirmado_em
              ? 'Confirmado'
              : 'Não confirmado'
          }
        />
      </div>

      {filtro === 'AGUARDANDO_EMAIL' && (
        <div className="cadastros84-email-waiting">
          <strong>
            Aguardando o solicitante
          </strong>

          <span>
            Esta solicitação ainda não pode ser aprovada.
            O endereço de e-mail precisa ser confirmado primeiro.
          </span>
        </div>
      )}

      {filtro === 'EXPIRADO' && (
        <div className="cadastros89-expired">
          <strong>
            Confirmação expirada
          </strong>

          <span>
            O endereço não foi confirmado dentro do prazo.
            Nenhuma conta foi criada. A pessoa pode fazer
            uma nova solicitação usando o mesmo e-mail.
          </span>
        </div>
      )}

      {item.motivo_rejeicao && (
        <div className="cadastros81-reason">
          <strong>
            Motivo da rejeição
          </strong>

          <span>
            {item.motivo_rejeicao}
          </span>
        </div>
      )}

      {item.analisado_por && (
        <div className="cadastros81-reviewed">
          Analisado por {
            item.analisado_por
          } em {
            formatarDataHora(
              item.analisado_em,
            )
          }.
        </div>
      )}

      {filtro === 'PENDENTE' && (
        <footer>
          <div>
            <strong>
              Se aprovado
            </strong>

            <span>
              O usuário será criado automaticamente
              com papel MEMBRO.
            </span>
          </div>

          <div className="cadastros81-actions">
            <button
              type="button"
              className="reject"
              disabled={isBusy}
              onClick={onReject}
            >
              {busy
                === `rejeitar:${item.id}`
                ? 'Rejeitando...'
                : 'Rejeitar'}
            </button>

            <button
              type="button"
              className="approve"
              disabled={isBusy}
              onClick={onApprove}
            >
              {busy
                === `aprovar:${item.id}`
                ? 'Aprovando...'
                : 'Aprovar cadastro'}
            </button>
          </div>
        </footer>
      )}

      {filtro === 'APROVADO'
        && item.usuario_criado_id && (
        <div className="cadastros81-created">
          Usuário criado: #{
            item.usuario_criado_id
          }
        </div>
      )}
    </article>
  )
}

function Detail({
  label,
  value,
}) {
  return (
    <div>
      <span>
        {label}
      </span>

      <strong>
        {value}
      </strong>
    </div>
  )
}

function iniciais(
  nome,
) {
  return String(
    nome
    || '?',
  )
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map(
      (item) =>
        item[0]
        || '',
    )
    .join('')
    .toUpperCase()
}

function formatarData(
  valor,
) {
  if (!valor) {
    return 'Não informada'
  }

  const [
    ano,
    mes,
    dia,
  ] =
    String(valor)
      .slice(0, 10)
      .split('-')
      .map(Number)

  return new Date(
    ano,
    mes - 1,
    dia,
    12,
    0,
    0,
  )
    .toLocaleDateString(
      'pt-BR',
    )
}

function formatarDataHora(
  valor,
) {
  if (!valor) {
    return '—'
  }

  const data =
    new Date(
      String(valor)
        .replace(
          ' ',
          'T',
        ),
    )

  if (
    Number.isNaN(
      data.getTime(),
    )
  ) {
    return String(valor)
  }

  return data
    .toLocaleString(
      'pt-BR',
      {
        dateStyle:
          'short',
        timeStyle:
          'short',
      },
    )
}
