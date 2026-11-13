-- Pulso — Gestão de Ginásio · Esquema da base de dados (RBAC v2)
-- Regra: nada se apaga; sócios, planos e aulas são arquivados (arquivado_em) para manter o histórico.
SET NAMES utf8mb4;

-- ---------- Acessos (RBAC) ----------
CREATE TABLE perfis (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  nome      VARCHAR(40) NOT NULL UNIQUE,
  bloqueado TINYINT(1) NOT NULL DEFAULT 0   -- 1 = permissões não podem ser alteradas (Gestor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE permissoes (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  codigo     VARCHAR(40) NOT NULL UNIQUE,
  descricao  VARCHAR(120) NOT NULL,
  grupo      VARCHAR(40) NOT NULL,
  tem_ambito TINYINT(1) NOT NULL DEFAULT 0  -- 1 = pode ser limitada a "só as suas"
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE perfil_permissao (
  perfil_id    INT NOT NULL,
  permissao_id INT NOT NULL,
  ambito       ENUM('todas','proprias') NOT NULL DEFAULT 'todas',
  PRIMARY KEY (perfil_id, permissao_id),
  FOREIGN KEY (perfil_id)    REFERENCES perfis(id)     ON DELETE CASCADE,
  FOREIGN KEY (permissao_id) REFERENCES permissoes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE utilizadores (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  nome          VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  perfil_id     INT NOT NULL,
  ativo         TINYINT(1) NOT NULL DEFAULT 1,
  criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (perfil_id) REFERENCES perfis(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Sócios e pagamentos ----------
CREATE TABLE planos (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  nome          VARCHAR(40) NOT NULL,
  duracao_meses INT NOT NULL,
  preco         DECIMAL(8,2) NOT NULL,
  arquivado_em  DATETIME NULL              -- NULL = ativo
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE socios (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  numero         VARCHAR(10) NOT NULL UNIQUE,
  utilizador_id  INT NULL UNIQUE,
  nome           VARCHAR(100) NOT NULL,
  plano_id       INT NOT NULL,
  data_inscricao DATE NOT NULL,
  arquivado_em   DATETIME NULL,            -- NULL = ativo; arquivado mantém mensalidades e presenças
  FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id),
  FOREIGN KEY (plano_id) REFERENCES planos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE mensalidades (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  socio_id       INT NOT NULL,
  referencia     VARCHAR(40) NOT NULL,
  valor          DECIMAL(8,2) NOT NULL,
  data_limite    DATE NOT NULL,
  data_pagamento DATE NULL,
  estado         ENUM('Pendente','Paga','Em atraso') NOT NULL DEFAULT 'Pendente',
  metodo         ENUM('Receção','MB Way','Cartão') NULL,  -- como foi paga
  registado_por  INT NULL,                 -- quem registou o pagamento
  FOREIGN KEY (socio_id) REFERENCES socios(id),
  FOREIGN KEY (registado_por) REFERENCES utilizadores(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Aulas ----------
CREATE TABLE modalidades (
  id   INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE aulas (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  modalidade_id INT NOT NULL,
  treinador_id  INT NOT NULL,
  dia_semana    ENUM('Segunda','Terça','Quarta','Quinta','Sexta','Sábado') NOT NULL,
  hora_inicio   TIME NOT NULL,
  sala          VARCHAR(20) NOT NULL,
  lotacao_max   INT NOT NULL,
  arquivado_em  DATETIME NULL,
  FOREIGN KEY (modalidade_id) REFERENCES modalidades(id),
  FOREIGN KEY (treinador_id)  REFERENCES utilizadores(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE inscricoes (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  aula_id   INT NOT NULL,
  socio_id  INT NOT NULL,
  presente  TINYINT(1) NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY (aula_id, socio_id),
  FOREIGN KEY (aula_id)  REFERENCES aulas(id) ON DELETE CASCADE,
  FOREIGN KEY (socio_id) REFERENCES socios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
