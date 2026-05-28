PRAGMA foreign_keys = OFF;

DROP TABLE IF EXISTS objetivos;
DROP TABLE IF EXISTS treinos;
DROP TABLE IF EXISTS relatorios;
DROP TABLE IF EXISTS avaliacoes;
DROP TABLE IF EXISTS equipamentos;
DROP TABLE IF EXISTS inscricoes_aulas;
DROP TABLE IF EXISTS aulas;
DROP TABLE IF EXISTS administradores;
DROP TABLE IF EXISTS treinadores;
DROP TABLE IF EXISTS membros;
DROP TABLE IF EXISTS utilizadores;
DROP TABLE IF EXISTS planos;
DROP TABLE IF EXISTS ginasios;

PRAGMA foreign_keys = ON;

CREATE TABLE utilizadores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome_utilizador TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    palavra_passe TEXT NOT NULL,
    nome TEXT NOT NULL,
    apelido TEXT NOT NULL,
    fotografia TEXT,
    papel TEXT NOT NULL DEFAULT 'membro'
        CHECK (papel IN ('membro', 'treinador', 'administrador')),
    estado TEXT NOT NULL DEFAULT 'ativo'
        CHECK (estado IN ('ativo', 'inativo'))
);

CREATE TABLE ginasios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL UNIQUE,
    morada TEXT NOT NULL,
    cidade TEXT NOT NULL,
    codigo_postal TEXT NOT NULL,
    telefone TEXT
);

CREATE TABLE planos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL UNIQUE,
    preco_mensal REAL NOT NULL CHECK (preco_mensal >= 0),
    descricao TEXT NOT NULL,
    beneficios TEXT NOT NULL
);

CREATE TABLE membros (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    utilizador_id INTEGER NOT NULL UNIQUE
        REFERENCES utilizadores(id) ON UPDATE CASCADE ON DELETE CASCADE,
    data_nascimento TEXT,
    telefone TEXT,
    morada TEXT,
    cidade TEXT,
    codigo_postal TEXT,
    plano_id INTEGER
        REFERENCES planos(id) ON UPDATE CASCADE ON DELETE SET NULL,
    ginasio_id INTEGER
        REFERENCES ginasios(id) ON UPDATE CASCADE ON DELETE SET NULL,
    inscrito_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE treinadores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    utilizador_id INTEGER NOT NULL UNIQUE
        REFERENCES utilizadores(id) ON UPDATE CASCADE ON DELETE CASCADE,
    biografia TEXT,
    especializacoes TEXT,
    certificacoes TEXT
);

CREATE TABLE administradores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    utilizador_id INTEGER NOT NULL UNIQUE
        REFERENCES utilizadores(id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE aulas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    tipo TEXT NOT NULL,
    descricao TEXT,
    treinador_id INTEGER NOT NULL
        REFERENCES treinadores(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    ginasio_id INTEGER NOT NULL
        REFERENCES ginasios(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    dia_semana TEXT NOT NULL
        CHECK (dia_semana IN ('segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo')),
    inicio TEXT NOT NULL,
    fim TEXT NOT NULL,
    lotacao INTEGER NOT NULL CHECK (lotacao > 0),
    sala TEXT,
    estado TEXT NOT NULL DEFAULT 'agendada'
        CHECK (estado IN ('agendada', 'cancelada', 'concluida'))
);

CREATE TABLE inscricoes_aulas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    membro_id INTEGER NOT NULL
        REFERENCES membros(id) ON UPDATE CASCADE ON DELETE CASCADE,
    aula_id INTEGER NOT NULL
        REFERENCES aulas(id) ON UPDATE CASCADE ON DELETE CASCADE,
    estado TEXT NOT NULL DEFAULT 'inscrito'
        CHECK (estado IN ('inscrito', 'cancelado', 'presente', 'faltou')),
    inscrito_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    cancelado_em TEXT,
    UNIQUE (membro_id, aula_id)
);

CREATE TABLE equipamentos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    zona TEXT NOT NULL,
    estado TEXT NOT NULL DEFAULT 'disponivel'
        CHECK (estado IN ('disponivel', 'ocupado', 'manutencao')),
    quantidade INTEGER NOT NULL DEFAULT 1 CHECK (quantidade > 0),
    atualizado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE relatorios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    utilizador_id INTEGER NOT NULL REFERENCES utilizadores(id),
    tipo TEXT NOT NULL CHECK(tipo IN ('equipamento', 'aula', 'outro')),
    assunto TEXT NOT NULL,
    descricao TEXT NOT NULL,
    estado TEXT NOT NULL DEFAULT 'pendente' CHECK(estado IN ('pendente', 'em_analise', 'resolvido')),
    resposta_admin TEXT,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE treinos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    membro_id INTEGER NOT NULL REFERENCES membros(id) ON UPDATE CASCADE ON DELETE CASCADE,
    data TEXT NOT NULL,
    tipo TEXT NOT NULL CHECK (tipo IN ('musculacao', 'cardio', 'funcional', 'yoga', 'outro')),
    duracao_minutos INTEGER NOT NULL CHECK (duracao_minutos > 0),
    notas TEXT,
    criado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE objetivos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    membro_id INTEGER NOT NULL REFERENCES membros(id) ON UPDATE CASCADE ON DELETE CASCADE,
    descricao TEXT NOT NULL,
    valor_alvo REAL NOT NULL CHECK (valor_alvo > 0),
    valor_atual REAL NOT NULL DEFAULT 0 CHECK (valor_atual >= 0),
    unidade TEXT NOT NULL DEFAULT '',
    data_limite TEXT,
    concluido INTEGER NOT NULL DEFAULT 0 CHECK (concluido IN (0, 1)),
    criado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE avaliacoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    membro_id INTEGER NOT NULL
        REFERENCES membros(id) ON UPDATE CASCADE ON DELETE CASCADE,
    aula_id INTEGER NOT NULL
        REFERENCES aulas(id) ON UPDATE CASCADE ON DELETE CASCADE,
    classificacao INTEGER NOT NULL CHECK (classificacao BETWEEN 1 AND 10),
    comentario TEXT,
    criada_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (membro_id, aula_id)
);

INSERT INTO ginasios (nome, morada, cidade, codigo_postal, telefone) VALUES
    ('LA', 'Largo Carlos Araújo', 'Vila do Conde', '4480-123', '911978544'),
    ('Caxinas', 'Avenida das Caxinas 22', 'Vila do Conde', '4480-671', '911978545'),
    ('Póvoa de Varzim', 'Rua da Junqueira 41', 'Póvoa de Varzim', '4490-519', '911978546'),
    ('Ramalde', 'Rua de Ramalde 100', 'Porto', '4250-344', '911978547');

INSERT INTO planos (nome, preco_mensal, descricao, beneficios) VALUES
    ('Básico', 19.99, 'Acesso a um ginásio, zona de cardio e zona de musculação.', 'Acesso a 1 ginásio|Acesso 24/7|Zona cardio e musculação|Wifi grátis'),
    ('Ilimitado', 29.99, 'Acesso a todos os ginásios e aulas de grupo.', 'Acesso a todos os ginásios|Aulas de grupo incluídas|Equipamento premium|App exclusiva'),
    ('Premium', 39.99, 'Plano completo com treino personalizado e avaliação física.', 'Tudo do plano ilimitado|Treino personalizado|Avaliação física|Zona VIP');

INSERT INTO utilizadores (nome_utilizador, email, palavra_passe, nome, apelido, papel, estado) VALUES
    ('admin', 'admin@lafit.test', 'p4s5w0rd', 'Admin', 'LAFit', 'administrador', 'ativo'),
    ('member', 'member@lafit.test', '1234', 'Member', 'LAFit', 'membro', 'ativo'),
    ('trainer', 'trainer@lafit.test', '1234', 'Trainer', 'LAFit', 'treinador', 'ativo');

INSERT INTO administradores (utilizador_id) VALUES
    (1);

INSERT INTO membros (utilizador_id, data_nascimento, telefone, morada, cidade, codigo_postal, plano_id, ginasio_id) VALUES
    (2, '2004-05-12', '912000111', 'Rua da Escola 10', 'Vila do Conde', '4480-000', 2, 1);

INSERT INTO treinadores (utilizador_id, biografia, especializacoes, certificacoes) VALUES
    (3, 'Treinador da LAFit.', 'Cycling, Pilates, Hyrox, Kickbox, Karaté', 'Certificação interna LAFit');

INSERT INTO aulas (nome, tipo, descricao, treinador_id, ginasio_id, dia_semana, inicio, fim, lotacao, sala, estado) VALUES
    ('Cycling', 'cycling', 'Aula de bicicleta indoor com foco em resistência cardiovascular.', 1, 1, 'segunda', '18:30', '19:30', 30, 'Estúdio', 'agendada'),
    ('Pilates', 'pilates', 'Aula de controlo postural, mobilidade e fortalecimento do core.', 1, 1, 'terca', '18:30', '19:30', 25, 'Estúdio', 'agendada'),
    ('Hyrox', 'hyrox', 'Treino funcional de alta intensidade com corrida e exercícios de força.', 1, 2, 'quarta', '18:30', '19:30', 20, 'Sala Funcional', 'agendada'),
    ('Kickbox', 'kickbox', 'Aula de combate com técnica, coordenação e condicionamento físico.', 1, 3, 'quinta', '18:30', '19:30', 22, 'Estúdio', 'agendada'),
    ('Karaté', 'karate', 'Aula de artes marciais focada em técnica, disciplina e defesa pessoal.', 1, 4, 'sexta', '18:30', '19:30', 18, 'Sala 2', 'agendada'),
    ('Funcional', 'funcional', 'Treino funcional com exercícios de força, equilíbrio e coordenação para todos os níveis.', 1, 1, 'sabado', '10:00', '11:00', 20, 'Sala Funcional', 'agendada');

INSERT INTO inscricoes_aulas (membro_id, aula_id, estado) VALUES
    (1, 1, 'presente');

INSERT INTO equipamentos (nome, zona, estado, quantidade) VALUES
    ('Passadeira', 'Cardio', 'disponivel', 12),
    ('Bicicleta estática', 'Cardio', 'disponivel', 10),
    ('Banco de supino', 'Musculação', 'ocupado', 4),
    ('Máquina de remo', 'Funcional', 'manutencao', 2),
    ('Polia', 'Musculação', 'disponivel', 4),
    ('Remada convergente', 'Musculação', 'disponivel', 1),
    ('Máquina de súpino vertical', 'Musculação', 'disponivel', 1),
    ('Prensa de pernas', 'Musculação', 'disponivel', 2),
    ('Puxada frontal', 'Musculação', 'disponivel', 2),
    ('Barra guiada', 'Musculação', 'disponivel', 1),
    ('Remada barra T', 'Musculação', 'disponivel', 1),
    ('Remada frontal', 'Musculação', 'disponivel', 2),
    ('Plataforma de Peso Livre', 'Funcional', 'disponivel', 4),
    ('Barra de agachamento', 'Musculação', 'disponivel', 1),
    ('Barra de agachamento', 'Musculação', 'ocupado', 1),
    ('Agachamento guiado', 'Musculação', 'disponivel', 1),
    ('Máquina de elevação lateral', 'Musculação', 'disponivel', 1),
    ('Barra de elevações', 'Musculação', 'disponivel', 2),
    ('Banco de abdominais', 'Funcional', 'disponivel', 1),
    ('Banco de abdominais', 'Funcional', 'ocupado', 2),
    ('Banco de fortalecimento do core', 'Funcional', 'disponivel', 2);

INSERT INTO avaliacoes (membro_id, aula_id, classificacao, comentario) VALUES
    (1, 1, 9, 'Aula intensa e bem acompanhada.');
