-- ============================================================
-- SYN - Banco de Dados v1.0
-- Arquivo: 01_create_database_syn.sql
-- Banco: MariaDB
-- Baseado no Documento de Requisitos SYN v1.0 (30/08/2026)
-- ============================================================

CREATE DATABASE IF NOT EXISTS syn
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE syn;

-- ============================================================
-- 1. IGREJA
-- Cada instalação do SYN representa uma única igreja.
-- O campo singleton, com UNIQUE + CHECK, impede mais de um
-- registro institucional nesta base.
-- ============================================================
CREATE TABLE igreja (
    id TINYINT UNSIGNED NOT NULL DEFAULT 1,
    singleton TINYINT UNSIGNED NOT NULL DEFAULT 1,
    nome VARCHAR(150) NOT NULL,
    logotipo VARCHAR(255) NULL,
    cep VARCHAR(10) NULL,
    logradouro VARCHAR(150) NULL,
    numero VARCHAR(20) NULL,
    complemento VARCHAR(100) NULL,
    bairro VARCHAR(100) NULL,
    cidade VARCHAR(100) NULL,
    estado CHAR(2) NULL,
    telefone VARCHAR(30) NULL,
    email VARCHAR(150) NULL,
    site VARCHAR(180) NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_igreja_singleton (singleton),
    CONSTRAINT chk_igreja_singleton CHECK (singleton = 1)
) ENGINE=InnoDB;

-- ============================================================
-- 2. PAPÉIS DE ACESSO
-- Os papéis são fixos: Administrador, Organizador e Membro.
-- ============================================================
CREATE TABLE papeis (
    id TINYINT UNSIGNED NOT NULL,
    codigo VARCHAR(30) NOT NULL,
    nome VARCHAR(60) NOT NULL,
    descricao VARCHAR(255) NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uq_papeis_codigo (codigo),
    UNIQUE KEY uq_papeis_nome (nome)
) ENGINE=InnoDB;

INSERT INTO papeis (id, codigo, nome, descricao) VALUES
    (1, 'ADMINISTRADOR', 'Administrador', 'Configura e administra o sistema.'),
    (2, 'ORGANIZADOR', 'Organizador', 'Organiza programações e escalas conforme as permissões atribuídas.'),
    (3, 'MEMBRO', 'Membro', 'Consulta programações, compromissos e responde às escalas.')
ON DUPLICATE KEY UPDATE
    nome = VALUES(nome),
    descricao = VALUES(descricao);

-- ============================================================
-- 3. USUÁRIOS
-- Guarda o estado ATUAL da pessoa. Usuários com histórico não
-- devem ser apagados: devem ser marcados como INATIVOS.
-- ============================================================
CREATE TABLE usuarios (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    papel_id TINYINT UNSIGNED NOT NULL,
    nome VARCHAR(150) NOT NULL,
    data_nascimento DATE NULL,
    telefone VARCHAR(30) NULL,
    email VARCHAR(150) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    foto VARCHAR(255) NULL,
    status ENUM('ATIVO', 'INATIVO') NOT NULL DEFAULT 'ATIVO',
    ultimo_login_em DATETIME NULL,
    desativado_em DATETIME NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email),
    KEY idx_usuarios_papel (papel_id),
    KEY idx_usuarios_status (status),

    CONSTRAINT fk_usuarios_papel
        FOREIGN KEY (papel_id) REFERENCES papeis(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- 4. NECESSIDADES ESPECÍFICAS
-- Tabela separada para facilitar a restrição de acesso desses
-- dados a Administradores e Organizadores autorizados.
-- ============================================================
CREATE TABLE necessidades_especificas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id BIGINT UNSIGNED NOT NULL,
    observacao TEXT NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_necessidades_usuario (usuario_id),

    CONSTRAINT fk_necessidades_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- 5. DEPARTAMENTOS
-- Exemplos: Infantil, Louvor, Coral, Orquestra.
-- ============================================================
CREATE TABLE departamentos (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(120) NOT NULL,
    descricao VARCHAR(500) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    desativado_em DATETIME NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_departamentos_nome (nome),
    KEY idx_departamentos_ativo (ativo)
) ENGINE=InnoDB;

-- ============================================================
-- 6. FUNÇÕES
-- Função = habilitação para atuar em uma atividade.
-- Uma função pode estar associada a um departamento.
-- ============================================================
CREATE TABLE funcoes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    departamento_id BIGINT UNSIGNED NULL,
    nome VARCHAR(120) NOT NULL,
    descricao VARCHAR(500) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    desativado_em DATETIME NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_funcoes_nome_departamento (nome, departamento_id),
    KEY idx_funcoes_departamento (departamento_id),
    KEY idx_funcoes_ativo (ativo),

    CONSTRAINT fk_funcoes_departamento
        FOREIGN KEY (departamento_id) REFERENCES departamentos(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- 7. USUÁRIOS x FUNÇÕES
-- Relação atual N:N. Pode ser removida quando a pessoa deixa de
-- exercer a função, pois a participação histórica fica registrada
-- separadamente na tabela participacoes.
-- ============================================================
CREATE TABLE usuarios_funcoes (
    usuario_id BIGINT UNSIGNED NOT NULL,
    funcao_id BIGINT UNSIGNED NOT NULL,
    atribuido_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (usuario_id, funcao_id),
    KEY idx_usuarios_funcoes_funcao (funcao_id),

    CONSTRAINT fk_usuarios_funcoes_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT fk_usuarios_funcoes_funcao
        FOREIGN KEY (funcao_id) REFERENCES funcoes(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- 8. TIPOS DE PROGRAMAÇÃO
-- Exemplos: Culto Dominical, Culto Infantil, Ensaio.
-- ============================================================
CREATE TABLE tipos_programacao (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(120) NOT NULL,
    descricao VARCHAR(500) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    desativado_em DATETIME NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_tipos_programacao_nome (nome),
    KEY idx_tipos_programacao_ativo (ativo)
) ENGINE=InnoDB;

-- ============================================================
-- 9. FUNÇÕES x TIPOS DE PROGRAMAÇÃO
-- Define a elegibilidade: quais funções podem ser usadas em cada
-- tipo de programação.
-- ============================================================
CREATE TABLE funcoes_tipos_programacao (
    funcao_id BIGINT UNSIGNED NOT NULL,
    tipo_programacao_id BIGINT UNSIGNED NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (funcao_id, tipo_programacao_id),
    KEY idx_ftp_tipo (tipo_programacao_id),

    CONSTRAINT fk_ftp_funcao
        FOREIGN KEY (funcao_id) REFERENCES funcoes(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT fk_ftp_tipo
        FOREIGN KEY (tipo_programacao_id) REFERENCES tipos_programacao(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- 10. LOCAIS
-- Toda programação deverá possuir um local.
-- ============================================================
CREATE TABLE locais (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(120) NOT NULL,
    descricao VARCHAR(500) NULL,
    capacidade SMALLINT UNSIGNED NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    desativado_em DATETIME NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_locais_nome (nome),
    KEY idx_locais_ativo (ativo)
) ENGINE=InnoDB;

-- ============================================================
-- 11. SÉRIES DE PROGRAMAÇÃO
-- Suporte às programações recorrentes. Cada ocorrência concreta
-- será materializada na tabela programacoes.
-- A regra_recorrencia será interpretada pela API; o formato final
-- da regra pode ser definido quando implementarmos o serviço de
-- recorrência.
-- ============================================================
CREATE TABLE series_programacao (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tipo_programacao_id BIGINT UNSIGNED NOT NULL,
    local_id BIGINT UNSIGNED NOT NULL,
    organizador_id BIGINT UNSIGNED NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    descricao TEXT NULL,
    inicio_base DATETIME NOT NULL,
    fim_base DATETIME NOT NULL,
    regra_recorrencia VARCHAR(500) NOT NULL,
    data_limite DATE NULL,
    ativa TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_series_tipo (tipo_programacao_id),
    KEY idx_series_local (local_id),
    KEY idx_series_organizador (organizador_id),

    CONSTRAINT chk_series_periodo CHECK (fim_base > inicio_base),

    CONSTRAINT fk_series_tipo
        FOREIGN KEY (tipo_programacao_id) REFERENCES tipos_programacao(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT fk_series_local
        FOREIGN KEY (local_id) REFERENCES locais(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT fk_series_organizador
        FOREIGN KEY (organizador_id) REFERENCES usuarios(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- 12. PROGRAMAÇÕES
-- Ocorrências concretas: o que acontecerá, quando e onde.
-- Campos *_historico preservam a forma como os dados eram
-- apresentados naquela ocorrência, mesmo se cadastros forem
-- alterados posteriormente.
-- ============================================================
CREATE TABLE programacoes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    serie_id BIGINT UNSIGNED NULL,
    tipo_programacao_id BIGINT UNSIGNED NOT NULL,
    local_id BIGINT UNSIGNED NOT NULL,
    organizador_id BIGINT UNSIGNED NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    descricao TEXT NULL,
    inicio_em DATETIME NOT NULL,
    fim_em DATETIME NOT NULL,
    status ENUM('RASCUNHO', 'AGENDADA', 'REALIZADA', 'CANCELADA') NOT NULL DEFAULT 'RASCUNHO',
    permite_resposta TINYINT(1) NOT NULL DEFAULT 1,

    tipo_programacao_nome_historico VARCHAR(120) NOT NULL,
    local_nome_historico VARCHAR(120) NOT NULL,
    organizador_nome_historico VARCHAR(150) NOT NULL,

    cancelada_em DATETIME NULL,
    motivo_cancelamento VARCHAR(500) NULL,
    realizado_em DATETIME NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_programacoes_serie (serie_id),
    KEY idx_programacoes_tipo (tipo_programacao_id),
    KEY idx_programacoes_local_periodo (local_id, inicio_em, fim_em),
    KEY idx_programacoes_organizador (organizador_id),
    KEY idx_programacoes_status_inicio (status, inicio_em),

    CONSTRAINT chk_programacoes_periodo CHECK (fim_em > inicio_em),

    CONSTRAINT fk_programacoes_serie
        FOREIGN KEY (serie_id) REFERENCES series_programacao(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT fk_programacoes_tipo
        FOREIGN KEY (tipo_programacao_id) REFERENCES tipos_programacao(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT fk_programacoes_local
        FOREIGN KEY (local_id) REFERENCES locais(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT fk_programacoes_organizador
        FOREIGN KEY (organizador_id) REFERENCES usuarios(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- 13. PARTICIPAÇÕES / ESCALAS
-- Registro histórico de quem fez o quê em uma programação.
-- A mesma função pode ter várias pessoas na mesma programação.
-- A mesma pessoa também pode exercer mais de uma função.
-- ============================================================
CREATE TABLE participacoes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    programacao_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    funcao_id BIGINT UNSIGNED NOT NULL,
    status ENUM('ESCALADO', 'CONFIRMADO', 'INDISPONIVEL', 'RECUSADO', 'CANCELADO') NOT NULL DEFAULT 'ESCALADO',

    usuario_nome_historico VARCHAR(150) NOT NULL,
    funcao_nome_historico VARCHAR(120) NOT NULL,
    departamento_nome_historico VARCHAR(120) NULL,

    observacao VARCHAR(500) NULL,
    respondido_em DATETIME NULL,
    cancelado_em DATETIME NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_participacao_pessoa_funcao (programacao_id, usuario_id, funcao_id),
    KEY idx_participacoes_usuario_status (usuario_id, status),
    KEY idx_participacoes_programacao_status (programacao_id, status),
    KEY idx_participacoes_funcao (funcao_id),

    CONSTRAINT fk_participacoes_programacao
        FOREIGN KEY (programacao_id) REFERENCES programacoes(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT fk_participacoes_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT fk_participacoes_funcao
        FOREIGN KEY (funcao_id) REFERENCES funcoes(id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- OBSERVAÇÕES DE PROJETO
-- ============================================================
-- 1) Não usamos ON DELETE CASCADE em entidades históricas.
-- 2) Usuários, funções, departamentos, tipos e locais devem ser
--    desativados quando houver histórico associado.
-- 3) O conflito de local/horário será detectado pela API através
--    de consulta por sobreposição usando o índice de programacoes.
-- 4) usuarios_funcoes representa apenas as habilitações ATUAIS.
--    participacoes representa o FATO HISTÓRICO.
-- 5) A granularidade das permissões específicas do Organizador
--    não foi detalhada no documento de requisitos; portanto não
--    foi inventada nesta primeira versão do schema.
-- ============================================================
