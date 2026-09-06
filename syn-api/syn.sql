-- ============================================================
-- SYN - BANCO LIMPO DE PRODUÇÃO
-- Instalação: IP Filadélfia
-- Banco: syn_ipfiladelfia
--
-- IMPORTAÇÃO PELO phpMyAdmin
-- 1. Selecione o banco syn_ipfiladelfia.
-- 2. Abra a aba Importar.
-- 3. Escolha este arquivo syn.sql.
-- 4. Execute a importação.
--
-- Este arquivo contém apenas estrutura, configurações fixas
-- do sistema e o registro institucional inicial da igreja.
-- O primeiro Administrador será criado depois da importação.
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

USE syn_ipfiladelfia;



-- ============================================================
-- MIGRAÇÃO 01: 01_schema_base_ipfiladelfia.sql
-- ============================================================

-- ============================================================
-- SYN - Banco de Dados v1.0
-- Arquivo: 01_create_database_syn.sql
-- Banco: MariaDB
-- Baseado no Documento de Requisitos SYN v1.0 (30/08/2026)
-- ============================================================
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


-- ============================================================
-- MIGRAÇÃO 02: 02_seed_producao_ipfiladelfia.sql
-- ============================================================

-- ============================================================
-- SYN - Seed limpo de PRODUÇÃO
-- Instalação: ipfiladelfia
-- ============================================================
START TRANSACTION;

-- Cada instalação SYN representa uma única igreja.
-- Este registro é necessário porque a aplicação atualiza
-- o singleton existente, em vez de criar a igreja no primeiro uso.
INSERT INTO igreja (
    id,
    singleton,
    nome
)
VALUES (
    1,
    1,
    'Igreja Filadélfia'
)
ON DUPLICATE KEY UPDATE
    nome = VALUES(nome);

COMMIT;


-- ============================================================
-- MIGRAÇÃO 03: 03_permissoes_organizador.sql
-- ============================================================

-- ============================================================
-- SYN - MIGRAÇÃO 04
-- Escopo do Organizador por tipo de programação
-- ============================================================
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


-- ============================================================
-- MIGRAÇÃO 04: 04_permissoes_especiais.sql
-- ============================================================

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


-- ============================================================
-- MIGRAÇÃO 05: 05_recuperacao_senha.sql
-- ============================================================

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


-- ============================================================
-- MIGRAÇÃO 06: 06_notificacoes.sql
-- ============================================================

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


-- ============================================================
-- MIGRAÇÃO 07: 07_eventos_programacao.sql
-- ============================================================

-- ============================================================
-- ETAPA 33
-- Histórico de alterações importantes da programação
-- + notificações automáticas aos participantes ativos.
--
-- Execute este script APÓS a Etapa 32, pois ele utiliza
-- a tabela notificacoes.
-- ============================================================

CREATE TABLE IF NOT EXISTS eventos_programacao (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    programacao_id BIGINT UNSIGNED NOT NULL,

    tipo_evento VARCHAR(60) NOT NULL,

    titulo_anterior VARCHAR(180) NULL,
    titulo_novo VARCHAR(180) NULL,

    descricao_anterior TEXT NULL,
    descricao_nova TEXT NULL,

    inicio_anterior DATETIME NULL,
    inicio_novo DATETIME NULL,

    fim_anterior DATETIME NULL,
    fim_novo DATETIME NULL,

    local_anterior VARCHAR(180) NULL,
    local_novo VARCHAR(180) NULL,

    status_anterior VARCHAR(30) NULL,
    status_novo VARCHAR(30) NULL,

    criada_em TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    CONSTRAINT fk_eventos_programacao_programacao
        FOREIGN KEY (programacao_id)
        REFERENCES programacoes (id)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    KEY idx_eventos_programacao (
        programacao_id,
        criada_em
    ),

    KEY idx_eventos_tipo (
        tipo_evento,
        criada_em
    )
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


DROP TRIGGER IF EXISTS trg_programacoes_eventos_au;

DELIMITER $$

CREATE TRIGGER trg_programacoes_eventos_au
AFTER UPDATE ON programacoes
FOR EACH ROW
BEGIN
    DECLARE v_evento_id BIGINT UNSIGNED;
    DECLARE v_tipo_evento VARCHAR(60);
    DECLARE v_titulo_notificacao VARCHAR(160);
    DECLARE v_mensagem_notificacao VARCHAR(500);

    /*
     * Cancelamento tem prioridade.
     *
     * Caso contrário, registramos alterações importantes:
     * título, descrição, início, fim ou local histórico.
     */
    IF NOT (OLD.status <=> NEW.status)
       AND NEW.status = 'CANCELADA'
    THEN

        SET v_tipo_evento =
            'PROGRAMACAO_CANCELADA';

        SET v_titulo_notificacao =
            'Programação cancelada';

        SET v_mensagem_notificacao =
            CONCAT(
                'A programação "',
                NEW.titulo,
                '" foi cancelada.'
            );

        INSERT INTO eventos_programacao (
            programacao_id,
            tipo_evento,

            titulo_anterior,
            titulo_novo,

            descricao_anterior,
            descricao_nova,

            inicio_anterior,
            inicio_novo,

            fim_anterior,
            fim_novo,

            local_anterior,
            local_novo,

            status_anterior,
            status_novo
        )
        VALUES (
            NEW.id,
            v_tipo_evento,

            OLD.titulo,
            NEW.titulo,

            OLD.descricao,
            NEW.descricao,

            OLD.inicio_em,
            NEW.inicio_em,

            OLD.fim_em,
            NEW.fim_em,

            OLD.local_nome_historico,
            NEW.local_nome_historico,

            OLD.status,
            NEW.status
        );

        SET v_evento_id =
            LAST_INSERT_ID();

        /*
         * Notifica somente quem ainda estava efetivamente
         * comprometido com a programação.
         */
        INSERT IGNORE INTO notificacoes (
            usuario_id,
            tipo,
            titulo,
            mensagem,
            url_acao,
            origem_tipo,
            origem_id,
            expira_em
        )
        SELECT
            p.usuario_id,
            v_tipo_evento,
            v_titulo_notificacao,
            v_mensagem_notificacao,
            CONCAT(
                '/programacoes/',
                NEW.id
            ),
            'EVENTO_PROGRAMACAO',
            v_evento_id,
            DATE_ADD(
                NOW(),
                INTERVAL 30 DAY
            )
        FROM participacoes p
        WHERE p.programacao_id = NEW.id
          AND p.status IN (
              'ESCALADO',
              'CONFIRMADO'
          );

    ELSEIF
        NOT (OLD.titulo <=> NEW.titulo)
        OR NOT (OLD.descricao <=> NEW.descricao)
        OR NOT (OLD.inicio_em <=> NEW.inicio_em)
        OR NOT (OLD.fim_em <=> NEW.fim_em)
        OR NOT (
            OLD.local_nome_historico
            <=>
            NEW.local_nome_historico
        )
    THEN

        SET v_tipo_evento =
            'PROGRAMACAO_ALTERADA';

        SET v_titulo_notificacao =
            'Programação atualizada';

        SET v_mensagem_notificacao =
            CONCAT(
                'A programação "',
                NEW.titulo,
                '" teve informações atualizadas. Confira os detalhes.'
            );

        INSERT INTO eventos_programacao (
            programacao_id,
            tipo_evento,

            titulo_anterior,
            titulo_novo,

            descricao_anterior,
            descricao_nova,

            inicio_anterior,
            inicio_novo,

            fim_anterior,
            fim_novo,

            local_anterior,
            local_novo,

            status_anterior,
            status_novo
        )
        VALUES (
            NEW.id,
            v_tipo_evento,

            OLD.titulo,
            NEW.titulo,

            OLD.descricao,
            NEW.descricao,

            OLD.inicio_em,
            NEW.inicio_em,

            OLD.fim_em,
            NEW.fim_em,

            OLD.local_nome_historico,
            NEW.local_nome_historico,

            OLD.status,
            NEW.status
        );

        SET v_evento_id =
            LAST_INSERT_ID();

        INSERT IGNORE INTO notificacoes (
            usuario_id,
            tipo,
            titulo,
            mensagem,
            url_acao,
            origem_tipo,
            origem_id,
            expira_em
        )
        SELECT
            p.usuario_id,
            v_tipo_evento,
            v_titulo_notificacao,
            v_mensagem_notificacao,
            CONCAT(
                '/programacoes/',
                NEW.id
            ),
            'EVENTO_PROGRAMACAO',
            v_evento_id,

            CASE
                WHEN NEW.fim_em >= NOW()
                    THEN NEW.fim_em
                ELSE DATE_ADD(
                    NOW(),
                    INTERVAL 30 DAY
                )
            END

        FROM participacoes p

        WHERE p.programacao_id = NEW.id
          AND p.status IN (
              'ESCALADO',
              'CONFIRMADO'
          );

    END IF;
END$$

DELIMITER ;


-- ============================================================
-- MIGRAÇÃO 08: 08_auditoria.sql
-- ============================================================

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


-- ============================================================
-- MIGRAÇÃO 09: 09_modo_publico.sql
-- ============================================================

-- ============================================================
-- SYN - ETAPA 72
-- MODO PÚBLICO / PROGRAMAÇÕES PÚBLICAS
-- ============================================================
--
-- Objetivo:
-- permitir que determinadas programações sejam exibidas
-- sem login, preservando como INTERNAS todas as programações
-- existentes e futuras por padrão.
--
-- IMPORTANTE:
-- esta migration NÃO publica nenhuma programação automaticamente.
-- Isso evita exposição acidental de informações internas.
-- ============================================================
ALTER TABLE programacoes
    ADD COLUMN IF NOT EXISTS descricao_publica TEXT NULL
        AFTER descricao,
    ADD COLUMN IF NOT EXISTS visibilidade
        ENUM('INTERNA', 'PUBLICA')
        NOT NULL
        DEFAULT 'INTERNA'
        AFTER permite_resposta;

ALTER TABLE programacoes
    ADD INDEX IF NOT EXISTS
        idx_programacoes_visibilidade_status_inicio
        (visibilidade, status, inicio_em);

-- ============================================================
-- TESTE OPCIONAL EM DESENVOLVIMENTO
-- ============================================================
-- Execute MANUALMENTE apenas se quiser publicar uma programação
-- para testar a nova API.
--
-- Troque o ID conforme necessário.
--
-- UPDATE programacoes
-- SET
--     visibilidade = 'PUBLICA',
--     descricao_publica =
--         'Programação aberta à comunidade.'
-- WHERE id = 1;
--
-- Para voltar a ser interna:
--
-- UPDATE programacoes
-- SET visibilidade = 'INTERNA'
-- WHERE id = 1;
-- ============================================================


-- ============================================================
-- MIGRAÇÃO 10: 10_cadastro_com_aprovacao.sql
-- ============================================================

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


-- ============================================================
-- MIGRAÇÃO 11: 11_rate_limit.sql
-- ============================================================

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


-- ============================================================
-- MIGRAÇÃO 12: 12_confirmacao_email_cadastro.sql
-- ============================================================

-- ============================================================
-- SYN - ETAPA 84
-- CONFIRMAÇÃO DO E-MAIL ANTES DA APROVAÇÃO DO CADASTRO
-- ============================================================
--
-- Novo fluxo:
--
-- cadastro público
--     ↓
-- AGUARDANDO_EMAIL
--     ↓
-- usuário confirma pelo link recebido
--     ↓
-- PENDENTE
--     ↓
-- Administrador/Organizador autorizado analisa
--     ↓
-- APROVADO ou REJEITADO
--
-- O token nunca é armazenado em texto puro.
-- O banco guarda somente SHA-256(token).
-- ============================================================
ALTER TABLE solicitacoes_cadastro
    MODIFY COLUMN status ENUM(
        'AGUARDANDO_EMAIL',
        'PENDENTE',
        'APROVADO',
        'REJEITADO'
    ) NOT NULL DEFAULT 'AGUARDANDO_EMAIL';

ALTER TABLE solicitacoes_cadastro
    ADD COLUMN IF NOT EXISTS email_confirmacao_token_hash CHAR(64) NULL
        AFTER senha_hash,
    ADD COLUMN IF NOT EXISTS email_confirmacao_expira_em DATETIME NULL
        AFTER email_confirmacao_token_hash,
    ADD COLUMN IF NOT EXISTS email_confirmado_em DATETIME NULL
        AFTER email_confirmacao_expira_em;

CREATE INDEX IF NOT EXISTS idx_solicitacoes_email_confirmacao_token
    ON solicitacoes_cadastro (
        email_confirmacao_token_hash
    );

CREATE INDEX IF NOT EXISTS idx_solicitacoes_email_confirmacao_expira
    ON solicitacoes_cadastro (
        email_confirmacao_expira_em
    );

-- Solicitações criadas antes desta etapa já passaram pelo fluxo anterior.
-- Marcamos essas linhas como "e-mail confirmado por legado" para não
-- invalidar cadastros já existentes.
UPDATE solicitacoes_cadastro
SET email_confirmado_em =
    COALESCE(
        email_confirmado_em,
        solicitado_em
    )
WHERE status IN (
    'PENDENTE',
    'APROVADO',
    'REJEITADO'
)
  AND email_confirmado_em IS NULL;

SELECT
    id,
    nome,
    email,
    status,
    email_confirmado_em
FROM solicitacoes_cadastro
ORDER BY id DESC
LIMIT 20;


-- ============================================================
-- MIGRAÇÃO 13: 13_revogacao_sessoes.sql
-- ============================================================

-- ============================================================
-- SYN - ETAPA 85
-- REVOGAÇÃO DE SESSÕES JWT
-- ============================================================
--
-- Problema resolvido:
--
-- Antes desta etapa, um JWT já emitido continuava válido até expirar,
-- mesmo depois de uma redefinição de senha.
--
-- A partir de agora cada usuário possui uma "versão de sessão".
--
-- O JWT recebe:
--
--     sv = sessao_versao
--
-- Em cada requisição autenticada o backend compara:
--
--     token.sv === usuarios.sessao_versao
--
-- Sempre que for necessário invalidar todas as sessões, a versão é
-- incrementada. Todos os tokens antigos deixam de funcionar
-- imediatamente.
-- ============================================================
ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS sessao_versao
        INT UNSIGNED NOT NULL DEFAULT 1
        AFTER ultimo_login_em;

-- Garante valor válido em instalações que possam ter sido alteradas
-- manualmente durante o desenvolvimento.
UPDATE usuarios
SET sessao_versao = 1
WHERE sessao_versao IS NULL
   OR sessao_versao < 1;

SELECT
    id,
    nome,
    email,
    status,
    sessao_versao
FROM usuarios
ORDER BY id;


-- ============================================================
-- MIGRAÇÃO 14: 14_alteracao_segura_email.sql
-- ============================================================

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


-- ============================================================
-- MIGRAÇÃO 15: 15_expiracao_cadastros_email.sql
-- ============================================================

-- ============================================================
-- SYN - ETAPA 89
-- EXPIRAÇÃO DE CADASTROS QUE NÃO CONFIRMARAM O E-MAIL
-- ============================================================
--
-- Problema:
--
-- A Etapa 84 criou o estado AGUARDANDO_EMAIL com token válido por
-- 24 horas. Porém, depois do vencimento, a solicitação continuava
-- indefinidamente com esse status.
--
-- Solução:
--
-- AGUARDANDO_EMAIL + prazo vencido
--              ↓
--          EXPIRADO
--
-- A pessoa poderá preencher o cadastro novamente com o mesmo e-mail.
-- O histórico antigo continua preservado no mesmo registro.
-- ============================================================
ALTER TABLE solicitacoes_cadastro
    MODIFY COLUMN status ENUM(
        'AGUARDANDO_EMAIL',
        'PENDENTE',
        'APROVADO',
        'REJEITADO',
        'EXPIRADO'
    ) NOT NULL DEFAULT 'AGUARDANDO_EMAIL';

-- Corrige imediatamente solicitações antigas cujo link já venceu.
UPDATE solicitacoes_cadastro
SET
    status = 'EXPIRADO',
    email_confirmacao_token_hash = NULL
WHERE status = 'AGUARDANDO_EMAIL'
  AND email_confirmacao_expira_em IS NOT NULL
  AND email_confirmacao_expira_em < NOW();

SELECT
    id,
    nome,
    email,
    status,
    solicitado_em,
    email_confirmacao_expira_em
FROM solicitacoes_cadastro
ORDER BY id DESC
LIMIT 30;


-- ============================================================
-- MIGRAÇÃO 16: 16_eventos_seguranca_conta.sql
-- ============================================================

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
