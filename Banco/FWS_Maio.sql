-- --------------------------------------------------------
-- Banco de dados: fws (versão final para TCC)
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `fws` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */;
USE `fws`;

-- ============================
-- Tabela categorias
-- ============================
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================
-- Tabela fornecedores
-- ============================
CREATE TABLE IF NOT EXISTS `fornecedores` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `razao_social` VARCHAR(150) NOT NULL,
  `nome_fantasia` VARCHAR(100),
  `cnpj` CHAR(14) NOT NULL UNIQUE,
  `contato` VARCHAR(100),
  `telefone` VARCHAR(20),
  `email` VARCHAR(100),
  `endereco` VARCHAR(255),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================
-- Tabela usuarios
-- ============================
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `data_nascimento` DATE NOT NULL,
  `cpf` CHAR(11) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_login` DATETIME NULL,
  `ativo` BOOLEAN DEFAULT TRUE,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf` (`cpf`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================
-- Tabela funcionarios
-- ============================
CREATE TABLE IF NOT EXISTS `funcionarios` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `cpf` VARCHAR(14) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `nivel_permissao` TINYINT NOT NULL,
  `criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_login` DATETIME NULL,
  `ativo` BOOLEAN DEFAULT TRUE,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cpf` (`cpf`),
  UNIQUE KEY `email` (`email`),
  CONSTRAINT `funcionarios_chk_1` CHECK ((`nivel_permissao` BETWEEN 1 AND 3))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================
-- Tabela produtos
-- ============================
CREATE TABLE IF NOT EXISTS `produtos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `codigo` VARCHAR(50) NOT NULL,
  `categoria_id` INT NOT NULL,
  `fornecedor_id` INT NOT NULL,
  `descricao` TEXT,
  `foto_produto` VARCHAR(255) DEFAULT NULL,
  `preco` DECIMAL(10,2) DEFAULT NULL,
  `estoque` INT DEFAULT '0',
  `status` ENUM('ativo','inativo') DEFAULT 'ativo',
  `criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `categoria_id` (`categoria_id`),
  KEY `fornecedor_id` (`fornecedor_id`),
  CONSTRAINT `produtos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  CONSTRAINT `produtos_ibfk_2` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================
-- Tabela despesas
-- ============================
CREATE TABLE IF NOT EXISTS `despesas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `descricao` VARCHAR(255) NOT NULL,
  `valor` DECIMAL(10,2) NOT NULL,
  `data_despesa` DATE NOT NULL,
  `tipo` ENUM('compra','salario','manutencao','agua','luz','internet','outros') DEFAULT 'outros',
  `categoria_id` INT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================
-- Tabela vendas
-- ============================
CREATE TABLE IF NOT EXISTS `vendas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `funcionario_id` INT NOT NULL,
  `usuario_id` INT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  `data_venda` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `funcionario_id` (`funcionario_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `vendas_ibfk_1` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`),
  CONSTRAINT `vendas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================
-- Tabela itens_vendidos
-- ============================
CREATE TABLE IF NOT EXISTS `itens_vendidos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `venda_id` INT NOT NULL,
  `produto_id` INT NOT NULL,
  `quantidade` INT NOT NULL,
  `preco_unitario` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `venda_id` (`venda_id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `itens_vendidos_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`),
  CONSTRAINT `itens_vendidos_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================
-- Tabela retiradas
-- ============================
CREATE TABLE IF NOT EXISTS `retiradas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `produto_id` INT NOT NULL,
  `usuario_id` INT NULL,
  `funcionario_id` INT NULL,
  `quantidade` INT NOT NULL,
  `data_retirada` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `tipo_motivo` ENUM('uso_interno','roubo','quebra','doacao','outros') DEFAULT 'outros',
  `motivo` VARCHAR(255),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================
-- Tabela movimentacao_estoque
-- ============================
CREATE TABLE IF NOT EXISTS `movimentacao_estoque` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `produto_id` INT NOT NULL,
  `quantidade` INT NOT NULL,
  `tipo_movimentacao` ENUM('entrada','saida') NOT NULL,
  `data_movimentacao` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `descricao` VARCHAR(255),
  `venda_id` INT NULL,
  `fornecedor_id` INT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`),
  FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`),
  FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================
-- Tabela pagamentos
-- ============================
CREATE TABLE IF NOT EXISTS `pagamentos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `venda_id` INT NOT NULL,
  `valor_pago` DECIMAL(10,2) NOT NULL,
  `data_pagamento` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `metodo` ENUM('dinheiro','cartao_credito','cartao_debito','pix','boleto','outros') DEFAULT 'dinheiro',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================
-- Inserts existentes
-- ============================
INSERT INTO `funcionarios` (`id`, `nome`, `cpf`, `email`, `senha`, `nivel_permissao`, `criado_em`) VALUES
(1, 'Mônica', '123.456.789-10', 'Monica@redecampeao.com.br', '$2y$10$6hajNuGfFlg6txtji4XJ..HQCDtd.bonljgRVxPfqEE30GIOXNxIu', 3, '2025-08-13 10:43:01');

