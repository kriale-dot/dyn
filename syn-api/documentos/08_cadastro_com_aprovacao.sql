-- ============================================================
-- SYN - ETAPA 81
-- CADASTRO PÚBLICO COM APROVAÇÃO
-- ============================================================
--
-- Decisão de arquitetura:
--
-- Uma solicitação pública NÃO entra imediatamente em `usuarios`.
--
-- Primeiro ela fica em `solicitacoes_cadastro`.
-- Somente após aprovação ela vira um usuário real com papel MEMBRO.
--
-- Isso preserva toda a arquitetura atual do SYN:
--
-- - usuários continuam representando pessoas já autorizadas;
-- - login continua funcionando apenas para usuários ATIVOS;
-- - funções, escalas e permissões nunca apontam para um cadastro
--   ainda não aprovado.
-- ============================================================

USE syn;

CREATE TABLE IF NOT EXISTS solicitacoes_cadastro (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    nome VARCHAR(150) NOT NULL,
    data_nascimento DATE NULL,
    telefone VARCHAR(30) NULL,
    email VARCHAR(150) NOT NULL,

    -- Fica preenchido somente enquanto a solicitação está pendente.
    -- Após aprovação ou rejeição o hash é removido desta tabela.
    senha_hash VARCHAR(255) NULL,

    status ENUM(
        'PENDENTE',
        'APROVADO',
        'REJEITADO'
    ) NOT NULL DEFAULT 'PENDENTE',

    tentativas SMALLINT UNSIGNED NOT NULL DEFAULT 1,

    motivo_rejeicao VARCHAR(500) NULL,

    analisado_por_usuario_id BIGINT UNSIGNED NULL,
    usuario_criado_id BIGINT UNSIGNED NULL,

    solicitado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    analisado_em DATETIME NULL,

    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    -- Um e-mail possui apenas uma solicitação corrente.
    -- Se uma solicitação for rejeitada, o próprio registro pode ser
    -- reaberto por uma nova tentativa.
    UNIQUE KEY uq_solicitacoes_cadastro_email (email),

    KEY idx_solicitacoes_cadastro_status_data (
        status,
        solicitado_em
    ),

    KEY idx_solicitacoes_cadastro_analisador (
        analisado_por_usuario_id
    ),

    KEY idx_solicitacoes_cadastro_usuario_criado (
        usuario_criado_id
    ),

    CONSTRAINT fk_solicitacoes_cadastro_analisador
        FOREIGN KEY (analisado_por_usuario_id)
        REFERENCES usuarios(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT fk_solicitacoes_cadastro_usuario_criado
        FOREIGN KEY (usuario_criado_id)
        REFERENCES usuarios(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- PERMISSÃO ESPECIAL
-- ============================================================
--
-- Administradores possuem acesso por definição.
--
-- Um ORGANIZADOR só poderá aprovar/rejeitar cadastros quando o
-- Administrador conceder explicitamente esta permissão.
--
-- Ela NÃO é uma "função ministerial". É uma permissão administrativa
-- especial, preservando a separação Papel x Função do SYN.
-- ============================================================

INSERT INTO permissoes_especiais (
    codigo,
    nome,
    descricao,
    ativo
)
VALUES (
    'CADASTROS_APROVAR',
    'Aprovar cadastros',
    'Permite consultar, aprovar e rejeitar solicitações públicas de cadastro.',
    1
)
ON DUPLICATE KEY UPDATE
    nome = VALUES(nome),
    descricao = VALUES(descricao),
    ativo = 1;


-- ============================================================
-- CONFERÊNCIA
-- ============================================================

SELECT
    id,
    codigo,
    nome,
    ativo
FROM permissoes_especiais
WHERE codigo = 'CADASTROS_APROVAR';

SHOW COLUMNS
FROM solicitacoes_cadastro;
