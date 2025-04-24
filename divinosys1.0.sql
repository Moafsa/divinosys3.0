-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 23/04/2025 às 22:06
-- Versão do servidor: 10.4.28-MariaDB
-- Versão do PHP: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `pdv`
--

DELIMITER $$
--
-- Procedimentos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `insert_produto_com_ingredientes` (IN `p_categoria` VARCHAR(50), IN `p_nome` VARCHAR(100), IN `p_preco` DECIMAL(10,2), IN `p_ingredientes` TEXT)   BEGIN
    DECLARE produto_id INT;
    DECLARE ingredient_id INT;
    DECLARE done INT DEFAULT FALSE;
    DECLARE ingredients_cursor CURSOR FOR 
        SELECT CAST(TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(p_ingredientes, ',', n.n), ',', -1)) AS UNSIGNED) as id
        FROM 
        (SELECT 1 + units.i + tens.i * 10 n
         FROM 
         (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) units,
         (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) tens
         WHERE 1 + units.i + tens.i * 10 <= (LENGTH(p_ingredientes) - LENGTH(REPLACE(p_ingredientes, ',', '')) + 1)
        ) n;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Inserir produto
    INSERT INTO produtos (categoria, nome, preco_venda) 
    VALUES (p_categoria, p_nome, p_preco);
    
    SET produto_id = LAST_INSERT_ID();
    
    -- Inserir ingredientes
    OPEN ingredients_cursor;
    
    read_loop: LOOP
        FETCH ingredients_cursor INTO ingredient_id;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        INSERT INTO produto_ingredientes (produto_id, ingrediente_id)
        VALUES (produto_id, ingredient_id);
    END LOOP;
    
    CLOSE ingredients_cursor;
END$$

--
-- Funções
--
CREATE DEFINER=`root`@`localhost` FUNCTION `generate_product_code` () RETURNS CHAR(4) CHARSET utf8mb4 COLLATE utf8mb4_general_ci DETERMINISTIC BEGIN
    DECLARE new_code CHAR(4);
    DECLARE code_exists INT;
    
    generate_code: LOOP
        -- Gerar número aleatório entre 1000 e 9999
        SET new_code = LPAD(FLOOR(RAND() * 8999 + 1000), 4, '0');
        
        -- Verificar se código já existe
        SELECT COUNT(*) INTO code_exists 
        FROM produtos 
        WHERE codigo = new_code;
        
        IF code_exists = 0 THEN
            LEAVE generate_code;
        END IF;
    END LOOP;
    
    RETURN new_code;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `atividade`
--

CREATE TABLE `atividade` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `atividade` varchar(255) NOT NULL,
  `ordem` int(11) NOT NULL,
  `condicao` int(11) NOT NULL,
  `start` datetime DEFAULT NULL,
  `color` varchar(10) DEFAULT NULL,
  `end` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `imagem` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`, `created_at`, `imagem`) VALUES
(1, 'XIS', '2025-04-17 21:22:58', 'uploads/categorias/6808407a6b548.jpeg'),
(2, 'Cachorro-Quente', '2025-04-17 21:22:58', 'uploads/categorias/6808402e81e2d.jpg'),
(3, 'Bauru', '2025-04-17 21:22:58', 'uploads/categorias/68084002d6db7.jpg'),
(4, 'PF e A La Minuta', '2025-04-17 21:22:58', 'uploads/categorias/680840402bdc6.jpg'),
(5, 'Torrada', '2025-04-17 21:22:58', 'uploads/categorias/6808406eb51b1.jpg'),
(6, 'Rodízio', '2025-04-17 21:22:58', 'uploads/categorias/6808405db8576.jpg'),
(7, 'Porções', '2025-04-17 21:22:58', 'uploads/categorias/6808404dc15af.jpg'),
(8, 'Bebidas', '2025-04-17 21:22:58', 'uploads/categorias/68084014a32c3.jpg'),
(9, 'Bebidas Alcoólicas', '2025-04-17 21:22:58', 'uploads/categorias/6808402551958.png');

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(100) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `endereco` varchar(255) NOT NULL,
  `bairro` varchar(255) NOT NULL,
  `cidade` varchar(255) NOT NULL,
  `estado` varchar(255) NOT NULL,
  `complemento` varchar(255) NOT NULL,
  `cep` varchar(255) NOT NULL,
  `ponto_referecia` varchar(255) NOT NULL,
  `tel1` varchar(255) NOT NULL,
  `tel2` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cpf_cnpj` varchar(255) NOT NULL,
  `rg` varchar(255) NOT NULL,
  `condominio` varchar(255) NOT NULL,
  `bloco` varchar(255) NOT NULL,
  `apartamento` varchar(255) NOT NULL,
  `local_entrega` varchar(255) NOT NULL,
  `observacoes` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cor`
--

CREATE TABLE `cor` (
  `id` int(100) NOT NULL,
  `cor` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `cor`
--

INSERT INTO `cor` (`id`, `cor`) VALUES
(1, 'success'),
(1, 'success');

-- --------------------------------------------------------

--
-- Estrutura para tabela `despesas`
--

CREATE TABLE `despesas` (
  `id` int(100) NOT NULL,
  `valor` varchar(255) NOT NULL,
  `despesa` varchar(255) NOT NULL,
  `data` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque`
--

CREATE TABLE `estoque` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `estoque_atual` decimal(10,2) DEFAULT 0.00,
  `estoque_minimo` decimal(10,2) DEFAULT 0.00,
  `preco_custo` decimal(10,2) DEFAULT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `fornecedor` varchar(100) DEFAULT NULL,
  `data_compra` date DEFAULT NULL,
  `data_validade` date DEFAULT NULL,
  `unidade` varchar(10) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ingredientes`
--

CREATE TABLE `ingredientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `tipo` enum('pao','proteina','queijo','salada','molho','complemento') NOT NULL,
  `preco_adicional` decimal(10,2) DEFAULT 0.00,
  `disponivel` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `ingredientes`
--

INSERT INTO `ingredientes` (`id`, `nome`, `tipo`, `preco_adicional`, `disponivel`, `created_at`) VALUES
(1, 'Pão de Xis', 'pao', 0.00, 1, '2025-04-17 21:22:58'),
(2, 'Pão de Hot Dog', 'pao', 0.00, 1, '2025-04-17 21:22:58'),
(3, 'Hambúrguer', 'proteina', 0.00, 1, '2025-04-17 21:22:58'),
(4, 'Coração de Frango', 'proteina', 0.00, 1, '2025-04-17 21:22:58'),
(5, 'Filé', 'proteina', 0.00, 1, '2025-04-17 21:22:58'),
(6, 'Frango', 'proteina', 0.00, 1, '2025-04-17 21:22:58'),
(7, 'Calabresa', 'proteina', 0.00, 1, '2025-04-17 21:22:58'),
(8, 'Bacon', 'proteina', 0.00, 1, '2025-04-17 21:22:58'),
(9, 'Salsicha', 'proteina', 0.00, 1, '2025-04-17 21:22:58'),
(10, 'Salsicha Vegetariana', 'proteina', 0.00, 1, '2025-04-17 21:22:58'),
(11, 'Patinho', 'proteina', 0.00, 1, '2025-04-17 21:22:58'),
(12, 'Alcatra', 'proteina', 0.00, 1, '2025-04-17 21:22:58'),
(13, 'Coxão Mole', 'proteina', 0.00, 1, '2025-04-17 21:22:58'),
(14, 'Queijo', 'queijo', 0.00, 1, '2025-04-17 21:22:58'),
(15, 'Queijo Ralado', 'queijo', 0.00, 1, '2025-04-17 21:22:58'),
(16, 'Queijo Cheddar', 'queijo', 0.00, 1, '2025-04-17 21:22:58'),
(17, 'Alface', 'salada', 0.00, 1, '2025-04-17 21:22:58'),
(18, 'Tomate', 'salada', 0.00, 1, '2025-04-17 21:22:58'),
(19, 'Cebola', 'salada', 0.00, 1, '2025-04-17 21:22:58'),
(20, 'Rúcula', 'salada', 0.00, 1, '2025-04-17 21:22:58'),
(21, 'Tomate Seco', 'salada', 0.00, 1, '2025-04-17 21:22:58'),
(22, 'Palmito', 'salada', 0.00, 1, '2025-04-17 21:22:58'),
(23, 'Pepino', 'salada', 0.00, 1, '2025-04-17 21:22:58'),
(24, 'Salada Mista', 'salada', 0.00, 1, '2025-04-17 21:22:58'),
(25, 'Maionese', 'molho', 0.00, 1, '2025-04-17 21:22:58'),
(26, 'Molho', 'molho', 0.00, 1, '2025-04-17 21:22:58'),
(27, 'Ovo', 'complemento', 0.00, 1, '2025-04-17 21:22:58'),
(28, 'Presunto', 'complemento', 0.00, 1, '2025-04-17 21:22:58'),
(29, 'Milho', 'complemento', 0.00, 1, '2025-04-17 21:22:58'),
(30, 'Ervilha', 'complemento', 0.00, 1, '2025-04-17 21:22:58'),
(31, 'Batata Palha', 'complemento', 0.00, 1, '2025-04-17 21:22:58'),
(32, 'Batata Frita', 'complemento', 0.00, 1, '2025-04-17 21:22:58'),
(33, 'Arroz', 'complemento', 0.00, 1, '2025-04-17 21:22:58'),
(34, 'Feijão', 'complemento', 0.00, 1, '2025-04-17 21:22:58'),
(35, 'Azeitona', 'complemento', 0.00, 1, '2025-04-17 21:22:58'),
(36, 'Ovo de Codorna', 'complemento', 0.00, 1, '2025-04-17 21:22:58'),
(37, 'Polenta', 'complemento', 0.00, 1, '2025-04-17 21:22:58');

-- --------------------------------------------------------

--
-- Estrutura para tabela `log_pedidos`
--

CREATE TABLE `log_pedidos` (
  `id` int(11) NOT NULL,
  `idpedido` int(11) NOT NULL,
  `status_anterior` varchar(50) DEFAULT NULL,
  `novo_status` varchar(50) DEFAULT NULL,
  `usuario` varchar(100) DEFAULT NULL,
  `data_alteracao` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `mesas`
--

CREATE TABLE `mesas` (
  `id` int(100) NOT NULL,
  `id_mesa` varchar(255) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `mesas`
--

INSERT INTO `mesas` (`id`, `id_mesa`, `nome`, `status`) VALUES
(471, '1', '', '1'),
(472, '2', '', '1'),
(473, '3', '', '1'),
(474, '4', '', '1'),
(475, '5', '', '1');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedido`
--

CREATE TABLE `pedido` (
  `idpedido` int(11) NOT NULL,
  `idmesa` int(11) NOT NULL,
  `cliente` varchar(255) DEFAULT NULL,
  `delivery` tinyint(1) DEFAULT 0,
  `data` date NOT NULL,
  `hora_pedido` time NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pendente',
  `valor_total` decimal(10,2) DEFAULT 0.00,
  `observacao` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo` varchar(20) DEFAULT 'mesa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedido_item_ingredientes`
--

CREATE TABLE `pedido_item_ingredientes` (
  `id` int(11) NOT NULL,
  `pedido_item_id` int(11) NOT NULL,
  `ingrediente_id` int(11) NOT NULL,
  `incluido` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedido_itens`
--

CREATE TABLE `pedido_itens` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `valor_unitario` decimal(10,2) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `observacao` text DEFAULT NULL,
  `ingredientes_sem` text DEFAULT NULL,
  `ingredientes_com` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `codigo` char(4) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco_normal` decimal(10,2) NOT NULL,
  `preco_mini` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `imagem` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `codigo`, `categoria_id`, `nome`, `descricao`, `preco_normal`, `preco_mini`, `created_at`, `imagem`) VALUES
(12, '1001', 1, 'XIS DA CASA', 'Pão, hambúrguer, ovo, presunto, queijo, milho, ervilha, alface, tomate, maionese', 29.00, 26.00, '2025-04-17 21:26:18', NULL),
(13, '1002', 1, 'XIS CORAÇÃO', 'Pão, coração de frango, ovo, presunto, queijo, milho, ervilha, alface, tomate, maionese', 34.00, 29.00, '2025-04-17 21:26:18', NULL),
(14, '1003', 1, 'XIS DUPLO', 'Pão, 2 hambúrgueres, 2 ovos, 2 presuntos, queijos, milho, ervilha, alface, tomate, maionese', 35.00, 30.00, '2025-04-17 21:26:18', NULL),
(15, '1004', 1, 'XIS CALABRESA', 'Pão, hambúrguer, calabresa, ovo, presunto, queijo, milho, ervilha, alface, tomate, maionese', 30.00, 26.00, '2025-04-17 21:26:18', NULL),
(16, '1005', 1, 'XIS BACON', 'Pão, hambúrguer, bacon, ovo, presunto, queijo, milho, ervilha, alface, tomate, maionese', 34.00, 30.00, '2025-04-17 21:26:18', NULL),
(17, '1006', 1, 'XIS VEGETARIANO', 'Pão, alface, tomate, queijo, palmito, pepino, milho, ervilha, maionese', 28.00, 25.00, '2025-04-17 21:26:18', NULL),
(18, '1007', 1, 'FILÉ', 'Pão, filé, ovo, presunto, queijo, milho, ervilha, alface, tomate, maionese', 40.00, 35.00, '2025-04-17 21:26:18', NULL),
(19, '1008', 1, 'XIS CEBOLA', 'Pão, hambúrguer, cebola, ovo, presunto, queijo, milho, ervilha, alface, tomate, maionese', 31.00, 29.00, '2025-04-17 21:26:18', NULL),
(20, '1009', 1, 'XIS FRANGO', 'Pão, frango, ovo, presunto, queijo, milho, ervilha, alface, tomate, maionese', 33.00, 28.00, '2025-04-17 21:26:18', NULL),
(21, '1010', 1, 'XIS TOMATE SECO COM RÚCULA', 'Pão, filé, rúcula, tomate seco, ovo, presunto, queijo, milho, ervilha, maionese', 43.00, 37.00, '2025-04-17 21:26:18', NULL),
(22, '1011', 1, 'XIS ENTREVERO', 'Pão, calabresa, coração, carne, frango, bacon, cebola, ovo, queijo, presunto, alface, tomate, milho, ervilha, maionese', 40.00, 35.00, '2025-04-17 21:26:18', NULL),
(23, '2001', 2, 'CACHORRO-QUENTE SIMPLES', 'Pão, 1 salsicha, molho, milho, ervilha, queijo ralado, maionese e batata palha', 21.00, NULL, '2025-04-17 21:26:18', NULL),
(24, '2002', 2, 'CACHORRO-QUENTE DUPLO', 'Pão, 2 salsichas, molho, milho, ervilha, queijo ralado, maionese e batata palha', 23.00, NULL, '2025-04-17 21:26:18', NULL),
(25, '2003', 2, 'CACHORRO-QUENTE VEGETARIANO', 'Pão, 1 salsicha vegetariana, molho, milho, ervilha, queijo ralado, maionese e batata palha', 25.00, NULL, '2025-04-17 21:26:18', NULL),
(26, '3001', 3, '1/4 BAURU FILÉ (1 PESSOA)', 'Bife de filé com molho, presunto, queijo, salada mista, batata frita e arroz', 65.00, NULL, '2025-04-17 21:26:18', NULL),
(27, '3002', 3, '1/2 BAURU FILÉ (2 PESSOAS)', 'Bife de filé com molho, presunto, queijo, salada mista, batata frita e arroz', 100.00, NULL, '2025-04-17 21:26:18', NULL),
(28, '3003', 3, 'BAURU FILÉ (4 PESSOAS)', 'Bife de filé com molho, presunto, queijo, salada mista, batata frita e arroz', 190.00, NULL, '2025-04-17 21:26:18', NULL),
(29, '3004', 3, '1/4 BAURU ALCATRA (1 PESSOA)', 'Bife de alcatra com molho, presunto, queijo, salada mista, batata frita e arroz', 55.00, NULL, '2025-04-17 21:26:18', NULL),
(30, '3005', 3, '1/2 BAURU ALCATRA (2 PESSOAS)', 'Bife de alcatra com molho, presunto, queijo, salada mista, batata frita e arroz', 90.00, NULL, '2025-04-17 21:26:18', NULL),
(31, '3006', 3, 'BAURU ALCATRA (4 PESSOAS)', 'Bife de alcatra com molho, presunto, queijo, salada mista, batata frita e arroz', 150.00, NULL, '2025-04-17 21:26:18', NULL),
(32, '4001', 4, 'PRATO FEITO DA CASA', 'Patinho, arroz, feijão, batata frita, ovo, salada mista e pão', 30.00, NULL, '2025-04-17 21:26:19', NULL),
(33, '4002', 4, 'PRATO FEITO FILÉ', 'Filé, arroz, feijão, batata frita, ovo, salada mista e pão', 42.00, NULL, '2025-04-17 21:26:19', NULL),
(34, '4003', 4, 'PRATO FEITO COXÃO MOLE', 'Coxão mole, arroz, feijão, batata frita, ovo, salada mista e pão', 38.00, NULL, '2025-04-17 21:26:19', NULL),
(35, '4004', 4, 'À LA MINUTA ALCATRA', 'Bife de alcatra, arroz, feijão, batata frita, ovo, salada mista e pão', 40.00, NULL, '2025-04-17 21:26:19', NULL),
(36, '4005', 4, 'À LA MINUTA FILÉ', 'Bife de filé, arroz, salada e batata palha ou batata frita', 45.00, NULL, '2025-04-17 21:26:19', NULL),
(37, '5001', 5, 'TORRADA AMERICANA', 'Pão de xis, tomate, alface, maionese, 2 fatias de presunto, 2 fatias de queijo e ovo', 24.00, NULL, '2025-04-17 21:26:19', NULL),
(38, '5002', 5, 'TORRADA COM BACON', '3 pães, 2 fatias de presunto, 4 fatias de queijo, alface, tomate e maionese', 28.00, NULL, '2025-04-17 21:26:19', NULL),
(39, '6001', 6, 'RODÍZIO DE BIFES', 'Bife de gado, frango e porco, bauru, arroz, batata frita, massa, salada e pão', 62.00, NULL, '2025-04-17 21:26:19', NULL),
(40, '7001', 7, 'TÁBUA DE FRIOS PEQUENA', 'Azeitona, queijo, palmito, pepino, pão torrado, ovo de codorna e filé', 55.00, NULL, '2025-04-17 21:26:19', NULL),
(41, '7002', 7, 'TÁBUA DE FRIOS MÉDIA', 'Azeitona, queijo, palmito, pepino, pão torrado, ovo de codorna e filé', 90.00, NULL, '2025-04-17 21:26:19', NULL),
(42, '7003', 7, 'TÁBUA DE FRIOS GRANDE', 'Carnes (frango e gado), batata, polenta, queijo, ovo de codorna e cebola', 105.00, NULL, '2025-04-17 21:26:19', NULL),
(43, '7004', 7, 'BATATA FRITA PEQUENA (200G)', NULL, 18.00, NULL, '2025-04-17 21:26:19', NULL),
(44, '7005', 7, 'BATATA FRITA PEQUENA COM CHEDDAR E BACON', NULL, 30.00, NULL, '2025-04-17 21:26:19', NULL),
(45, '7006', 7, 'BATATA FRITA GRANDE (400G)', NULL, 28.00, NULL, '2025-04-17 21:26:19', NULL),
(46, '7007', 7, 'BATATA FRITA GRANDE COM CHEDDAR E BACON', NULL, 35.00, NULL, '2025-04-17 21:26:19', NULL),
(47, '7008', 7, 'POLENTA FRITA (500G)', NULL, 22.00, NULL, '2025-04-17 21:26:19', NULL),
(48, '7009', 7, 'QUEIJO NA CHAPA', NULL, 10.00, NULL, '2025-04-17 21:26:19', NULL),
(49, '7010', 7, 'BATATA, POLENTA E QUEIJO', NULL, 42.00, NULL, '2025-04-17 21:26:19', NULL),
(50, '8001', 8, 'ÁGUA MINERAL', NULL, 5.00, NULL, '2025-04-17 21:26:19', NULL),
(51, '8002', 8, 'ÁGUA TÔNICA (LATA)', NULL, 6.00, NULL, '2025-04-17 21:26:19', NULL),
(52, '8003', 8, 'H2O 500ML', NULL, 7.00, NULL, '2025-04-17 21:26:19', NULL),
(53, '8004', 8, 'H2O 1,5L', NULL, 12.00, NULL, '2025-04-17 21:26:19', NULL),
(54, '8005', 8, 'REFRIGERANTE (LATA)', NULL, 7.00, NULL, '2025-04-17 21:26:19', NULL),
(55, '8006', 8, 'REFRIGERANTE KS', NULL, 5.00, NULL, '2025-04-17 21:26:19', NULL),
(56, '8007', 8, 'REFRIGERANTE 600ML', NULL, 8.00, NULL, '2025-04-17 21:26:19', NULL),
(57, '8008', 8, 'REFRIGERANTE 1L', NULL, 10.00, NULL, '2025-04-17 21:26:19', NULL),
(58, '8009', 8, 'REFRIGERANTE 2L', NULL, 17.00, NULL, '2025-04-17 21:26:19', NULL),
(59, '8010', 8, 'COCA-COLA 2L', NULL, 15.00, NULL, '2025-04-17 21:26:19', NULL),
(60, '8011', 8, 'SUCO NATURAL', NULL, 9.00, NULL, '2025-04-17 21:26:19', NULL),
(61, '9001', 9, 'CERVEJA LONG NECK', NULL, 10.00, NULL, '2025-04-17 21:26:19', NULL),
(62, '9002', 9, 'CERVEJA 600ML', NULL, 17.00, NULL, '2025-04-17 21:26:19', NULL),
(63, '9003', 9, 'CHOPP 300ML', NULL, 11.00, NULL, '2025-04-17 21:26:19', NULL),
(64, '9004', 9, 'CHOPP 1L', NULL, 30.00, NULL, '2025-04-17 21:26:19', NULL),
(65, '9005', 9, 'TAÇA DE VINHO MOSCATO', NULL, 12.00, NULL, '2025-04-17 21:26:19', NULL),
(66, '9006', 9, 'TAÇA DE VINHO MERLOT', NULL, 15.00, NULL, '2025-04-17 21:26:19', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto_ingredientes`
--

CREATE TABLE `produto_ingredientes` (
  `produto_id` int(11) NOT NULL,
  `ingrediente_id` int(11) NOT NULL,
  `padrao` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `produto_ingredientes`
--

INSERT INTO `produto_ingredientes` (`produto_id`, `ingrediente_id`, `padrao`, `created_at`) VALUES
(12, 1, 1, '2025-04-17 21:27:15'),
(12, 3, 1, '2025-04-17 21:27:15'),
(12, 14, 1, '2025-04-17 21:27:15'),
(12, 17, 1, '2025-04-17 21:27:15'),
(12, 18, 1, '2025-04-17 21:27:15'),
(12, 25, 1, '2025-04-17 21:27:15'),
(12, 27, 1, '2025-04-17 21:27:15'),
(12, 28, 1, '2025-04-17 21:27:15'),
(12, 29, 1, '2025-04-17 21:27:15'),
(12, 30, 1, '2025-04-17 21:27:15'),
(13, 1, 1, '2025-04-17 21:27:15'),
(13, 4, 1, '2025-04-17 21:27:15'),
(13, 14, 1, '2025-04-17 21:27:15'),
(13, 17, 1, '2025-04-17 21:27:15'),
(13, 18, 1, '2025-04-17 21:27:15'),
(13, 25, 1, '2025-04-17 21:27:15'),
(13, 27, 1, '2025-04-17 21:27:15'),
(13, 28, 1, '2025-04-17 21:27:15'),
(13, 29, 1, '2025-04-17 21:27:15'),
(13, 30, 1, '2025-04-17 21:27:15'),
(14, 1, 1, '2025-04-17 21:27:15'),
(14, 3, 1, '2025-04-17 21:27:15'),
(14, 14, 1, '2025-04-17 21:27:15'),
(14, 17, 1, '2025-04-17 21:27:15'),
(14, 18, 1, '2025-04-17 21:27:15'),
(14, 25, 1, '2025-04-17 21:27:15'),
(14, 27, 1, '2025-04-17 21:27:15'),
(14, 28, 1, '2025-04-17 21:27:15'),
(14, 29, 1, '2025-04-17 21:27:15'),
(14, 30, 1, '2025-04-17 21:27:15'),
(15, 1, 1, '2025-04-17 21:27:15'),
(15, 3, 1, '2025-04-17 21:27:15'),
(15, 7, 1, '2025-04-17 21:27:15'),
(15, 14, 1, '2025-04-17 21:27:15'),
(15, 17, 1, '2025-04-17 21:27:15'),
(15, 18, 1, '2025-04-17 21:27:15'),
(15, 25, 1, '2025-04-17 21:27:15'),
(15, 27, 1, '2025-04-17 21:27:15'),
(15, 28, 1, '2025-04-17 21:27:15'),
(15, 29, 1, '2025-04-17 21:27:15'),
(15, 30, 1, '2025-04-17 21:27:15'),
(16, 1, 1, '2025-04-17 21:27:15'),
(16, 3, 1, '2025-04-17 21:27:15'),
(16, 8, 1, '2025-04-17 21:27:15'),
(16, 14, 1, '2025-04-17 21:27:15'),
(16, 17, 1, '2025-04-17 21:27:15'),
(16, 18, 1, '2025-04-17 21:27:15'),
(16, 25, 1, '2025-04-17 21:27:15'),
(16, 27, 1, '2025-04-17 21:27:15'),
(16, 28, 1, '2025-04-17 21:27:15'),
(16, 29, 1, '2025-04-17 21:27:15'),
(16, 30, 1, '2025-04-17 21:27:15'),
(17, 1, 1, '2025-04-17 21:27:15'),
(17, 14, 1, '2025-04-17 21:27:15'),
(17, 17, 1, '2025-04-17 21:27:15'),
(17, 18, 1, '2025-04-17 21:27:15'),
(17, 22, 1, '2025-04-17 21:27:15'),
(17, 23, 1, '2025-04-17 21:27:15'),
(17, 25, 1, '2025-04-17 21:27:15'),
(17, 29, 1, '2025-04-17 21:27:15'),
(17, 30, 1, '2025-04-17 21:27:15'),
(18, 1, 1, '2025-04-17 21:27:15'),
(18, 5, 1, '2025-04-17 21:27:15'),
(18, 14, 1, '2025-04-17 21:27:15'),
(18, 17, 1, '2025-04-17 21:27:15'),
(18, 18, 1, '2025-04-17 21:27:15'),
(18, 25, 1, '2025-04-17 21:27:15'),
(18, 27, 1, '2025-04-17 21:27:15'),
(18, 28, 1, '2025-04-17 21:27:15'),
(18, 29, 1, '2025-04-17 21:27:15'),
(18, 30, 1, '2025-04-17 21:27:15'),
(19, 1, 1, '2025-04-17 21:27:15'),
(19, 3, 1, '2025-04-17 21:27:15'),
(19, 14, 1, '2025-04-17 21:27:15'),
(19, 17, 1, '2025-04-17 21:27:15'),
(19, 18, 1, '2025-04-17 21:27:15'),
(19, 19, 1, '2025-04-17 21:27:15'),
(19, 25, 1, '2025-04-17 21:27:15'),
(19, 27, 1, '2025-04-17 21:27:15'),
(19, 28, 1, '2025-04-17 21:27:15'),
(19, 29, 1, '2025-04-17 21:27:15'),
(19, 30, 1, '2025-04-17 21:27:15'),
(20, 1, 1, '2025-04-17 21:27:15'),
(20, 6, 1, '2025-04-17 21:27:15'),
(20, 14, 1, '2025-04-17 21:27:15'),
(20, 17, 1, '2025-04-17 21:27:15'),
(20, 18, 1, '2025-04-17 21:27:15'),
(20, 25, 1, '2025-04-17 21:27:15'),
(20, 27, 1, '2025-04-17 21:27:15'),
(20, 28, 1, '2025-04-17 21:27:15'),
(20, 29, 1, '2025-04-17 21:27:15'),
(20, 30, 1, '2025-04-17 21:27:15'),
(21, 1, 1, '2025-04-17 21:27:15'),
(21, 5, 1, '2025-04-17 21:27:15'),
(21, 14, 1, '2025-04-17 21:27:15'),
(21, 20, 1, '2025-04-17 21:27:15'),
(21, 21, 1, '2025-04-17 21:27:15'),
(21, 25, 1, '2025-04-17 21:27:15'),
(21, 27, 1, '2025-04-17 21:27:15'),
(21, 28, 1, '2025-04-17 21:27:15'),
(21, 29, 1, '2025-04-17 21:27:15'),
(21, 30, 1, '2025-04-17 21:27:15'),
(22, 1, 1, '2025-04-17 21:27:15'),
(22, 3, 1, '2025-04-17 21:27:15'),
(22, 4, 1, '2025-04-17 21:27:15'),
(22, 6, 1, '2025-04-17 21:27:15'),
(22, 7, 1, '2025-04-17 21:27:15'),
(22, 8, 1, '2025-04-17 21:27:15'),
(22, 14, 1, '2025-04-17 21:27:15'),
(22, 17, 1, '2025-04-17 21:27:15'),
(22, 18, 1, '2025-04-17 21:27:15'),
(22, 19, 1, '2025-04-17 21:27:15'),
(22, 25, 1, '2025-04-17 21:27:15'),
(22, 27, 1, '2025-04-17 21:27:15'),
(22, 28, 1, '2025-04-17 21:27:15'),
(22, 29, 1, '2025-04-17 21:27:15'),
(22, 30, 1, '2025-04-17 21:27:15'),
(23, 2, 1, '2025-04-17 21:27:15'),
(23, 9, 1, '2025-04-17 21:27:15'),
(23, 15, 1, '2025-04-17 21:27:15'),
(23, 25, 1, '2025-04-17 21:27:15'),
(23, 26, 1, '2025-04-17 21:27:15'),
(23, 29, 1, '2025-04-17 21:27:15'),
(23, 30, 1, '2025-04-17 21:27:15'),
(23, 31, 1, '2025-04-17 21:27:15'),
(24, 2, 1, '2025-04-17 21:27:15'),
(24, 9, 1, '2025-04-17 21:27:15'),
(24, 15, 1, '2025-04-17 21:27:15'),
(24, 25, 1, '2025-04-17 21:27:15'),
(24, 26, 1, '2025-04-17 21:27:15'),
(24, 29, 1, '2025-04-17 21:27:15'),
(24, 30, 1, '2025-04-17 21:27:15'),
(24, 31, 1, '2025-04-17 21:27:15'),
(25, 2, 1, '2025-04-17 21:27:15'),
(25, 10, 1, '2025-04-17 21:27:15'),
(25, 15, 1, '2025-04-17 21:27:15'),
(25, 25, 1, '2025-04-17 21:27:15'),
(25, 26, 1, '2025-04-17 21:27:15'),
(25, 29, 1, '2025-04-17 21:27:15'),
(25, 30, 1, '2025-04-17 21:27:15'),
(25, 31, 1, '2025-04-17 21:27:15'),
(26, 5, 1, '2025-04-17 21:27:16'),
(26, 14, 1, '2025-04-17 21:27:16'),
(26, 24, 1, '2025-04-17 21:27:16'),
(26, 26, 1, '2025-04-17 21:27:16'),
(26, 28, 1, '2025-04-17 21:27:16'),
(26, 32, 1, '2025-04-17 21:27:16'),
(26, 33, 1, '2025-04-17 21:27:16'),
(27, 5, 1, '2025-04-17 21:27:16'),
(27, 14, 1, '2025-04-17 21:27:16'),
(27, 24, 1, '2025-04-17 21:27:16'),
(27, 26, 1, '2025-04-17 21:27:16'),
(27, 28, 1, '2025-04-17 21:27:16'),
(27, 32, 1, '2025-04-17 21:27:16'),
(27, 33, 1, '2025-04-17 21:27:16'),
(28, 5, 1, '2025-04-17 21:27:16'),
(28, 14, 1, '2025-04-17 21:27:16'),
(28, 24, 1, '2025-04-17 21:27:16'),
(28, 26, 1, '2025-04-17 21:27:16'),
(28, 28, 1, '2025-04-17 21:27:16'),
(28, 32, 1, '2025-04-17 21:27:16'),
(28, 33, 1, '2025-04-17 21:27:16'),
(29, 12, 1, '2025-04-17 21:27:16'),
(29, 14, 1, '2025-04-17 21:27:16'),
(29, 24, 1, '2025-04-17 21:27:16'),
(29, 26, 1, '2025-04-17 21:27:16'),
(29, 28, 1, '2025-04-17 21:27:16'),
(29, 32, 1, '2025-04-17 21:27:16'),
(29, 33, 1, '2025-04-17 21:27:16'),
(30, 12, 1, '2025-04-17 21:27:16'),
(30, 14, 1, '2025-04-17 21:27:16'),
(30, 24, 1, '2025-04-17 21:27:16'),
(30, 26, 1, '2025-04-17 21:27:16'),
(30, 28, 1, '2025-04-17 21:27:16'),
(30, 32, 1, '2025-04-17 21:27:16'),
(30, 33, 1, '2025-04-17 21:27:16'),
(31, 12, 1, '2025-04-17 21:27:16'),
(31, 14, 1, '2025-04-17 21:27:16'),
(31, 24, 1, '2025-04-17 21:27:16'),
(31, 26, 1, '2025-04-17 21:27:16'),
(31, 28, 1, '2025-04-17 21:27:16'),
(31, 32, 1, '2025-04-17 21:27:16'),
(31, 33, 1, '2025-04-17 21:27:16'),
(32, 11, 1, '2025-04-17 21:27:16'),
(32, 24, 1, '2025-04-17 21:27:16'),
(32, 27, 1, '2025-04-17 21:27:16'),
(32, 32, 1, '2025-04-17 21:27:16'),
(32, 33, 1, '2025-04-17 21:27:16'),
(32, 34, 1, '2025-04-17 21:27:16'),
(33, 5, 1, '2025-04-17 21:27:16'),
(33, 24, 1, '2025-04-17 21:27:16'),
(33, 27, 1, '2025-04-17 21:27:16'),
(33, 32, 1, '2025-04-17 21:27:16'),
(33, 33, 1, '2025-04-17 21:27:16'),
(33, 34, 1, '2025-04-17 21:27:16'),
(34, 13, 1, '2025-04-17 21:27:16'),
(34, 24, 1, '2025-04-17 21:27:16'),
(34, 27, 1, '2025-04-17 21:27:16'),
(34, 32, 1, '2025-04-17 21:27:16'),
(34, 33, 1, '2025-04-17 21:27:16'),
(34, 34, 1, '2025-04-17 21:27:16'),
(35, 12, 1, '2025-04-17 21:27:16'),
(35, 24, 1, '2025-04-17 21:27:16'),
(35, 27, 1, '2025-04-17 21:27:16'),
(35, 32, 1, '2025-04-17 21:27:16'),
(35, 33, 1, '2025-04-17 21:27:16'),
(35, 34, 1, '2025-04-17 21:27:16'),
(36, 5, 1, '2025-04-17 21:27:16'),
(36, 24, 1, '2025-04-17 21:27:16'),
(36, 31, 1, '2025-04-17 21:27:16'),
(36, 32, 1, '2025-04-17 21:27:16'),
(36, 33, 1, '2025-04-17 21:27:16'),
(37, 1, 1, '2025-04-17 21:27:16'),
(37, 14, 1, '2025-04-17 21:27:16'),
(37, 17, 1, '2025-04-17 21:27:16'),
(37, 18, 1, '2025-04-17 21:27:16'),
(37, 25, 1, '2025-04-17 21:27:16'),
(37, 27, 1, '2025-04-17 21:27:16'),
(37, 28, 1, '2025-04-17 21:27:16'),
(38, 1, 1, '2025-04-17 21:27:16'),
(38, 8, 1, '2025-04-17 21:27:16'),
(38, 14, 1, '2025-04-17 21:27:16'),
(38, 17, 1, '2025-04-17 21:27:16'),
(38, 18, 1, '2025-04-17 21:27:16'),
(38, 25, 1, '2025-04-17 21:27:16'),
(38, 28, 1, '2025-04-17 21:27:16'),
(39, 5, 1, '2025-04-17 21:27:16'),
(39, 6, 1, '2025-04-17 21:27:16'),
(39, 24, 1, '2025-04-17 21:27:16'),
(39, 32, 1, '2025-04-17 21:27:16'),
(39, 33, 1, '2025-04-17 21:27:16'),
(40, 5, 1, '2025-04-17 21:27:16'),
(40, 14, 1, '2025-04-17 21:27:16'),
(40, 22, 1, '2025-04-17 21:27:16'),
(40, 23, 1, '2025-04-17 21:27:16'),
(40, 35, 1, '2025-04-17 21:27:16'),
(40, 36, 1, '2025-04-17 21:27:16'),
(41, 5, 1, '2025-04-17 21:27:16'),
(41, 14, 1, '2025-04-17 21:27:16'),
(41, 22, 1, '2025-04-17 21:27:16'),
(41, 23, 1, '2025-04-17 21:27:16'),
(41, 35, 1, '2025-04-17 21:27:16'),
(41, 36, 1, '2025-04-17 21:27:16'),
(42, 5, 1, '2025-04-17 21:27:16'),
(42, 14, 1, '2025-04-17 21:27:16'),
(42, 22, 1, '2025-04-17 21:27:16'),
(42, 23, 1, '2025-04-17 21:27:16'),
(42, 35, 1, '2025-04-17 21:27:16'),
(42, 36, 1, '2025-04-17 21:27:16'),
(44, 8, 1, '2025-04-17 21:27:16'),
(44, 16, 1, '2025-04-17 21:27:16'),
(44, 32, 1, '2025-04-17 21:27:16'),
(46, 8, 1, '2025-04-17 21:27:16'),
(46, 16, 1, '2025-04-17 21:27:16'),
(46, 32, 1, '2025-04-17 21:27:16');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(100) NOT NULL,
  `login` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nivel` int(100) NOT NULL,
  `pergunta` varchar(255) NOT NULL,
  `resposta` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `login`, `senha`, `nivel`, `pergunta`, `resposta`) VALUES
(7, 'admin2', '$2y$10$jXb/fYAb/u7CrudBx/BQLOmq65AAu02hmIiDCY0dMVx7wr/wkoyxK', 1, 'admin12', 'admin12');

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--

CREATE TABLE `vendas` (
  `id` int(100) NOT NULL,
  `valor` varchar(255) NOT NULL,
  `cliente` varchar(255) NOT NULL,
  `rendimento` varchar(255) NOT NULL,
  `data` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `atividade`
--
ALTER TABLE `atividade`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `despesas`
--
ALTER TABLE `despesas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `estoque`
--
ALTER TABLE `estoque`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_produto_id` (`produto_id`),
  ADD KEY `idx_estoque_atual` (`estoque_atual`),
  ADD KEY `idx_data_validade` (`data_validade`);

--
-- Índices de tabela `ingredientes`
--
ALTER TABLE `ingredientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Índices de tabela `log_pedidos`
--
ALTER TABLE `log_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idpedido` (`idpedido`);

--
-- Índices de tabela `mesas`
--
ALTER TABLE `mesas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`idpedido`),
  ADD KEY `idx_idmesa` (`idmesa`),
  ADD KEY `idx_status` (`status`);

--
-- Índices de tabela `pedido_item_ingredientes`
--
ALTER TABLE `pedido_item_ingredientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_item_id` (`pedido_item_id`);

--
-- Índices de tabela `pedido_itens`
--
ALTER TABLE `pedido_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `idx_produto` (`produto_id`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Índices de tabela `produto_ingredientes`
--
ALTER TABLE `produto_ingredientes`
  ADD PRIMARY KEY (`produto_id`,`ingrediente_id`),
  ADD KEY `ingrediente_id` (`ingrediente_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `vendas`
--
ALTER TABLE `vendas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `atividade`
--
ALTER TABLE `atividade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `despesas`
--
ALTER TABLE `despesas`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `estoque`
--
ALTER TABLE `estoque`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ingredientes`
--
ALTER TABLE `ingredientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de tabela `log_pedidos`
--
ALTER TABLE `log_pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `mesas`
--
ALTER TABLE `mesas`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=491;

--
-- AUTO_INCREMENT de tabela `pedido`
--
ALTER TABLE `pedido`
  MODIFY `idpedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de tabela `pedido_item_ingredientes`
--
ALTER TABLE `pedido_item_ingredientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=193;

--
-- AUTO_INCREMENT de tabela `pedido_itens`
--
ALTER TABLE `pedido_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `estoque`
--
ALTER TABLE `estoque`
  ADD CONSTRAINT `estoque_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `log_pedidos`
--
ALTER TABLE `log_pedidos`
  ADD CONSTRAINT `log_pedidos_ibfk_1` FOREIGN KEY (`idpedido`) REFERENCES `pedido` (`idpedido`);

--
-- Restrições para tabelas `pedido_item_ingredientes`
--
ALTER TABLE `pedido_item_ingredientes`
  ADD CONSTRAINT `fk_pedido_item_ingredientes_pedido_itens` FOREIGN KEY (`pedido_item_id`) REFERENCES `pedido_itens` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `pedido_itens`
--
ALTER TABLE `pedido_itens`
  ADD CONSTRAINT `fk_pedido_itens_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`idpedido`) ON DELETE CASCADE;

--
-- Restrições para tabelas `produtos`
--
ALTER TABLE `produtos`
  ADD CONSTRAINT `produtos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Restrições para tabelas `produto_ingredientes`
--
ALTER TABLE `produto_ingredientes`
  ADD CONSTRAINT `produto_ingredientes_ibfk_1` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `produto_ingredientes_ibfk_2` FOREIGN KEY (`ingrediente_id`) REFERENCES `ingredientes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
