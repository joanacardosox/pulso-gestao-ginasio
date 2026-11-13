-- Pulso · Dados iniciais (RBAC v2). Nomes, valores e datas são dados de exemplo.
SET NAMES utf8mb4;

-- 5 perfis. O Visitante é quem usa o site sem login. O Gestor (dono do ginásio) está bloqueado:
-- as permissões dele não se alteram, para ninguém ficar sem acesso à gestão.
INSERT INTO perfis (id, nome, bloqueado) VALUES
 (1, 'Gestor', 1),
 (2, 'Rececionista', 0),
 (3, 'Treinador', 0),
 (4, 'Sócio', 0),
 (5, 'Visitante', 0);

-- 28 permissões: cada ação é uma permissão (ver, criar, editar, arquivar).
-- tem_ambito = 1 → pode ser limitada a "só as suas" (o treinador só mexe nas suas aulas).
INSERT INTO permissoes (id, codigo, descricao, grupo, tem_ambito) VALUES
 (1,  'publico.ver',            'Ver o site público (planos, horário, treinadores)', 'Site',          0),
 (2,  'conta.criar',            'Criar conta de sócio (registo)',                   'Site',          0),
 (3,  'mensalidades.proprias',  'Ver as suas mensalidades',                         'Área do sócio', 0),
 (4,  'mensalidades.pagar',     'Pagar as suas mensalidades (MB Way ou cartão)',    'Área do sócio', 0),
 (5,  'aulas.inscrever',        'Inscrever-se em aulas',                            'Área do sócio', 0),
 (6,  'socios.ver',             'Ver sócios',                                       'Sócios',        0),
 (7,  'socios.criar',           'Criar sócios',                                     'Sócios',        0),
 (8,  'socios.editar',          'Editar sócios',                                    'Sócios',        0),
 (9,  'socios.arquivar',        'Arquivar sócios (o histórico fica guardado)',      'Sócios',        0),
 (10, 'planos.ver',             'Ver planos',                                       'Planos',        0),
 (11, 'planos.criar',           'Criar planos',                                     'Planos',        0),
 (12, 'planos.editar',          'Editar planos e preços',                           'Planos',        0),
 (13, 'planos.arquivar',        'Arquivar planos',                                  'Planos',        0),
 (14, 'mensalidades.ver',       'Ver as mensalidades de todos',                     'Mensalidades',  0),
 (15, 'mensalidades.criar',     'Criar mensalidades',                               'Mensalidades',  0),
 (16, 'mensalidades.registar',  'Registar pagamentos (receção, MB Way ou cartão)',  'Mensalidades',  0),
 (17, 'aulas.ver',              'Ver aulas e inscritos',                            'Aulas',         1),
 (18, 'aulas.criar',            'Criar aulas',                                      'Aulas',         0),
 (19, 'aulas.editar',           'Editar aulas',                                     'Aulas',         1),
 (20, 'aulas.arquivar',         'Arquivar aulas',                                   'Aulas',         0),
 (21, 'presencas.marcar',       'Marcar presenças',                                 'Presenças',     1),
 (22, 'dashboard.ver',          'Ver o dashboard (indicadores e melhores sócios)',  'Dashboard',     0),
 (23, 'utilizadores.ver',       'Ver a equipa',                                     'Equipa',        0),
 (24, 'utilizadores.criar',     'Criar utilizadores da equipa',                     'Equipa',        0),
 (25, 'utilizadores.editar',    'Mudar o perfil de um utilizador',                  'Equipa',        0),
 (26, 'utilizadores.desativar', 'Desativar e reativar utilizadores',                'Equipa',        0),
 (27, 'perfis.ver',             'Ver perfis e permissões',                          'Acessos',       0),
 (28, 'perfis.editar',          'Alterar as permissões dos perfis',                 'Acessos',       0);

-- Matriz perfil × permissão (tabela perfil_permissao)
-- Gestor: site público e todo o back office (a área do sócio e o registo são só para sócios e visitantes)
INSERT INTO perfil_permissao (perfil_id, permissao_id) SELECT 1, id FROM permissoes WHERE id NOT IN (2, 3, 4, 5);
-- Rececionista: sócios (ver, criar, editar), ver planos, mensalidades, aulas e presenças
INSERT INTO perfil_permissao (perfil_id, permissao_id) VALUES
 (2,1),(2,6),(2,7),(2,8),(2,10),(2,14),(2,15),(2,16),(2,17),(2,18),(2,19),(2,21);
-- Treinador: só as suas aulas
INSERT INTO perfil_permissao (perfil_id, permissao_id, ambito) VALUES
 (3,1,'todas'),(3,17,'proprias'),(3,19,'proprias'),(3,21,'proprias');
-- Sócio
INSERT INTO perfil_permissao (perfil_id, permissao_id) VALUES (4,1),(4,3),(4,4),(4,5);
-- Visitante (sem login)
INSERT INTO perfil_permissao (perfil_id, permissao_id) VALUES (5,1),(5,2);

-- Utilizadores de demonstração (passwords guardadas com password_hash)
-- gestor@pulso.test / Gestor123!      rececao@pulso.test / Rececao123!
-- treinador@pulso.test / Treinador123! (também treinador.a e treinador.b)   socio@pulso.test / Socio123!
INSERT INTO utilizadores (id, nome, email, password_hash, perfil_id) VALUES
 (1, 'Gestor (demo)',        'gestor@pulso.test',      '$2y$10$OFgzCcPvWRYfeNJfVu57j.RjHzHYhEux9zKlJ8r50TRbvHC/wp9fW', 1),
 (2, 'Rececionista (demo)',  'rececao@pulso.test',     '$2y$10$SFlHH5CrGZdEC0esxJt4W.ivcf13rHwzT2IaUERAwfYw0ibbLYFDe', 2),
 (3, 'Treinador C (demo)',   'treinador@pulso.test',   '$2y$10$PjSr3MuscHfGMbh8CxFqPuJAttPGbOjNPcwWpAzfCrcgsvz79jQ.i', 3),
 (4, 'Treinador A (demo)',   'treinador.a@pulso.test', '$2y$10$PjSr3MuscHfGMbh8CxFqPuJAttPGbOjNPcwWpAzfCrcgsvz79jQ.i', 3),
 (5, 'Treinador B (demo)',   'treinador.b@pulso.test', '$2y$10$PjSr3MuscHfGMbh8CxFqPuJAttPGbOjNPcwWpAzfCrcgsvz79jQ.i', 3),
 (6, 'Ana Ribeiro',          'socio@pulso.test',       '$2y$10$HTcM3iRhfueHHgHEDBwsbOoVr/d6AzK/k3NNkYS/o75PmMzQcxyti', 4);

INSERT INTO planos (id, nome, duracao_meses, preco) VALUES
 (1, 'Mensal', 1, 30.00), (2, 'Trimestral', 3, 80.00), (3, 'Anual', 12, 300.00);

INSERT INTO socios (id, numero, utilizador_id, nome, plano_id, data_inscricao) VALUES
 (1, '0142', 6,    'Ana Ribeiro',    2, '2025-09-02'),
 (2, '0143', NULL, 'Bruno Costa',    1, '2025-09-15'),
 (3, '0151', NULL, 'Carla Mendes',   3, '2025-10-01'),
 (4, '0158', NULL, 'Diogo Ferreira', 1, '2026-01-10'),
 (5, '0163', NULL, 'Inês Carvalho',  2, '2026-02-03'),
 (6, '0170', NULL, 'João Martins',   1, '2026-03-12'),
 (7, '0174', NULL, 'Marta Lopes',    3, '2026-04-20'),
 (8, '0177', NULL, 'Sofia Pires',    2, '2026-05-05'),
 (9, '0181', NULL, 'Rui Almeida',    1, '2026-06-01');

-- Exemplo de sócio arquivado: sai da lista, mas o histórico fica guardado
UPDATE socios SET arquivado_em = '2026-10-31 10:00:00' WHERE id = 9;

INSERT INTO mensalidades (socio_id, referencia, valor, data_limite, data_pagamento, estado, metodo, registado_por) VALUES
 (1, 'ago – out 2026',      80.00, '2026-08-01', '2026-08-01', 'Paga',      'Receção', 2),
 (1, 'nov 2026 – jan 2027', 80.00, '2026-11-15', NULL,         'Pendente',  NULL,      NULL),
 (2, 'out 2026',            30.00, '2026-10-15', NULL,         'Em atraso', NULL,      NULL),
 (3, '2026 – 2027',        300.00, '2026-09-01', '2026-08-30', 'Paga',      'MB Way',  2),
 (4, 'nov 2026',            30.00, '2026-11-10', NULL,         'Pendente',  NULL,      NULL),
 (5, 'nov 2026 – jan 2027', 80.00, '2026-11-20', NULL,         'Pendente',  NULL,      NULL),
 (6, 'out 2026',            30.00, '2026-10-22', NULL,         'Em atraso', NULL,      NULL),
 (7, '2026 – 2027',        300.00, '2026-10-01', '2026-09-28', 'Paga',      'Cartão',  2),
 (8, 'nov 2026 – jan 2027', 80.00, '2026-11-01', NULL,         'Em atraso', NULL,      NULL),
 (9, 'set 2026',            30.00, '2026-09-01', '2026-09-01', 'Paga',      'Receção', 2);

INSERT INTO modalidades (id, nome) VALUES
 (1,'Yoga'),(2,'Pilates'),(3,'Spinning'),(4,'HIIT'),(5,'Boxe'),(6,'Funcional'),(7,'Zumba');

-- Treinador A = utilizador 4, Treinador B = 5, Treinador C = 3
INSERT INTO aulas (id, modalidade_id, treinador_id, dia_semana, hora_inicio, sala, lotacao_max) VALUES
 (1, 1, 4, 'Segunda', '08:00', 'Sala 1', 20),
 (2, 4, 5, 'Segunda', '18:00', 'Sala 2', 15),
 (3, 2, 4, 'Segunda', '19:30', 'Sala 1', 15),
 (4, 3, 5, 'Terça',   '12:30', 'Sala 2', 20),
 (5, 5, 3, 'Terça',   '18:00', 'Sala 1', 16),
 (6, 6, 3, 'Terça',   '19:30', 'Sala 2', 18),
 (7, 1, 4, 'Quarta',  '08:00', 'Sala 1', 20),
 (8, 4, 5, 'Quarta',  '18:00', 'Sala 2', 15),
 (9, 7, 5, 'Quarta',  '19:30', 'Sala 1', 25),
 (10, 3, 5, 'Quinta', '12:30', 'Sala 2', 20),
 (11, 5, 3, 'Quinta', '18:00', 'Sala 1', 16),
 (12, 2, 4, 'Sexta',  '08:00', 'Sala 1', 15),
 (13, 1, 4, 'Sexta',  '18:00', 'Sala 1', 20);

INSERT INTO inscricoes (aula_id, socio_id, presente) VALUES
 (5,1,1),(5,3,1),(5,4,0),(5,5,1),(5,7,1),(5,8,NULL),
 (6,1,NULL),(6,5,NULL),(1,1,NULL),(1,3,NULL),(2,4,NULL),
 -- presenças de semanas anteriores (alimentam os "melhores sócios" do dashboard)
 (3,1,1),(3,3,1),(7,1,1),(7,5,1),(12,1,1),(12,7,1),(4,3,1),(4,4,1),(9,5,1),(9,7,0),(13,1,1),(13,3,1),(10,9,1),(11,9,1);
