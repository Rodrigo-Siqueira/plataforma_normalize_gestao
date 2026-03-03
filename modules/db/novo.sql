
CREATE TABLE paciente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pessoa_id INT NOT NULL,
    psicologo_responsavel_id INT NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pessoa_id) REFERENCES pessoa(id),
    FOREIGN KEY (psicologo_responsavel_id) REFERENCES psicologo(id)
);

CREATE TABLE responsavel_paciente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    responsavel_id INT NOT NULL, -- pessoa
    paciente_id INT NOT NULL,
    tipo ENUM('PROPRIO','PAI','MAE','RESPONSAVEL','OUTRO') NOT NULL DEFAULT 'RESPONSAVEL',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (responsavel_id) REFERENCES pessoa(id),
    FOREIGN KEY (paciente_id) REFERENCES paciente(id),
    UNIQUE (responsavel_id, paciente_id)
) ENGINE=InnoDB;

CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,

    ultimo_login TIMESTAMP NULL,
    tentativas_login INT NOT NULL DEFAULT 0,
    bloqueado_ate TIMESTAMP NULL,

    reset_senha_token VARCHAR(255),
    reset_senha_expira_em TIMESTAMP,

    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE papel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao VARCHAR(255),
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE usuario_papel (
    usuario_id INT NOT NULL,
    papel_id INT NOT NULL,
    atribuido_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (usuario_id, papel_id),
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (papel_id) REFERENCES papel(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

ALTER TABLE psicologo
ADD usuario_id INT UNIQUE,
ADD CONSTRAINT fk_psicologo_usuario
FOREIGN KEY (usuario_id) REFERENCES usuario(id)
ON DELETE RESTRICT;

CREATE INDEX idx_usuario_email_ativo
ON usuario(email, ativo);

CREATE INDEX idx_usuario_bloqueio
ON usuario(bloqueado_ate);


