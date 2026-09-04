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

import EscalasSemanaPage
  from './pages/EscalasSemanaPage'

import GestaoEscalaPage
  from './pages/GestaoEscalaPage'

import GestaoProgramacoesPage
  from './pages/GestaoProgramacoesPage'

import HomePage
  from './pages/HomePage'

import IgrejaConfigPage
  from './pages/IgrejaConfigPage'

import AuditoriaPage
  from './pages/AuditoriaPage'

import LoginPage
  from './pages/LoginPage'

import CadastroPage
  from './pages/CadastroPage'

import ConfirmarEmailCadastroPage
  from './pages/ConfirmarEmailCadastroPage'

import ConfirmarAlteracaoEmailPage
  from './pages/ConfirmarAlteracaoEmailPage'

import CadastrosPendentesPage
  from './pages/CadastrosPendentesPage'

import EsqueciSenhaPage
  from './pages/EsqueciSenhaPage'

import RedefinirSenhaPage
  from './pages/RedefinirSenhaPage'

import NecessidadesPage
  from './pages/NecessidadesPage'

import PerfilPage
  from './pages/PerfilPage'

import ProgramacaoDetalhePage
  from './pages/ProgramacaoDetalhePage'

import ProgramacaoFormPage
  from './pages/ProgramacaoFormPage'

import ProgramacoesPage
  from './pages/ProgramacoesPage'

import SemanaPage
  from './pages/SemanaPage'

import SeriesProgramacaoPage
  from './pages/SeriesProgramacaoPage'

import SerieProgramacaoFormPage
  from './pages/SerieProgramacaoFormPage'

import SerieProgramacaoDetalhePage
  from './pages/SerieProgramacaoDetalhePage'

import TipoFuncoesPage
  from './pages/TipoFuncoesPage'

import UsuariosPage
  from './pages/UsuariosPage'

import PlaceholderPage
  from './pages/PlaceholderPage'

import PublicHomePage
  from './pages/PublicHomePage'

import PublicProgramacaoDetalhePage
  from './pages/PublicProgramacaoDetalhePage'

import PublicProgramacoesPage
  from './pages/PublicProgramacoesPage'

import PublicDivulgacaoPage
  from './pages/PublicDivulgacaoPage'

import RootEntryPage
  from './pages/RootEntryPage'

export default function App() {
  return (
    <Routes>
      {/*
       * A raiz do SYN agora é uma porta de entrada inteligente:
       *
       * - sem sessão -> Mapa Público;
       * - com sessão -> Home interna.
       */}
      <Route
        path="/"
        element={<RootEntryPage />}
      />

      <Route
        path="/publico"
        element={<PublicHomePage />}
      />

      <Route
        path="/publico/programacoes"
        element={
          <PublicProgramacoesPage />
        }
      />

      <Route
        path="/publico/divulgar"
        element={
          <PublicDivulgacaoPage />
        }
      />

      <Route
        path="/publico/programacoes/:id"
        element={
          <PublicProgramacaoDetalhePage />
        }
      />

      <Route
        path="/login"
        element={<LoginPage />}
      />

      <Route
        path="/cadastro"
        element={<CadastroPage />}
      />

      <Route
        path="/cadastro/confirmar-email"
        element={<ConfirmarEmailCadastroPage />}
      />

      <Route
        path="/conta/confirmar-email"
        element={<ConfirmarAlteracaoEmailPage />}
      />

      <Route
        path="/esqueci-senha"
        element={<EsqueciSenhaPage />}
      />

      <Route
        path="/redefinir-senha"
        element={<RedefinirSenhaPage />}
      />

      <Route
        element={
          <ProtectedRoute>
            <AppShell />
          </ProtectedRoute>
        }
      >
        <Route
          path="inicio"
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
          path="gestao/escalas-semana"
          element={
            <EscalasSemanaPage />
          }
        />

        <Route
          path="gestao/cadastros"
          element={
            <CadastrosPendentesPage />
          }
        />

        <Route
          path="gestao/programacoes/nova"
          element={
            <ProgramacaoFormPage />
          }
        />

        <Route
          path="gestao/programacoes/:id/editar"
          element={
            <ProgramacaoFormPage />
          }
        />

        <Route
          path="gestao/programacoes/:id/escala"
          element={
            <GestaoEscalaPage />
          }
        />

        <Route
          path="gestao/series"
          element={
            <SeriesProgramacaoPage />
          }
        />

        <Route
          path="gestao/series/nova"
          element={
            <SerieProgramacaoFormPage />
          }
        />

        <Route
          path="gestao/series/:id"
          element={
            <SerieProgramacaoDetalhePage />
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
          path="admin/igreja"
          element={<IgrejaConfigPage />}
        />

        <Route
          path="admin/auditoria"
          element={<AuditoriaPage />}
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
