-- ============================================================
-- SYN - ETAPA 84
-- CONFIRMAÇÃO DO E-MAIL ANTES DA APROVAÇÃO DO CADASTRO
-- ============================================================
--
-- Novo fluxo:
--
-- cadastro público
--     ↓
-- AGUARDANDO_EMAIL
--     ↓
-- usuário confirma pelo link recebido
--     ↓
-- PENDENTE
--     ↓
-- Administrador/Organizador autorizado analisa
--     ↓
-- APROVADO ou REJEITADO
--
-- O token nunca é armazenado em texto puro.
-- O banco guarda somente SHA-256(token).
-- ============================================================

USE syn;

ALTER TABLE solicitacoes_cadastro
    MODIFY COLUMN status ENUM(
        'AGUARDANDO_EMAIL',
        'PENDENTE',
        'APROVADO',
        'REJEITADO'
    ) NOT NULL DEFAULT 'AGUARDANDO_EMAIL';

ALTER TABLE solicitacoes_cadastro
    ADD COLUMN IF NOT EXISTS email_confirmacao_token_hash CHAR(64) NULL
        AFTER senha_hash,
    ADD COLUMN IF NOT EXISTS email_confirmacao_expira_em DATETIME NULL
        AFTER email_confirmacao_token_hash,
    ADD COLUMN IF NOT EXISTS email_confirmado_em DATETIME NULL
        AFTER email_confirmacao_expira_em;

CREATE INDEX IF NOT EXISTS idx_solicitacoes_email_confirmacao_token
    ON solicitacoes_cadastro (
        email_confirmacao_token_hash
    );

CREATE INDEX IF NOT EXISTS idx_solicitacoes_email_confirmacao_expira
    ON solicitacoes_cadastro (
        email_confirmacao_expira_em
    );

-- Solicitações criadas antes desta etapa já passaram pelo fluxo anterior.
-- Marcamos essas linhas como "e-mail confirmado por legado" para não
-- invalidar cadastros já existentes.
UPDATE solicitacoes_cadastro
SET email_confirmado_em =
    COALESCE(
        email_confirmado_em,
        solicitado_em
    )
WHERE status IN (
    'PENDENTE',
    'APROVADO',
    'REJEITADO'
)
  AND email_confirmado_em IS NULL;

SELECT
    id,
    nome,
    email,
    status,
    email_confirmado_em
FROM solicitacoes_cadastro
ORDER BY id DESC
LIMIT 20;
