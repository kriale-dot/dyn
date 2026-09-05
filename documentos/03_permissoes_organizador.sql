-- ============================================================
-- SYN - MIGRAÇÃO 04
-- Escopo do Organizador por tipo de programação
-- ============================================================

USE syn;

CREATE TABLE IF NOT EXISTS organizadores_tipos_programacao (
    usuario_id BIGINT UNSIGNED NOT NULL,
    tipo_programacao_id BIGINT UNSIGNED NOT NULL,
    atribuido_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (usuario_id, tipo_programacao_id),
    KEY idx_otp_tipo (tipo_programacao_id),

    CONSTRAINT fk_otp_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE RESTRICT ON DELETE RESTRICT,

    CONSTRAINT fk_otp_tipo
        FOREIGN KEY (tipo_programacao_id) REFERENCES tipos_programacao(id)
        ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Permissão inicial de desenvolvimento:
INSERT IGNORE INTO organizadores_tipos_programacao (
    usuario_id,
    tipo_programacao_id
)
SELECT
    u.id,
    tp.id
FROM usuarios u
INNER JOIN papeis p ON p.id = u.papel_id
INNER JOIN tipos_programacao tp ON tp.nome = 'Culto Infantil'
WHERE u.email = 'organizador@syn.local'
  AND p.codigo = 'ORGANIZADOR';

SELECT
    u.id AS organizador_id,
    u.nome AS organizador,
    tp.id AS tipo_programacao_id,
    tp.nome AS tipo_programacao,
    otp.atribuido_em
FROM organizadores_tipos_programacao otp
INNER JOIN usuarios u ON u.id = otp.usuario_id
INNER JOIN tipos_programacao tp ON tp.id = otp.tipo_programacao_id
ORDER BY u.nome, tp.nome;
