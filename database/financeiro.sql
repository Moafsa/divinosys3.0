-- Estrutura para tabela `categorias_financeiras`
CREATE TABLE `categorias_financeiras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `tipo` enum('receita','despesa') NOT NULL,
  `descricao` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estrutura para tabela `contas_financeiras`
CREATE TABLE `contas_financeiras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `tipo` enum('conta_corrente','poupanca','carteira','outros') NOT NULL,
  `saldo_inicial` decimal(10,2) NOT NULL DEFAULT '0.00',
  `saldo_atual` decimal(10,2) NOT NULL DEFAULT '0.00',
  `banco` varchar(100),
  `agencia` varchar(20),
  `conta` varchar(20),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estrutura para tabela `movimentacoes_financeiras`
CREATE TABLE `movimentacoes_financeiras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo` enum('receita','despesa') NOT NULL,
  `categoria_id` int NOT NULL,
  `conta_id` int NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_movimentacao` date NOT NULL,
  `data_vencimento` date,
  `descricao` text,
  `status` enum('pendente','pago','cancelado') NOT NULL DEFAULT 'pendente',
  `forma_pagamento` enum('dinheiro','cartao_credito','cartao_debito','pix','transferencia','outros') NOT NULL,
  `comprovante` varchar(255),
  `observacoes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`),
  KEY `conta_id` (`conta_id`),
  CONSTRAINT `fk_movimentacao_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_financeiras` (`id`),
  CONSTRAINT `fk_movimentacao_conta` FOREIGN KEY (`conta_id`) REFERENCES `contas_financeiras` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estrutura para tabela `parcelas_financeiras`
CREATE TABLE `parcelas_financeiras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `movimentacao_id` int NOT NULL,
  `numero_parcela` int NOT NULL,
  `total_parcelas` int NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_vencimento` date NOT NULL,
  `status` enum('pendente','pago','cancelado') NOT NULL DEFAULT 'pendente',
  `data_pagamento` date,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `movimentacao_id` (`movimentacao_id`),
  CONSTRAINT `fk_parcela_movimentacao` FOREIGN KEY (`movimentacao_id`) REFERENCES `movimentacoes_financeiras` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir categorias padrão
INSERT INTO `categorias_financeiras` (`nome`, `tipo`, `descricao`) VALUES
('Vendas', 'receita', 'Receitas provenientes de vendas'),
('Serviços', 'receita', 'Receitas provenientes de serviços'),
('Aluguel', 'despesa', 'Despesas com aluguel'),
('Salários', 'despesa', 'Despesas com salários'),
('Fornecedores', 'despesa', 'Despesas com fornecedores'),
('Impostos', 'despesa', 'Despesas com impostos'),
('Manutenção', 'despesa', 'Despesas com manutenção'),
('Outros', 'receita', 'Outras receitas'),
('Outros', 'despesa', 'Outras despesas'); 