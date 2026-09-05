USE syn;

CREATE TABLE IF NOT EXISTS notificacoes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    usuario_id BIGINT UNSIGNED NOT NULL,

    tipo VARCHAR(60) NOT NULL,

    titulo VARCHAR(160) NOT NULL,
    mensagem VARCHAR(500) NOT NULL,

    url_acao VARCHAR(255) NULL,

    origem_tipo VARCHAR(60) NULL,
    origem_id BIGINT UNSIGNED NULL,

    lida_em DATETIME NULL,
    expira_em DATETIME NULL,

    criada_em TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    CONSTRAINT fk_notificacoes_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios (id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    UNIQUE KEY uk_notificacao_origem (
        usuario_id,
        tipo,
        origem_tipo,
        origem_id
    ),

    KEY idx_notificacoes_usuario_criada (
        usuario_id,
        criada_em
    ),

    KEY idx_notificacoes_usuario_lida (
        usuario_id,
        lida_em
    ),

    KEY idx_notificacoes_expira (
        expira_em
    )
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
