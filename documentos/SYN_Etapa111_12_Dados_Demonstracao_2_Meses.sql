-- ============================================================
-- SYN - ETAPA 111.12
-- MASSA DE DADOS DE DEMONSTRAÇÃO - 2 MESES
-- Instalação: Igreja Filadélfia
-- Período: 09/09/2026 a 08/11/2026
-- Banco: syn_ipfiladelfia
--
-- Este arquivo foi preparado para ser importado pelo phpMyAdmin.
--
-- IMPORTANTE:
-- 1. Faça um backup do banco antes da importação.
-- 2. O script PRESERVA o Administrador real já existente.
-- 3. Os usuários criados usam e-mails fictícios @demo.syn.local.
-- 4. As senhas desses usuários são aleatórias e não foram preservadas.
--    Portanto, ninguém consegue entrar nessas contas por adivinhação.
--    O Administrador pode redefinir uma senha posteriormente, se quiser
--    demonstrar a visão de um Membro ou Organizador.
-- 5. O script foi pensado para uma base de produção recém-instalada.
-- ============================================================

USE syn_ipfiladelfia;

SET NAMES utf8mb4;
SET time_zone = '-03:00';

START TRANSACTION;



-- ============================================================
-- 1. DEPARTAMENTOS
-- ============================================================


INSERT INTO departamentos (nome, descricao, ativo)
SELECT 'Departamento Infantil', 'Atendimento e programação voltados às crianças.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM departamentos WHERE nome = 'Departamento Infantil'
);

INSERT INTO departamentos (nome, descricao, ativo)
SELECT 'Departamento de Adolescentes', 'Integração, discipulado e atividades com adolescentes.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM departamentos WHERE nome = 'Departamento de Adolescentes'
);

INSERT INTO departamentos (nome, descricao, ativo)
SELECT 'Departamento de Jovens', 'Programações, discipulado e integração dos jovens.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM departamentos WHERE nome = 'Departamento de Jovens'
);

INSERT INTO departamentos (nome, descricao, ativo)
SELECT 'Departamento de Mulheres', 'Atividades e ações do ministério feminino.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM departamentos WHERE nome = 'Departamento de Mulheres'
);

INSERT INTO departamentos (nome, descricao, ativo)
SELECT 'Departamento do Coral', 'Coral da igreja, ensaios e participações especiais.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM departamentos WHERE nome = 'Departamento do Coral'
);

INSERT INTO departamentos (nome, descricao, ativo)
SELECT 'Departamento da Orquestra', 'Orquestra da igreja e atividades musicais instrumentais.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM departamentos WHERE nome = 'Departamento da Orquestra'
);

INSERT INTO departamentos (nome, descricao, ativo)
SELECT 'Ministério de Louvor', 'Equipe responsável pelo louvor congregacional.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM departamentos WHERE nome = 'Ministério de Louvor'
);

INSERT INTO departamentos (nome, descricao, ativo)
SELECT 'Ensino / EBD', 'Escola Bíblica Dominical, professores e ensino cristão.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM departamentos WHERE nome = 'Ensino / EBD'
);

INSERT INTO departamentos (nome, descricao, ativo)
SELECT 'Departamento de Homens', 'Encontros, discipulado e atividades com homens.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM departamentos WHERE nome = 'Departamento de Homens'
);

INSERT INTO departamentos (nome, descricao, ativo)
SELECT 'Conselho da Igreja', 'Conselho, reuniões administrativas e acompanhamento institucional.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM departamentos WHERE nome = 'Conselho da Igreja'
);


-- ============================================================
-- 2. FUNÇÕES
-- ============================================================


INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT NULL, 'Pastor / Dirigente', 'Condução pastoral e direção de cultos.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM funcoes
    WHERE nome = 'Pastor / Dirigente' AND departamento_id IS NULL
);

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT NULL, 'Pregador', 'Ministração da Palavra.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM funcoes
    WHERE nome = 'Pregador' AND departamento_id IS NULL
);

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Líder de Louvor', 'Coordena a equipe de louvor.', 1
FROM departamentos d
WHERE d.nome = 'Ministério de Louvor'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Líder de Louvor' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Vocalista', 'Participação vocal no louvor.', 1
FROM departamentos d
WHERE d.nome = 'Ministério de Louvor'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Vocalista' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Tecladista', 'Instrumentista de teclas.', 1
FROM departamentos d
WHERE d.nome = 'Ministério de Louvor'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Tecladista' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Guitarrista', 'Instrumentista de guitarra.', 1
FROM departamentos d
WHERE d.nome = 'Ministério de Louvor'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Guitarrista' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Baixista', 'Instrumentista de contrabaixo.', 1
FROM departamentos d
WHERE d.nome = 'Ministério de Louvor'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Baixista' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Baterista', 'Instrumentista de bateria.', 1
FROM departamentos d
WHERE d.nome = 'Ministério de Louvor'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Baterista' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Sonoplasta', 'Operação do sistema de sonorização.', 1
FROM departamentos d
WHERE d.nome = 'Ministério de Louvor'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Sonoplasta' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Multimídia / Projeção', 'Projeção, mídia e apoio audiovisual.', 1
FROM departamentos d
WHERE d.nome = 'Ministério de Louvor'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Multimídia / Projeção' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT NULL, 'Recepção', 'Recepção e acolhimento.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM funcoes
    WHERE nome = 'Recepção' AND departamento_id IS NULL
);

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT NULL, 'Diácono', 'Apoio à organização e ao culto.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM funcoes
    WHERE nome = 'Diácono' AND departamento_id IS NULL
);

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Coordenador Infantil', 'Coordenação do ministério infantil.', 1
FROM departamentos d
WHERE d.nome = 'Departamento Infantil'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Coordenador Infantil' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Professor Infantil', 'Ministração das atividades infantis.', 1
FROM departamentos d
WHERE d.nome = 'Departamento Infantil'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Professor Infantil' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Auxiliar Infantil', 'Apoio à equipe infantil.', 1
FROM departamentos d
WHERE d.nome = 'Departamento Infantil'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Auxiliar Infantil' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Líder de Adolescentes', 'Coordenação dos adolescentes.', 1
FROM departamentos d
WHERE d.nome = 'Departamento de Adolescentes'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Líder de Adolescentes' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Líder de Jovens', 'Coordenação dos jovens.', 1
FROM departamentos d
WHERE d.nome = 'Departamento de Jovens'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Líder de Jovens' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Líder do Ministério Feminino', 'Coordenação das mulheres.', 1
FROM departamentos d
WHERE d.nome = 'Departamento de Mulheres'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Líder do Ministério Feminino' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Regente do Coral', 'Regência do coral.', 1
FROM departamentos d
WHERE d.nome = 'Departamento do Coral'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Regente do Coral' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Coralista', 'Integrante do coral.', 1
FROM departamentos d
WHERE d.nome = 'Departamento do Coral'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Coralista' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Maestro da Orquestra', 'Regência da orquestra.', 1
FROM departamentos d
WHERE d.nome = 'Departamento da Orquestra'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Maestro da Orquestra' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Instrumentista da Orquestra', 'Integrante instrumental da orquestra.', 1
FROM departamentos d
WHERE d.nome = 'Departamento da Orquestra'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Instrumentista da Orquestra' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Superintendente da EBD', 'Coordenação da Escola Bíblica Dominical.', 1
FROM departamentos d
WHERE d.nome = 'Ensino / EBD'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Superintendente da EBD' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Professor da EBD', 'Professor de classe da Escola Bíblica Dominical.', 1
FROM departamentos d
WHERE d.nome = 'Ensino / EBD'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Professor da EBD' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Líder de Homens', 'Coordenação do departamento de homens.', 1
FROM departamentos d
WHERE d.nome = 'Departamento de Homens'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Líder de Homens' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Conselheiro', 'Participação no conselho da igreja.', 1
FROM departamentos d
WHERE d.nome = 'Conselho da Igreja'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Conselheiro' AND f.departamento_id = d.id
  );

INSERT INTO funcoes (departamento_id, nome, descricao, ativo)
SELECT d.id, 'Secretário do Conselho', 'Registro e apoio administrativo ao conselho.', 1
FROM departamentos d
WHERE d.nome = 'Conselho da Igreja'
  AND NOT EXISTS (
      SELECT 1 FROM funcoes f
      WHERE f.nome = 'Secretário do Conselho' AND f.departamento_id = d.id
  );


-- ============================================================
-- 3. LOCAIS
-- ============================================================


INSERT INTO locais (nome, descricao, capacidade, ativo)
SELECT 'Santuário', 'Templo principal da igreja.', 450, 1
WHERE NOT EXISTS (
    SELECT 1 FROM locais WHERE nome = 'Santuário'
);

INSERT INTO locais (nome, descricao, capacidade, ativo)
SELECT 'Departamento Infantil', 'Espaço destinado ao culto e atividades infantis.', 90, 1
WHERE NOT EXISTS (
    SELECT 1 FROM locais WHERE nome = 'Departamento Infantil'
);

INSERT INTO locais (nome, descricao, capacidade, ativo)
SELECT 'Sala dos Adolescentes', 'Sala de encontros e discipulado dos adolescentes.', 55, 1
WHERE NOT EXISTS (
    SELECT 1 FROM locais WHERE nome = 'Sala dos Adolescentes'
);

INSERT INTO locais (nome, descricao, capacidade, ativo)
SELECT 'Salão dos Jovens', 'Espaço de encontros do departamento de jovens.', 100, 1
WHERE NOT EXISTS (
    SELECT 1 FROM locais WHERE nome = 'Salão dos Jovens'
);

INSERT INTO locais (nome, descricao, capacidade, ativo)
SELECT 'Sala do Coral', 'Sala de ensaio do coral.', 70, 1
WHERE NOT EXISTS (
    SELECT 1 FROM locais WHERE nome = 'Sala do Coral'
);

INSERT INTO locais (nome, descricao, capacidade, ativo)
SELECT 'Sala da Orquestra', 'Sala de ensaio e preparação da orquestra.', 65, 1
WHERE NOT EXISTS (
    SELECT 1 FROM locais WHERE nome = 'Sala da Orquestra'
);

INSERT INTO locais (nome, descricao, capacidade, ativo)
SELECT 'Salas de EBD', 'Conjunto de salas utilizadas pela Escola Bíblica Dominical.', 180, 1
WHERE NOT EXISTS (
    SELECT 1 FROM locais WHERE nome = 'Salas de EBD'
);

INSERT INTO locais (nome, descricao, capacidade, ativo)
SELECT 'Salão Social', 'Salão multiuso para encontros e confraternizações.', 220, 1
WHERE NOT EXISTS (
    SELECT 1 FROM locais WHERE nome = 'Salão Social'
);

INSERT INTO locais (nome, descricao, capacidade, ativo)
SELECT 'Sala do Conselho', 'Sala reservada para reuniões administrativas.', 24, 1
WHERE NOT EXISTS (
    SELECT 1 FROM locais WHERE nome = 'Sala do Conselho'
);

INSERT INTO locais (nome, descricao, capacidade, ativo)
SELECT 'Praça Central', 'Local externo utilizado em ações de evangelismo.', 300, 1
WHERE NOT EXISTS (
    SELECT 1 FROM locais WHERE nome = 'Praça Central'
);

INSERT INTO locais (nome, descricao, capacidade, ativo)
SELECT 'Chácara Recanto', 'Local externo para retiros, acampamentos e encontros.', 160, 1
WHERE NOT EXISTS (
    SELECT 1 FROM locais WHERE nome = 'Chácara Recanto'
);

INSERT INTO locais (nome, descricao, capacidade, ativo)
SELECT 'Destino da Viagem do Ministério Feminino', 'Local externo da viagem do ministério feminino.', 80, 1
WHERE NOT EXISTS (
    SELECT 1 FROM locais WHERE nome = 'Destino da Viagem do Ministério Feminino'
);


-- ============================================================
-- 4. TIPOS DE PROGRAMAÇÃO
-- ============================================================


INSERT INTO tipos_programacao (nome, descricao, ativo)
SELECT 'Culto', 'Cultos regulares e celebrações da igreja.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tipos_programacao WHERE nome = 'Culto'
);

INSERT INTO tipos_programacao (nome, descricao, ativo)
SELECT 'Culto Infantil', 'Culto simultâneo voltado às crianças.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tipos_programacao WHERE nome = 'Culto Infantil'
);

INSERT INTO tipos_programacao (nome, descricao, ativo)
SELECT 'Escola Bíblica Dominical (EBD)', 'Classes e atividades da Escola Bíblica Dominical.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tipos_programacao WHERE nome = 'Escola Bíblica Dominical (EBD)'
);

INSERT INTO tipos_programacao (nome, descricao, ativo)
SELECT 'Estudo Bíblico', 'Encontro semanal para estudo das Escrituras.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tipos_programacao WHERE nome = 'Estudo Bíblico'
);

INSERT INTO tipos_programacao (nome, descricao, ativo)
SELECT 'Ensaio do Grupo de Louvor', 'Ensaio semanal da equipe de louvor.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tipos_programacao WHERE nome = 'Ensaio do Grupo de Louvor'
);

INSERT INTO tipos_programacao (nome, descricao, ativo)
SELECT 'Ensaio do Coral', 'Ensaio semanal do coral.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tipos_programacao WHERE nome = 'Ensaio do Coral'
);

INSERT INTO tipos_programacao (nome, descricao, ativo)
SELECT 'Ensaio da Orquestra', 'Ensaio semanal da orquestra.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tipos_programacao WHERE nome = 'Ensaio da Orquestra'
);

INSERT INTO tipos_programacao (nome, descricao, ativo)
SELECT 'Encontro de Jovens', 'Encontros e programações do departamento de jovens.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tipos_programacao WHERE nome = 'Encontro de Jovens'
);

INSERT INTO tipos_programacao (nome, descricao, ativo)
SELECT 'Evangelismo com Amigos', 'Ação de evangelismo e integração.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tipos_programacao WHERE nome = 'Evangelismo com Amigos'
);

INSERT INTO tipos_programacao (nome, descricao, ativo)
SELECT 'Encontro da Família', 'Programação voltada às famílias.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tipos_programacao WHERE nome = 'Encontro da Família'
);

INSERT INTO tipos_programacao (nome, descricao, ativo)
SELECT 'Reunião do Conselho', 'Reunião administrativa fechada do conselho.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tipos_programacao WHERE nome = 'Reunião do Conselho'
);

INSERT INTO tipos_programacao (nome, descricao, ativo)
SELECT 'Encontro de Homens', 'Programação do departamento de homens.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tipos_programacao WHERE nome = 'Encontro de Homens'
);

INSERT INTO tipos_programacao (nome, descricao, ativo)
SELECT 'Viagem do Ministério Feminino', 'Viagem e programação externa das mulheres.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tipos_programacao WHERE nome = 'Viagem do Ministério Feminino'
);

INSERT INTO tipos_programacao (nome, descricao, ativo)
SELECT 'Acampamento', 'Retiro ou acampamento de departamentos da igreja.', 1
WHERE NOT EXISTS (
    SELECT 1 FROM tipos_programacao WHERE nome = 'Acampamento'
);


-- ============================================================
-- 5. RESPONSÁVEIS / ORGANIZADORES E MEMBROS
-- ============================================================


INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Carlos Henrique Mendes', '1978-10-14', NULL, 'carlos.mendes@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'ORGANIZADOR'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'carlos.mendes@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Rafael Augusto Souza', '1987-09-22', NULL, 'rafael.souza@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'ORGANIZADOR'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'rafael.souza@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Mariana Costa Alves', '1989-10-05', NULL, 'mariana.alves@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'ORGANIZADOR'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'mariana.alves@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Lucas Ferreira Rocha', '1993-11-02', NULL, 'lucas.rocha@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'ORGANIZADOR'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'lucas.rocha@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Juliana Martins Oliveira', '1985-09-28', NULL, 'juliana.oliveira@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'ORGANIZADOR'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'juliana.oliveira@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Roberto Lima Nunes', '1975-10-30', NULL, 'roberto.nunes@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'ORGANIZADOR'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'roberto.nunes@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'André Monteiro Silva', '1982-09-16', NULL, 'andre.monteiro@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'ORGANIZADOR'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'andre.monteiro@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Cláudia Barros Teixeira', '1984-10-19', NULL, 'claudia.barros@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'ORGANIZADOR'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'claudia.barros@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Ana Paula Ribeiro', '1994-09-15', NULL, 'ana.ribeiro@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'ana.ribeiro@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Beatriz Santos Lima', '1998-10-03', NULL, 'beatriz.lima@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'beatriz.lima@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Bruno Carvalho Dias', '1990-11-06', NULL, 'bruno.dias@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'bruno.dias@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Camila Fernandes Melo', '1992-09-25', NULL, 'camila.melo@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'camila.melo@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Daniel Almeida Costa', '1988-10-11', NULL, 'daniel.costa@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'daniel.costa@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Eduardo Batista Rocha', '1981-09-30', NULL, 'eduardo.rocha@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'eduardo.rocha@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Felipe Gomes Martins', '1996-10-22', NULL, 'felipe.martins@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'felipe.martins@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Gabriela Moreira Alves', '1995-09-12', NULL, 'gabriela.alves@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'gabriela.alves@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Helena Barros Nunes', '1979-10-08', NULL, 'helena.nunes@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'helena.nunes@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Igor Martins Ribeiro', '2000-11-01', NULL, 'igor.ribeiro@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'igor.ribeiro@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Isabela Rocha Santos', '1999-09-19', NULL, 'isabela.santos@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'isabela.santos@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'João Pedro Lima', '1997-10-17', NULL, 'joao.lima@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'joao.lima@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Larissa Costa Freitas', '2001-09-27', NULL, 'larissa.freitas@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'larissa.freitas@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Marcelo Nunes Andrade', '1986-10-24', NULL, 'marcelo.andrade@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'marcelo.andrade@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Natália Oliveira Moraes', '1991-09-10', NULL, 'natalia.moraes@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'natalia.moraes@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Otávio Pereira Silva', '1983-11-04', NULL, 'otavio.silva@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'otavio.silva@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Patrícia Azevedo Gomes', '1987-10-01', NULL, 'patricia.gomes@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'patricia.gomes@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Renato Silva Cardoso', '1989-09-21', NULL, 'renato.cardoso@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'renato.cardoso@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Sabrina Melo Souza', '2002-10-13', NULL, 'sabrina.souza@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'sabrina.souza@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Thiago Barbosa Reis', '1993-09-18', NULL, 'thiago.reis@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'thiago.reis@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Valéria Freitas Lopes', '1980-10-27', NULL, 'valeria.lopes@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'valeria.lopes@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Vinícius Andrade Ramos', '1998-09-24', NULL, 'vinicius.ramos@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'vinicius.ramos@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Wesley Moraes Castro', '1990-10-09', NULL, 'wesley.castro@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'wesley.castro@demo.syn.local');

INSERT INTO usuarios (papel_id, nome, data_nascimento, telefone, email, senha_hash, status)
SELECT p.id, 'Yasmin Teixeira Lima', '2003-11-05', NULL, 'yasmin.lima@demo.syn.local', '$2y$12$QIKz4rvuGFDxildonmJFDeZPkF930Ar.O8lMKUVnU507ot3qszERa', 'ATIVO'
FROM papeis p
WHERE p.codigo = 'MEMBRO'
  AND NOT EXISTS (SELECT 1 FROM usuarios WHERE email = 'yasmin.lima@demo.syn.local');


-- ============================================================
-- 6. ATRIBUIÇÕES DE FUNÇÕES AOS USUÁRIOS
-- ============================================================


INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Pastor / Dirigente'
WHERE u.email = 'carlos.mendes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Pregador'
WHERE u.email = 'carlos.mendes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Conselheiro'
WHERE u.email = 'carlos.mendes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Líder de Louvor'
WHERE u.email = 'rafael.souza@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Guitarrista'
WHERE u.email = 'rafael.souza@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Coordenador Infantil'
WHERE u.email = 'mariana.alves@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Professor Infantil'
WHERE u.email = 'mariana.alves@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Líder de Jovens'
WHERE u.email = 'lucas.rocha@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Líder de Adolescentes'
WHERE u.email = 'lucas.rocha@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Líder do Ministério Feminino'
WHERE u.email = 'juliana.oliveira@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Superintendente da EBD'
WHERE u.email = 'roberto.nunes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Professor da EBD'
WHERE u.email = 'roberto.nunes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Líder de Homens'
WHERE u.email = 'roberto.nunes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Conselheiro'
WHERE u.email = 'roberto.nunes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Maestro da Orquestra'
WHERE u.email = 'andre.monteiro@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
WHERE u.email = 'andre.monteiro@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Regente do Coral'
WHERE u.email = 'claudia.barros@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Coralista'
WHERE u.email = 'claudia.barros@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Vocalista'
WHERE u.email = 'ana.ribeiro@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Coralista'
WHERE u.email = 'ana.ribeiro@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Professor Infantil'
WHERE u.email = 'beatriz.lima@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Auxiliar Infantil'
WHERE u.email = 'beatriz.lima@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Baixista'
WHERE u.email = 'bruno.dias@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
WHERE u.email = 'bruno.dias@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Coralista'
WHERE u.email = 'camila.melo@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Baterista'
WHERE u.email = 'daniel.costa@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Diácono'
WHERE u.email = 'eduardo.rocha@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Recepção'
WHERE u.email = 'eduardo.rocha@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Sonoplasta'
WHERE u.email = 'felipe.martins@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Tecladista'
WHERE u.email = 'gabriela.alves@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Coralista'
WHERE u.email = 'gabriela.alves@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Professor da EBD'
WHERE u.email = 'helena.nunes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Auxiliar Infantil'
WHERE u.email = 'igor.ribeiro@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Vocalista'
WHERE u.email = 'isabela.santos@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Coralista'
WHERE u.email = 'isabela.santos@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Guitarrista'
WHERE u.email = 'joao.lima@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Líder de Jovens'
WHERE u.email = 'joao.lima@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
WHERE u.email = 'larissa.freitas@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
WHERE u.email = 'marcelo.andrade@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Coralista'
WHERE u.email = 'natalia.moraes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Líder do Ministério Feminino'
WHERE u.email = 'natalia.moraes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Recepção'
WHERE u.email = 'otavio.silva@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Diácono'
WHERE u.email = 'otavio.silva@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Professor da EBD'
WHERE u.email = 'patricia.gomes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Auxiliar Infantil'
WHERE u.email = 'patricia.gomes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Baixista'
WHERE u.email = 'renato.cardoso@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
WHERE u.email = 'renato.cardoso@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Líder de Jovens'
WHERE u.email = 'sabrina.souza@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Líder de Adolescentes'
WHERE u.email = 'sabrina.souza@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Baterista'
WHERE u.email = 'thiago.reis@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
WHERE u.email = 'thiago.reis@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Recepção'
WHERE u.email = 'valeria.lopes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Líder do Ministério Feminino'
WHERE u.email = 'valeria.lopes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
WHERE u.email = 'vinicius.ramos@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Coralista'
WHERE u.email = 'wesley.castro@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Professor Infantil'
WHERE u.email = 'yasmin.lima@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO usuarios_funcoes (usuario_id, funcao_id)
SELECT u.id, f.id
FROM usuarios u
JOIN funcoes f ON f.nome = 'Líder de Jovens'
WHERE u.email = 'yasmin.lima@demo.syn.local'
LIMIT 1;


-- ============================================================
-- 7. FUNÇÕES PERMITIDAS POR TIPO DE PROGRAMAÇÃO
-- ============================================================


INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Culto'
WHERE f.nome = 'Pastor / Dirigente'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Culto'
WHERE f.nome = 'Pregador'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Culto'
WHERE f.nome = 'Líder de Louvor'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Culto'
WHERE f.nome = 'Vocalista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Culto'
WHERE f.nome = 'Tecladista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Culto'
WHERE f.nome = 'Guitarrista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Culto'
WHERE f.nome = 'Baixista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Culto'
WHERE f.nome = 'Baterista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Culto'
WHERE f.nome = 'Sonoplasta'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Culto'
WHERE f.nome = 'Multimídia / Projeção'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Culto'
WHERE f.nome = 'Recepção'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Culto'
WHERE f.nome = 'Diácono'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Culto'
WHERE f.nome = 'Coralista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Culto'
WHERE f.nome = 'Instrumentista da Orquestra'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Culto Infantil'
WHERE f.nome = 'Coordenador Infantil'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Culto Infantil'
WHERE f.nome = 'Professor Infantil'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Culto Infantil'
WHERE f.nome = 'Auxiliar Infantil'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Culto Infantil'
WHERE f.nome = 'Sonoplasta'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Escola Bíblica Dominical (EBD)'
WHERE f.nome = 'Superintendente da EBD'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Escola Bíblica Dominical (EBD)'
WHERE f.nome = 'Professor da EBD'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Escola Bíblica Dominical (EBD)'
WHERE f.nome = 'Recepção'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Estudo Bíblico'
WHERE f.nome = 'Pastor / Dirigente'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Estudo Bíblico'
WHERE f.nome = 'Pregador'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Estudo Bíblico'
WHERE f.nome = 'Sonoplasta'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Estudo Bíblico'
WHERE f.nome = 'Multimídia / Projeção'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Ensaio do Grupo de Louvor'
WHERE f.nome = 'Líder de Louvor'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Ensaio do Grupo de Louvor'
WHERE f.nome = 'Vocalista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Ensaio do Grupo de Louvor'
WHERE f.nome = 'Tecladista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Ensaio do Grupo de Louvor'
WHERE f.nome = 'Guitarrista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Ensaio do Grupo de Louvor'
WHERE f.nome = 'Baixista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Ensaio do Grupo de Louvor'
WHERE f.nome = 'Baterista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Ensaio do Grupo de Louvor'
WHERE f.nome = 'Sonoplasta'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Ensaio do Coral'
WHERE f.nome = 'Regente do Coral'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Ensaio do Coral'
WHERE f.nome = 'Coralista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Ensaio do Coral'
WHERE f.nome = 'Tecladista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Ensaio da Orquestra'
WHERE f.nome = 'Maestro da Orquestra'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Ensaio da Orquestra'
WHERE f.nome = 'Instrumentista da Orquestra'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Encontro de Jovens'
WHERE f.nome = 'Líder de Jovens'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Encontro de Jovens'
WHERE f.nome = 'Líder de Louvor'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Encontro de Jovens'
WHERE f.nome = 'Vocalista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Encontro de Jovens'
WHERE f.nome = 'Tecladista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Encontro de Jovens'
WHERE f.nome = 'Guitarrista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Encontro de Jovens'
WHERE f.nome = 'Baixista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Encontro de Jovens'
WHERE f.nome = 'Baterista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Encontro de Jovens'
WHERE f.nome = 'Sonoplasta'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Encontro de Jovens'
WHERE f.nome = 'Multimídia / Projeção'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Evangelismo com Amigos'
WHERE f.nome = 'Pastor / Dirigente'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Evangelismo com Amigos'
WHERE f.nome = 'Líder de Jovens'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Evangelismo com Amigos'
WHERE f.nome = 'Diácono'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Evangelismo com Amigos'
WHERE f.nome = 'Recepção'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Evangelismo com Amigos'
WHERE f.nome = 'Líder de Louvor'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Evangelismo com Amigos'
WHERE f.nome = 'Vocalista'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Encontro da Família'
WHERE f.nome = 'Pastor / Dirigente'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Encontro da Família'
WHERE f.nome = 'Líder de Homens'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Encontro da Família'
WHERE f.nome = 'Líder do Ministério Feminino'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Encontro da Família'
WHERE f.nome = 'Líder de Jovens'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Encontro da Família'
WHERE f.nome = 'Coordenador Infantil'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Encontro da Família'
WHERE f.nome = 'Recepção'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Reunião do Conselho'
WHERE f.nome = 'Pastor / Dirigente'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Reunião do Conselho'
WHERE f.nome = 'Conselheiro'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Reunião do Conselho'
WHERE f.nome = 'Secretário do Conselho'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Encontro de Homens'
WHERE f.nome = 'Pastor / Dirigente'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Encontro de Homens'
WHERE f.nome = 'Líder de Homens'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Encontro de Homens'
WHERE f.nome = 'Diácono'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Viagem do Ministério Feminino'
WHERE f.nome = 'Líder do Ministério Feminino'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Viagem do Ministério Feminino'
WHERE f.nome = 'Recepção'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Acampamento'
WHERE f.nome = 'Líder de Jovens'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Acampamento'
WHERE f.nome = 'Líder de Adolescentes'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Acampamento'
WHERE f.nome = 'Pastor / Dirigente'
LIMIT 1;

INSERT IGNORE INTO funcoes_tipos_programacao (funcao_id, tipo_programacao_id)
SELECT f.id, tp.id
FROM funcoes f
JOIN tipos_programacao tp ON tp.nome = 'Acampamento'
WHERE f.nome = 'Coordenador Infantil'
LIMIT 1;


-- ============================================================
-- 8. PERMISSÕES DOS ORGANIZADORES
-- ============================================================


INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Culto'
WHERE u.email = 'carlos.mendes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Estudo Bíblico'
WHERE u.email = 'carlos.mendes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Encontro da Família'
WHERE u.email = 'carlos.mendes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Evangelismo com Amigos'
WHERE u.email = 'carlos.mendes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Reunião do Conselho'
WHERE u.email = 'carlos.mendes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Acampamento'
WHERE u.email = 'carlos.mendes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Ensaio do Grupo de Louvor'
WHERE u.email = 'rafael.souza@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Culto'
WHERE u.email = 'rafael.souza@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Encontro de Jovens'
WHERE u.email = 'rafael.souza@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Culto Infantil'
WHERE u.email = 'mariana.alves@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Encontro da Família'
WHERE u.email = 'mariana.alves@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Encontro de Jovens'
WHERE u.email = 'lucas.rocha@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Acampamento'
WHERE u.email = 'lucas.rocha@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Evangelismo com Amigos'
WHERE u.email = 'lucas.rocha@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Viagem do Ministério Feminino'
WHERE u.email = 'juliana.oliveira@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Encontro da Família'
WHERE u.email = 'juliana.oliveira@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Escola Bíblica Dominical (EBD)'
WHERE u.email = 'roberto.nunes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Encontro de Homens'
WHERE u.email = 'roberto.nunes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Reunião do Conselho'
WHERE u.email = 'roberto.nunes@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Ensaio da Orquestra'
WHERE u.email = 'andre.monteiro@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Culto'
WHERE u.email = 'andre.monteiro@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Ensaio do Coral'
WHERE u.email = 'claudia.barros@demo.syn.local'
LIMIT 1;

INSERT IGNORE INTO organizadores_tipos_programacao (usuario_id, tipo_programacao_id)
SELECT u.id, tp.id
FROM usuarios u
JOIN tipos_programacao tp ON tp.nome = 'Culto'
WHERE u.email = 'claudia.barros@demo.syn.local'
LIMIT 1;


-- Permissão especial para o organizador geral aprovar novos cadastros.
INSERT IGNORE INTO usuarios_permissoes_especiais (usuario_id, permissao_id)
SELECT u.id, p.id
FROM usuarios u
JOIN permissoes_especiais p ON p.codigo = 'CADASTROS_APROVAR'
WHERE u.email = 'carlos.mendes@demo.syn.local'
LIMIT 1;

-- Permissão especial da responsável pelo Infantil para necessidades específicas.
INSERT IGNORE INTO usuarios_permissoes_especiais (usuario_id, permissao_id)
SELECT u.id, p.id
FROM usuarios u
JOIN permissoes_especiais p ON p.codigo = 'NECESSIDADES_ESPECIFICAS_GERENCIAR'
WHERE u.email = 'mariana.alves@demo.syn.local'
LIMIT 1;



-- Exemplo de necessidade específica sem diagnóstico médico.
INSERT INTO necessidades_especificas (usuario_id, observacao, ativo)
SELECT u.id, 'Prefere acesso pela entrada lateral sem escadas em atividades realizadas no salão social.', 1
FROM usuarios u
WHERE u.email = 'valeria.lopes@demo.syn.local'
  AND NOT EXISTS (
      SELECT 1 FROM necessidades_especificas ne WHERE ne.usuario_id = u.id
  );



-- ============================================================
-- 9. SÉRIES DE PROGRAMAÇÃO RECORRENTE
-- ============================================================


INSERT INTO series_programacao (
    tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, inicio_base, fim_base,
    regra_recorrencia, data_limite, ativa
)
SELECT tp.id, l.id, u.id,
       'Estudo Bíblico - Quarta-feira', 'Encontro semanal de estudo bíblico e aplicação prática.', '2026-09-09 19:00:00', '2026-09-09 20:30:00',
       '{"frequencia":"SEMANAL","intervalo_semanas":1,"permite_resposta":true}',
       '2026-11-08', 1
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
WHERE tp.nome = 'Estudo Bíblico'
  AND NOT EXISTS (
      SELECT 1 FROM series_programacao s
      WHERE s.titulo = 'Estudo Bíblico - Quarta-feira'
        AND s.inicio_base = '2026-09-09 19:00:00'
  );

INSERT INTO series_programacao (
    tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, inicio_base, fim_base,
    regra_recorrencia, data_limite, ativa
)
SELECT tp.id, l.id, u.id,
       'Ensaio do Grupo de Louvor', 'Preparação musical e técnica para os cultos do fim de semana.', '2026-09-10 19:00:00', '2026-09-10 21:00:00',
       '{"frequencia":"SEMANAL","intervalo_semanas":1,"permite_resposta":true}',
       '2026-11-08', 1
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
WHERE tp.nome = 'Ensaio do Grupo de Louvor'
  AND NOT EXISTS (
      SELECT 1 FROM series_programacao s
      WHERE s.titulo = 'Ensaio do Grupo de Louvor'
        AND s.inicio_base = '2026-09-10 19:00:00'
  );

INSERT INTO series_programacao (
    tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, inicio_base, fim_base,
    regra_recorrencia, data_limite, ativa
)
SELECT tp.id, l.id, u.id,
       'Escola Bíblica Dominical (EBD)', 'Classes bíblicas dominicais para diferentes faixas etárias.', '2026-09-13 09:00:00', '2026-09-13 10:15:00',
       '{"frequencia":"SEMANAL","intervalo_semanas":1,"permite_resposta":true}',
       '2026-11-08', 1
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Salas de EBD'
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
WHERE tp.nome = 'Escola Bíblica Dominical (EBD)'
  AND NOT EXISTS (
      SELECT 1 FROM series_programacao s
      WHERE s.titulo = 'Escola Bíblica Dominical (EBD)'
        AND s.inicio_base = '2026-09-13 09:00:00'
  );

INSERT INTO series_programacao (
    tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, inicio_base, fim_base,
    regra_recorrencia, data_limite, ativa
)
SELECT tp.id, l.id, u.id,
       'Ensaio do Coral', 'Ensaio semanal do coral antes do culto noturno.', '2026-09-13 17:30:00', '2026-09-13 18:30:00',
       '{"frequencia":"SEMANAL","intervalo_semanas":1,"permite_resposta":true}',
       '2026-11-08', 1
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala do Coral'
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
WHERE tp.nome = 'Ensaio do Coral'
  AND NOT EXISTS (
      SELECT 1 FROM series_programacao s
      WHERE s.titulo = 'Ensaio do Coral'
        AND s.inicio_base = '2026-09-13 17:30:00'
  );

INSERT INTO series_programacao (
    tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, inicio_base, fim_base,
    regra_recorrencia, data_limite, ativa
)
SELECT tp.id, l.id, u.id,
       'Ensaio da Orquestra', 'Preparação e passagem musical da orquestra.', '2026-09-13 18:30:00', '2026-09-13 19:00:00',
       '{"frequencia":"SEMANAL","intervalo_semanas":1,"permite_resposta":true}',
       '2026-11-08', 1
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala da Orquestra'
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
WHERE tp.nome = 'Ensaio da Orquestra'
  AND NOT EXISTS (
      SELECT 1 FROM series_programacao s
      WHERE s.titulo = 'Ensaio da Orquestra'
        AND s.inicio_base = '2026-09-13 18:30:00'
  );

INSERT INTO series_programacao (
    tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, inicio_base, fim_base,
    regra_recorrencia, data_limite, ativa
)
SELECT tp.id, l.id, u.id,
       'Culto de Celebração', 'Culto dominical da igreja.', '2026-09-13 19:00:00', '2026-09-13 21:00:00',
       '{"frequencia":"SEMANAL","intervalo_semanas":1,"permite_resposta":true}',
       '2026-11-08', 1
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
WHERE tp.nome = 'Culto'
  AND NOT EXISTS (
      SELECT 1 FROM series_programacao s
      WHERE s.titulo = 'Culto de Celebração'
        AND s.inicio_base = '2026-09-13 19:00:00'
  );

INSERT INTO series_programacao (
    tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, inicio_base, fim_base,
    regra_recorrencia, data_limite, ativa
)
SELECT tp.id, l.id, u.id,
       'Culto Infantil', 'Culto infantil realizado simultaneamente ao culto no santuário.', '2026-09-13 19:00:00', '2026-09-13 20:30:00',
       '{"frequencia":"SEMANAL","intervalo_semanas":1,"permite_resposta":true}',
       '2026-11-08', 1
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Departamento Infantil'
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
WHERE tp.nome = 'Culto Infantil'
  AND NOT EXISTS (
      SELECT 1 FROM series_programacao s
      WHERE s.titulo = 'Culto Infantil'
        AND s.inicio_base = '2026-09-13 19:00:00'
  );


-- ============================================================
-- 10. PROGRAMAÇÃO RECORRENTE MATERIALIZADA (2 MESES)
-- ============================================================


INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Estudo Bíblico - Quarta-feira', 'Estudo bíblico semanal com exposição das Escrituras, oração e aplicação prática.', 'Uma noite de estudo bíblico, comunhão e crescimento na Palavra.',
       '2026-09-09 19:00:00', '2026-09-09 20:30:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Estudo Bíblico - Quarta-feira'
WHERE tp.nome = 'Estudo Bíblico'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Estudo Bíblico - Quarta-feira')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
        AND p.inicio_em = '2026-09-09 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Estudo Bíblico - Quarta-feira', 'Estudo bíblico semanal com exposição das Escrituras, oração e aplicação prática.', 'Uma noite de estudo bíblico, comunhão e crescimento na Palavra.',
       '2026-09-16 19:00:00', '2026-09-16 20:30:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Estudo Bíblico - Quarta-feira'
WHERE tp.nome = 'Estudo Bíblico'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Estudo Bíblico - Quarta-feira')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
        AND p.inicio_em = '2026-09-16 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Estudo Bíblico - Quarta-feira', 'Estudo bíblico semanal com exposição das Escrituras, oração e aplicação prática.', 'Uma noite de estudo bíblico, comunhão e crescimento na Palavra.',
       '2026-09-23 19:00:00', '2026-09-23 20:30:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Estudo Bíblico - Quarta-feira'
WHERE tp.nome = 'Estudo Bíblico'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Estudo Bíblico - Quarta-feira')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
        AND p.inicio_em = '2026-09-23 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Estudo Bíblico - Quarta-feira', 'Estudo bíblico semanal com exposição das Escrituras, oração e aplicação prática.', 'Uma noite de estudo bíblico, comunhão e crescimento na Palavra.',
       '2026-09-30 19:00:00', '2026-09-30 20:30:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Estudo Bíblico - Quarta-feira'
WHERE tp.nome = 'Estudo Bíblico'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Estudo Bíblico - Quarta-feira')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
        AND p.inicio_em = '2026-09-30 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Estudo Bíblico - Quarta-feira', 'Estudo bíblico semanal com exposição das Escrituras, oração e aplicação prática.', 'Uma noite de estudo bíblico, comunhão e crescimento na Palavra.',
       '2026-10-07 19:00:00', '2026-10-07 20:30:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Estudo Bíblico - Quarta-feira'
WHERE tp.nome = 'Estudo Bíblico'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Estudo Bíblico - Quarta-feira')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
        AND p.inicio_em = '2026-10-07 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Estudo Bíblico - Quarta-feira', 'Estudo bíblico semanal com exposição das Escrituras, oração e aplicação prática.', 'Uma noite de estudo bíblico, comunhão e crescimento na Palavra.',
       '2026-10-14 19:00:00', '2026-10-14 20:30:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Estudo Bíblico - Quarta-feira'
WHERE tp.nome = 'Estudo Bíblico'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Estudo Bíblico - Quarta-feira')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
        AND p.inicio_em = '2026-10-14 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Estudo Bíblico - Quarta-feira', 'Estudo bíblico semanal com exposição das Escrituras, oração e aplicação prática.', 'Uma noite de estudo bíblico, comunhão e crescimento na Palavra.',
       '2026-10-21 19:00:00', '2026-10-21 20:30:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Estudo Bíblico - Quarta-feira'
WHERE tp.nome = 'Estudo Bíblico'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Estudo Bíblico - Quarta-feira')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
        AND p.inicio_em = '2026-10-21 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Estudo Bíblico - Quarta-feira', 'Estudo bíblico semanal com exposição das Escrituras, oração e aplicação prática.', 'Uma noite de estudo bíblico, comunhão e crescimento na Palavra.',
       '2026-10-28 19:00:00', '2026-10-28 20:30:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Estudo Bíblico - Quarta-feira'
WHERE tp.nome = 'Estudo Bíblico'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Estudo Bíblico - Quarta-feira')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
        AND p.inicio_em = '2026-10-28 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Estudo Bíblico - Quarta-feira', 'Estudo bíblico semanal com exposição das Escrituras, oração e aplicação prática.', 'Uma noite de estudo bíblico, comunhão e crescimento na Palavra.',
       '2026-11-04 19:00:00', '2026-11-04 20:30:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Estudo Bíblico - Quarta-feira'
WHERE tp.nome = 'Estudo Bíblico'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Estudo Bíblico - Quarta-feira')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
        AND p.inicio_em = '2026-11-04 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio do Grupo de Louvor', 'Ensaio interno do ministério de louvor: repertório, passagem de som e alinhamento.', NULL,
       '2026-09-10 19:00:00', '2026-09-10 21:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio do Grupo de Louvor'
WHERE tp.nome = 'Ensaio do Grupo de Louvor'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio do Grupo de Louvor')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio do Grupo de Louvor'
        AND p.inicio_em = '2026-09-10 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio do Grupo de Louvor', 'Ensaio interno do ministério de louvor: repertório, passagem de som e alinhamento.', NULL,
       '2026-09-17 19:00:00', '2026-09-17 21:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio do Grupo de Louvor'
WHERE tp.nome = 'Ensaio do Grupo de Louvor'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio do Grupo de Louvor')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio do Grupo de Louvor'
        AND p.inicio_em = '2026-09-17 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio do Grupo de Louvor', 'Ensaio interno do ministério de louvor: repertório, passagem de som e alinhamento.', NULL,
       '2026-09-24 19:00:00', '2026-09-24 21:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio do Grupo de Louvor'
WHERE tp.nome = 'Ensaio do Grupo de Louvor'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio do Grupo de Louvor')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio do Grupo de Louvor'
        AND p.inicio_em = '2026-09-24 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio do Grupo de Louvor', 'Ensaio interno do ministério de louvor: repertório, passagem de som e alinhamento.', NULL,
       '2026-10-01 19:00:00', '2026-10-01 21:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio do Grupo de Louvor'
WHERE tp.nome = 'Ensaio do Grupo de Louvor'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio do Grupo de Louvor')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio do Grupo de Louvor'
        AND p.inicio_em = '2026-10-01 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio do Grupo de Louvor', 'Ensaio interno do ministério de louvor: repertório, passagem de som e alinhamento.', NULL,
       '2026-10-08 19:00:00', '2026-10-08 21:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio do Grupo de Louvor'
WHERE tp.nome = 'Ensaio do Grupo de Louvor'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio do Grupo de Louvor')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio do Grupo de Louvor'
        AND p.inicio_em = '2026-10-08 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio do Grupo de Louvor', 'Ensaio interno do ministério de louvor: repertório, passagem de som e alinhamento.', NULL,
       '2026-10-15 19:00:00', '2026-10-15 21:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio do Grupo de Louvor'
WHERE tp.nome = 'Ensaio do Grupo de Louvor'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio do Grupo de Louvor')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio do Grupo de Louvor'
        AND p.inicio_em = '2026-10-15 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio do Grupo de Louvor', 'Ensaio interno do ministério de louvor: repertório, passagem de som e alinhamento.', NULL,
       '2026-10-22 19:00:00', '2026-10-22 21:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio do Grupo de Louvor'
WHERE tp.nome = 'Ensaio do Grupo de Louvor'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio do Grupo de Louvor')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio do Grupo de Louvor'
        AND p.inicio_em = '2026-10-22 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio do Grupo de Louvor', 'Ensaio interno do ministério de louvor: repertório, passagem de som e alinhamento.', NULL,
       '2026-10-29 19:00:00', '2026-10-29 21:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio do Grupo de Louvor'
WHERE tp.nome = 'Ensaio do Grupo de Louvor'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio do Grupo de Louvor')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio do Grupo de Louvor'
        AND p.inicio_em = '2026-10-29 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio do Grupo de Louvor', 'Ensaio interno do ministério de louvor: repertório, passagem de som e alinhamento.', NULL,
       '2026-11-05 19:00:00', '2026-11-05 21:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio do Grupo de Louvor'
WHERE tp.nome = 'Ensaio do Grupo de Louvor'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio do Grupo de Louvor')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio do Grupo de Louvor'
        AND p.inicio_em = '2026-11-05 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Escola Bíblica Dominical (EBD)', 'Classes de ensino bíblico para adultos, jovens, adolescentes e crianças.', 'Domingo pela manhã é tempo de aprender e crescer juntos na Palavra.',
       '2026-09-13 09:00:00', '2026-09-13 10:15:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Salas de EBD'
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Escola Bíblica Dominical (EBD)'
WHERE tp.nome = 'Escola Bíblica Dominical (EBD)'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Escola Bíblica Dominical (EBD)')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
        AND p.inicio_em = '2026-09-13 09:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio do Coral', 'Ensaio interno do coral e preparação das participações no culto.', NULL,
       '2026-09-13 17:30:00', '2026-09-13 18:30:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala do Coral'
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio do Coral'
WHERE tp.nome = 'Ensaio do Coral'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio do Coral')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio do Coral'
        AND p.inicio_em = '2026-09-13 17:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio da Orquestra', 'Passagem e preparação musical da orquestra antes do culto.', NULL,
       '2026-09-13 18:30:00', '2026-09-13 19:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala da Orquestra'
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio da Orquestra'
WHERE tp.nome = 'Ensaio da Orquestra'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio da Orquestra')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio da Orquestra'
        AND p.inicio_em = '2026-09-13 18:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Culto de Celebração', 'Culto dominical com louvor, oração, comunhão e mensagem bíblica.', 'Celebração dominical com louvor, oração e mensagem bíblica. Você é bem-vindo.',
       '2026-09-13 19:00:00', '2026-09-13 21:00:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Culto de Celebração'
WHERE tp.nome = 'Culto'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Culto de Celebração')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Culto de Celebração'
        AND p.inicio_em = '2026-09-13 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Culto Infantil', 'Programação infantil simultânea ao culto principal, em ambiente próprio.', 'Programação preparada especialmente para as crianças durante o culto das 19h.',
       '2026-09-13 19:00:00', '2026-09-13 20:30:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Departamento Infantil'
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Culto Infantil'
WHERE tp.nome = 'Culto Infantil'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Culto Infantil')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Culto Infantil'
        AND p.inicio_em = '2026-09-13 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Escola Bíblica Dominical (EBD)', 'Classes de ensino bíblico para adultos, jovens, adolescentes e crianças.', 'Domingo pela manhã é tempo de aprender e crescer juntos na Palavra.',
       '2026-09-20 09:00:00', '2026-09-20 10:15:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Salas de EBD'
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Escola Bíblica Dominical (EBD)'
WHERE tp.nome = 'Escola Bíblica Dominical (EBD)'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Escola Bíblica Dominical (EBD)')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
        AND p.inicio_em = '2026-09-20 09:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio do Coral', 'Ensaio interno do coral e preparação das participações no culto.', NULL,
       '2026-09-20 17:30:00', '2026-09-20 18:30:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala do Coral'
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio do Coral'
WHERE tp.nome = 'Ensaio do Coral'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio do Coral')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio do Coral'
        AND p.inicio_em = '2026-09-20 17:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio da Orquestra', 'Passagem e preparação musical da orquestra antes do culto.', NULL,
       '2026-09-20 18:30:00', '2026-09-20 19:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala da Orquestra'
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio da Orquestra'
WHERE tp.nome = 'Ensaio da Orquestra'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio da Orquestra')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio da Orquestra'
        AND p.inicio_em = '2026-09-20 18:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Culto de Celebração', 'Culto dominical com louvor, oração, comunhão e mensagem bíblica.', 'Celebração dominical com louvor, oração e mensagem bíblica. Você é bem-vindo.',
       '2026-09-20 19:00:00', '2026-09-20 21:00:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Culto de Celebração'
WHERE tp.nome = 'Culto'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Culto de Celebração')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Culto de Celebração'
        AND p.inicio_em = '2026-09-20 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Culto Infantil', 'Programação infantil simultânea ao culto principal, em ambiente próprio.', 'Programação preparada especialmente para as crianças durante o culto das 19h.',
       '2026-09-20 19:00:00', '2026-09-20 20:30:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Departamento Infantil'
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Culto Infantil'
WHERE tp.nome = 'Culto Infantil'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Culto Infantil')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Culto Infantil'
        AND p.inicio_em = '2026-09-20 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Escola Bíblica Dominical (EBD)', 'Classes de ensino bíblico para adultos, jovens, adolescentes e crianças.', 'Domingo pela manhã é tempo de aprender e crescer juntos na Palavra.',
       '2026-09-27 09:00:00', '2026-09-27 10:15:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Salas de EBD'
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Escola Bíblica Dominical (EBD)'
WHERE tp.nome = 'Escola Bíblica Dominical (EBD)'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Escola Bíblica Dominical (EBD)')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
        AND p.inicio_em = '2026-09-27 09:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio do Coral', 'Ensaio interno do coral e preparação das participações no culto.', NULL,
       '2026-09-27 17:30:00', '2026-09-27 18:30:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala do Coral'
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio do Coral'
WHERE tp.nome = 'Ensaio do Coral'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio do Coral')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio do Coral'
        AND p.inicio_em = '2026-09-27 17:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio da Orquestra', 'Passagem e preparação musical da orquestra antes do culto.', NULL,
       '2026-09-27 18:30:00', '2026-09-27 19:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala da Orquestra'
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio da Orquestra'
WHERE tp.nome = 'Ensaio da Orquestra'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio da Orquestra')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio da Orquestra'
        AND p.inicio_em = '2026-09-27 18:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Culto de Celebração', 'Culto dominical com louvor, oração, comunhão e mensagem bíblica.', 'Celebração dominical com louvor, oração e mensagem bíblica. Você é bem-vindo.',
       '2026-09-27 19:00:00', '2026-09-27 21:00:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Culto de Celebração'
WHERE tp.nome = 'Culto'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Culto de Celebração')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Culto de Celebração'
        AND p.inicio_em = '2026-09-27 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Culto Infantil', 'Programação infantil simultânea ao culto principal, em ambiente próprio.', 'Programação preparada especialmente para as crianças durante o culto das 19h.',
       '2026-09-27 19:00:00', '2026-09-27 20:30:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Departamento Infantil'
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Culto Infantil'
WHERE tp.nome = 'Culto Infantil'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Culto Infantil')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Culto Infantil'
        AND p.inicio_em = '2026-09-27 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Escola Bíblica Dominical (EBD)', 'Classes de ensino bíblico para adultos, jovens, adolescentes e crianças.', 'Domingo pela manhã é tempo de aprender e crescer juntos na Palavra.',
       '2026-10-04 09:00:00', '2026-10-04 10:15:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Salas de EBD'
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Escola Bíblica Dominical (EBD)'
WHERE tp.nome = 'Escola Bíblica Dominical (EBD)'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Escola Bíblica Dominical (EBD)')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
        AND p.inicio_em = '2026-10-04 09:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio do Coral', 'Ensaio interno do coral e preparação das participações no culto.', NULL,
       '2026-10-04 17:30:00', '2026-10-04 18:30:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala do Coral'
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio do Coral'
WHERE tp.nome = 'Ensaio do Coral'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio do Coral')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio do Coral'
        AND p.inicio_em = '2026-10-04 17:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio da Orquestra', 'Passagem e preparação musical da orquestra antes do culto.', NULL,
       '2026-10-04 18:30:00', '2026-10-04 19:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala da Orquestra'
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio da Orquestra'
WHERE tp.nome = 'Ensaio da Orquestra'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio da Orquestra')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio da Orquestra'
        AND p.inicio_em = '2026-10-04 18:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Culto de Ceia', 'Culto dominical com celebração da Ceia do Senhor.', 'Culto de Ceia com louvor, comunhão e mensagem bíblica.',
       '2026-10-04 19:00:00', '2026-10-04 21:00:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Culto de Celebração'
WHERE tp.nome = 'Culto'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Culto de Celebração')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Culto de Ceia'
        AND p.inicio_em = '2026-10-04 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Culto Infantil', 'Programação infantil simultânea ao culto principal, em ambiente próprio.', 'Programação preparada especialmente para as crianças durante o culto das 19h.',
       '2026-10-04 19:00:00', '2026-10-04 20:30:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Departamento Infantil'
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Culto Infantil'
WHERE tp.nome = 'Culto Infantil'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Culto Infantil')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Culto Infantil'
        AND p.inicio_em = '2026-10-04 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Escola Bíblica Dominical (EBD)', 'Classes de ensino bíblico para adultos, jovens, adolescentes e crianças.', 'Domingo pela manhã é tempo de aprender e crescer juntos na Palavra.',
       '2026-10-11 09:00:00', '2026-10-11 10:15:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Salas de EBD'
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Escola Bíblica Dominical (EBD)'
WHERE tp.nome = 'Escola Bíblica Dominical (EBD)'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Escola Bíblica Dominical (EBD)')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
        AND p.inicio_em = '2026-10-11 09:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio do Coral', 'Ensaio interno do coral e preparação das participações no culto.', NULL,
       '2026-10-11 17:30:00', '2026-10-11 18:30:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala do Coral'
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio do Coral'
WHERE tp.nome = 'Ensaio do Coral'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio do Coral')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio do Coral'
        AND p.inicio_em = '2026-10-11 17:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio da Orquestra', 'Passagem e preparação musical da orquestra antes do culto.', NULL,
       '2026-10-11 18:30:00', '2026-10-11 19:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala da Orquestra'
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio da Orquestra'
WHERE tp.nome = 'Ensaio da Orquestra'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio da Orquestra')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio da Orquestra'
        AND p.inicio_em = '2026-10-11 18:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Culto de Celebração', 'Culto dominical com louvor, oração, comunhão e mensagem bíblica.', 'Celebração dominical com louvor, oração e mensagem bíblica. Você é bem-vindo.',
       '2026-10-11 19:00:00', '2026-10-11 21:00:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Culto de Celebração'
WHERE tp.nome = 'Culto'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Culto de Celebração')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Culto de Celebração'
        AND p.inicio_em = '2026-10-11 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Culto Infantil', 'Programação infantil simultânea ao culto principal, em ambiente próprio.', 'Programação preparada especialmente para as crianças durante o culto das 19h.',
       '2026-10-11 19:00:00', '2026-10-11 20:30:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Departamento Infantil'
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Culto Infantil'
WHERE tp.nome = 'Culto Infantil'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Culto Infantil')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Culto Infantil'
        AND p.inicio_em = '2026-10-11 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Escola Bíblica Dominical (EBD)', 'Classes de ensino bíblico para adultos, jovens, adolescentes e crianças.', 'Domingo pela manhã é tempo de aprender e crescer juntos na Palavra.',
       '2026-10-18 09:00:00', '2026-10-18 10:15:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Salas de EBD'
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Escola Bíblica Dominical (EBD)'
WHERE tp.nome = 'Escola Bíblica Dominical (EBD)'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Escola Bíblica Dominical (EBD)')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
        AND p.inicio_em = '2026-10-18 09:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio do Coral', 'Ensaio interno do coral e preparação das participações no culto.', NULL,
       '2026-10-18 17:30:00', '2026-10-18 18:30:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala do Coral'
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio do Coral'
WHERE tp.nome = 'Ensaio do Coral'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio do Coral')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio do Coral'
        AND p.inicio_em = '2026-10-18 17:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio da Orquestra', 'Passagem e preparação musical da orquestra antes do culto.', NULL,
       '2026-10-18 18:30:00', '2026-10-18 19:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala da Orquestra'
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio da Orquestra'
WHERE tp.nome = 'Ensaio da Orquestra'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio da Orquestra')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio da Orquestra'
        AND p.inicio_em = '2026-10-18 18:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Culto de Celebração', 'Culto dominical com louvor, oração, comunhão e mensagem bíblica.', 'Celebração dominical com louvor, oração e mensagem bíblica. Você é bem-vindo.',
       '2026-10-18 19:00:00', '2026-10-18 21:00:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Culto de Celebração'
WHERE tp.nome = 'Culto'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Culto de Celebração')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Culto de Celebração'
        AND p.inicio_em = '2026-10-18 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Culto Infantil', 'Programação infantil simultânea ao culto principal, em ambiente próprio.', 'Programação preparada especialmente para as crianças durante o culto das 19h.',
       '2026-10-18 19:00:00', '2026-10-18 20:30:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Departamento Infantil'
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Culto Infantil'
WHERE tp.nome = 'Culto Infantil'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Culto Infantil')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Culto Infantil'
        AND p.inicio_em = '2026-10-18 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Escola Bíblica Dominical (EBD)', 'Classes de ensino bíblico para adultos, jovens, adolescentes e crianças.', 'Domingo pela manhã é tempo de aprender e crescer juntos na Palavra.',
       '2026-10-25 09:00:00', '2026-10-25 10:15:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Salas de EBD'
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Escola Bíblica Dominical (EBD)'
WHERE tp.nome = 'Escola Bíblica Dominical (EBD)'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Escola Bíblica Dominical (EBD)')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
        AND p.inicio_em = '2026-10-25 09:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio do Coral', 'Ensaio interno do coral e preparação das participações no culto.', NULL,
       '2026-10-25 17:30:00', '2026-10-25 18:30:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala do Coral'
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio do Coral'
WHERE tp.nome = 'Ensaio do Coral'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio do Coral')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio do Coral'
        AND p.inicio_em = '2026-10-25 17:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio da Orquestra', 'Passagem e preparação musical da orquestra antes do culto.', NULL,
       '2026-10-25 18:30:00', '2026-10-25 19:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala da Orquestra'
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio da Orquestra'
WHERE tp.nome = 'Ensaio da Orquestra'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio da Orquestra')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio da Orquestra'
        AND p.inicio_em = '2026-10-25 18:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Culto de Missões', 'Culto temático de missões e evangelização.', 'Uma noite especial dedicada a missões, louvor e mensagem bíblica.',
       '2026-10-25 19:00:00', '2026-10-25 21:00:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Culto de Celebração'
WHERE tp.nome = 'Culto'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Culto de Celebração')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Culto de Missões'
        AND p.inicio_em = '2026-10-25 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Culto Infantil', 'Programação infantil simultânea ao culto principal, em ambiente próprio.', 'Programação preparada especialmente para as crianças durante o culto das 19h.',
       '2026-10-25 19:00:00', '2026-10-25 20:30:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Departamento Infantil'
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Culto Infantil'
WHERE tp.nome = 'Culto Infantil'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Culto Infantil')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Culto Infantil'
        AND p.inicio_em = '2026-10-25 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Escola Bíblica Dominical (EBD)', 'Classes de ensino bíblico para adultos, jovens, adolescentes e crianças.', 'Domingo pela manhã é tempo de aprender e crescer juntos na Palavra.',
       '2026-11-01 09:00:00', '2026-11-01 10:15:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Salas de EBD'
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Escola Bíblica Dominical (EBD)'
WHERE tp.nome = 'Escola Bíblica Dominical (EBD)'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Escola Bíblica Dominical (EBD)')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
        AND p.inicio_em = '2026-11-01 09:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio do Coral', 'Ensaio interno do coral e preparação das participações no culto.', NULL,
       '2026-11-01 17:30:00', '2026-11-01 18:30:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala do Coral'
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio do Coral'
WHERE tp.nome = 'Ensaio do Coral'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio do Coral')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio do Coral'
        AND p.inicio_em = '2026-11-01 17:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio da Orquestra', 'Passagem e preparação musical da orquestra antes do culto.', NULL,
       '2026-11-01 18:30:00', '2026-11-01 19:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala da Orquestra'
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio da Orquestra'
WHERE tp.nome = 'Ensaio da Orquestra'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio da Orquestra')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio da Orquestra'
        AND p.inicio_em = '2026-11-01 18:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Culto de Ceia', 'Culto dominical com celebração da Ceia do Senhor.', 'Culto de Ceia com louvor, comunhão e mensagem bíblica.',
       '2026-11-01 19:00:00', '2026-11-01 21:00:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Culto de Celebração'
WHERE tp.nome = 'Culto'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Culto de Celebração')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Culto de Ceia'
        AND p.inicio_em = '2026-11-01 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Culto Infantil', 'Programação infantil simultânea ao culto principal, em ambiente próprio.', 'Programação preparada especialmente para as crianças durante o culto das 19h.',
       '2026-11-01 19:00:00', '2026-11-01 20:30:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Departamento Infantil'
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Culto Infantil'
WHERE tp.nome = 'Culto Infantil'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Culto Infantil')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Culto Infantil'
        AND p.inicio_em = '2026-11-01 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Escola Bíblica Dominical (EBD)', 'Classes de ensino bíblico para adultos, jovens, adolescentes e crianças.', 'Domingo pela manhã é tempo de aprender e crescer juntos na Palavra.',
       '2026-11-08 09:00:00', '2026-11-08 10:15:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Salas de EBD'
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Escola Bíblica Dominical (EBD)'
WHERE tp.nome = 'Escola Bíblica Dominical (EBD)'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Escola Bíblica Dominical (EBD)')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
        AND p.inicio_em = '2026-11-08 09:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio do Coral', 'Ensaio interno do coral e preparação das participações no culto.', NULL,
       '2026-11-08 17:30:00', '2026-11-08 18:30:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala do Coral'
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio do Coral'
WHERE tp.nome = 'Ensaio do Coral'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio do Coral')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio do Coral'
        AND p.inicio_em = '2026-11-08 17:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Ensaio da Orquestra', 'Passagem e preparação musical da orquestra antes do culto.', NULL,
       '2026-11-08 18:30:00', '2026-11-08 19:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala da Orquestra'
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Ensaio da Orquestra'
WHERE tp.nome = 'Ensaio da Orquestra'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Ensaio da Orquestra')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Ensaio da Orquestra'
        AND p.inicio_em = '2026-11-08 18:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Culto de Celebração', 'Culto dominical com louvor, oração, comunhão e mensagem bíblica.', 'Celebração dominical com louvor, oração e mensagem bíblica. Você é bem-vindo.',
       '2026-11-08 19:00:00', '2026-11-08 21:00:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Santuário'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Culto de Celebração'
WHERE tp.nome = 'Culto'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Culto de Celebração')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Culto de Celebração'
        AND p.inicio_em = '2026-11-08 19:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT s.id, tp.id, l.id, u.id,
       'Culto Infantil', 'Programação infantil simultânea ao culto principal, em ambiente próprio.', 'Programação preparada especialmente para as crianças durante o culto das 19h.',
       '2026-11-08 19:00:00', '2026-11-08 20:30:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Departamento Infantil'
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN series_programacao s ON s.titulo = 'Culto Infantil'
WHERE tp.nome = 'Culto Infantil'
 AND s.inicio_base = (SELECT MIN(s2.inicio_base) FROM series_programacao s2 WHERE s2.titulo = 'Culto Infantil')
  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Culto Infantil'
        AND p.inicio_em = '2026-11-08 19:00:00'
        AND p.local_id = l.id
  );


-- ============================================================
-- 11. PROGRAMAÇÕES AVULSAS
-- ============================================================


INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT NULL, tp.id, l.id, u.id,
       'Evangelismo com Amigos', 'Ação de evangelismo, música, acolhimento e conversa com a comunidade.', 'Uma tarde de música, amizade e esperança aberta a toda a comunidade.',
       '2026-09-12 15:00:00', '2026-09-12 18:00:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Praça Central'
JOIN usuarios u ON u.email = 'lucas.rocha@demo.syn.local'

WHERE tp.nome = 'Evangelismo com Amigos'

  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Evangelismo com Amigos'
        AND p.inicio_em = '2026-09-12 15:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT NULL, tp.id, l.id, u.id,
       'Encontro de Jovens - Conexão', 'Noite de integração dos jovens com louvor, dinâmica e reflexão.', 'Uma noite de conexão, amizade, louvor e reflexão para jovens.',
       '2026-09-19 19:30:00', '2026-09-19 22:00:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Salão dos Jovens'
JOIN usuarios u ON u.email = 'lucas.rocha@demo.syn.local'

WHERE tp.nome = 'Encontro de Jovens'

  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Encontro de Jovens - Conexão'
        AND p.inicio_em = '2026-09-19 19:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT NULL, tp.id, l.id, u.id,
       'Encontro da Família', 'Programação para famílias com atividades, reflexão e confraternização.', 'Uma tarde especial para fortalecer vínculos, comunhão e vida em família.',
       '2026-09-26 16:00:00', '2026-09-26 21:00:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Salão Social'
JOIN usuarios u ON u.email = 'juliana.oliveira@demo.syn.local'

WHERE tp.nome = 'Encontro da Família'

  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Encontro da Família'
        AND p.inicio_em = '2026-09-26 16:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT NULL, tp.id, l.id, u.id,
       'Acampamento de Adolescentes', 'Fim de semana de integração, estudos, atividades esportivas e momentos de comunhão.', 'Acampamento de adolescentes com atividades, comunhão e aprendizado.',
       '2026-10-02 18:00:00', '2026-10-04 14:00:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Chácara Recanto'
JOIN usuarios u ON u.email = 'lucas.rocha@demo.syn.local'

WHERE tp.nome = 'Acampamento'

  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Acampamento de Adolescentes'
        AND p.inicio_em = '2026-10-02 18:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT NULL, tp.id, l.id, u.id,
       'Viagem do Ministério Feminino', 'Viagem de integração e participação em encontro regional do ministério feminino.', NULL,
       '2026-10-10 06:30:00', '2026-10-10 21:00:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Destino da Viagem do Ministério Feminino'
JOIN usuarios u ON u.email = 'juliana.oliveira@demo.syn.local'

WHERE tp.nome = 'Viagem do Ministério Feminino'

  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Viagem do Ministério Feminino'
        AND p.inicio_em = '2026-10-10 06:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT NULL, tp.id, l.id, u.id,
       'Reunião do Conselho', 'Reunião fechada para avaliação, planejamento e decisões administrativas.', NULL,
       '2026-10-12 19:30:00', '2026-10-12 21:30:00', 'AGENDADA', 1, 'INTERNA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Sala do Conselho'
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'

WHERE tp.nome = 'Reunião do Conselho'

  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Reunião do Conselho'
        AND p.inicio_em = '2026-10-12 19:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT NULL, tp.id, l.id, u.id,
       'Encontro de Homens', 'Noite de comunhão, conversa e reflexão para homens.', 'Encontro de homens com comunhão, reflexão e amizade.',
       '2026-10-17 19:30:00', '2026-10-17 22:00:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Salão Social'
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'

WHERE tp.nome = 'Encontro de Homens'

  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Encontro de Homens'
        AND p.inicio_em = '2026-10-17 19:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT NULL, tp.id, l.id, u.id,
       'Encontro de Jovens - Propósito', 'Encontro mensal dos jovens com louvor e reflexão sobre propósito.', 'Jovens reunidos para uma noite de louvor, amizade e propósito.',
       '2026-10-24 19:30:00', '2026-10-24 22:00:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Salão dos Jovens'
JOIN usuarios u ON u.email = 'lucas.rocha@demo.syn.local'

WHERE tp.nome = 'Encontro de Jovens'

  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Encontro de Jovens - Propósito'
        AND p.inicio_em = '2026-10-24 19:30:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT NULL, tp.id, l.id, u.id,
       'Evangelismo com Amigos', 'Ação de evangelismo e integração com a comunidade.', 'Uma tarde aberta à comunidade com música, amizade e esperança.',
       '2026-10-31 15:00:00', '2026-10-31 18:00:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Praça Central'
JOIN usuarios u ON u.email = 'lucas.rocha@demo.syn.local'

WHERE tp.nome = 'Evangelismo com Amigos'

  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Evangelismo com Amigos'
        AND p.inicio_em = '2026-10-31 15:00:00'
        AND p.local_id = l.id
  );

INSERT INTO programacoes (
    serie_id, tipo_programacao_id, local_id, organizador_id,
    titulo, descricao, descricao_publica,
    inicio_em, fim_em, status, permite_resposta, visibilidade,
    tipo_programacao_nome_historico, local_nome_historico, organizador_nome_historico
)
SELECT NULL, tp.id, l.id, u.id,
       'Noite da Família', 'Encontro de encerramento do ciclo de dois meses com famílias da igreja.', 'Uma noite especial de comunhão e fortalecimento das famílias.',
       '2026-11-07 18:00:00', '2026-11-07 21:00:00', 'AGENDADA', 1, 'PUBLICA',
       tp.nome, l.nome, u.nome
FROM tipos_programacao tp
JOIN locais l ON l.nome = 'Salão Social'
JOIN usuarios u ON u.email = 'juliana.oliveira@demo.syn.local'

WHERE tp.nome = 'Encontro da Família'

  AND NOT EXISTS (
      SELECT 1 FROM programacoes p
      WHERE p.titulo = 'Noite da Família'
        AND p.inicio_em = '2026-11-07 18:00:00'
        AND p.local_id = l.id
  );


-- ============================================================
-- 12. ESCALAS / PARTICIPAÇÕES
-- ============================================================


INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pregador'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-09-09 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-09-09 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-09-09 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pregador'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-09-16 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-09-16 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-09-16 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pregador'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-09-23 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-09-23 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-09-23 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pregador'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-09-30 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-09-30 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-09-30 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pregador'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-10-07 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-10-07 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-10-07 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pregador'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-10-14 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-10-14 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-10-14 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pregador'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-10-21 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-10-21 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-10-21 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pregador'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-10-28 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-10-28 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-10-28 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pregador'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-11-04 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-11-04 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Estudo Bíblico - Quarta-feira'
  AND p.inicio_em = '2026-11-04 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-10 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-10 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-10 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-10 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'joao.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Guitarrista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-10 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baixista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-10 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'daniel.costa@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baterista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-10 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-10 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-17 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-17 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-17 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-17 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'joao.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Guitarrista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-17 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baixista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-17 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'daniel.costa@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baterista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-17 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-17 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-24 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-24 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-24 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-24 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'joao.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Guitarrista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-24 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baixista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-24 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'daniel.costa@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baterista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-24 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-09-24 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'joao.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Guitarrista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baixista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'daniel.costa@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baterista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'joao.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Guitarrista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baixista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'daniel.costa@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baterista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-15 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-15 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-15 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-15 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'joao.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Guitarrista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-15 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baixista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-15 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'daniel.costa@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baterista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-15 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-15 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-22 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-22 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-22 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-22 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'joao.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Guitarrista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-22 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baixista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-22 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'daniel.costa@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baterista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-22 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-22 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-29 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-29 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-29 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-29 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'joao.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Guitarrista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-29 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baixista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-29 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'daniel.costa@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baterista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-29 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-10-29 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-11-05 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-11-05 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-11-05 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-11-05 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'joao.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Guitarrista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-11-05 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baixista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-11-05 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'daniel.costa@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baterista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-11-05 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Grupo de Louvor'
  AND p.inicio_em = '2026-11-05 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Superintendente da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-09-13 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'helena.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-09-13 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'patricia.gomes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-09-13 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'eduardo.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-09-13 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
JOIN funcoes f ON f.nome = 'Regente do Coral'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-13 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-13 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'camila.melo@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-13 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-13 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'natalia.moraes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-13 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'wesley.castro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-13 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-13 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Maestro da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-09-13 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-09-13 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'marcelo.andrade@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-09-13 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'renato.cardoso@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-09-13 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'thiago.reis@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-09-13 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'vinicius.ramos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-09-13 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pastor / Dirigente'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-13 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-13 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-13 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-13 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'joao.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Guitarrista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-13 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baixista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-13 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'daniel.costa@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baterista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-13 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-13 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-13 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'eduardo.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-13 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coordenador Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-09-13 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'beatriz.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-09-13 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'igor.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Auxiliar Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-09-13 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Superintendente da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-09-20 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'helena.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-09-20 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'patricia.gomes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-09-20 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'eduardo.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-09-20 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
JOIN funcoes f ON f.nome = 'Regente do Coral'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-20 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-20 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'camila.melo@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-20 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-20 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'natalia.moraes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-20 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'wesley.castro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-20 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-20 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Maestro da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-09-20 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-09-20 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'marcelo.andrade@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-09-20 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'renato.cardoso@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-09-20 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'thiago.reis@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-09-20 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'vinicius.ramos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-09-20 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pregador'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-20 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-20 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-20 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-20 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'joao.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Guitarrista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-20 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'renato.cardoso@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baixista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-20 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'thiago.reis@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baterista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-20 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-20 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-20 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'otavio.silva@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-20 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coordenador Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-09-20 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'yasmin.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-09-20 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'patricia.gomes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Auxiliar Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-09-20 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Superintendente da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-09-27 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'helena.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-09-27 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'INDISPONIVEL',
       u.nome, f.nome, d.nome,
       'Compromisso familiar neste domingo.', CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'patricia.gomes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-09-27 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'eduardo.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-09-27 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
JOIN funcoes f ON f.nome = 'Regente do Coral'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-27 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-27 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'camila.melo@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-27 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-27 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'natalia.moraes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-27 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'wesley.castro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-27 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-09-27 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Maestro da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-09-27 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-09-27 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'marcelo.andrade@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-09-27 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'renato.cardoso@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-09-27 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'thiago.reis@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-09-27 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'vinicius.ramos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-09-27 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pastor / Dirigente'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-27 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-27 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-27 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-27 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'joao.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Guitarrista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-27 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baixista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-27 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'daniel.costa@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baterista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-27 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-27 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-27 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'eduardo.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-09-27 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coordenador Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-09-27 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'beatriz.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-09-27 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'igor.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Auxiliar Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-09-27 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Superintendente da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-10-04 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'helena.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-10-04 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'patricia.gomes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-10-04 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'eduardo.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-10-04 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
JOIN funcoes f ON f.nome = 'Regente do Coral'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-04 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-04 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'camila.melo@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-04 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-04 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'natalia.moraes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-04 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'wesley.castro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-04 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-04 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Maestro da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-04 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-04 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'marcelo.andrade@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-04 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'renato.cardoso@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-04 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'thiago.reis@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-04 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'vinicius.ramos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-04 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pregador'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-10-04 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-10-04 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-10-04 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-10-04 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'joao.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Guitarrista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-10-04 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'renato.cardoso@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baixista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-10-04 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'thiago.reis@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baterista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-10-04 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-10-04 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-10-04 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'otavio.silva@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-10-04 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coordenador Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-10-04 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'yasmin.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-10-04 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'patricia.gomes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Auxiliar Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-10-04 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Superintendente da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-10-11 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'helena.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-10-11 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'patricia.gomes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-10-11 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'eduardo.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-10-11 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
JOIN funcoes f ON f.nome = 'Regente do Coral'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-11 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-11 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'camila.melo@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-11 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-11 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'natalia.moraes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-11 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'wesley.castro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-11 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-11 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Maestro da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-11 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-11 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'marcelo.andrade@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-11 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'renato.cardoso@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-11 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'thiago.reis@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-11 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'vinicius.ramos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-11 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pastor / Dirigente'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-11 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-11 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-11 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-11 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'joao.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Guitarrista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-11 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baixista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-11 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'daniel.costa@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baterista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-11 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-11 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-11 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'eduardo.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-11 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coordenador Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-10-11 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'beatriz.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-10-11 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'igor.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Auxiliar Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-10-11 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Superintendente da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-10-18 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'helena.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-10-18 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'patricia.gomes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-10-18 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'eduardo.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-10-18 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
JOIN funcoes f ON f.nome = 'Regente do Coral'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-18 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-18 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'camila.melo@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-18 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-18 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'natalia.moraes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-18 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'wesley.castro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-18 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-18 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Maestro da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-18 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-18 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'marcelo.andrade@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-18 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'renato.cardoso@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-18 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'thiago.reis@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-18 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'vinicius.ramos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-18 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pregador'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-18 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-18 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-18 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-18 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'joao.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Guitarrista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-18 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'renato.cardoso@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baixista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-18 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'thiago.reis@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baterista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-18 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-18 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-18 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'otavio.silva@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-10-18 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coordenador Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-10-18 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'yasmin.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-10-18 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'patricia.gomes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Auxiliar Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-10-18 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Superintendente da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-10-25 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'helena.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-10-25 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'patricia.gomes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-10-25 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'eduardo.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-10-25 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
JOIN funcoes f ON f.nome = 'Regente do Coral'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-25 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-25 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'camila.melo@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-25 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-25 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'natalia.moraes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-25 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'wesley.castro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-25 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-10-25 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Maestro da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-25 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-25 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'marcelo.andrade@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-25 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'renato.cardoso@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-25 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'thiago.reis@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-25 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'vinicius.ramos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-10-25 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pastor / Dirigente'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Missões'
  AND p.inicio_em = '2026-10-25 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Missões'
  AND p.inicio_em = '2026-10-25 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Missões'
  AND p.inicio_em = '2026-10-25 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Missões'
  AND p.inicio_em = '2026-10-25 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'joao.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Guitarrista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Missões'
  AND p.inicio_em = '2026-10-25 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baixista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Missões'
  AND p.inicio_em = '2026-10-25 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'daniel.costa@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baterista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Missões'
  AND p.inicio_em = '2026-10-25 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Missões'
  AND p.inicio_em = '2026-10-25 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Missões'
  AND p.inicio_em = '2026-10-25 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'eduardo.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Missões'
  AND p.inicio_em = '2026-10-25 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coordenador Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-10-25 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'beatriz.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-10-25 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'igor.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Auxiliar Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-10-25 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Superintendente da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-11-01 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'helena.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-11-01 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'patricia.gomes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-11-01 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'eduardo.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-11-01 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
JOIN funcoes f ON f.nome = 'Regente do Coral'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-11-01 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-11-01 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'camila.melo@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-11-01 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-11-01 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'natalia.moraes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-11-01 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'wesley.castro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-11-01 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-11-01 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Maestro da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-11-01 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-11-01 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'marcelo.andrade@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-11-01 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'renato.cardoso@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-11-01 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'thiago.reis@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-11-01 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'vinicius.ramos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-11-01 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pregador'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-11-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-11-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-11-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-11-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'joao.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Guitarrista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-11-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'renato.cardoso@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baixista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-11-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'thiago.reis@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baterista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-11-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-11-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-11-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'otavio.silva@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Ceia'
  AND p.inicio_em = '2026-11-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coordenador Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-11-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'yasmin.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-11-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'patricia.gomes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Auxiliar Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-11-01 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Superintendente da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-11-08 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'helena.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-11-08 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'patricia.gomes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor da EBD'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-11-08 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'eduardo.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Escola Bíblica Dominical (EBD)'
  AND p.inicio_em = '2026-11-08 09:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'claudia.barros@demo.syn.local'
JOIN funcoes f ON f.nome = 'Regente do Coral'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-11-08 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-11-08 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'camila.melo@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-11-08 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-11-08 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'natalia.moraes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-11-08 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'wesley.castro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coralista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-11-08 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio do Coral'
  AND p.inicio_em = '2026-11-08 17:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'andre.monteiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Maestro da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-11-08 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-11-08 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'marcelo.andrade@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-11-08 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'renato.cardoso@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-11-08 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'thiago.reis@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-11-08 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'vinicius.ramos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Instrumentista da Orquestra'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Ensaio da Orquestra'
  AND p.inicio_em = '2026-11-08 18:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pastor / Dirigente'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-11-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-11-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-11-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'gabriela.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Tecladista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-11-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'joao.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Guitarrista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-11-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'bruno.dias@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baixista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-11-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'daniel.costa@demo.syn.local'
JOIN funcoes f ON f.nome = 'Baterista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-11-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-11-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-11-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'eduardo.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto de Celebração'
  AND p.inicio_em = '2026-11-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coordenador Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-11-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'beatriz.lima@demo.syn.local'
JOIN funcoes f ON f.nome = 'Professor Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-11-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'igor.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Auxiliar Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Culto Infantil'
  AND p.inicio_em = '2026-11-08 19:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'lucas.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Jovens'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Evangelismo com Amigos'
  AND p.inicio_em = '2026-09-12 15:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pastor / Dirigente'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Evangelismo com Amigos'
  AND p.inicio_em = '2026-09-12 15:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'eduardo.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Diácono'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Evangelismo com Amigos'
  AND p.inicio_em = '2026-09-12 15:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Evangelismo com Amigos'
  AND p.inicio_em = '2026-09-12 15:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Evangelismo com Amigos'
  AND p.inicio_em = '2026-09-12 15:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'lucas.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Jovens'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Encontro de Jovens - Conexão'
  AND p.inicio_em = '2026-09-19 19:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Encontro de Jovens - Conexão'
  AND p.inicio_em = '2026-09-19 19:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Encontro de Jovens - Conexão'
  AND p.inicio_em = '2026-09-19 19:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'larissa.freitas@demo.syn.local'
JOIN funcoes f ON f.nome = 'Multimídia / Projeção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Encontro de Jovens - Conexão'
  AND p.inicio_em = '2026-09-19 19:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Encontro de Jovens - Conexão'
  AND p.inicio_em = '2026-09-19 19:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pastor / Dirigente'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Encontro da Família'
  AND p.inicio_em = '2026-09-26 16:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'juliana.oliveira@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder do Ministério Feminino'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Encontro da Família'
  AND p.inicio_em = '2026-09-26 16:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Homens'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Encontro da Família'
  AND p.inicio_em = '2026-09-26 16:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'mariana.alves@demo.syn.local'
JOIN funcoes f ON f.nome = 'Coordenador Infantil'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Encontro da Família'
  AND p.inicio_em = '2026-09-26 16:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'valeria.lopes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Encontro da Família'
  AND p.inicio_em = '2026-09-26 16:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'lucas.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Adolescentes'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Acampamento de Adolescentes'
  AND p.inicio_em = '2026-10-02 18:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'sabrina.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Adolescentes'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Acampamento de Adolescentes'
  AND p.inicio_em = '2026-10-02 18:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pastor / Dirigente'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Acampamento de Adolescentes'
  AND p.inicio_em = '2026-10-02 18:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'juliana.oliveira@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder do Ministério Feminino'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Viagem do Ministério Feminino'
  AND p.inicio_em = '2026-10-10 06:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'natalia.moraes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder do Ministério Feminino'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Viagem do Ministério Feminino'
  AND p.inicio_em = '2026-10-10 06:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'valeria.lopes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Viagem do Ministério Feminino'
  AND p.inicio_em = '2026-10-10 06:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Conselheiro'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Reunião do Conselho'
  AND p.inicio_em = '2026-10-12 19:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Conselheiro'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Reunião do Conselho'
  AND p.inicio_em = '2026-10-12 19:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Homens'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Encontro de Homens'
  AND p.inicio_em = '2026-10-17 19:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pastor / Dirigente'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Encontro de Homens'
  AND p.inicio_em = '2026-10-17 19:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'otavio.silva@demo.syn.local'
JOIN funcoes f ON f.nome = 'Diácono'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Encontro de Homens'
  AND p.inicio_em = '2026-10-17 19:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'lucas.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Jovens'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Encontro de Jovens - Propósito'
  AND p.inicio_em = '2026-10-24 19:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Encontro de Jovens - Propósito'
  AND p.inicio_em = '2026-10-24 19:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'ana.ribeiro@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Encontro de Jovens - Propósito'
  AND p.inicio_em = '2026-10-24 19:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'felipe.martins@demo.syn.local'
JOIN funcoes f ON f.nome = 'Sonoplasta'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Encontro de Jovens - Propósito'
  AND p.inicio_em = '2026-10-24 19:30:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'lucas.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Jovens'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Evangelismo com Amigos'
  AND p.inicio_em = '2026-10-31 15:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'eduardo.rocha@demo.syn.local'
JOIN funcoes f ON f.nome = 'Diácono'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Evangelismo com Amigos'
  AND p.inicio_em = '2026-10-31 15:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'rafael.souza@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Louvor'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Evangelismo com Amigos'
  AND p.inicio_em = '2026-10-31 15:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'isabela.santos@demo.syn.local'
JOIN funcoes f ON f.nome = 'Vocalista'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Evangelismo com Amigos'
  AND p.inicio_em = '2026-10-31 15:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'ESCALADO',
       u.nome, f.nome, d.nome,
       NULL, NULL
FROM programacoes p
JOIN usuarios u ON u.email = 'carlos.mendes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Pastor / Dirigente'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Noite da Família'
  AND p.inicio_em = '2026-11-07 18:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'juliana.oliveira@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder do Ministério Feminino'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Noite da Família'
  AND p.inicio_em = '2026-11-07 18:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'roberto.nunes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Líder de Homens'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Noite da Família'
  AND p.inicio_em = '2026-11-07 18:00:00'
LIMIT 1;

INSERT IGNORE INTO participacoes (
    programacao_id, usuario_id, funcao_id, status,
    usuario_nome_historico, funcao_nome_historico, departamento_nome_historico,
    observacao, respondido_em
)
SELECT p.id, u.id, f.id, 'CONFIRMADO',
       u.nome, f.nome, d.nome,
       NULL, CURRENT_TIMESTAMP
FROM programacoes p
JOIN usuarios u ON u.email = 'valeria.lopes@demo.syn.local'
JOIN funcoes f ON f.nome = 'Recepção'
LEFT JOIN departamentos d ON d.id = f.departamento_id
WHERE p.titulo = 'Noite da Família'
  AND p.inicio_em = '2026-11-07 18:00:00'
LIMIT 1;


-- ============================================================
-- 13. NOTIFICAÇÕES DE ESCALAS PENDENTES
-- ============================================================

INSERT IGNORE INTO notificacoes (
    usuario_id, tipo, titulo, mensagem,
    url_acao, origem_tipo, origem_id, expira_em
)
SELECT
    pa.usuario_id,
    'ESCALA_PENDENTE',
    'Confirme sua participação',
    CONCAT(
        'Você foi escalado como ',
        pa.funcao_nome_historico,
        ' em ',
        pr.titulo,
        '.'
    ),
    CONCAT('/programacoes/', pr.id),
    'PARTICIPACAO',
    pa.id,
    DATE_ADD(pr.fim_em, INTERVAL 1 DAY)
FROM participacoes pa
JOIN programacoes pr ON pr.id = pa.programacao_id
JOIN usuarios u ON u.id = pa.usuario_id
WHERE pa.status = 'ESCALADO'
  AND u.email LIKE '%@demo.syn.local'
  AND pr.inicio_em BETWEEN '2026-09-09 00:00:00' AND '2026-11-08 23:59:59';



-- ============================================================
-- 14. HISTÓRICO DE ALGUMAS PROGRAMAÇÕES
-- ============================================================

INSERT INTO eventos_programacao (
    programacao_id, tipo_evento,
    titulo_anterior, titulo_novo,
    inicio_anterior, inicio_novo,
    fim_anterior, fim_novo,
    local_anterior, local_novo,
    status_anterior, status_novo
)
SELECT p.id, 'ALTERACAO',
       p.titulo, p.titulo,
       '2026-09-26 15:00:00', p.inicio_em,
       '2026-09-26 20:00:00', p.fim_em,
       p.local_nome_historico, p.local_nome_historico,
       'AGENDADA', 'AGENDADA'
FROM programacoes p
WHERE p.titulo = 'Encontro da Família'
  AND p.inicio_em = '2026-09-26 16:00:00'
  AND NOT EXISTS (
      SELECT 1 FROM eventos_programacao ep
      WHERE ep.programacao_id = p.id
        AND ep.tipo_evento = 'ALTERACAO'
  );

INSERT INTO eventos_programacao (
    programacao_id, tipo_evento,
    titulo_anterior, titulo_novo,
    local_anterior, local_novo,
    status_anterior, status_novo
)
SELECT p.id, 'CRIACAO',
       NULL, p.titulo,
       NULL, p.local_nome_historico,
       NULL, 'AGENDADA'
FROM programacoes p
WHERE p.titulo = 'Reunião do Conselho'
  AND p.inicio_em = '2026-10-12 19:30:00'
  AND NOT EXISTS (
      SELECT 1 FROM eventos_programacao ep
      WHERE ep.programacao_id = p.id
        AND ep.tipo_evento = 'CRIACAO'
  );



COMMIT;

-- ============================================================
-- 15. CONFERÊNCIA FINAL
-- ============================================================

SELECT 'Departamentos' AS item, COUNT(*) AS quantidade
FROM departamentos
UNION ALL
SELECT 'Funções', COUNT(*) FROM funcoes
UNION ALL
SELECT 'Usuários demo', COUNT(*) FROM usuarios WHERE email LIKE '%@demo.syn.local'
UNION ALL
SELECT 'Tipos de programação', COUNT(*) FROM tipos_programacao
UNION ALL
SELECT 'Locais', COUNT(*) FROM locais
UNION ALL
SELECT 'Séries recorrentes demo', COUNT(*)
FROM series_programacao
WHERE inicio_base BETWEEN '2026-09-09 00:00:00' AND '2026-09-13 23:59:59'
UNION ALL
SELECT 'Programações no período', COUNT(*)
FROM programacoes
WHERE inicio_em BETWEEN '2026-09-09 00:00:00' AND '2026-11-08 23:59:59'
UNION ALL
SELECT 'Participações / escalas', COUNT(*)
FROM participacoes pa
JOIN programacoes p ON p.id = pa.programacao_id
WHERE p.inicio_em BETWEEN '2026-09-09 00:00:00' AND '2026-11-08 23:59:59'
UNION ALL
SELECT 'Programações públicas', COUNT(*)
FROM programacoes
WHERE visibilidade = 'PUBLICA'
  AND inicio_em BETWEEN '2026-09-09 00:00:00' AND '2026-11-08 23:59:59';

SELECT
    p.inicio_em,
    p.fim_em,
    p.titulo,
    p.local_nome_historico AS local,
    p.organizador_nome_historico AS responsavel,
    p.visibilidade,
    p.status
FROM programacoes p
WHERE p.inicio_em BETWEEN '2026-09-09 00:00:00' AND '2026-11-08 23:59:59'
ORDER BY p.inicio_em, p.local_nome_historico;

