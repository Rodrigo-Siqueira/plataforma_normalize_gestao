
--  Passos para migração psicologo <--> pessooa
-- 1. Criar a tabela de pessoas
-- 2. Mover os dados da tabela psicologo para pessoa
INSERT INTO pessoa (nome_completo, contato) 
    SELECT nome_completo, contato FROM psicologo;
-- 3. Adicionar campo pessoa_id na tabela psicologo
ALTER TABLE psicologo 
ADD pessoa_id INT NULL AFTER id;
-- 4. Fazer o update em psicologo
UPDATE psicologo psi
JOIN pessoa pe ON pe.nome_completo = psi.nome_completo
SET psi.pessoa_id = pe.id;
-- 5. Verificar se deu certo
SELECT * FROM psicologo WHERE pessoa_id IS NULL; -- deve retornar 0
-- 6. Consolidar a estrutura
ALTER TABLE psicologo 
MODIFY pessoa_id INT NOT NULL;

ALTER TABLE psicologo 
ADD UNIQUE (pessoa_id);

ALTER TABLE psicologo 
ADD CONSTRAINT fk_psicologo_pessoa
FOREIGN KEY (pessoa_id) REFERENCES pessoa(id);

-- 7. Remover os campos duplicado de psicologo
ALTER TABLE psicologo DROP COLUMN nome_completo;
ALTER TABLE psicologo DROP COLUMN contato;

--  Fim migração psicologo <--> pessooa


--  Passos para migração paciente <--> pessooa
-- 1. Verificar possivel duplicidade em pacientes.
SELECT nome_completo, contato, COUNT(*) as total
FROM paciente
GROUP BY nome_completo, contato
HAVING COUNT(*) > 1;
-- 2. Criar nova tabela responsavel_paciente
-- 3. Adicionar campo pessoa_id na tabela paciente
ALTER TABLE paciente 
ADD COLUMN pessoa_id INT NULL AFTER id;
-- 4. Mover os dados da tabela paciente para pessoa
INSERT INTO pessoa (nome_completo, contato)
SELECT DISTINCT pa.nome_completo, pa.contato
FROM paciente pa
LEFT JOIN pessoa pe
    ON pe.nome_completo = pa.nome_completo
    AND (
        pe.contato = pa.contato
        OR (pe.contato IS NULL AND pa.contato IS NULL)
    )
WHERE pe.id IS NULL;
-- 5. Adicionar campo pessoa_id na tabela psicologo
UPDATE paciente pa
JOIN pessoa pe
    ON pe.nome_completo = pa.nome_completo
    AND (
        pe.contato = pa.contato
        OR (pe.contato IS NULL AND pa.contato IS NULL)
    )
SET pa.pessoa_id = pe.id;
-- 6. Verificar se deu certo
SELECT * FROM paciente WHERE pessoa_id IS NULL;
-- 7. Verificar possivel duplicidade em resp financeiro.
SELECT nome_completo, contato, COUNT(*) as total
FROM responsavel_financeiro
GROUP BY nome_completo, contato
HAVING COUNT(*) > 1;
-- 8. Mover os dados da tabela paciente para pessoa
INSERT INTO pessoa (nome_completo, contato)
SELECT DISTINCT rf.nome_completo, rf.contato
FROM responsavel_financeiro rf
LEFT JOIN pessoa pe
    ON pe.nome_completo = rf.nome_completo
    AND (
        pe.contato = rf.contato
        OR (pe.contato IS NULL AND rf.contato IS NULL)
    )
WHERE pe.id IS NULL;
-- 9. Criar vinculo responsavel <--> paciente
INSERT INTO responsavel_paciente (responsavel_id, paciente_id, tipo)
SELECT 
    pe.id,
    pa.id,
    CASE 
        WHEN rf.eh_paciente = 1 THEN 'PROPRIO'
        ELSE 'OUTRO'
    END
FROM paciente pa
JOIN responsavel_financeiro rf 
    ON rf.id = pa.responsavel_financeiro_id
JOIN pessoa pe
    ON pe.nome_completo = rf.nome_completo
    AND (
        pe.contato = rf.contato
        OR (pe.contato IS NULL AND rf.contato IS NULL)
    );
-- 10. Ajustar venda Criar coluna
ALTER TABLE venda 
ADD responsavel_pessoa_id INT NULL 
AFTER paciente_id;
-- 11. Migrar dados em venda
UPDATE venda v
JOIN responsavel_financeiro rf 
    ON rf.id = v.responsavel_id
JOIN pessoa pe 
    ON pe.nome_completo = rf.nome_completo
    AND pe.contato = rf.contato
SET v.responsavel_pessoa_id = pe.id;
-- 12. Validar
SELECT * FROM venda WHERE responsavel_pessoa_id IS NULL;
-- 13. Consolidar a estrutura
ALTER TABLE venda 
MODIFY responsavel_pessoa_id INT NOT NULL;

ALTER TABLE venda 
ADD CONSTRAINT fk_venda_responsavel
FOREIGN KEY (responsavel_pessoa_id) REFERENCES pessoa(id);
-- 14. Consolidar a estrutura Paciente
ALTER TABLE paciente 
MODIFY pessoa_id INT NOT NULL;

ALTER TABLE paciente 
ADD CONSTRAINT fk_paciente_pessoa
FOREIGN KEY (pessoa_id) REFERENCES pessoa(id);
-- AUDITAR
SELECT nome_completo, contato, COUNT(*) as total
FROM pessoa
GROUP BY nome_completo, contato
HAVING COUNT(*) > 1;
ALTER TABLE paciente DROP COLUMN nome_completo;
ALTER TABLE paciente DROP COLUMN contato;

ALTER TABLE paciente DROP FOREIGN KEY paciente_ibfk_1;
ALTER TABLE paciente DROP INDEX responsavel_financeiro_id;
ALTER TABLE paciente DROP COLUMN responsavel_financeiro_id;


ALTER TABLE venda DROP FOREIGN KEY venda_ibfk_2;
ALTER TABLE venda DROP INDEX responsavel_id;
ALTER TABLE venda DROP COLUMN responsavel_id;
--  Fim migração paciente <--> pessooa