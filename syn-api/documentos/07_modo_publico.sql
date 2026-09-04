-- ============================================================
-- SYN - ETAPA 72
-- MODO PÚBLICO / PROGRAMAÇÕES PÚBLICAS
-- ============================================================
--
-- Objetivo:
-- permitir que determinadas programações sejam exibidas
-- sem login, preservando como INTERNAS todas as programações
-- existentes e futuras por padrão.
--
-- IMPORTANTE:
-- esta migration NÃO publica nenhuma programação automaticamente.
-- Isso evita exposição acidental de informações internas.
-- ============================================================

USE syn;

ALTER TABLE programacoes
    ADD COLUMN IF NOT EXISTS descricao_publica TEXT NULL
        AFTER descricao,
    ADD COLUMN IF NOT EXISTS visibilidade
        ENUM('INTERNA', 'PUBLICA')
        NOT NULL
        DEFAULT 'INTERNA'
        AFTER permite_resposta;

ALTER TABLE programacoes
    ADD INDEX IF NOT EXISTS
        idx_programacoes_visibilidade_status_inicio
        (visibilidade, status, inicio_em);

-- ============================================================
-- TESTE OPCIONAL EM DESENVOLVIMENTO
-- ============================================================
-- Execute MANUALMENTE apenas se quiser publicar uma programação
-- para testar a nova API.
--
-- Troque o ID conforme necessário.
--
-- UPDATE programacoes
-- SET
--     visibilidade = 'PUBLICA',
--     descricao_publica =
--         'Programação aberta à comunidade.'
-- WHERE id = 1;
--
-- Para voltar a ser interna:
--
-- UPDATE programacoes
-- SET visibilidade = 'INTERNA'
-- WHERE id = 1;
-- ============================================================
