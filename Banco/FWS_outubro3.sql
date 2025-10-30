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
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.carrinho: ~9 rows (aproximadamente)
INSERT INTO `carrinho` (`id`, `usuario_id`, `produto_id`, `quantidade`, `preco_unitario`, `codigo_cupom`, `data_criacao`) VALUES
	(8, 9, 9, 3, 6.50, NULL, '2025-10-23 22:27:10');

-- Copiando estrutura para tabela fws.categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `cor` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.categorias: ~11 rows (aproximadamente)
INSERT INTO `categorias` (`id`, `nome`, `cor`) VALUES
	(1, 'BEBIDAS ALCOÓLICAS', '#8B0000'),
	(2, 'BEBIDAS NÃO ALCOÓLICAS', '#1E90FF'),
	(3, 'SORVETES', '#a200ffff'),
	(4, 'DOCES', '#ff0783ff'),
	(5, 'PROTEICOS', '#228B22'),
	(6, 'SNACKS', '#ff9500ff'),
	(7, 'LATICÍNIOS', '#00fbffff'),
	(8, 'BISCOITOS', '#D2B48C'),
	(9, 'CIGARROS E ITENS DE FUMO', '#696969'),
	(10, 'SALGADOS', '#ceaf00ff'),
	(11, 'OUTROS', '#A9A9A9');

-- Copiando estrutura para tabela fws.cupom
CREATE TABLE IF NOT EXISTS `cupom` (
  `id` int NOT NULL,
  `nome` varchar(50) DEFAULT NULL,
  `desconto` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.cupom: ~1 rows (aproximadamente)
INSERT INTO `cupom` (`id`, `nome`, `desconto`) VALUES
	(1, 'VITAO20', 20);

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.expiracoes_pre_compras: ~0 rows (aproximadamente)
INSERT INTO `expiracoes_pre_compras` (`id`, `usuario_id`, `venda_id`, `data_expiracao`) VALUES
	(8, 4, 11, '2025-10-30 14:06:14');

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.itens_vendidos: ~0 rows (aproximadamente)
INSERT INTO `itens_vendidos` (`id`, `venda_id`, `produto_id`, `quantidade`, `preco_unitario`) VALUES
	(2, 11, 15, 3, 3.50);

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
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.produtos: ~79 rows (aproximadamente)
INSERT INTO `produtos` (`id`, `nome`, `categoria_id`, `fornecedor_id`, `descricao`, `foto_produto`, `preco_venda`, `preco_compra`, `estoque`, `status`, `criado_em`) VALUES
	(1, 'VINHO BUENO AIRES MALBE 750ML', 1, 1, 'Um vinho Malbec elegante, produzido com uvas selecionadas que garantem sabor intenso, aromas frutados e taninos suaves. Ideal para acompanhar carnes vermelhas e momentos especiais.', '/TCC_FWS/IMG_Produtos/1.png', 69.90, 54.90, 1, 'ativo', '2025-10-20 17:54:49'),
	(2, 'VINHO DE LOS MAN CABERNET SAUVIGNON BRANCO 750ML', 1, 1, 'Vinho Cabernet Sauvignon, disponível nas versões branco ou tinto, feito com uvas premium que trazem equilíbrio entre acidez e corpo, perfeito para harmonizar com queijos e pratos sofisticados.', '/TCC_FWS/IMG_Produtos/2.png', 69.90, 54.90, 3, 'ativo', '2025-10-20 17:54:49'),
	(3, 'ÁGUA DE COCO KERO COCO 1L', 2, 3, 'Água de coco 100% natural, rica em eletrólitos essenciais para hidratação rápida e renovação de energia, perfeita para o dia a dia e práticas esportivas.', '/TCC_FWS/IMG_Produtos/3.png', 31.99, 15.87, 5, 'ativo', '2025-10-20 17:54:49'),
	(4, 'ÁGUA DE COCO KERO COCO 330ML', 2, 3, 'Em embalagem prática, essa água de coco natural é fonte de potássio e minerais, ideal para refrescar e manter a hidratação em qualquer momento.', '/TCC_FWS/IMG_Produtos/4.png', 14.50, 6.56, 16, 'ativo', '2025-10-20 17:54:49'),
	(5, 'ÁGUA DE COCO KERO COCO CX 200ML', 2, 3, 'Compacta e nutritiva, essa água de coco oferece hidratação natural e energia, com sabor refrescante e leve, ótima para levar na bolsa ou lancheira.', '/TCC_FWS/IMG_Produtos/5.png', 6.90, 3.20, 15, 'ativo', '2025-10-20 17:54:49'),
	(6, 'ÁGUA MINERAL CRYSTAL COM GÁS PET 500ML', 2, 4, 'Água mineral com gás Crystal, puro frescor e efervescência suave que revitalizam seu paladar a qualquer hora do dia.', '/TCC_FWS/IMG_Produtos/6.png', 6.50, 2.24, 1, 'ativo', '2025-10-20 17:54:49'),
	(7, 'ÁGUA MINERAL MINALBA COM GÁS PET 1,5L', 2, 5, 'Água mineral com gás Minalba em embalagem econômica, leve e refrescante, perfeita para acompanhar suas refeições ou momentos de lazer.', '/TCC_FWS/IMG_Produtos/7.png', 8.99, 3.17, 13, 'ativo', '2025-10-20 17:54:49'),
	(8, 'ÁGUA MINERAL PRATA SEM GÁS PET 510ML', 2, 6, 'Água mineral sem gás Prata, naturalmente equilibrada em minerais essenciais para uma hidratação pura e saudável.', '/TCC_FWS/IMG_Produtos/8.png', 7.00, 2.85, 54, 'ativo', '2025-10-20 17:54:49'),
	(9, 'ÁGUA MINERAL MINALBA SEM GÁS PET 510ML', 2, 6, 'Água mineral sem gás Minalba, de sabor leve e refrescante, ideal para manter seu corpo hidratado com qualidade e naturalidade.', '/TCC_FWS/IMG_Produtos/9.png', 6.50, 2.75, 67, 'ativo', '2025-10-20 17:54:49'),
	(10, 'ÁGUA TONICA ANTARCTICA DIET LT 350ML', 2, 7, 'Água tônica dietética Antarctica, com sabor marcante e refrescante, adoçada artificialmente para quem busca sabor sem calorias.', '/TCC_FWS/IMG_Produtos/10.png', 8.00, 2.60, 7, 'ativo', '2025-10-20 17:54:49'),
	(11, 'ÁGUA TONICA ANTARCTICA LT 350ML', 2, 7, 'Água tônica Antarctica clássica, com mistura perfeita de quinino e gás que proporciona um sabor único e refrescante para seus drinks ou momentos de relaxamento.', '/TCC_FWS/IMG_Produtos/11.png', 8.00, 2.52, 10, 'ativo', '2025-10-20 17:54:49'),
	(12, 'AP BARBEAR BIC CONF3 NORMAL', 11, 2, 'Kit de aparelhos de barbear BIC com 3 lâminas, oferecendo precisão e conforto para um barbear eficiente e seguro, ideal para o cuidado diário da pele.', '/TCC_FWS/IMG_Produtos/12.png', 8.50, 4.11, 8, 'ativo', '2025-10-20 17:54:49'),
	(13, 'BALA DROPS HALLS MENTA PCT 28G', 4, 8, 'Bala Drops Halls sabor menta, proporciona frescor imediato para a garganta, ajudando a aliviar desconfortos e refrescar o hálito.', '/TCC_FWS/IMG_Produtos/13.png', 3.50, 1.19, 22, 'ativo', '2025-10-20 17:54:49'),
	(14, 'BALA DROPS HALLS MENTA PRATA PCT 28G', 4, 8, 'Bala Drops Halls Menta Prata com sabor intenso e refrescante, formulada para aliviar irritações na garganta e manter o hálito puro.', '/TCC_FWS/IMG_Produtos/14.png', 3.50, 1.14, 47, 'ativo', '2025-10-20 17:54:49'),
	(15, 'BALA DROPS HALLS MENTOL PCT 28G', 4, 8, 'Bala Drops Halls Mentol, combinando frescor e alívio imediato para a garganta, ideal para dias frios ou ambientes secos.', '/TCC_FWS/IMG_Produtos/15.png', 3.50, 1.19, 27, 'ativo', '2025-10-20 17:54:49'),
	(16, 'BALA DROPS HALLS MORANGO PCT 28G', 4, 8, 'Bala Drops Halls sabor morango, doce e refrescante, que suaviza a garganta enquanto oferece um gostinho frutado irresistível.', '/TCC_FWS/IMG_Produtos/16.png', 3.50, 1.19, 34, 'ativo', '2025-10-20 17:54:49'),
	(17, 'BALA DROPS HALLS UVA VERDE PCT 28G', 4, 8, 'Bala Drops Halls sabor uva verde, mistura um sabor frutado com efeito refrescante, perfeita para quem busca alívio e sabor juntos.', '/TCC_FWS/IMG_Produtos/17.png', 3.50, 1.19, 28, 'ativo', '2025-10-20 17:54:49'),
	(18, 'BALA FINI BEIJOS MORANGO DE GELATINA PCT 100G', 4, 9, 'Bala de gelatina Fini formato beijo sabor morango, macia e saborosa, perfeita para adoçar o dia com uma explosão de sabor frutado.', '/TCC_FWS/IMG_Produtos/18.png', 9.99, 4.99, 2, 'ativo', '2025-10-20 17:54:49'),
	(19, 'BALA FINI DENTADURA DE GELATINA PCT 100G', 4, 9, 'Bala de gelatina Fini dentadura sabor doce, divertida e saborosa, ideal para crianças e adultos que gostam de doces macios e divertidos.', '/TCC_FWS/IMG_Produtos/19.png', 10.99, 4.99, 5, 'ativo', '2025-10-20 17:54:49'),
	(20, 'BALA FINI TUBES MORANGO DE GOMA PCT 80G', 4, 9, 'Bala de goma Fini sabor morango, em formato de tubo, macia e saborosa, proporciona uma experiência divertida e deliciosa para qualquer hora.', '/TCC_FWS/IMG_Produtos/20.png', 9.99, 4.99, 13, 'ativo', '2025-10-20 17:54:49'),
	(21, 'BALA FRUITTELLA MASTIGAVEL BLUEBERRY PCT 40G', 4, 9, 'Bala mastigável sabor blueberry, deliciosa e prática para qualquer momento do dia.', '/TCC_FWS/IMG_Produtos/21.png', 6.99, 1.96, 21, 'ativo', '2025-10-30 12:59:48'),
	(22, 'BALA FRUITTELLA MORANGO C CREME LEITE PCT 45G', 4, 9, 'Bala sabor morango com creme de leite, combinação irresistível de sabor e suavidade.', '/TCC_FWS/IMG_Produtos/22.png', 3.99, 1.96, 11, 'ativo', '2025-10-30 12:59:48'),
	(23, 'BALA MENTOS FANTA LARANJA PCT 37,5G', 4, 10, 'Bala Mentos sabor Fanta Laranja, refrescante e divertida, perfeita para qualquer hora.', '/TCC_FWS/IMG_Produtos/23.png', 4.00, 1.89, 4, 'ativo', '2025-10-30 12:59:48'),
	(24, 'BALA MENTOS KISS MENTA FORTE 35G', 4, 10, 'Bala Mentos sabor menta forte, oferece refrescância intensa e duradoura.', '/TCC_FWS/IMG_Produtos/24.png', 17.99, 8.60, 9, 'ativo', '2025-10-30 12:59:48'),
	(25, 'BALA MENTOS KISS MENTA LT 35G 50UN', 4, 10, 'Bala Mentos Kiss sabor menta em embalagem lata, ideal para compartilhar e manter o hálito fresco.', '/TCC_FWS/IMG_Produtos/25.png', 17.99, 8.60, 10, 'ativo', '2025-10-30 12:59:48'),
	(26, 'BALA MENTOS KISS MORANGO LT 35G', 4, 10, 'Bala Mentos Kiss sabor morango, refrescante e saborosa, perfeita para o dia a dia.', '/TCC_FWS/IMG_Produtos/26.png', 17.99, 8.60, 3, 'ativo', '2025-10-30 12:59:48'),
	(27, 'BALA TIC TAC PASTILHA LARANJA CX 15G', 4, 6, 'Pastilhas Tic Tac sabor laranja, pequenas e refrescantes, ideais para levar no bolso.', '/TCC_FWS/IMG_Produtos/27.png', 5.50, 1.93, 14, 'ativo', '2025-10-30 12:59:48'),
	(28, 'BARRA PROTEICA INTEGRAL MEDICA CRISP BROWNIE EMB 45G', 5, 11, 'Barra proteica sabor brownie crocante, ideal para recuperação muscular e nutrição prática.', '/TCC_FWS/IMG_Produtos/28.png', 13.50, 7.05, 8, 'ativo', '2025-10-30 12:59:48'),
	(29, 'BARRA PROTEICA NUTRATA CARAMELO EMB 45G', 5, 11, 'Barra proteica sabor caramelo, com alto teor de proteínas e textura cremosa.', '/TCC_FWS/IMG_Produtos/29.png', 15.99, 0.00, 2, 'ativo', '2025-10-30 12:59:48'),
	(30, 'BARRA DE CEREAL BAUDUCCO MAXI CHOCOLATE PCT 25G', 6, 10, 'Barra de cereal Bauducco sabor chocolate, deliciosa opção de lanche rápido e nutritivo.', '/TCC_FWS/IMG_Produtos/30.png', 3.50, 1.13, 3, 'ativo', '2025-10-30 12:59:48'),
	(31, 'BARRA DE CEREAL NUTS BAR CASTANHAS CHOCOLATE PCT 25G', 6, 9, 'Barra de cereal com castanhas e chocolate, fonte de energia e sabor para o dia a dia.', '/TCC_FWS/IMG_Produtos/31.png', 5.99, 2.93, 13, 'ativo', '2025-10-30 13:02:47'),
	(32, 'BARRA DE CEREAL NUTS CASTANHA E SEMENTES PCT 25G', 6, 9, 'Barra de cereal com castanhas e sementes, perfeita para lanches nutritivos e saudáveis.', '/TCC_FWS/IMG_Produtos/32.png', 5.99, 2.93, 6, 'ativo', '2025-10-30 13:02:47'),
	(33, 'BARRA DE CEREAL SUPINO PROTEIN CAPPUCCINO PCT 30G', 5, 9, 'Barra proteica sabor cappuccino, ideal para recuperação muscular e energia extra.', '/TCC_FWS/IMG_Produtos/33.png', 8.50, 3.64, 5, 'ativo', '2025-10-30 13:02:47'),
	(34, 'BARRA DE PROTEINA SUPINO COCO C CHOCOLATE 30G', 5, 9, 'Barra proteica sabor coco com chocolate, deliciosa e rica em proteínas.', '/TCC_FWS/IMG_Produtos/34.png', 8.50, 3.86, 3, 'ativo', '2025-10-30 13:02:47'),
	(35, 'BARRA NUTRATA PROTOBAR AVELA WHEY PCT 70G', 5, 11, 'Barra de proteína sabor avelã com whey, ideal para atletas e quem busca nutrição equilibrada.', '/TCC_FWS/IMG_Produtos/35.png', 22.99, 12.22, 10, 'ativo', '2025-10-30 13:02:47'),
	(36, 'BARRA NUTRATA PROTOBAR COCONUT PCT 70G', 5, 11, 'Barra de proteína sabor coco, combina alto teor proteico com sabor irresistível.', '/TCC_FWS/IMG_Produtos/36.png', 22.99, 12.22, 8, 'ativo', '2025-10-30 13:02:47'),
	(37, 'BARRA NUTRATA YOPRO PROTEICA MORANGO 55G', 5, 11, 'Barra proteica Yopro sabor morango, perfeita para suplementar proteínas com sabor.', '/TCC_FWS/IMG_Produtos/37.png', 19.99, 10.47, 10, 'ativo', '2025-10-30 13:02:47'),
	(38, 'BARRA PROTEICA INTEGRAL MEDICA TRUFA AVELA CHOC 45G', 5, 11, 'Barra proteica sabor trufa de avelã com chocolate, rica em proteínas e muito saborosa.', '/TCC_FWS/IMG_Produtos/38.png', 13.50, 7.05, 12, 'ativo', '2025-10-30 13:02:47'),
	(39, 'BARRA PROTEICA INTEGRAL MEDICA BAR NINHO CREME DE AVELA 45G', 5, 11, 'Barra proteica sabor Ninho com creme de avelã, deliciosa opção pós-treino.', '/TCC_FWS/IMG_Produtos/39.png', 13.50, 6.63, 15, 'ativo', '2025-10-30 13:02:47'),
	(40, 'BARRA PROTOBAR BROWNIE C DOCE DE LEITE HAVANNA PCT 70G', 5, 11, 'Barra proteica sabor brownie com doce de leite Havanna, indulgente e nutritiva.', '/TCC_FWS/IMG_Produtos/40.png', 25.90, 13.09, 11, 'ativo', '2025-10-30 13:02:47'),
	(41, 'BARRA PROTOBAR NUTRATA HAVANNA 70G', 5, 11, 'Barra proteica Nutrata sabor Havanna, perfeita para um lanche nutritivo e saboroso.', '/TCC_FWS/IMG_Produtos/41.png', 25.90, 13.86, 7, 'ativo', '2025-10-30 13:04:29'),
	(42, 'BARRA PROTOBAR NUTRATA SENSATIONS WHEY PCT 70G', 5, 11, 'Barra proteica Sensations Whey, ideal para complementar proteínas com praticidade e sabor.', '/TCC_FWS/IMG_Produtos/42.png', 23.99, 12.22, 3, 'ativo', '2025-10-30 13:04:29'),
	(43, 'BEB LACTEA 3 CORACOES CAPPUCCINO CHOCOLATE CX 260ML', 7, 12, 'Bebida láctea sabor cappuccino com chocolate, cremosa e nutritiva, perfeita para qualquer momento.', '/TCC_FWS/IMG_Produtos/43.png', 12.50, 5.72, 1, 'ativo', '2025-10-30 13:04:29'),
	(45, 'BEB LACTEA 3 CORACOES POWER WHEY CAPPUCCINO CLASSICO 250ML', 7, 12, 'Bebida láctea Power Whey sabor cappuccino, fonte de proteínas para energia e recuperação.', '/TCC_FWS/IMG_Produtos/45.png', 13.99, 6.67, 18, 'ativo', '2025-10-30 13:04:29'),
	(46, 'BEB LACTEA NESTLE NESCAU FAST 270ML', 7, 13, 'Bebida láctea Nesquik Fast, prática e saborosa, ideal para complementar sua nutrição diária.', '/TCC_FWS/IMG_Produtos/46.png', 12.50, 5.14, 8, 'ativo', '2025-10-30 13:04:29'),
	(47, 'BEB LACTEA NESTON FAST VITAMINA C CEREAL 280ML', 7, 13, 'Bebida láctea Neston Fast com vitamina C e cereal, perfeita para um lanche nutritivo.', '/TCC_FWS/IMG_Produtos/47.png', 12.50, 5.00, 15, 'ativo', '2025-10-30 13:04:29'),
	(48, 'BEB LACTEA TODDYNHO LEVINHO CX 200ML', 7, 3, 'Bebida láctea Toddy Leve, sabor chocolate suave, ideal para lanches rápidos e deliciosos.', '/TCC_FWS/IMG_Produtos/48.png', 6.50, 2.35, 8, 'ativo', '2025-10-30 13:04:29'),
	(49, 'BEB LACTEA TODDYNHO TRADICIONAL CX 200ML', 7, 3, 'Bebida láctea Toddy Tradicional, sabor clássico de chocolate, perfeita para crianças e adultos.', '/TCC_FWS/IMG_Produtos/49.png', 6.50, 3.14, 28, 'ativo', '2025-10-30 13:04:29'),
	(50, 'BEBIDA DA CAFETEIRA CAPUCCINO 3 CORACOES 200ML', 2, 12, 'Bebida de cappuccino pronta para consumo, prática, cremosa e perfeita para qualquer hora.', '/TCC_FWS/IMG_Produtos/50.png', 6.99, 1.72, 415, 'ativo', '2025-10-30 13:04:29'),
	(51, 'BISC BAUDUCCO BISCUIT CHOCOLATE MEIO AMARGO PCT 80G', 8, 10, 'Biscoito Bauducco sabor chocolate meio amargo, crocante e delicioso para lanches e cafés.', '/TCC_FWS/IMG_Produtos/51.png', 12.90, 6.01, 5, 'ativo', '2025-10-30 13:07:21'),
	(52, 'BISC BAUDUCCO CEREALE CACAU AVEIA E MEL PCT 170G', 8, 10, 'Biscoito Bauducco Cereal com cacau, aveia e mel, nutritivo e saboroso para qualquer hora do dia.', '/TCC_FWS/IMG_Produtos/52.png', 7.50, 3.58, 1, 'ativo', '2025-10-30 13:07:21'),
	(53, 'BISC BAUDUCCO CHOCO BISCUIT CHOCOLATE AO LEITE 80G', 8, 10, 'Biscoito Bauducco Choco Biscuit com chocolate ao leite, perfeito para lanches rápidos e doces.', '/TCC_FWS/IMG_Produtos/53.png', 12.90, 6.01, 4, 'ativo', '2025-10-30 13:07:21'),
	(54, 'BISC BAUDUCCO COOKIE CHOCOLATE PCT 100G', 8, 10, 'Cookie Bauducco sabor chocolate, macio por dentro e crocante por fora, ideal para acompanhar café ou lanche.', '/TCC_FWS/IMG_Produtos/54.png', 8.99, 3.32, 5, 'ativo', '2025-10-30 13:07:21'),
	(55, 'BISC BAUDUCCO COOKIE ORIGINAL PCT 100G', 8, 10, 'Cookie Bauducco original, sabor clássico e textura irresistível para qualquer hora do dia.', '/TCC_FWS/IMG_Produtos/55.png', 8.99, 3.32, 7, 'ativo', '2025-10-30 13:07:21'),
	(56, 'BISC BAUDUCCO WAFER CHOCOLATE PCT 140G', 8, 10, 'Wafer Bauducco sabor chocolate, crocante e recheado, perfeito para lanches rápidos.', '/TCC_FWS/IMG_Produtos/56.png', 7.50, 3.45, 8, 'ativo', '2025-10-30 13:07:21'),
	(57, 'BISC BAUDUCCO WAFER MAXI CHOCOLATE PCT 104G', 8, 10, 'Wafer Bauducco Maxi chocolate, sabor intenso e textura crocante, ideal para crianças e adultos.', '/TCC_FWS/IMG_Produtos/57.png', 7.50, 3.45, 3, 'ativo', '2025-10-30 13:07:21'),
	(58, 'BISC BAUDUCCO WAFER MORANGO PCT 140G', 8, 10, 'Wafer Bauducco sabor morango, crocante e delicioso, perfeito para lanches e cafés.', '/TCC_FWS/IMG_Produtos/58.png', 7.50, 3.45, 6, 'ativo', '2025-10-30 13:07:21'),
	(59, 'BISC BAUDUCCO WAFER TRIPLO CHOCOLATE PCT 140G', 8, 10, 'Wafer Bauducco Triplo Chocolate, crocante e recheado, ideal para quem ama chocolate.', '/TCC_FWS/IMG_Produtos/59.png', 7.50, 3.45, 3, 'ativo', '2025-10-30 13:07:21'),
	(60, 'BISC CASSINI POLVILHO SALGADO PCT 100G', 8, 9, 'Biscoito de polvilho Cassini, crocante e levemente salgado, ótimo para lanches e aperitivos.', '/TCC_FWS/IMG_Produtos/60.png', 8.50, 3.19, 15, 'ativo', '2025-10-30 13:07:21'),
	(61, 'BISC CLUB SOCIAL CROSTINI QUEIJO PARMESAO E VEGETAIS PCT 80G', 8, 8, 'Biscoito Club Social Crostini com queijo parmesão e vegetais, leve e crocante, ideal para lanches saudáveis.', '/TCC_FWS/IMG_Produtos/61.png', 10.90, 5.03, 4, 'ativo', '2025-10-30 13:07:42'),
	(62, 'BISC CLUB SOCIAL INTEGRAL PCT 144G', 8, 8, 'Biscoito Club Social Integral, nutritivo e crocante, perfeito para quem busca opções mais saudáveis.', '/TCC_FWS/IMG_Produtos/62.png', 11.50, 5.03, 3, 'ativo', '2025-10-30 13:07:42'),
	(63, 'BISC CLUB SOCIAL ORIGINAL 141G', 8, 8, 'Biscoito Club Social Original, sabor clássico e textura crocante, ideal para lanches rápidos.', '/TCC_FWS/IMG_Produtos/63.png', 11.50, 5.03, 6, 'ativo', '2025-10-30 13:07:42'),
	(64, 'BISC NESTLE CALIPSO RECH ORIGINAL PCT 130G', 8, 13, 'Biscoito Nestlé Calipso recheado original, sabor delicioso e perfeito para sobremesas e lanches.', '/TCC_FWS/IMG_Produtos/64.png', 13.90, 6.53, 6, 'ativo', '2025-10-30 13:07:42'),
	(65, 'BISC NESTLE CLASSIC RECH CHOCOLATE PCT 140G', 8, 13, 'Biscoito Nestlé Classic recheado com chocolate, crocante e irresistível, ideal para qualquer hora.', '/TCC_FWS/IMG_Produtos/65.png', 7.50, 3.92, 4, 'ativo', '2025-10-30 13:07:42'),
	(66, 'BISC NESTLE MOCA RECH PCT 140G', 8, 13, 'Biscoito Nestlé Moça recheado, sabor clássico e delicioso, perfeito para café ou lanche.', '/TCC_FWS/IMG_Produtos/66.png', 7.50, 3.75, 5, 'ativo', '2025-10-30 13:07:42'),
	(67, 'BISC NESTLE NESFIT INTEGRAL CACAU E CEREAIS PCT 160G', 8, 13, 'Biscoito Nestlé Nesfit integral com cacau e cereais, nutritivo e crocante, ideal para lanches saudáveis.', '/TCC_FWS/IMG_Produtos/67.png', 7.50, 3.03, 5, 'ativo', '2025-10-30 13:07:42'),
	(68, 'BISC NESTLE NESFIT INTEGRAL MORANGO E CEREAIS PCT 160G', 8, 13, 'Biscoito Nestlé Nesfit integral sabor morango com cereais, saudável e delicioso para qualquer momento.', '/TCC_FWS/IMG_Produtos/68.png', 7.50, 3.58, 3, 'ativo', '2025-10-30 13:07:42'),
	(69, 'BISC NESTLE PASSATEMPO RECHEADO CHOCOLATE 130G', 8, 13, 'Biscoito Passatempo recheado com chocolate, macio e saboroso, perfeito para lanches infantis.', '/TCC_FWS/IMG_Produtos/69.png', 6.90, 2.63, 7, 'ativo', '2025-10-30 13:07:42'),
	(70, 'BISC NESTLE PASSATEMPO RECHEADO MORANGO PCT 130G', 8, 13, 'Biscoito Passatempo recheado com morango, macio e doce, ideal para crianças e lanches rápidos.', '/TCC_FWS/IMG_Produtos/70.png', 6.90, 2.63, 6, 'ativo', '2025-10-30 13:07:42'),
	(71, 'BISC NESTLE RECHEADO NESCAU PCT 140G', 8, 13, 'Biscoito Nestlé recheado com Nesquik, crocante e doce, ideal para lanches e sobremesas.', '/TCC_FWS/IMG_Produtos/71.png', 7.50, 3.92, 6, 'ativo', '2025-10-30 13:08:18'),
	(72, 'BISC OREO ORIGINAL 90G', 8, 14, 'Biscoito Oreo Original, recheio cremoso e sabor icônico, perfeito para qualquer lanche ou sobremesa.', '/TCC_FWS/IMG_Produtos/72.png', 7.50, 3.40, 1, 'ativo', '2025-10-30 13:08:18'),
	(73, 'BISC OREO RECHEADO CHOCOLATE 90G', 8, 14, 'Biscoito Oreo recheado com chocolate, crocante e irresistível, ótimo para lanches rápidos.', '/TCC_FWS/IMG_Produtos/73.png', 7.50, 3.46, 4, 'ativo', '2025-10-30 13:08:18'),
	(74, 'BISC OREO RECHEADO MILKSHAKE MORANGO 90G', 8, 14, 'Biscoito Oreo recheado sabor milkshake de morango, doce e cremoso, perfeito para sobremesas ou lanches.', '/TCC_FWS/IMG_Produtos/74.png', 7.50, 3.46, 4, 'ativo', '2025-10-30 13:08:18'),
	(75, 'BISC RECHEADO BONO LIMAO PCT 90G', 8, 13, 'Biscoito Bono recheado sabor limão, macio e refrescante, ideal para lanche da tarde.', '/TCC_FWS/IMG_Produtos/75.png', 6.50, 2.27, 6, 'ativo', '2025-10-30 13:08:18'),
	(76, 'BISC RECHEADO NABISCO CHOCOLICIA CHOCOLATE PCT 132G', 8, 14, 'Biscoito Chocólicia recheado com chocolate, sabor intenso e irresistível, perfeito para momentos doces.', '/TCC_FWS/IMG_Produtos/76.png', 12.00, 5.30, 3, 'ativo', '2025-10-30 13:08:18'),
	(77, 'BISC RECHEADO NESTLE BONO CHOCOLATE PCT 90G', 8, 13, 'Biscoito Nestlé Bono recheado com chocolate, sabor clássico e macio, ideal para lanches rápidos.', '/TCC_FWS/IMG_Produtos/77.png', 6.50, 2.27, 8, 'ativo', '2025-10-30 13:08:18'),
	(78, 'BISC RECHEADO NESTLE NEGRESCO CHOCOLATE PCT 90G', 8, 13, 'Biscoito Nestlé Negresco recheado com chocolate, crocante e sabor intenso, perfeito para sobremesas.', '/TCC_FWS/IMG_Produtos/78.png', 6.50, 2.14, 5, 'ativo', '2025-10-30 13:08:18'),
	(79, 'BISC RECHEADO NESTLE NEGRESCO MORANGO PCT 90G', 8, 13, 'Biscoito Nestlé Negresco recheado sabor morango, doce e crocante, ótimo para lanches infantis.', '/TCC_FWS/IMG_Produtos/79.png', 6.50, 2.27, 4, 'ativo', '2025-10-30 13:08:18'),
	(80, 'BISC TOSTINES NESTLE MACA E CANELA PCT 160G', 8, 13, 'Biscoito Tostines Nestlé sabor maçã e canela, crocante e aromático, perfeito para cafés e lanches.', '/TCC_FWS/IMG_Produtos/80.png', 7.50, 3.34, 4, 'ativo', '2025-10-30 13:08:18');

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.usuarios: ~5 rows (aproximadamente)
INSERT INTO `usuarios` (`id`, `nome`, `data_nascimento`, `telefone`, `cpf`, `email`, `senha`, `criado_em`, `ultimo_login`, `ativo`, `google_id`) VALUES
	(4, 'NIKOLAS DE SOUZA LIMA', '2007-01-09', '(11) 96854-4147', '47944286859', 'nikolas.souzalima007@gmail.com', '$2y$10$DN2sT4jhtzeogW.CVlBzb.Y2s0n.6pp3tswHQb.R7yXe2eLg43ZFq', '2025-10-20 23:59:00', '2025-10-30 10:24:24', 1, NULL),
	(6, 'Sabrina', '2007-02-14', '11930265543', '54449709888', 'sabrina@gmail.com', '$2y$10$xVCz9nu7WWZVB25HuJQAJuVeIWKOMuqMtlEP68.sorCERfJ7LVO9.', '2025-10-22 11:30:07', '2025-10-22 08:32:23', 1, NULL),
	(7, 'Nicolly Clement de Freitas', '2007-07-25', '11928926150', '50674089871', 'clementnicolly@gmail.com', '$2y$10$0Igs1yOGF5dtBMYbX/vlme9BcBJ3gu/VfRlwxSsOjCwq6ytGu3dJi', '2025-10-22 14:07:05', '2025-10-22 11:08:02', 1, NULL),
	(8, 'nathally ferreira', '2007-08-13', '11999284328', '49681441800', 'nathally@gmail.com', '$2y$10$M45ldEWNbN1mc.pYonwGu.rJg9leM2BjTe3kynY6lN9K8KmVu.BPi', '2025-10-22 14:59:52', '2025-10-22 12:01:16', 1, NULL),
	(9, 'Maria Da Silva', '2005-04-29', '11967222222', '85577959047', 'Maria@gmail.com', '$2y$10$oUmViqIQVL8Ys.jdemrrSOuSFaoECwDtUIR.bxzDzVKW63npUZMK2', '2025-10-24 01:22:47', '2025-10-23 22:23:34', 1, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Copiando dados para a tabela fws.vendas: ~0 rows (aproximadamente)
INSERT INTO `vendas` (`id`, `funcionario_id`, `usuario_id`, `total`, `status_pagamento`, `situacao_compra`, `metodo_pagamento`, `tempo_chegada`, `data_criacao`, `data_finalizacao`) VALUES
	(11, 1, 4, 10.50, 'pendente', 'cancelada', 'dinheiro', '00:45:00', '2025-10-30 11:05:40', NULL);

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
