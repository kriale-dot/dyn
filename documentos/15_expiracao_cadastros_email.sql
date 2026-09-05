-- ============================================================
-- SYN - ETAPA 89
-- EXPIRAÇÃO DE CADASTROS QUE NÃO CONFIRMARAM O E-MAIL
-- ============================================================
--
-- Problema:
--
-- A Etapa 84 criou o estado AGUARDANDO_EMAIL com token válido por
-- 24 horas. Porém, depois do vencimento, a solicitação continuava
-- indefinidamente com esse status.
--
-- Solução:
--
-- AGUARDANDO_EMAIL + prazo vencido
--              ↓
--          EXPIRADO
--
-- A pessoa poderá preencher o cadastro novamente com o mesmo e-mail.
-- O histórico antigo continua preservado no mesmo registro.
-- ============================================================

USE syn;

ALTER TABLE solicitacoes_cadastro
    MODIFY COLUMN status ENUM(
        'AGUARDANDO_EMAIL',
        'PENDENTE',
        'APROVADO',
        'REJEITADO',
        'EXPIRADO'
    ) NOT NULL DEFAULT 'AGUARDANDO_EMAIL';

-- Corrige imediatamente solicitações antigas cujo link já venceu.
UPDATE solicitacoes_cadastro
SET
    status = 'EXPIRADO',
    email_confirmacao_token_hash = NULL
WHERE status = 'AGUARDANDO_EMAIL'
  AND email_confirmacao_expira_em IS NOT NULL
  AND email_confirmacao_expira_em < NOW();

SELECT
    id,
    nome,
    email,
    status,
    solicitado_em,
    email_confirmacao_expira_em
FROM solicitacoes_cadastro
ORDER BY id DESC
LIMIT 30;
