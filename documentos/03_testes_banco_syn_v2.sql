-- ============================================================
-- SYN - Testes do banco de dados v1.0 - VERSAO 2
-- Arquivo: 03_testes_banco_syn_v2.sql
--
-- CORRECAO IMPORTANTE:
-- Todas as tabelas sao referenciadas com o nome do banco (syn.tabela).
-- Assim, os testes funcionam mesmo que o phpMyAdmin esteja com
-- information_schema ou outro banco selecionado na interface.
-- ============================================================

-- ============================================================
-- TESTE 0 - Verificar se o banco SYN existe e listar as tabelas
-- Esperado: o banco 'syn' existe e possui 13 tabelas.
-- ============================================================
SELECT SCHEMA_NAME AS banco
FROM information_schema.SCHEMATA
WHERE SCHEMA_NAME = 'syn';

SHOW TABLES FROM syn;

-- ============================================================
-- TESTE 1 - Quantidade de tabelas do schema SYN
-- Esperado: 13
-- ============================================================
SELECT COUNT(*) AS quantidade_tabelas
FROM information_schema.tables
WHERE table_schema = 'syn';

-- ============================================================
-- TESTE 2 - Papeis fixos
-- Esperado: ADMINISTRADOR, ORGANIZADOR e MEMBRO
-- ============================================================
SELECT id, codigo, nome
FROM syn.papeis
ORDER BY id;

-- ============================================================
-- TESTE 3 - Cadastro institucional unico
-- Esperado: somente 1 registro
-- ============================================================
SELECT COUNT(*) AS quantidade_igrejas
FROM syn.igreja;

SELECT id, nome, cidade, estado, email
FROM syn.igreja;

-- ============================================================
-- TESTE 4 - Usuarios e papeis
-- ============================================================
SELECT
    u.id,
    u.nome,
    u.email,
    p.nome AS papel,
    u.status
FROM syn.usuarios u
INNER JOIN syn.papeis p ON p.id = u.papel_id
ORDER BY u.id;

-- ============================================================
-- TESTE 5 - Funcoes atuais dos usuarios
-- Demonstra a relacao N:N usuarios_funcoes.
-- ============================================================
SELECT
    u.nome AS usuario,
    f.nome AS funcao,
    d.nome AS departamento
FROM syn.usuarios_funcoes uf
INNER JOIN syn.usuarios u ON u.id = uf.usuario_id
INNER JOIN syn.funcoes f ON f.id = uf.funcao_id
LEFT JOIN syn.departamentos d ON d.id = f.departamento_id
ORDER BY u.nome, f.nome;

-- ============================================================
-- TESTE 6 - Elegibilidade para um tipo de programacao
-- Retorna usuarios ATIVOS cujas funcoes atuais estao autorizadas
-- para o tipo "Culto Infantil".
-- Esperado no cenario inicial: Maria / Professor Infantil.
-- ============================================================
SELECT DISTINCT
    u.id AS usuario_id,
    u.nome AS usuario,
    f.id AS funcao_id,
    f.nome AS funcao,
    tp.nome AS tipo_programacao
FROM syn.usuarios u
INNER JOIN syn.usuarios_funcoes uf
    ON uf.usuario_id = u.id
INNER JOIN syn.funcoes f
    ON f.id = uf.funcao_id
INNER JOIN syn.funcoes_tipos_programacao ftp
    ON ftp.funcao_id = f.id
INNER JOIN syn.tipos_programacao tp
    ON tp.id = ftp.tipo_programacao_id
WHERE u.status = 'ATIVO'
  AND f.ativo = 1
  AND tp.ativo = 1
  AND tp.nome = 'Culto Infantil'
ORDER BY u.nome, f.nome;

-- ============================================================
-- TESTE 7 - Consulta que servira de base para "Minha Semana"
-- Intervalo fixo para tornar o teste reproduzivel.
-- Esperado: compromisso de Maria em 06/09/2026.
-- ============================================================
SELECT
    p.id AS participacao_id,
    p.usuario_nome_historico AS usuario,
    pr.titulo AS programacao,
    pr.inicio_em,
    pr.fim_em,
    p.funcao_nome_historico AS funcao,
    pr.local_nome_historico AS local,
    p.status
FROM syn.participacoes p
INNER JOIN syn.programacoes pr
    ON pr.id = p.programacao_id
INNER JOIN syn.usuarios u
    ON u.id = p.usuario_id
WHERE u.email = 'maria@syn.local'
  AND pr.inicio_em >= '2026-08-31 00:00:00'
  AND pr.inicio_em <  '2026-09-07 00:00:00'
  AND pr.status <> 'CANCELADA'
ORDER BY pr.inicio_em;

-- ============================================================
-- TESTE 8 - Deteccao de conflito de local/horario
-- Verifica se ja existe uma programacao na Sala Infantil que
-- sobreponha o periodo 06/09/2026 10:30-11:00.
-- Esperado: encontrar o Culto Infantil das 10:00-11:30.
--
-- Regra de sobreposicao:
-- existente.inicio < novo.fim
-- E existente.fim > novo.inicio
-- ============================================================
SELECT
    pr.id,
    pr.titulo,
    pr.inicio_em,
    pr.fim_em,
    pr.local_nome_historico
FROM syn.programacoes pr
INNER JOIN syn.locais l ON l.id = pr.local_id
WHERE l.nome = 'Sala Infantil'
  AND pr.status <> 'CANCELADA'
  AND pr.inicio_em < '2026-09-06 11:00:00'
  AND pr.fim_em    > '2026-09-06 10:30:00';

-- ============================================================
-- TESTE 9 - Preservacao historica ao remover uma funcao ATUAL
-- Usa transacao e termina com ROLLBACK: nada fica alterado.
-- ============================================================
START TRANSACTION;

SET @teste_maria_id = (
    SELECT id
    FROM syn.usuarios
    WHERE email = 'maria@syn.local'
    LIMIT 1
);

SET @teste_professor_id = (
    SELECT id
    FROM syn.funcoes
    WHERE nome = 'Professor Infantil'
    LIMIT 1
);

DELETE FROM syn.usuarios_funcoes
WHERE usuario_id = @teste_maria_id
  AND funcao_id = @teste_professor_id;

-- Esperado: 0 linhas para a funcao atual de Maria.
SELECT
    u.nome AS usuario,
    f.nome AS funcao_atual
FROM syn.usuarios_funcoes uf
INNER JOIN syn.usuarios u ON u.id = uf.usuario_id
INNER JOIN syn.funcoes f ON f.id = uf.funcao_id
WHERE u.id = @teste_maria_id
  AND f.id = @teste_professor_id;

-- Esperado: o historico continua mostrando Maria como
-- Professor Infantil na programacao.
SELECT
    p.usuario_nome_historico,
    p.funcao_nome_historico,
    pr.titulo,
    pr.inicio_em,
    p.status
FROM syn.participacoes p
INNER JOIN syn.programacoes pr ON pr.id = p.programacao_id
WHERE p.usuario_id = @teste_maria_id
ORDER BY pr.inicio_em;

ROLLBACK;

-- ============================================================
-- TESTE 10 - Desativacao preserva historico
-- Usa ROLLBACK para nao alterar os dados de desenvolvimento.
-- ============================================================
START TRANSACTION;

UPDATE syn.usuarios
SET status = 'INATIVO',
    desativado_em = NOW()
WHERE email = 'maria@syn.local';

-- Esperado: Maria aparece temporariamente como INATIVO.
SELECT id, nome, email, status, desativado_em
FROM syn.usuarios
WHERE email = 'maria@syn.local';

-- Esperado: participacao historica continua existindo.
SELECT
    p.usuario_nome_historico,
    p.funcao_nome_historico,
    pr.titulo,
    pr.inicio_em
FROM syn.participacoes p
INNER JOIN syn.programacoes pr ON pr.id = p.programacao_id
WHERE p.usuario_id = @teste_maria_id;

ROLLBACK;

-- ============================================================
-- TESTE 11 - Integridade referencial
-- Usuario com participacao nao deve ser apagado fisicamente.
--
-- O comando abaixo fica COMENTADO porque o erro de FK e esperado
-- e pode interromper a execucao do arquivo no phpMyAdmin.
-- ============================================================
-- DELETE FROM syn.usuarios
-- WHERE email = 'maria@syn.local';
--
-- Esperado:
-- ERROR 1451: Cannot delete or update a parent row...

-- ============================================================
-- TESTE FINAL - Confirmar que os ROLLBACKS restauraram Maria
-- Esperado: status ATIVO e funcao Professor Infantil presente.
-- ============================================================
SELECT
    u.id,
    u.nome,
    u.status,
    f.nome AS funcao
FROM syn.usuarios u
LEFT JOIN syn.usuarios_funcoes uf ON uf.usuario_id = u.id
LEFT JOIN syn.funcoes f ON f.id = uf.funcao_id
WHERE u.email = 'maria@syn.local';

-- ============================================================
-- FIM DOS TESTES
-- ============================================================
