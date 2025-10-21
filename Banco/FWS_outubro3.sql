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

-- Copiando estrutura para tabela fws.carrinho
CREATE TABLE IF NOT EXISTS `carrinho` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `produto_id` int NOT NULL,
  `quantidade` int NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `codigo_cupom` varchar(50) DEFAULT NULL,
  `data_criacao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_produto` (`usuario_id`,`produto_id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `carrinho_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`),
  CONSTRAINT `carrinho_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.carrinho: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela fws.categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.categorias: ~10 rows (aproximadamente)
INSERT INTO `categorias` (`id`, `nome`) VALUES
	(1, 'BEBIDAS ALCOÓLICAS'),
	(2, 'BEBIDAS NÃO ALCOÓLICAS'),
	(8, 'BISCOITOS'),
	(9, 'CIGARROS E ITENS DE FUMO'),
	(4, 'DOCES'),
	(7, 'LATICÍNIOS'),
	(11, 'OUTROS'),
	(5, 'PROTEICOS'),
	(10, 'SALGADOS'),
	(6, 'SNACKS'),
	(3, 'SORVETES');

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

-- Copiando estrutura para evento fws.ev_expirar_pre_compras
DELIMITER //
CREATE EVENT `ev_expirar_pre_compras` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-10-19 20:51:14' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    
    INSERT INTO expiracoes_pre_compras (usuario_id, venda_id, data_expiracao)
    SELECT usuario_id, id, NOW()
    FROM vendas
    WHERE situacao_compra = 'pre_compra'
      AND TIMESTAMPDIFF(SECOND, data_criacao, NOW()) > TIME_TO_SEC(tempo_chegada);

    
    UPDATE vendas
    SET situacao_compra = 'cancelada'
    WHERE situacao_compra = 'pre_compra'
      AND TIMESTAMPDIFF(SECOND, data_criacao, NOW()) > TIME_TO_SEC(tempo_chegada);
END//
DELIMITER ;

-- Copiando estrutura para evento fws.ev_usuarios_7d
DELIMITER //
CREATE EVENT `ev_usuarios_7d` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-10-19 21:09:20' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    
    UPDATE usuarios u
    LEFT JOIN (
        SELECT usuario_id, COUNT(*) AS expiracoes_7d
        FROM expiracoes_pre_compras
        WHERE data_expiracao >= NOW() - INTERVAL 7 DAY
        GROUP BY usuario_id
    ) e ON u.id = e.usuario_id
    SET u.ativo = 1
    WHERE COALESCE(e.expiracoes_7d, 0) <= 1;

    
    UPDATE usuarios u
    JOIN (
        SELECT usuario_id, COUNT(*) AS expiracoes_7d
        FROM expiracoes_pre_compras
        WHERE data_expiracao >= NOW() - INTERVAL 7 DAY
        GROUP BY usuario_id
        HAVING expiracoes_7d >= 2
    ) e ON u.id = e.usuario_id
    SET u.ativo = 0;
END//
DELIMITER ;

-- Copiando estrutura para tabela fws.expiracoes_pre_compras
CREATE TABLE IF NOT EXISTS `expiracoes_pre_compras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `venda_id` int NOT NULL,
  `data_expiracao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `fk_exp_venda` (`venda_id`),
  CONSTRAINT `fk_exp_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_exp_venda` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.expiracoes_pre_compras: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela fws.fornecedores
CREATE TABLE IF NOT EXISTS `fornecedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `cnpj` varchar(14) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cnpj` (`cnpj`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.fornecedores: ~23 rows (aproximadamente)
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
	(22, 'LP COMERCIO DE SALGADOS LTDA', '24836224000104', '1138715778', 'legal@mwa.com.br'),
	(23, 'Fornecedor Teste', '99999999000199', NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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

-- Copiando estrutura para tabela fws.produtos
CREATE TABLE IF NOT EXISTS `produtos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
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
  UNIQUE KEY `id` (`id`),
  KEY `categoria_id` (`categoria_id`),
  KEY `fornecedor_id` (`fornecedor_id`),
  CONSTRAINT `produtos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  CONSTRAINT `produtos_ibfk_2` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.produtos: ~0 rows (aproximadamente)
INSERT INTO `produtos` (`id`, `nome`, `categoria_id`, `fornecedor_id`, `descricao`, `foto_produto`, `preco_venda`, `preco_compra`, `estoque`, `status`, `criado_em`) VALUES
	(1, 'VINHO BUENO AIRES MALBE 750ML', 1, 1, 'Um vinho Malbec elegante, produzido com uvas selecionadas que garantem sabor intenso, aromas frutados e taninos suaves. Ideal para acompanhar carnes vermelhas e momentos especiais.', '/IMG_Produtos/1.png', 69.90, 54.90, 1, 'ativo', '2025-10-20 17:54:49'),
	(2, 'VINHO FABRICANTE DE LOS MAN CABERNET SAUVIGNON BRANCO OU TINTO GF 750ML', 1, 1, 'Vinho Cabernet Sauvignon, disponível nas versões branco ou tinto, feito com uvas premium que trazem equilíbrio entre acidez e corpo, perfeito para harmonizar com queijos e pratos sofisticados.', '/IMG_Produtos/2.png', 69.90, 54.90, 3, 'ativo', '2025-10-20 17:54:49'),
	(3, 'ÁGUA DE COCO KERO COCO 1L', 2, 3, 'Água de coco 100% natural, rica em eletrólitos essenciais para hidratação rápida e renovação de energia, perfeita para o dia a dia e práticas esportivas.', '/IMG_Produtos/3.png', 31.99, 15.87, 5, 'ativo', '2025-10-20 17:54:49'),
	(4, 'ÁGUA DE COCO KERO COCO 330ML', 2, 3, 'Em embalagem prática, essa água de coco natural é fonte de potássio e minerais, ideal para refrescar e manter a hidratação em qualquer momento.', '/IMG_Produtos/4.png', 14.50, 6.56, 16, 'ativo', '2025-10-20 17:54:49'),
	(5, 'ÁGUA DE COCO KERO COCO CX 200ML', 2, 3, 'Compacta e nutritiva, essa água de coco oferece hidratação natural e energia, com sabor refrescante e leve, ótima para levar na bolsa ou lancheira.', '/IMG_Produtos/5.png', 6.90, 3.20, 15, 'ativo', '2025-10-20 17:54:49'),
	(6, 'ÁGUA MINERAL CRYSTAL COM GÁS PET 500ML', 2, 4, 'Água mineral com gás Crystal, puro frescor e efervescência suave que revitalizam seu paladar a qualquer hora do dia.', '/IMG_Produtos/6.png', 6.50, 2.24, 1, 'ativo', '2025-10-20 17:54:49'),
	(7, 'ÁGUA MINERAL MINALBA COM GÁS PET 1,5L', 2, 5, 'Água mineral com gás Minalba em embalagem econômica, leve e refrescante, perfeita para acompanhar suas refeições ou momentos de lazer.', '/IMG_Produtos/7.png', 8.99, 3.17, 13, 'ativo', '2025-10-20 17:54:49'),
	(8, 'ÁGUA MINERAL PRATA SEM GÁS PET 510ML', 2, 6, 'Água mineral sem gás Prata, naturalmente equilibrada em minerais essenciais para uma hidratação pura e saudável.', '/IMG_Produtos/8.png', 7.00, 2.85, 54, 'ativo', '2025-10-20 17:54:49'),
	(9, 'ÁGUA MINERAL MINALBA SEM GÁS PET 510ML', 2, 6, 'Água mineral sem gás Minalba, de sabor leve e refrescante, ideal para manter seu corpo hidratado com qualidade e naturalidade.', '/IMG_Produtos/9.png', 6.50, 2.75, 67, 'ativo', '2025-10-20 17:54:49'),
	(10, 'ÁGUA TONICA ANTARCTICA DIET LT 350ML', 2, 7, 'Água tônica dietética Antarctica, com sabor marcante e refrescante, adoçada artificialmente para quem busca sabor sem calorias.', '/IMG_Produtos/10.png', 8.00, 2.60, 7, 'ativo', '2025-10-20 17:54:49'),
	(11, 'ÁGUA TONICA ANTARCTICA LT 350ML', 2, 7, 'Água tônica Antarctica clássica, com mistura perfeita de quinino e gás que proporciona um sabor único e refrescante para seus drinks ou momentos de relaxamento.', '/IMG_Produtos/11.png', 8.00, 2.52, 10, 'ativo', '2025-10-20 17:54:49'),
	(12, 'AP BARBEAR BIC CONF3 NORMAL', 11, 2, 'Kit de aparelhos de barbear BIC com 3 lâminas, oferecendo precisão e conforto para um barbear eficiente e seguro, ideal para o cuidado diário da pele.', '/IMG_Produtos/12.png', 8.50, 4.11, 8, 'ativo', '2025-10-20 17:54:49'),
	(13, 'BALA DROPS HALLS MENTA PCT 28G', 4, 8, 'Bala Drops Halls sabor menta, proporciona frescor imediato para a garganta, ajudando a aliviar desconfortos e refrescar o hálito.', '/IMG_Produtos/13.png', 3.50, 1.19, 22, 'ativo', '2025-10-20 17:54:49'),
	(14, 'BALA DROPS HALLS MENTA PRATA PCT 28G', 4, 8, 'Bala Drops Halls Menta Prata com sabor intenso e refrescante, formulada para aliviar irritações na garganta e manter o hálito puro.', '/IMG_Produtos/14.png', 3.50, 1.14, 47, 'ativo', '2025-10-20 17:54:49'),
	(15, 'BALA DROPS HALLS MENTOL PCT 28G', 4, 8, 'Bala Drops Halls Mentol, combinando frescor e alívio imediato para a garganta, ideal para dias frios ou ambientes secos.', '/IMG_Produtos/15.png', 3.50, 1.19, 27, 'ativo', '2025-10-20 17:54:49'),
	(16, 'BALA DROPS HALLS MORANGO PCT 28G', 4, 8, 'Bala Drops Halls sabor morango, doce e refrescante, que suaviza a garganta enquanto oferece um gostinho frutado irresistível.', '/IMG_Produtos/16.png', 3.50, 1.19, 34, 'ativo', '2025-10-20 17:54:49'),
	(17, 'BALA DROPS HALLS UVA VERDE PCT 28G', 4, 8, 'Bala Drops Halls sabor uva verde, mistura um sabor frutado com efeito refrescante, perfeita para quem busca alívio e sabor juntos.', '/IMG_Produtos/17.png', 3.50, 1.19, 28, 'ativo', '2025-10-20 17:54:49'),
	(18, 'BALA FINI BEIJOS MORANGO DE GELATINA PCT 100G', 4, 9, 'Bala de gelatina Fini formato beijo sabor morango, macia e saborosa, perfeita para adoçar o dia com uma explosão de sabor frutado.', '/IMG_Produtos/18.png', 9.99, 4.99, 2, 'ativo', '2025-10-20 17:54:49'),
	(19, 'BALA FINI DENTADURA DE GELATINA PCT 100G', 4, 9, 'Bala de gelatina Fini dentadura sabor doce, divertida e saborosa, ideal para crianças e adultos que gostam de doces macios e divertidos.', '/IMG_Produtos/19.png', 10.99, 4.99, 5, 'ativo', '2025-10-20 17:54:49'),
	(20, 'BALA FINI TUBES MORANGO DE GOMA PCT 80G', 4, 9, 'Bala de goma Fini sabor morango, em formato de tubo, macia e saborosa, proporciona uma experiência divertida e deliciosa para qualquer hora.', '/IMG_Produtos/20.png', 9.99, 4.99, 13, 'ativo', '2025-10-20 17:54:49');

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
  `telefone` varchar(50) NOT NULL DEFAULT '',
  `cpf` char(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_login` datetime DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `google_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cpf` (`cpf`),
  UNIQUE KEY `telefone` (`telefone`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.usuarios: ~1 rows (aproximadamente)
INSERT INTO `usuarios` (`id`, `nome`, `data_nascimento`, `telefone`, `cpf`, `email`, `senha`, `criado_em`, `ultimo_login`, `ativo`, `google_id`) VALUES
	(4, 'NIKOLAS DE SOUZA LIMA', '2007-01-09', '11968544146', '47944286859', 'nikolas.souzalima007@gmail.com', '$2y$10$DN2sT4jhtzeogW.CVlBzb.Y2s0n.6pp3tswHQb.R7yXe2eLg43ZFq', '2025-10-20 23:59:00', '2025-10-20 22:02:50', 1, NULL);

-- Copiando estrutura para tabela fws.vendas
CREATE TABLE IF NOT EXISTS `vendas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `funcionario_id` int NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `status_pagamento` enum('pago','pendente','cancelado') DEFAULT 'pendente',
  `situacao_compra` enum('pre_compra','finalizada','cancelada') DEFAULT 'pre_compra',
  `metodo_pagamento` enum('dinheiro','cartao_credito','cartao_debito','pix','boleto','outros') DEFAULT 'dinheiro',
  `tempo_chegada` time DEFAULT NULL,
  `data_criacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_finalizacao` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `funcionario_id` (`funcionario_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `vendas_ibfk_1` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`),
  CONSTRAINT `vendas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.vendas: ~0 rows (aproximadamente)

-- Copiando estrutura para trigger fws.trg_estoque_insuficiente
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_estoque_insuficiente` BEFORE INSERT ON `movimentacao_estoque` FOR EACH ROW BEGIN
    DECLARE estoque_atual INT;

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

-- Copiando estrutura para trigger fws.trg_movimentacao_estoque
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_movimentacao_estoque` AFTER INSERT ON `movimentacao_estoque` FOR EACH ROW BEGIN
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

-- Copiando estrutura para trigger fws.trg_nao_cancelar_finalizada
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_nao_cancelar_finalizada` BEFORE UPDATE ON `vendas` FOR EACH ROW BEGIN
    IF OLD.situacao_compra = 'finalizada' AND NEW.situacao_compra = 'cancelada' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Não é possível cancelar uma venda finalizada';
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Copiando estrutura para trigger fws.trg_vendas_finalizadas
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_vendas_finalizadas` AFTER UPDATE ON `vendas` FOR EACH ROW BEGIN
    IF NEW.situacao_compra = 'finalizada' AND OLD.situacao_compra = 'pre_compra' THEN
        INSERT INTO movimentacao_estoque (produto_id, quantidade, tipo_movimentacao, venda_id, descricao)
        SELECT produto_id, quantidade, 'saida', NEW.id, CONCAT('Venda ID ', NEW.id)
        FROM itens_vendidos
        WHERE venda_id = NEW.id;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
