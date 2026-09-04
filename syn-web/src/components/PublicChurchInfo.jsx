import './PublicChurchInfo.css'

export default function PublicChurchInfo({
  igreja,
  compact = false,
}) {
  if (!igreja) {
    return null
  }

  const endereco =
    montarEndereco(
      igreja?.endereco,
    )

  const telefone =
    igreja
      ?.contatos
      ?.telefone
    || ''

  const email =
    igreja
      ?.contatos
      ?.email
    || ''

  const site =
    igreja
      ?.contatos
      ?.site
    || ''

  const possuiInformacao =
    Boolean(
      endereco
      || telefone
      || email
      || site,
    )

  if (!possuiInformacao) {
    return null
  }

  return (
    <section
      className={
        compact
          ? 'public76-church compact'
          : 'public76-church'
      }
    >
      <div className="public76-church-heading">
        <span>
          Informações da igreja
        </span>

        <strong>
          {igreja?.nome
            || 'Igreja'}
        </strong>
      </div>

      <div className="public76-church-grid">
        {endereco && (
          <div className="public76-church-item">
            <span>
              Endereço
            </span>

            <strong>
              {endereco}
            </strong>

            <button
              type="button"
              onClick={() =>
                abrirMapa(
                  endereco,
                )
              }
            >
              Como chegar
            </button>
          </div>
        )}

        {telefone && (
          <div className="public76-church-item">
            <span>
              Telefone
            </span>

            <strong>
              {telefone}
            </strong>

            <a
              href={
                `tel:${somenteTelefone(
                  telefone,
                )}`
              }
            >
              Ligar
            </a>
          </div>
        )}

        {email && (
          <div className="public76-church-item">
            <span>
              E-mail
            </span>

            <strong>
              {email}
            </strong>

            <a
              href={
                `mailto:${email}`
              }
            >
              Enviar e-mail
            </a>
          </div>
        )}

        {site && (
          <div className="public76-church-item">
            <span>
              Site
            </span>

            <strong>
              {limparSite(
                site,
              )}
            </strong>

            <a
              href={
                normalizarSite(
                  site,
                )
              }
              target="_blank"
              rel="noreferrer"
            >
              Abrir site
            </a>
          </div>
        )}
      </div>
    </section>
  )
}

function montarEndereco(
  endereco,
) {
  if (!endereco) {
    return ''
  }

  const linha1 = [
    endereco.logradouro,
    endereco.numero,
  ]
    .filter(Boolean)
    .join(', ')

  const linha2 = [
    endereco.bairro,
    endereco.cidade,
    endereco.estado,
  ]
    .filter(Boolean)
    .join(' · ')

  return [
    linha1,
    linha2,
  ]
    .filter(Boolean)
    .join(' — ')
}

function abrirMapa(
  endereco,
) {
  const url =
    `https://www.google.com/maps/search/?api=1&query=${
      encodeURIComponent(
        endereco,
      )
    }`

  window.open(
    url,
    '_blank',
    'noopener,noreferrer',
  )
}

function somenteTelefone(
  telefone,
) {
  return String(
    telefone,
  ).replace(
    /[^\d+]/g,
    '',
  )
}

function normalizarSite(
  site,
) {
  const valor =
    String(
      site,
    ).trim()

  if (
    /^https?:\/\//i.test(
      valor,
    )
  ) {
    return valor
  }

  return `https://${valor}`
}

function limparSite(
  site,
) {
  return String(
    site,
  )
    .replace(
      /^https?:\/\//i,
      '',
    )
    .replace(
      /\/$/,
      '',
    )
}
