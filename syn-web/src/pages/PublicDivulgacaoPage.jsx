import {
  useEffect,
  useMemo,
  useState,
} from 'react'

import {
  useNavigate,
  useSearchParams,
} from 'react-router-dom'

import QRCode
  from 'qrcode'

import {
  getIgrejaPublica,
  getMapaSemanaPublico,
  resolveApiAssetUrl,
} from '../api/api'

import './PublicDivulgacaoPage.css'

export default function PublicDivulgacaoPage() {
  const navigate =
    useNavigate()

  const [
    searchParams,
  ] =
    useSearchParams()

  const referenciaInicial =
    dataISOValida(
      searchParams.get(
        'data_referencia',
      ),
    )
    || hojeISO()

  const [igreja, setIgreja] =
    useState(null)

  const [mapa, setMapa] =
    useState(null)

  const [
    modoLink,
    setModoLink,
  ] =
    useState('SEMANA')

  const [qrDataUrl, setQrDataUrl] =
    useState('')

  const [loading, setLoading] =
    useState(true)

  const [error, setError] =
    useState('')

  const [copiado, setCopiado] =
    useState(false)

  useEffect(() => {
    let ativo = true

    async function carregar() {
      setLoading(true)
      setError('')

      try {
        const [
          igrejaResponse,
          mapaResponse,
        ] =
          await Promise.all([
            getIgrejaPublica(),
            getMapaSemanaPublico(
              referenciaInicial,
            ),
          ])

        if (!ativo) {
          return
        }

        setIgreja(
          igrejaResponse?.dados
          ?? null,
        )

        setMapa(
          mapaResponse?.dados
          ?? null,
        )
      } catch (err) {
        if (ativo) {
          setError(
            err?.message
            || 'Não foi possível preparar a divulgação pública.',
          )
        }
      } finally {
        if (ativo) {
          setLoading(false)
        }
      }
    }

    carregar()

    return () => {
      ativo = false
    }
  }, [referenciaInicial])

  const urlPublica =
    useMemo(
      () => {
        const origem =
          window.location.origin

        if (
          modoLink === 'GERAL'
          || !mapa?.semana?.inicio
        ) {
          return `${origem}/publico`
        }

        return `${origem}/publico?data_referencia=${mapa.semana.inicio}`
      },
      [
        modoLink,
        mapa,
      ],
    )

  useEffect(() => {
    let ativo = true

    async function gerar() {
      try {
        const imagem =
          await QRCode.toDataURL(
            urlPublica,
            {
              width: 360,
              margin: 2,
              errorCorrectionLevel: 'M',
            },
          )

        if (ativo) {
          setQrDataUrl(
            imagem,
          )
        }
      } catch {
        if (ativo) {
          setQrDataUrl('')
          setError(
            'Não foi possível gerar o QR Code.',
          )
        }
      }
    }

    gerar()

    return () => {
      ativo = false
    }
  }, [urlPublica])

  const logo =
    resolveApiAssetUrl(
      igreja?.logotipo,
    )

  const numeroSemana =
    mapa
      ?.semana
      ?.numero_iso
    ?? null

  const periodo =
    mapa?.semana?.inicio
    && mapa?.semana?.fim
      ? `${
          formatarDataCurta(
            mapa.semana.inicio,
          )
        } — ${
          formatarDataCurta(
            mapa.semana.fim,
          )
        }`
      : '—'

  async function compartilhar() {
    const titulo =
      igreja?.nome
      ? `Programação — ${igreja.nome}`
      : 'Programação da igreja'

    const texto =
      modoLink === 'SEMANA'
      && numeroSemana
        ? `${titulo} — Semana ${numeroSemana}`
        : titulo

    if (navigator.share) {
      try {
        await navigator.share({
          title:
            titulo,
          text:
            texto,
          url:
            urlPublica,
        })

        return
      } catch (err) {
        if (
          err?.name
          === 'AbortError'
        ) {
          return
        }
      }
    }

    await copiarLink()
  }

  async function copiarLink() {
    try {
      await navigator
        .clipboard
        .writeText(
          urlPublica,
        )

      setCopiado(
        true,
      )

      window.setTimeout(
        () =>
          setCopiado(
            false,
          ),
        1800,
      )
    } catch {
      window.prompt(
        'Copie o link público:',
        urlPublica,
      )
    }
  }

  function imprimir() {
    window.print()
  }

  return (
    <main className="public77-page">
      <header className="public77-topbar">
        <button
          type="button"
          onClick={() =>
            navigate(
              mapa?.semana?.inicio
                ? `/publico?data_referencia=${mapa.semana.inicio}`
                : '/publico',
            )
          }
        >
          ← Voltar ao mapa
        </button>

        <strong>
          Divulgação pública
        </strong>
      </header>

      {error && (
        <div className="public77-error">
          {error}
        </div>
      )}

      {loading ? (
        <section className="public77-loading">
          Preparando divulgação...
        </section>
      ) : (
        <section className="public77-layout">
          <div className="public77-editor">
            <span className="public77-eyebrow">
              Compartilhar o SYN
            </span>

            <h1>
              Leve a programação pública
              para cartazes, grupos e redes.
            </h1>

            <p>
              O QR Code é gerado dentro do próprio navegador.
              Nenhum serviço externo recebe o endereço da igreja.
            </p>

            <div className="public77-mode">
              <button
                type="button"
                className={
                  modoLink === 'SEMANA'
                    ? 'active'
                    : ''
                }
                onClick={() =>
                  setModoLink(
                    'SEMANA',
                  )
                }
              >
                <strong>
                  Esta semana
                </strong>

                <span>
                  {numeroSemana
                    ? `Semana ${numeroSemana}`
                    : 'Semana atual'}
                </span>
              </button>

              <button
                type="button"
                className={
                  modoLink === 'GERAL'
                    ? 'active'
                    : ''
                }
                onClick={() =>
                  setModoLink(
                    'GERAL',
                  )
                }
              >
                <strong>
                  Mapa público
                </strong>

                <span>
                  Sempre abre a programação pública
                </span>
              </button>
            </div>

            <div className="public77-link-box">
              <span>
                Link que será divulgado
              </span>

              <strong>
                {urlPublica}
              </strong>
            </div>

            <div className="public77-actions">
              <button
                type="button"
                className="primary"
                onClick={
                  compartilhar
                }
              >
                Compartilhar
              </button>

              <button
                type="button"
                onClick={
                  copiarLink
                }
              >
                {copiado
                  ? 'Link copiado'
                  : 'Copiar link'}
              </button>

              <button
                type="button"
                onClick={
                  imprimir
                }
              >
                Imprimir cartão
              </button>
            </div>

            <div className="public77-tip">
              <strong>
                Onde usar
              </strong>

              <span>
                Boletim da igreja, recepção, mural,
                projeção, grupos de WhatsApp, redes
                sociais ou material impresso.
              </span>
            </div>
          </div>

          <article className="public77-card">
            <div className="public77-card-brand">
              {logo ? (
                <img
                  src={logo}
                  alt=""
                />
              ) : (
                <span>
                  SYN
                </span>
              )}

              <div>
                <small>
                  Programação pública
                </small>

                <strong>
                  {igreja?.nome
                    || 'Igreja'}
                </strong>
              </div>
            </div>

            <div className="public77-card-copy">
              <span>
                Aponte a câmera do celular
              </span>

              <h2>
                Veja a programação
                da igreja
              </h2>

              {modoLink === 'SEMANA' ? (
                <p>
                  {numeroSemana
                    ? `Semana ${numeroSemana}`
                    : 'Semana atual'}
                  {' · '}
                  {periodo}
                </p>
              ) : (
                <p>
                  Acesse o Mapa Público do SYN
                </p>
              )}
            </div>

            <div className="public77-qr-shell">
              {qrDataUrl ? (
                <img
                  src={qrDataUrl}
                  alt="QR Code da programação pública"
                />
              ) : (
                <span>
                  Gerando QR Code...
                </span>
              )}
            </div>

            <div className="public77-card-footer">
              <strong>
                Não é necessário fazer login
              </strong>

              <span>
                Somente programações marcadas
                como públicas são exibidas.
              </span>
            </div>
          </article>
        </section>
      )}
    </main>
  )
}

function dataISOValida(
  valor,
) {
  if (
    !valor
    || !/^\d{4}-\d{2}-\d{2}$/.test(
      String(valor),
    )
  ) {
    return null
  }

  const [
    ano,
    mes,
    dia,
  ] =
    String(valor)
      .split('-')
      .map(Number)

  const data =
    new Date(
      ano,
      mes - 1,
      dia,
      12,
      0,
      0,
    )

  if (
    data.getFullYear() !== ano
    || data.getMonth()
      !== mes - 1
    || data.getDate()
      !== dia
  ) {
    return null
  }

  return String(valor)
}

function hojeISO() {
  const agora =
    new Date()

  const ano =
    agora.getFullYear()

  const mes =
    String(
      agora.getMonth() + 1,
    ).padStart(2, '0')

  const dia =
    String(
      agora.getDate(),
    ).padStart(2, '0')

  return `${ano}-${mes}-${dia}`
}

function formatarDataCurta(
  iso,
) {
  const [
    ano,
    mes,
    dia,
  ] =
    String(iso)
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
      {
        day: '2-digit',
        month: 'short',
      },
    )
    .replace('.', '')
}
