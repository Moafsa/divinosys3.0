-- Corrigir o nome da coluna na tabela clientes
ALTER TABLE clientes CHANGE COLUMN ponto_referecia ponto_referencia VARCHAR(255);

-- Adicionar a coluna na tabela pedido
ALTER TABLE pedido ADD COLUMN ponto_referencia VARCHAR(255) AFTER endereco_entrega; 