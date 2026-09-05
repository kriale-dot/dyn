-- ============================================================
-- SYN - MIGRAÇÃO 06
-- Recuperação segura de senha
-- ============================================================
--
-- A API nunca grava o token puro no banco.
--
-- Fluxo:
-- 1. gera token criptograficamente seguro;
-- 2. envia/entrega o token ao usuário;
-- 3. grava somente SHA-256(token) no banco;
-- 4. token expira;
-- 5. token é de uso único.
-- ============================================================

USE syn;

CREATE TABLE IF NOT EXISTS recuperacoes_senha (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    usuario_id BIGINT UNSIGNED NOT NULL,

    -- SHA-256 do token real. O token puro NÃO fica no banco.
    token_hash CHAR(64) NOT NULL,

    expira_em DATETIME NOT NULL,

    usado_em DATETIME NULL,

    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uq_recuperacoes_senha_token_hash (
        token_hash
    ),

    KEY idx_recuperacoes_senha_usuario (
        usuario_id
    ),

    KEY idx_recuperacoes_senha_expira (
        expira_em
    ),

    CONSTRAINT fk_recuperacoes_senha_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Conferência:
SHOW CREATE TABLE recuperacoes_senha;
