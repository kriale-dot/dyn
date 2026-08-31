-- ============================================================
-- SYN - Dados iniciais / cenário de validação do banco
-- Arquivo: 02_dados_iniciais_syn.sql
-- Banco: MariaDB
-- Baseado no cenário de aceitação do Documento de Requisitos
-- SYN v1.0 (30/08/2026)
-- ============================================================

USE syn;

START TRANSACTION;

-- ============================================================
-- 1. IGREJA
-- Cada instalação do SYN representa uma única igreja.
-- ============================================================
INSERT INTO igreja (
    id,
    singleton,
    nome,
    telefone,
    email,
    cidade,
    estado
)
VALUES (
    1,
    1,
    'Igreja Exemplo SYN',
    '(16) 0000-0000',
    'contato@exemplo.local',
    'São Carlos',
    'SP'
)
ON DUPLICATE KEY UPDATE
    nome = VALUES(nome),
    telefone = VALUES(telefone),
    email = VALUES(email),
    cidade = VALUES(cidade),
    estado = VALUES(estado);

-- ============================================================
-- 2. DEPARTAMENTO INFANTIL
-- Cenário de aceitação: cadastrar o departamento Infantil.
-- ============================================================
INSERT INTO departamentos (nome, descricao)
SELECT
    'Infantil',
    'Departamento responsável pelas atividades com crianças.'
WHERE NOT EXISTS (
    SELECT 1
    FROM departamentos
    WHERE nome = 'Infantil'
);

SET @departamento_infantil_id = (
    SELECT id
    FROM departamentos
    WHERE nome = 'Infantil'
    LIMIT 1
);

-- ============================================================
-- 3. FUNÇÕES
-- Cenário de aceitação: Professor Infantil e Auxiliar Infantil.
-- Pastor também é criado porque o documento o autoriza para o
-- tipo de programação Culto Infantil.
-- ============================================================
INSERT INTO funcoes (departamento_id, nome, descricao)
SELECT
    @departamento_infantil_id,
    'Professor Infantil',
    'Responsável pelo ensino em atividades do departamento infantil.'
WHERE NOT EXISTS (
    SELECT 1
    FROM funcoes
    WHERE nome = 'Professor Infantil'
      AND departamento_id = @departamento_infantil_id
);

INSERT INTO funcoes (departamento_id, nome, descricao)
SELECT
    @departamento_infantil_id,
    'Auxiliar Infantil',
    'Auxilia o professor nas atividades do departamento infantil.'
WHERE NOT EXISTS (
    SELECT 1
    FROM funcoes
    WHERE nome = 'Auxiliar Infantil'
      AND departamento_id = @departamento_infantil_id
);

INSERT INTO funcoes (departamento_id, nome, descricao)
SELECT
    NULL,
    'Pastor',
    'Função pastoral com atuação em programações autorizadas.'
WHERE NOT EXISTS (
    SELECT 1
    FROM funcoes
    WHERE nome = 'Pastor'
      AND departamento_id IS NULL
);

SET @funcao_professor_infantil_id = (
    SELECT id
    FROM funcoes
    WHERE nome = 'Professor Infantil'
      AND departamento_id = @departamento_infantil_id
    LIMIT 1
);

SET @funcao_auxiliar_infantil_id = (
    SELECT id
    FROM funcoes
    WHERE nome = 'Auxiliar Infantil'
      AND departamento_id = @departamento_infantil_id
    LIMIT 1
);

SET @funcao_pastor_id = (
    SELECT id
    FROM funcoes
    WHERE nome = 'Pastor'
      AND departamento_id IS NULL
    LIMIT 1
);

-- ============================================================
-- 4. TIPO DE PROGRAMAÇÃO: CULTO INFANTIL
-- ============================================================
INSERT INTO tipos_programacao (nome, descricao)
SELECT
    'Culto Infantil',
    'Programação voltada ao departamento infantil.'
WHERE NOT EXISTS (
    SELECT 1
    FROM tipos_programacao
    WHERE nome = 'Culto Infantil'
);

SET @tipo_culto_infantil_id = (
    SELECT id
    FROM tipos_programacao
    WHERE nome = 'Culto Infantil'
    LIMIT 1
);

-- ============================================================
-- 5. FUNÇÕES AUTORIZADAS PARA O CULTO INFANTIL
-- Esta tabela representa a relação central de elegibilidade.
-- ============================================================
INSERT IGNORE INTO funcoes_tipos_programacao (
    funcao_id,
    tipo_programacao_id
)
VALUES
    (@funcao_professor_infantil_id, @tipo_culto_infantil_id),
    (@funcao_auxiliar_infantil_id, @tipo_culto_infantil_id),
    (@funcao_pastor_id, @tipo_culto_infantil_id);

-- ============================================================
-- 6. LOCAL: SALA INFANTIL
-- ============================================================
INSERT INTO locais (nome, descricao, capacidade)
SELECT
    'Sala Infantil',
    'Sala destinada às programações do departamento infantil.',
    40
WHERE NOT EXISTS (
    SELECT 1
    FROM locais
    WHERE nome = 'Sala Infantil'
);

SET @local_sala_infantil_id = (
    SELECT id
    FROM locais
    WHERE nome = 'Sala Infantil'
    LIMIT 1
);

-- ============================================================
-- 7. USUÁRIOS DE TESTE
-- Senha de DESENVOLVIMENTO para os três usuários: 123456
-- O valor abaixo é um hash criado com password_hash() do PHP.
-- Nunca devemos usar esta senha em produção.
-- ============================================================
SET @senha_teste_hash = '$2y$12$nv9JxPfEPsmK22UHDjxPb.Nfflw2/EMyOjFXs2hpz4Tgbld5huEBa';

-- Administrador
INSERT INTO usuarios (
    papel_id,
    nome,
    email,
    senha_hash,
    status
)
VALUES (
    1,
    'Administrador SYN',
    'admin@syn.local',
    @senha_teste_hash,
    'ATIVO'
)
ON DUPLICATE KEY UPDATE
    papel_id = VALUES(papel_id),
    nome = VALUES(nome),
    senha_hash = VALUES(senha_hash),
    status = 'ATIVO';

-- Organizador
INSERT INTO usuarios (
    papel_id,
    nome,
    email,
    senha_hash,
    status
)
VALUES (
    2,
    'Organizador SYN',
    'organizador@syn.local',
    @senha_teste_hash,
    'ATIVO'
)
ON DUPLICATE KEY UPDATE
    papel_id = VALUES(papel_id),
    nome = VALUES(nome),
    senha_hash = VALUES(senha_hash),
    status = 'ATIVO';

-- Maria: papel Membro
INSERT INTO usuarios (
    papel_id,
    nome,
    data_nascimento,
    telefone,
    email,
    senha_hash,
    status
)
VALUES (
    3,
    'Maria',
    '1990-05-15',
    '(16) 99999-0000',
    'maria@syn.local',
    @senha_teste_hash,
    'ATIVO'
)
ON DUPLICATE KEY UPDATE
    papel_id = VALUES(papel_id),
    nome = VALUES(nome),
    data_nascimento = VALUES(data_nascimento),
    telefone = VALUES(telefone),
    senha_hash = VALUES(senha_hash),
    status = 'ATIVO';

SET @organizador_id = (
    SELECT id
    FROM usuarios
    WHERE email = 'organizador@syn.local'
    LIMIT 1
);

SET @maria_id = (
    SELECT id
    FROM usuarios
    WHERE email = 'maria@syn.local'
    LIMIT 1
);

-- ============================================================
-- 8. FUNÇÃO ATUAL DE MARIA
-- Maria possui a função Professor Infantil.
-- ============================================================
INSERT IGNORE INTO usuarios_funcoes (
    usuario_id,
    funcao_id
)
VALUES (
    @maria_id,
    @funcao_professor_infantil_id
);

-- ============================================================
-- 9. PROGRAMAÇÃO CONCRETA
-- Cenário de aceitação: Culto Infantil em 06/09/2026 às 10h,
-- realizado na Sala Infantil.
-- ============================================================
INSERT INTO programacoes (
    serie_id,
    tipo_programacao_id,
    local_id,
    organizador_id,
    titulo,
    descricao,
    inicio_em,
    fim_em,
    status,
    permite_resposta,
    tipo_programacao_nome_historico,
    local_nome_historico,
    organizador_nome_historico
)
SELECT
    NULL,
    @tipo_culto_infantil_id,
    @local_sala_infantil_id,
    @organizador_id,
    'Culto Infantil',
    'Programação utilizada para validar o cenário de aceitação da versão 1.0.',
    '2026-09-06 10:00:00',
    '2026-09-06 11:30:00',
    'AGENDADA',
    1,
    'Culto Infantil',
    'Sala Infantil',
    'Organizador SYN'
WHERE NOT EXISTS (
    SELECT 1
    FROM programacoes
    WHERE titulo = 'Culto Infantil'
      AND inicio_em = '2026-09-06 10:00:00'
      AND local_id = @local_sala_infantil_id
);

SET @programacao_culto_infantil_id = (
    SELECT id
    FROM programacoes
    WHERE titulo = 'Culto Infantil'
      AND inicio_em = '2026-09-06 10:00:00'
      AND local_id = @local_sala_infantil_id
    LIMIT 1
);

-- ============================================================
-- 10. ESCALA / PARTICIPAÇÃO DE MARIA
-- A função efetivamente exercida é copiada para os campos
-- históricos. Isso protege o passado contra mudanças futuras.
-- ============================================================
INSERT IGNORE INTO participacoes (
    programacao_id,
    usuario_id,
    funcao_id,
    status,
    usuario_nome_historico,
    funcao_nome_historico,
    departamento_nome_historico
)
VALUES (
    @programacao_culto_infantil_id,
    @maria_id,
    @funcao_professor_infantil_id,
    'ESCALADO',
    'Maria',
    'Professor Infantil',
    'Infantil'
);

COMMIT;

-- ============================================================
-- CONSULTAS DE VERIFICAÇÃO
-- Depois da execução, estas consultas mostram o cenário criado.
-- ============================================================

-- Papéis fixos do sistema
SELECT id, codigo, nome
FROM papeis
ORDER BY id;

-- Usuários e seus papéis
SELECT
    u.id,
    u.nome AS usuario,
    p.nome AS papel,
    u.email,
    u.status
FROM usuarios u
INNER JOIN papeis p ON p.id = u.papel_id
ORDER BY u.id;

-- Funções atuais de Maria
SELECT
    u.nome AS usuario,
    f.nome AS funcao,
    d.nome AS departamento
FROM usuarios_funcoes uf
INNER JOIN usuarios u ON u.id = uf.usuario_id
INNER JOIN funcoes f ON f.id = uf.funcao_id
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE u.email = 'maria@syn.local';

-- Funções autorizadas para Culto Infantil
SELECT
    tp.nome AS tipo_programacao,
    f.nome AS funcao
FROM funcoes_tipos_programacao ftp
INNER JOIN funcoes f ON f.id = ftp.funcao_id
INNER JOIN tipos_programacao tp ON tp.id = ftp.tipo_programacao_id
WHERE tp.nome = 'Culto Infantil'
ORDER BY f.nome;

-- Compromissos de Maria
SELECT
    p.id AS participacao_id,
    u.nome AS usuario_atual,
    pr.titulo AS programacao,
    pr.inicio_em,
    pr.fim_em,
    p.funcao_nome_historico AS funcao,
    pr.local_nome_historico AS local,
    p.status
FROM participacoes p
INNER JOIN usuarios u ON u.id = p.usuario_id
INNER JOIN programacoes pr ON pr.id = p.programacao_id
WHERE u.email = 'maria@syn.local'
ORDER BY pr.inicio_em;
