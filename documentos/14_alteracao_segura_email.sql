-- ============================================================
-- SYN - ETAPA 87
-- ALTERAÇÃO SEGURA DE E-MAIL
-- ============================================================
--
-- O e-mail é usado para login e recuperação de senha.
-- Portanto, não deve ser alterado diretamente pelo PUT /meu-perfil.
--
-- Novo fluxo:
--
-- usuário autenticado
--      ↓
-- informa senha atual + novo e-mail
--      ↓
-- token enviado ao NOVO e-mail
--      ↓
-- confirmação pelo link
--      ↓
-- usuarios.email é alterado
--      ↓
-- sessao_versao + 1
--      ↓
-- todos os JWT anteriores são invalidados
--
-- A tabela abaixo preserva o histórico das solicitações.
-- ============================================================

USE syn;

CREATE TABLE IF NOT EXISTS alteracoes_email (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    usuario_id BIGINT UNSIGNED NOT NULL,

    email_anterior VARCHAR(150) NOT NULL,
    novo_email VARCHAR(150) NOT NULL,

    token_hash CHAR(64) NULL,

    status ENUM(
        'PENDENTE',
        'CONFIRMADO',
        'CANCELADO',
        'EXPIRADO'
    ) NOT NULL DEFAULT 'PENDENTE',

    expira_em DATETIME NOT NULL,

    solicitado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    confirmado_em DATETIME NULL,
    cancelado_em DATETIME NULL,

    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_alteracoes_email_usuario_status (
        usuario_id,
        status
    ),

    KEY idx_alteracoes_email_token (
        token_hash
    ),

    KEY idx_alteracoes_email_expira (
        expira_em
    ),

    CONSTRAINT fk_alteracoes_email_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
