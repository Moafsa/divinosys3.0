-- Adicionar coluna valor_total se não existir
ALTER TABLE pedido ADD COLUMN IF NOT EXISTS valor_total DECIMAL(10,2) DEFAULT 0.00 AFTER status; 