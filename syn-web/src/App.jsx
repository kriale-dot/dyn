import {
  Navigate,
  Route,
  Routes,
} from 'react-router-dom'

import AppShell
  from './components/AppShell'

import ProtectedRoute
  from './components/ProtectedRoute'

import EstruturaPage
  from './pages/EstruturaPage'

import GestaoEscalaPage
  from './pages/GestaoEscalaPage'

import GestaoProgramacoesPage
  from './pages/GestaoProgramacoesPage'

import HomePage
  from './pages/HomePage'

import LoginPage
  from './pages/LoginPage'

import NecessidadesPage
  from './pages/NecessidadesPage'

import PerfilPage
  from './pages/PerfilPage'

import ProgramacaoDetalhePage
  from './pages/ProgramacaoDetalhePage'

import ProgramacoesPage
  from './pages/ProgramacoesPage'

import SemanaPage
  from './pages/SemanaPage'

import TipoFuncoesPage
  from './pages/TipoFuncoesPage'

import UsuariosPage
  from './pages/UsuariosPage'

import PlaceholderPage
  from './pages/PlaceholderPage'

export default function App() {
  return (
    <Routes>
      <Route
        path="/login"
        element={<LoginPage />}
      />

      <Route
        element={
          <ProtectedRoute>
            <AppShell />
          </ProtectedRoute>
        }
      >
        <Route
          index
          element={<HomePage />}
        />

        <Route
          path="semana"
          element={<SemanaPage />}
        />

        <Route
          path="programacoes"
          element={<ProgramacoesPage />}
        />

        <Route
          path="programacoes/:id"
          element={
            <ProgramacaoDetalhePage />
          }
        />

        <Route
          path="gestao/programacoes"
          element={
            <GestaoProgramacoesPage />
          }
        />

        <Route
          path="gestao/programacoes/:id/escala"
          element={
            <GestaoEscalaPage />
          }
        />

        <Route
          path="admin/usuarios"
          element={<UsuariosPage />}
        />

        <Route
          path="admin/estrutura"
          element={<EstruturaPage />}
        />

        <Route
          path="admin/estrutura/tipos-programacao/:id/funcoes"
          element={<TipoFuncoesPage />}
        />

        <Route
          path="gestao/necessidades"
          element={<NecessidadesPage />}
        />

        <Route
          path="perfil"
          element={<PerfilPage />}
        />
      </Route>

      <Route
        path="*"
        element={
          <Navigate
            to="/"
            replace
          />
        }
      />
    </Routes>
  )
}
