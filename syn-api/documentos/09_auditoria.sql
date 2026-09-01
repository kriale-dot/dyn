USE syn;

-- ============================================================
-- ETAPA 34
-- Auditoria das operações de escrita da API.
--
-- Objetivo:
-- registrar QUEM fez O QUÊ, em QUAL rota, QUANDO e com QUAL
-- resultado, sem armazenar senha, token JWT ou corpo completo
-- das requisições.
-- ============================================================

CREATE TABLE IF NOT EXISTS auditoria_operacoes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    request_id CHAR(32) NOT NULL,

    usuario_id BIGINT UNSIGNED NULL,
    usuario_nome_historico VARCHAR(180) NULL,
    papel_codigo_historico VARCHAR(50) NULL,

    metodo VARCHAR(10) NOT NULL,
    caminho VARCHAR(255) NOT NULL,

    recurso VARCHAR(80) NULL,
    entidade_id BIGINT UNSIGNED NULL,

    http_status SMALLINT UNSIGNED NOT NULL,
    sucesso TINYINT(1) NOT NULL,

    mensagem_resultado VARCHAR(500) NULL,

    ip VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,

    criado_em TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    UNIQUE KEY uk_auditoria_request_id (
        request_id
    ),

    KEY idx_auditoria_usuario_data (
        usuario_id,
        criado_em
    ),

    KEY idx_auditoria_recurso_data (
        recurso,
        criado_em
    ),

    KEY idx_auditoria_metodo_data (
        metodo,
        criado_em
    ),

    KEY idx_auditoria_status_data (
        http_status,
        criado_em
    ),

    CONSTRAINT fk_auditoria_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id)
        ON UPDATE RESTRICT
        ON DELETE SET NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
