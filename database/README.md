# Banco de dados

O arquivo `schema.sql` contém a estrutura atual do banco, sem dados.

## Criar o banco

```bash
mysql -u root -p -e "
CREATE DATABASE agendamento_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;
"