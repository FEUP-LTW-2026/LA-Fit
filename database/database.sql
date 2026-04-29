PRAGMA foreign_keys = OFF;
DROP TABLE IF EXISTS utilizadores;
DROP TABLE IF EXISTS membros;
DROP TABLE IF EXISTS treinadores;
DROP TABLE IF EXISTS administradores;
DROP TABLE IF EXISTS aulas;
DROP TABLE IF EXISTS inscricoes_aulas;
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

CREATE TABLE membros (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    utilizador_id INTEGER NOT NULL UNIQUE
        REFERENCES utilizadores(id) ON UPDATE CASCADE ON DELETE CASCADE,
    data_nascimento TEXT,
    telefone TEXT
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
    descricao TEXT
);




INSERT INTO ginasios (nome, morada, cidade, codigo_postal, telefone) VALUES
    ('LA', 'Largo Carlos Araújo', 'Vila do Conde', '4480-123', '911978544'),
    ('Caxinas', 'Avenida das Caxinas 22', 'Vila do Conde', '4480-671', '911978545'),
    ('Póvoa de Varzim', 'Rua da Junqueira 41', 'Póvoa de Varzim', '4490-519', '911978546'),
    ('Ramalde', 'Rua de Ramalde 100', 'Porto', '4250-344', '911978547');

INSERT INTO planos (nome, preco_mensal, descricao) VALUES
    ('Básico', 19.99, 'Acesso a um ginásio, zona de cardio e zona de musculação.'),
    ('Ilimitado', 29.99, 'Acesso a todos os ginásios e aulas de grupo.'),
    ('Premium', 39.99, 'Plano completo com treino personalizado e avaliação física.');

INSERT INTO utilizadores (nome_utilizador, email, palavra_passe, nome, apelido, papel, estado) VALUES
    ('admin', 'admin@lafit.test', 'p4s5w0rd', 'Admin', 'LAFit', 'administrador', 'ativo'),
    ('member', 'member@lafit.test', '1234', 'Member', 'LAFit', 'membro', 'ativo'),
    ('trainer', 'trainer@lafit.test', '1234', 'Trainer', 'LAFit', 'treinador', 'ativo');

INSERT INTO administradores (utilizador_id) VALUES
    (1);

INSERT INTO membros (utilizador_id) VALUES
    (2);

INSERT INTO treinadores (utilizador_id, biografia, especializacoes, certificacoes) VALUES
    (3, 'Treinador da LAFit.', 'Cycling, Pilates, Hyrox, Kickbox, Karaté', 'Certificação interna LAFit');

INSERT INTO aulas (nome, tipo, descricao, treinador_id, dia_semana, inicio, fim, lotacao, sala, estado) VALUES
    ('Cycling', 'cycling', 'Aula de bicicleta indoor com foco em resistência cardiovascular.', 1, 'segunda', '18:30', '19:30', 30, 'Estúdio', 'agendada'),
    ('Pilates', 'pilates', 'Aula de controlo postural, mobilidade e fortalecimento do core.', 1, 'terca', '18:30', '19:30', 30, 'Estúdio', 'agendada'),
    ('Hyrox', 'hyrox', 'Treino funcional de alta intensidade com corrida e exercícios de força.', 1, 'quarta', '18:30', '19:30', 30, 'Estúdio', 'agendada'),
    ('Kickbox', 'kickbox', 'Aula de combate com técnica, coordenação e condicionamento físico.', 1, 'quinta', '18:30', '19:30', 30, 'Estúdio', 'agendada'),
    ('Karaté', 'karate', 'Aula de artes marciais focada em técnica, disciplina e defesa pessoal.', 1, 'sexta', '18:30', '19:30', 30, 'Estúdio', 'agendada');
