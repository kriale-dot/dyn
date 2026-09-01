import {
  useLocation,
} from 'react-router-dom'

export default function PlaceholderPage() {
  const location =
    useLocation()

  return (
    <section className="panel">
      <span className="eyebrow">
        Próximas etapas
      </span>

      <h1>
        Tela em desenvolvimento
      </h1>

      <p>
        A rota do frontend já existe:
      </p>

      <code>
        {location.pathname}
      </code>

      <p className="muted">
        Ela será implementada passo a passo
        nas próximas etapas do SYN.
      </p>
    </section>
  )
}
