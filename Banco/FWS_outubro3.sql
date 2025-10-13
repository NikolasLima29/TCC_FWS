-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           8.0.30 - MySQL Community Server - GPL
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Copiando estrutura do banco de dados para fws
CREATE DATABASE IF NOT EXISTS `fws` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `fws`;

-- Copiando estrutura para tabela fws.categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.categorias: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela fws.despesas
CREATE TABLE IF NOT EXISTS `despesas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(255) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_despesa` date NOT NULL,
  `tipo` enum('compra','salario','manutencao','agua','luz','internet','outros') DEFAULT 'outros',
  `categoria_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `despesas_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.despesas: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela fws.fornecedores
CREATE TABLE IF NOT EXISTS `fornecedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `cnpj` varchar(14) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cnpj` (`cnpj`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.fornecedores: ~22 rows (aproximadamente)
INSERT INTO `fornecedores` (`id`, `nome`, `cnpj`, `telefone`, `email`) VALUES
	(1, 'SR VINHO COMÉRCIO DE VINHOS E BEBIDAS LTDA', '24330541000146', '11947927692', 'contato@srvinhos.com.br'),
	(2, 'MULT E DIGITAL COMERCIO DE PRODUTOS ALIMENTICIOS LTDA', '37885698000180', '1141310409', 'jocalbuquerque@bol.com.br'),
	(3, 'PEPSICO DO BRASIL LTDA', '31565104000177', '1151887742', 'regulatorio@pepsico.com'),
	(4, 'SPAL IND BRASILEIRA DE BEBIDAS', '61186888000193', '1129633135', 'fiscal@kof.com'),
	(5, 'MALTE COMERCIO DE BEBIDAS LTDA MATRIZ', '24179819000126', '1940186336', 'aceduardocarrer@riftdistribuidora.com.br'),
	(6, 'EMPRESA BRASILEIRA DE DISTRIBUICAO LTDA', '05402904000914', '9132049500', 'ebdatende@ebdgrupo.com.br'),
	(7, 'AMBEV S/A CDD - SAO PAULO', '02808708000956', '1130447236', 'opobrigaces@ambev.com.br'),
	(8, 'FACIL DISTRIBUIDORA DE ALIMENTOS LTDA', '11479810000199', '1136560177', 'contato@facilsp.com.br'),
	(9, 'AT DISTRIBUIDORA E REPR', '07782291000166', '1137815439', 'pedidos@atdis.com.br'),
	(10, 'KOZZY ALIMENTOS LTDA', '01820028000138', '1145493948', 'kozzy@kozzy.com.br'),
	(11, 'NEW JUICE DISTRIBUIDORA DE BEBIDAS LTDA', '03877063000119', '1135010350', 'contato@newjuice.com.br'),
	(12, 'TRES CORACOES ALIMENTOS SA', '63310411000101', '11989821930', 'atendimento@3coracoes.com.br'),
	(13, 'NESTLE BRASIL LTDA', '60409075000152', '11978933289', 'celso.mattos@br.nestle.com'),
	(14, 'CABOCLO DISTRIBUIDOR LTDA', '60959160000194', '1150743000', 'adm@caboblodistribuidor.com.br'),
	(15, 'SAMAUMA BRANDS', '16593757000419', '1145624222', 'mcastro@samaumabrands.com'),
	(16, 'FG7 COMERCIO E DISTRIBUICAO DE BEBIDAS - EIRELI - EPP', '18694748000105', '1158513772', 'site@fg7bebidas.com.br'),
	(17, 'BRASIL KIRIN LOGISTICA E DISTRIBUICAO LTDA', '05254957003950', '1121189500', 'vendas@heineken.com.br'),
	(18, 'CONTINENTAL DISTR DE PROD ALIMENT DE HIGIENE LTDA', '05706012000150', '1146671091', 'williamp@continentalbomdia.com.br'),
	(19, 'RZS NICOLA FABRICACAO E COMERCIO DE GELO - ME', '07733106000143', '1132582632', 'luiz@fastgelo.com.br'),
	(20, 'DMA INDUSTRIA E COMERCIO DE PRODUTOS ALIMENTICIOS LTDA', '05212531000161', '1132289925', 'yscontabil@gmail.com'),
	(21, 'UNILEVER BRASIL LTDA', '61068276000104', '1135688000', 'sac@ades.com.br'),
	(22, 'LP COMERCIO DE SALGADOS LTDA', '24836224000104', '1138715778', 'legal@mwa.com.br');

-- Copiando estrutura para tabela fws.funcionarios
CREATE TABLE IF NOT EXISTS `funcionarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nivel_permissao` tinyint NOT NULL,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_login` datetime DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf` (`cpf`),
  UNIQUE KEY `email` (`email`),
  CONSTRAINT `funcionarios_chk_1` CHECK ((`nivel_permissao` between 1 and 3))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.funcionarios: ~0 rows (aproximadamente)
INSERT INTO `funcionarios` (`id`, `nome`, `cpf`, `email`, `senha`, `nivel_permissao`, `criado_em`, `ultimo_login`, `ativo`) VALUES
	(1, 'Mônica', '123.456.789-10', 'Monica@redecampeao.com.br', '$2y$10$6hajNuGfFlg6txtji4XJ..HQCDtd.bonljgRVxPfqEE30GIOXNxIu', 3, '2025-08-13 13:43:01', NULL, 1);

-- Copiando estrutura para tabela fws.itens_vendidos
CREATE TABLE IF NOT EXISTS `itens_vendidos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `venda_id` int NOT NULL,
  `produto_id` int NOT NULL,
  `quantidade` int NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `venda_id` (`venda_id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `itens_vendidos_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`),
  CONSTRAINT `itens_vendidos_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.itens_vendidos: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela fws.movimentacao_estoque
CREATE TABLE IF NOT EXISTS `movimentacao_estoque` (
  `id` int NOT NULL AUTO_INCREMENT,
  `produto_id` int NOT NULL,
  `quantidade` int NOT NULL,
  `tipo_movimentacao` enum('entrada','saida') NOT NULL,
  `data_movimentacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `descricao` varchar(255) DEFAULT NULL,
  `venda_id` int DEFAULT NULL,
  `fornecedor_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `produto_id` (`produto_id`),
  KEY `venda_id` (`venda_id`),
  KEY `fornecedor_id` (`fornecedor_id`),
  CONSTRAINT `movimentacao_estoque_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`),
  CONSTRAINT `movimentacao_estoque_ibfk_2` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`),
  CONSTRAINT `movimentacao_estoque_ibfk_3` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.movimentacao_estoque: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela fws.pagamentos
CREATE TABLE IF NOT EXISTS `pagamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `venda_id` int NOT NULL,
  `valor_pago` decimal(10,2) NOT NULL,
  `data_pagamento` datetime DEFAULT CURRENT_TIMESTAMP,
  `metodo` enum('dinheiro','cartao_credito','cartao_debito','pix','boleto','outros') DEFAULT 'dinheiro',
  PRIMARY KEY (`id`),
  KEY `venda_id` (`venda_id`),
  CONSTRAINT `pagamentos_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.pagamentos: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela fws.produtos
CREATE TABLE IF NOT EXISTS `produtos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `categoria_id` int NOT NULL,
  `fornecedor_id` int NOT NULL,
  `descricao` text,
  `foto_produto` varchar(255) DEFAULT NULL,
  `preco_venda` decimal(10,2) DEFAULT NULL,
  `preco_compra` decimal(10,2) DEFAULT NULL,
  `estoque` int DEFAULT '0',
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `categoria_id` (`categoria_id`),
  KEY `fornecedor_id` (`fornecedor_id`),
  CONSTRAINT `produtos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  CONSTRAINT `produtos_ibfk_2` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.produtos: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela fws.retiradas
CREATE TABLE IF NOT EXISTS `retiradas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `produto_id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `funcionario_id` int DEFAULT NULL,
  `quantidade` int NOT NULL,
  `data_retirada` datetime DEFAULT CURRENT_TIMESTAMP,
  `tipo_motivo` enum('uso_interno','roubo','quebra','doacao','outros') DEFAULT 'outros',
  `motivo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `produto_id` (`produto_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `funcionario_id` (`funcionario_id`),
  CONSTRAINT `retiradas_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`),
  CONSTRAINT `retiradas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `retiradas_ibfk_3` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.retiradas: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela fws.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `data_nascimento` date NOT NULL,
  `cpf` char(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_login` datetime DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `google_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.usuarios: ~2 rows (aproximadamente)
INSERT INTO `usuarios` (`id`, `nome`, `data_nascimento`, `cpf`, `email`, `senha`, `criado_em`, `ultimo_login`, `ativo`, `google_id`) VALUES
	(1, 'nikolas', '2007-04-29', '11111111111', 'nathally@gmail.com', '$2y$10$McU82llwKohDhO0HP3jYseTFLndusQV/kSm.Z.VqW/aj3NeScuxlW', '2025-10-08 16:20:15', '2025-10-08 13:21:55', 1, NULL),
	(2, 'rafael', '2007-06-15', '22222222222', 'rafael11@gmail.com', '$2y$10$nE61x7RZrBoid/mxuoT2j.y3qleoLe90RfQbNox.tJFfwglF40pje', '2025-10-08 16:30:49', '2025-10-08 13:31:15', 1, NULL);

-- Copiando estrutura para tabela fws.vendas
CREATE TABLE IF NOT EXISTS `vendas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `funcionario_id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `status_pagamento` enum('pago','pendente','cancelado') DEFAULT 'pendente',
  `data_venda` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `funcionario_id` (`funcionario_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `vendas_ibfk_1` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`),
  CONSTRAINT `vendas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.vendas: ~0 rows (aproximadamente)

-- Copiando estrutura para trigger fws.atualizar_estoque
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `atualizar_estoque` AFTER INSERT ON `movimentacao_estoque` FOR EACH ROW BEGIN
    IF NEW.tipo_movimentacao = 'entrada' THEN
        UPDATE produtos
        SET estoque = estoque + NEW.quantidade
        WHERE id = NEW.produto_id;
    ELSEIF NEW.tipo_movimentacao = 'saida' THEN
        UPDATE produtos
        SET estoque = estoque - NEW.quantidade
        WHERE id = NEW.produto_id;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Copiando estrutura para trigger fws.registrar_saida_venda
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `registrar_saida_venda` AFTER INSERT ON `itens_vendidos` FOR EACH ROW BEGIN
    INSERT INTO movimentacao_estoque (produto_id, quantidade, tipo_movimentacao, venda_id, descricao)
    VALUES (NEW.produto_id, NEW.quantidade, 'saida', NEW.venda_id, CONCAT('Venda ID ', NEW.venda_id));
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Copiando estrutura para trigger fws.validar_estoque
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `validar_estoque` BEFORE INSERT ON `movimentacao_estoque` FOR EACH ROW BEGIN
    DECLARE estoque_atual INT DEFAULT 0;

    SELECT estoque INTO estoque_atual 
    FROM produtos 
    WHERE id = NEW.produto_id;

    IF NEW.tipo_movimentacao = 'saida' AND estoque_atual < NEW.quantidade THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Estoque insuficiente para essa saída';
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
