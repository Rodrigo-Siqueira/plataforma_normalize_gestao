-- Usando o banco de dados recém-criado
-- USE dev_agendamento_db;

-- Tabela 1: Produtos (Pacotes e Sessões Avulsas)
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('PACOTE', 'AVULSO') NOT NULL,
    numero_sessoes INT NOT NULL DEFAULT 1,
    preco DECIMAL(10, 2) NOT NULL,
    validade_dias INT NOT NULL DEFAULT 90, -- 0 para infinito
    ativo BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;

-- Tabela 2: Responsáveis Financeiros
-- Armazena os dados de quem paga pelas sessões.
CREATE TABLE responsaveis_financeiros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_completo VARCHAR(150) NOT NULL,
    contato VARCHAR(20),
    paciente BOOLEAN NOT NULL DEFAULT TRUE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;

-- Tabela 3: Pacientes
CREATE TABLE pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    responsavel_financeiro_id INT NOT NULL,
    nome_completo VARCHAR(150) NOT NULL,
    contato VARCHAR(20),
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (responsavel_financeiro_id) REFERENCES responsaveis_financeiros(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Tabela 4: Perfis dos Psicólogos
CREATE TABLE psicologo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_completo VARCHAR(150) NOT NULL,
    crp VARCHAR(8) NOT NULL,
    contato VARCHAR(20) NOT NULL,
    chave_pix VARCHAR(100),
    agencia VARCHAR(10),
    conta VARCHAR(20),
    percentual_comissao DECIMAL(5, 2) NOT NULL DEFAULT 0.50,
    data_inicio DATE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;

-- Tabela 5: Vendas
-- Registra a compra de um produto por um paciente/responsável.
CREATE TABLE vendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    responsavel_id INT NOT NULL,
    produto_id INT NOT NULL,
    valor_cobrado DECIMAL(10, 2) NOT NULL,
    data_venda TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status_pagamento ENUM('PENDENTE', 'PAGO', 'ATRASADO', 'CANCELADO') NOT NULL DEFAULT 'PENDENTE',
    data_pagamento DATE,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (responsavel_id) REFERENCES responsaveis_financeiros(id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB;

-- Tabela 6: Lotes de Repasse
-- Agrupa sessões para pagamento em lote aos psicólogos.
CREATE TABLE lotes_repasse (
    id INT AUTO_INCREMENT PRIMARY KEY,
    psicologo_id INT NOT NULL,
    periodo_inicio DATE NOT NULL,
    periodo_fim DATE NOT NULL,
    valor_liquido_pago DECIMAL(10, 2) NOT NULL,
    status ENUM('ABERTO', 'PAGO') NOT NULL DEFAULT 'ABERTO',
    data_geracao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_pagamento DATETIME,
    FOREIGN KEY (psicologo_id) REFERENCES psicologo(id)
) ENGINE=InnoDB;

-- Tabela 7: Sessões
-- O coração operacional: registra cada agendamento.
CREATE TABLE sessoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    psicologo_id INT NOT NULL,
    venda_origem_id INT NOT NULL,
    lote_repasse_id INT, -- Fica nulo até a sessão ser incluída em um lote
    data_agendada DATE NOT NULL,
    hora_agendada TIME NOT NULL,
    status ENUM('AGENDADA', 'REALIZADA', 'CANCELADA_PACIENTE', 'CANCELADA_PSICOLOGO', 'REMARCADA') NOT NULL DEFAULT 'AGENDADA',
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (psicologo_id) REFERENCES psicologo(id),
    FOREIGN KEY (venda_origem_id) REFERENCES vendas(id),
    FOREIGN KEY (lote_repasse_id) REFERENCES lotes_repasse(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Tabela 9: Despesas Operacionais
-- Para registrar outros custos da plataforma (marketing, software, etc.).
CREATE TABLE despesas_operacionais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(200) NOT NULL,
    tipo ENUM('FIXA', 'VARIAVEL') NOT NULL DEFAULT 'VARIAVEL',
    categoria ENUM('MARKETING', 'SOFTWARE', 'IMPOSTOS', 'CONTABILIDADE', 'INTERNET', 'GOOGLE ADS') NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data_competencia DATE NOT NULL,
    data_pagamento DATE,
    status ENUM('PENDENTE', 'PAGO') NOT NULL DEFAULT 'PENDENTE'
) ENGINE=InnoDB;









-- Tabela 1: Usuários (Autenticação Central)
-- Armazena os dados de login e o papel de cada pessoa no sistema.
CREATE TABLE `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `senha_hash` VARCHAR(256) NOT NULL,
    `papel` ENUM('GESTOR', 'PSICOLOGO') NOT NULL,
    `ativo` BOOLEAN NOT NULL DEFAULT TRUE,
    `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela 10: Auditoria (Logs)
-- Registra ações críticas para rastreabilidade.
CREATE TABLE `auditoria` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT, -- Quem fez a ação (pode ser nulo se for uma ação do sistema)
    `acao` VARCHAR(255) NOT NULL,
    `entidade` VARCHAR(50),
    `entidade_id` INT,
    `detalhes` TEXT,
    `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;



SELECT 
	s.id as sessao_id,
    v.valor_cobrado,
    prod.numero_sessoes,
    p.percentual_comissao
FROM sessoes s
JOIN vendas v ON s.venda_origem_id = v.id
JOIN psicologo p ON s.psicologo_id = p.id
JOIN produtos prod ON prod.id = v.produto_id
WHERE s.psicologo_id = 2
AND s.status = 'REALIZADA'
AND s.lote_repasse_id IS NULL
AND s.data_agendada BETWEEN '2025-09-01' AND '2025-09-08'


-- Entradas
SELECT
    v.data_pagamento as data,
    p.nome as produto_nome,
    pac.nome_completo as paciente_nome,
    v.valor_cobrado as valor,
    'Venda' as tipo
FROM vendas v
JOIN produtos p ON v.produto_id = p.id
JOIN pacientes pac ON v.paciente_id = pac.id
WHERE v.status_pagamento = 'PAGO'

-- Despesa
SELECT
    desp.data_pagamento as data,
    desp.descricao,
    desp.categoria,
    desp.valor,
	'Despesa' as tipo
FROM despesas_operacionais AS desp
WHERE desp.status = 'PAGO'

-- Repasses
SELECT 
	l.data_pagamento as data,
	p.nome_completo ,
    CONCAT(DATE_FORMAT(l.periodo_inicio, '%d'), 
           ' à ', 
           DATE_FORMAT(l.periodo_fim, '%d-%m-%Y')) as periodo,
    l.valor_liquido_pago,
	'REPASSE' as tipo
FROM lotes_repasse AS l 
JOIN psicologo p ON p.id = l.psicologo_id
WHERE l.status = 'PAGO'