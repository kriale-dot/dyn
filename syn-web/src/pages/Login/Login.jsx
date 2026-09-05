import {
  useEffect,
  useMemo,
  useState,
} from 'react';

import QRCode from 'qrcode';

import { useAuth } from '../../context/AuthContext.jsx';
import './Login.css';

function montarUrlAplicativo() {
  const configurada =
    import.meta.env.VITE_APP_URL?.trim();

  if (configurada) {
    const base = configurada.replace(/\/+$/, '');

    return base.endsWith('/login')
      ? base
      : `${base}/login`;
  }

  return `${window.location.origin}/login`;
}

function Login() {
  const { login } = useAuth();

  const [email, setEmail] = useState('');
  const [senha, setSenha] = useState('');
  const [erro, setErro] = useState('');
  const [enviando, setEnviando] = useState(false);
  const [qrCode, setQrCode] = useState('');

  const appUrl = useMemo(
    () => montarUrlAplicativo(),
    []
  );

  useEffect(() => {
    let ativo = true;

    QRCode.toDataURL(appUrl, {
      width: 240,
      margin: 1,
      errorCorrectionLevel: 'M',
    })
      .then((dataUrl) => {
        if (ativo) {
          setQrCode(dataUrl);
        }
      })
      .catch(() => {
        if (ativo) {
          setQrCode('');
        }
      });

    return () => {
      ativo = false;
    };
  }, [appUrl]);

  async function handleSubmit(event) {
    event.preventDefault();

    try {
      setEnviando(true);
      setErro('');

      await login(email, senha);
    } catch (error) {
      setErro(
        error.message ||
          'Não foi possível entrar no SYN.'
      );
    } finally {
      setEnviando(false);
    }
  }

  return (
    <main className="login-page">
      <section className="login-card">
        <div className="login-card__access">
          <header className="login-brand">
            <img
              className="login-brand__image"
              src="/images/logo-syn.png"
              alt="SYN"
            />

            <p className="login-brand__greek">
              σύν
            </p>
          </header>

          <div className="login-header">
            <h1>Entrar</h1>

            <p>
              Acesse seus compromissos e a
              programação da sua igreja.
            </p>
          </div>

          <form
            className="login-form"
            onSubmit={handleSubmit}
          >
            <label className="login-field">
              <span>E-mail</span>

              <input
                type="email"
                value={email}
                onChange={(event) =>
                  setEmail(event.target.value)
                }
                placeholder="seu@email.com"
                autoComplete="email"
                disabled={enviando}
                required
              />
            </label>

            <label className="login-field">
              <span>Senha</span>

              <input
                type="password"
                value={senha}
                onChange={(event) =>
                  setSenha(event.target.value)
                }
                placeholder="Digite sua senha"
                autoComplete="current-password"
                disabled={enviando}
                required
              />
            </label>

            {erro ? (
              <div
                className="login-error"
                role="alert"
              >
                {erro}
              </div>
            ) : null}

            <button
              className="login-button"
              type="submit"
              disabled={enviando}
            >
              {enviando
                ? 'Entrando...'
                : 'Entrar'}
            </button>
          </form>
        </div>

        <aside
          className="login-mobile-access"
          aria-label="Acesso pelo celular"
        >
          <div className="login-mobile-access__content">
            <span className="login-mobile-access__eyebrow">
              Acesso pelo celular
            </span>

            <h2>Abra o SYN no celular</h2>

            <p>
              Aponte a câmera do celular para o
              QR Code e abra o aplicativo.
            </p>

            <div className="login-qrcode">
              {qrCode ? (
                <img
                  src={qrCode}
                  alt="QR Code para abrir o SYN no celular"
                />
              ) : (
                <div
                  className="login-qrcode__loading"
                  aria-label="Gerando QR Code"
                >
                  Gerando QR Code…
                </div>
              )}
            </div>

            <span className="login-mobile-access__hint">
              Não é necessário digitar o endereço.
            </span>
          </div>
        </aside>
      </section>
    </main>
  );
}

export default Login;
