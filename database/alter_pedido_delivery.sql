-- First drop the existing check constraint if exists
SET @constraint_name = (
    SELECT CONSTRAINT_NAME 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE TABLE_NAME = 'pedido' 
    AND CONSTRAINT_TYPE = 'CHECK'
    AND TABLE_SCHEMA = DATABASE()
);

SET @sql = IF(@constraint_name IS NOT NULL, 
    CONCAT('ALTER TABLE pedido DROP CHECK ', @constraint_name),
    'SELECT 1');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create table for customers if not exists
CREATE TABLE IF NOT EXISTS clientes (
    id INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL UNIQUE,
    endereco TEXT,
    referencia TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add columns only if they don't exist
SET @dbname = DATABASE();

-- Add cliente_id if not exists
SELECT IF(
    EXISTS(
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'pedido' AND COLUMN_NAME = 'cliente_id'
    ),
    'SELECT 1',
    'ALTER TABLE pedido ADD COLUMN cliente_id INT NULL'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add endereco_entrega if not exists
SELECT IF(
    EXISTS(
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'pedido' AND COLUMN_NAME = 'endereco_entrega'
    ),
    'SELECT 1',
    'ALTER TABLE pedido ADD COLUMN endereco_entrega TEXT NULL'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add telefone_cliente if not exists
SELECT IF(
    EXISTS(
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'pedido' AND COLUMN_NAME = 'telefone_cliente'
    ),
    'SELECT 1',
    'ALTER TABLE pedido ADD COLUMN telefone_cliente VARCHAR(20) NULL'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add taxa_entrega if not exists
SELECT IF(
    EXISTS(
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'pedido' AND COLUMN_NAME = 'taxa_entrega'
    ),
    'SELECT 1',
    'ALTER TABLE pedido ADD COLUMN taxa_entrega DECIMAL(10,2) DEFAULT 0.00'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add forma_pagamento if not exists
SELECT IF(
    EXISTS(
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'pedido' AND COLUMN_NAME = 'forma_pagamento'
    ),
    'SELECT 1',
    'ALTER TABLE pedido ADD COLUMN forma_pagamento VARCHAR(50) NULL'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add troco_para if not exists
SELECT IF(
    EXISTS(
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'pedido' AND COLUMN_NAME = 'troco_para'
    ),
    'SELECT 1',
    'ALTER TABLE pedido ADD COLUMN troco_para DECIMAL(10,2) NULL'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add entregador_id if not exists
SELECT IF(
    EXISTS(
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'pedido' AND COLUMN_NAME = 'entregador_id'
    ),
    'SELECT 1',
    'ALTER TABLE pedido ADD COLUMN entregador_id INT NULL'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add hora_saida_entrega if not exists
SELECT IF(
    EXISTS(
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'pedido' AND COLUMN_NAME = 'hora_saida_entrega'
    ),
    'SELECT 1',
    'ALTER TABLE pedido ADD COLUMN hora_saida_entrega TIME NULL'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add hora_entrega if not exists
SELECT IF(
    EXISTS(
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'pedido' AND COLUMN_NAME = 'hora_entrega'
    ),
    'SELECT 1',
    'ALTER TABLE pedido ADD COLUMN hora_entrega TIME NULL'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create table for delivery personnel if not exists
CREATE TABLE IF NOT EXISTS entregadores (
    id INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    status ENUM('Ativo', 'Inativo', 'Em Entrega') NOT NULL DEFAULT 'Ativo',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add foreign keys if they don't exist
SELECT IF(
    EXISTS(
        SELECT * FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'pedido' AND CONSTRAINT_NAME = 'fk_pedido_cliente'
    ),
    'SELECT 1',
    'ALTER TABLE pedido ADD CONSTRAINT fk_pedido_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL ON UPDATE CASCADE'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT IF(
    EXISTS(
        SELECT * FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'pedido' AND CONSTRAINT_NAME = 'fk_pedido_entregador'
    ),
    'SELECT 1',
    'ALTER TABLE pedido ADD CONSTRAINT fk_pedido_entregador FOREIGN KEY (entregador_id) REFERENCES entregadores(id) ON DELETE SET NULL ON UPDATE CASCADE'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update status field to support delivery statuses
ALTER TABLE pedido 
MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Pendente';

-- Add new check constraint for status
ALTER TABLE pedido
ADD CONSTRAINT chk_pedido_status 
CHECK (status IN ('Pendente', 'Em Preparo', 'Pronto', 'Em Rota', 'Entregue', 'Cancelado')); 