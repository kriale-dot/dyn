-- ============================================================
-- SYN - ETAPA 90
-- HISTÓRICO DE ATIVIDADE DE SEGURANÇA DA CONTA
-- ============================================================
--
-- Objetivo:
--
-- permitir que o próprio usuário veja eventos importantes da conta:
--
-- - criação da conta;
-- - login bem-sucedido;
-- - alteração de senha;
-- - redefinição de senha;
-- - encerramento global de sessões;
-- - alteração do e-mail.
--
-- Não armazenamos senha, token JWT ou token de confirmação.
-- ============================================================

USE syn;

CREATE TABLE IF NOT EXISTS eventos_seguranca_conta (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    usuario_id BIGINT UNSIGNED NOT NULL,

    tipo VARCHAR(60) NOT NULL,

    titulo VARCHAR(150) NOT NULL,

    detalhe VARCHAR(500) NULL,

    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    KEY idx_eventos_seguranca_usuario_data (
        usuario_id,
        criado_em
    ),

    KEY idx_eventos_seguranca_tipo_data (
        tipo,
        criado_em
    ),

    CONSTRAINT fk_eventos_seguranca_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- BASELINE PARA CONTAS JÁ EXISTENTES
-- ============================================================
--
-- A tabela nasce agora. Para que o usuário não veja um histórico
-- totalmente vazio, registramos o último login conhecido anteriormente.
--
-- Só fazemos isso se ainda não houver evento para o usuário.
-- ============================================================

INSERT INTO eventos_seguranca_conta (
    usuario_id,
    tipo,
    titulo,
    detalhe,
    criado_em
)
SELECT
    u.id,
    'LOGIN_SUCESSO',
    'Login realizado',
    'Último acesso conhecido antes da ativação do histórico de segurança.',
    u.ultimo_login_em
FROM usuarios u
WHERE u.ultimo_login_em IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM eventos_seguranca_conta e
      WHERE e.usuario_id = u.id
  );


SELECT
    e.id,
    e.usuario_id,
    e.tipo,
    e.titulo,
    e.detalhe,
    e.criado_em
FROM eventos_seguranca_conta e
ORDER BY e.criado_em DESC, e.id DESC
LIMIT 30;
