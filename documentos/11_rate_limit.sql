-- ============================================================
-- SYN - ETAPA 83
-- RATE LIMIT DAS ROTAS PÚBLICAS SENSÍVEIS
-- ============================================================
--
-- Objetivo:
--
-- Evitar abuso de:
--
-- - login;
-- - recuperação de senha;
-- - tentativa de redefinição;
-- - cadastro público.
--
-- Privacidade:
--
-- O banco NÃO armazena e-mail nem IP em texto puro nesta tabela.
-- A chave operacional é armazenada somente como SHA-256.
-- ============================================================

USE syn;

CREATE TABLE IF NOT EXISTS limites_requisicao (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    acao VARCHAR(80) NOT NULL,

    -- SHA-256 de uma chave operacional:
    -- ex.: "ip:127.0.0.1"
    -- ou "ip:127.0.0.1|email:alguem@exemplo.com"
    chave_hash CHAR(64) NOT NULL,

    contador INT UNSIGNED NOT NULL DEFAULT 1,

    limite INT UNSIGNED NOT NULL,

    janela_segundos INT UNSIGNED NOT NULL,

    janela_iniciada_em DATETIME NOT NULL,

    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    atualizado_em TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_limites_requisicao_acao_chave (
        acao,
        chave_hash
    ),

    KEY idx_limites_requisicao_atualizado (
        atualizado_em
    )

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
