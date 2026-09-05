-- ============================================================
-- SYN - ETAPA 85
-- REVOGAÇÃO DE SESSÕES JWT
-- ============================================================
--
-- Problema resolvido:
--
-- Antes desta etapa, um JWT já emitido continuava válido até expirar,
-- mesmo depois de uma redefinição de senha.
--
-- A partir de agora cada usuário possui uma "versão de sessão".
--
-- O JWT recebe:
--
--     sv = sessao_versao
--
-- Em cada requisição autenticada o backend compara:
--
--     token.sv === usuarios.sessao_versao
--
-- Sempre que for necessário invalidar todas as sessões, a versão é
-- incrementada. Todos os tokens antigos deixam de funcionar
-- imediatamente.
-- ============================================================

USE syn;

ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS sessao_versao
        INT UNSIGNED NOT NULL DEFAULT 1
        AFTER ultimo_login_em;

-- Garante valor válido em instalações que possam ter sido alteradas
-- manualmente durante o desenvolvimento.
UPDATE usuarios
SET sessao_versao = 1
WHERE sessao_versao IS NULL
   OR sessao_versao < 1;

SELECT
    id,
    nome,
    email,
    status,
    sessao_versao
FROM usuarios
ORDER BY id;
