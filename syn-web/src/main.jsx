import React from 'react'
import ReactDOM from 'react-dom/client'

import {
  BrowserRouter,
} from 'react-router-dom'

import App from './App'
import {
  AuthProvider,
} from './contexts/AuthContext'

import {
  registrarServiceWorker,
} from './pwa/registerServiceWorker'

import './styles.css'
import './styles_compacto.css'
import './styles_mobile_identidade.css'
import './styles_mobile_etapa100.css'
import './styles_mobile_etapa102.css'
import './styles_mobile_etapa103.css'
import './styles_mobile_etapa107.css'

ReactDOM
  .createRoot(
    document.getElementById(
      'root',
    ),
  )
  .render(
    <React.StrictMode>
      <BrowserRouter>
        <AuthProvider>
          <App />
        </AuthProvider>
      </BrowserRouter>
    </React.StrictMode>,
  )

registrarServiceWorker()
