
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `dados_bancario_psicologo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dados_bancario_psicologo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `psicologo_id` int(11) NOT NULL,
  `pix_chave` varchar(50) NOT NULL,
  `agencia` varchar(10) NOT NULL,
  `conta` varchar(20) NOT NULL,
  `tipo_conta` enum('CORRENTE','POUPANÇA') NOT NULL DEFAULT 'CORRENTE',
  `cpf_cnpj` varchar(30) NOT NULL,
  `nome_titular` varchar(100) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `psicologo_id` (`psicologo_id`),
  CONSTRAINT `dados_bancario_psicologo_ibfk_1` FOREIGN KEY (`psicologo_id`) REFERENCES `psicologo` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `despesa_operacional`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `despesa_operacional` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(200) NOT NULL,
  `tipo` enum('FIXA','VARIAVEL') NOT NULL DEFAULT 'VARIAVEL',
  `categoria` enum('MARKETING','SOFTWARE','IMPOSTOS','CONTABILIDADE','INTERNET','GOOGLE ADS') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_competencia` date NOT NULL,
  `data_pagamento` date DEFAULT NULL,
  `status` enum('PENDENTE','PAGO') NOT NULL DEFAULT 'PENDENTE',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lote_repasse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lote_repasse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `psicologo_id` int(11) NOT NULL,
  `periodo_inicio` date NOT NULL,
  `periodo_fim` date NOT NULL,
  `valor_liquido_pago` decimal(10,2) NOT NULL,
  `status` enum('ABERTO','PAGO') NOT NULL DEFAULT 'ABERTO',
  `data_pagamento` datetime DEFAULT NULL,
  `data_geracao` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `psicologo_id` (`psicologo_id`),
  CONSTRAINT `lote_repasse_ibfk_1` FOREIGN KEY (`psicologo_id`) REFERENCES `psicologo` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `paciente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paciente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pessoa_id` int(11) NOT NULL,
  `psicologo_responsavel_id` int(11) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_paciente_pessoa` (`pessoa_id`),
  KEY `fk_paciente_psicologo` (`psicologo_responsavel_id`),
  CONSTRAINT `fk_paciente_pessoa` FOREIGN KEY (`pessoa_id`) REFERENCES `pessoa` (`id`),
  CONSTRAINT `fk_paciente_psicologo` FOREIGN KEY (`psicologo_responsavel_id`) REFERENCES `psicologo` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=173 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pessoa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pessoa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(100) NOT NULL,
  `contato` varchar(20) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=256 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `produto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `produto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `tipo` enum('PACOTE','AVULSO') NOT NULL,
  `numero_sessoes` int(11) NOT NULL DEFAULT 1,
  `preco` decimal(10,2) NOT NULL,
  `validade_dias` int(11) NOT NULL DEFAULT 30,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `psicologo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `psicologo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pessoa_id` int(11) NOT NULL,
  `crp` varchar(8) NOT NULL,
  `percentual_comissao` decimal(5,2) NOT NULL DEFAULT 0.50,
  `data_inicio` date DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_psicologo_pessoa` (`pessoa_id`),
  CONSTRAINT `fk_psicologo_pessoa` FOREIGN KEY (`pessoa_id`) REFERENCES `pessoa` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `responsavel_paciente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `responsavel_paciente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `responsavel_id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `tipo` enum('PROPRIO','PAI','MAE','OUTRO') NOT NULL DEFAULT 'OUTRO',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_responsavel_paciente` (`responsavel_id`,`paciente_id`),
  KEY `fk_responsavel_paciente_paciente` (`paciente_id`),
  CONSTRAINT `fk_responsavel_paciente_paciente` FOREIGN KEY (`paciente_id`) REFERENCES `paciente` (`id`),
  CONSTRAINT `fk_responsavel_paciente_pessoa` FOREIGN KEY (`responsavel_id`) REFERENCES `pessoa` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=256 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paciente_id` int(11) NOT NULL,
  `psicologo_id` int(11) NOT NULL,
  `venda_origem_id` int(11) NOT NULL,
  `lote_repasse_id` int(11) DEFAULT NULL,
  `data_agendada` date NOT NULL,
  `hora_agendada` time NOT NULL,
  `status` enum('AGENDADA','REALIZADA','CANCELADA_PACIENTE','CANCELADA_PSICOLOGO','REMARCADA') NOT NULL DEFAULT 'AGENDADA',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `paciente_id` (`paciente_id`),
  KEY `psicologo_id` (`psicologo_id`),
  KEY `venda_origem_id` (`venda_origem_id`),
  KEY `lote_repasse_id` (`lote_repasse_id`),
  CONSTRAINT `sessao_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `paciente` (`id`),
  CONSTRAINT `sessao_ibfk_2` FOREIGN KEY (`psicologo_id`) REFERENCES `psicologo` (`id`),
  CONSTRAINT `sessao_ibfk_3` FOREIGN KEY (`venda_origem_id`) REFERENCES `venda` (`id`),
  CONSTRAINT `sessao_ibfk_4` FOREIGN KEY (`lote_repasse_id`) REFERENCES `lote_repasse` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1366 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `venda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `venda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paciente_id` int(11) NOT NULL,
  `responsavel_pessoa_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `valor_cobrado` decimal(10,2) NOT NULL,
  `forma_pagamento` enum('PIX','CARTAO_CREDITO') NOT NULL DEFAULT 'PIX',
  `status_pagamento` enum('PENDENTE','PAGO','ATRASADO','CANCELADO') NOT NULL DEFAULT 'PENDENTE',
  `data_venda` date DEFAULT NULL,
  `data_pagamento` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `paciente_id` (`paciente_id`),
  KEY `produto_id` (`produto_id`),
  KEY `fk_venda_responsavel` (`responsavel_pessoa_id`),
  CONSTRAINT `fk_venda_responsavel` FOREIGN KEY (`responsavel_pessoa_id`) REFERENCES `pessoa` (`id`),
  CONSTRAINT `venda_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `paciente` (`id`),
  CONSTRAINT `venda_ibfk_3` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=472 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
