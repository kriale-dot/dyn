USE syn;

CREATE TABLE IF NOT EXISTS permissoes_especiais (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(80) NOT NULL,
    nome VARCHAR(120) NOT NULL,
    descricao VARCHAR(500) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_permissoes_especiais_codigo (codigo),
    UNIQUE KEY uq_permissoes_especiais_nome (nome),
    KEY idx_permissoes_especiais_ativo (ativo)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios_permissoes_especiais (
    usuario_id BIGINT UNSIGNED NOT NULL,
    permissao_id SMALLINT UNSIGNED NOT NULL,
    concedido_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, permissao_id),
    KEY idx_upe_permissao (permissao_id),
    CONSTRAINT fk_upe_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT fk_upe_permissao
        FOREIGN KEY (permissao_id) REFERENCES permissoes_especiais(id)
        ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

INSERT INTO permissoes_especiais (
    codigo, nome, descricao, ativo
)
VALUES (
    'NECESSIDADES_ESPECIFICAS_GERENCIAR',
    'Gerenciar necessidades específicas',
    'Permite consultar, registrar, atualizar e desativar informações de necessidades específicas.',
    1
)
ON DUPLICATE KEY UPDATE
    nome = VALUES(nome),
    descricao = VALUES(descricao),
    ativo = 1;

SELECT id, codigo, nome, ativo
FROM permissoes_especiais
ORDER BY id;
