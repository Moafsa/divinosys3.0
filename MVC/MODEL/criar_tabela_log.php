<?php
include_once 'conexao.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS log_pedidos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pedido_id INT NOT NULL,
        usuario_id INT NOT NULL,
        status_anterior VARCHAR(50),
        status_novo VARCHAR(50) NOT NULL,
        data_atualizacao DATETIME NOT NULL,
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
        INDEX idx_pedido (pedido_id),
        INDEX idx_usuario (usuario_id),
        INDEX idx_data (data_atualizacao)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if (!mysqli_query($conn, $sql)) {
        throw new Exception("Erro ao criar tabela de log: " . mysqli_error($conn));
    }

    echo "Tabela de log criada com sucesso!";

} catch (Exception $e) {
    error_log("Erro ao criar tabela de log: " . $e->getMessage());
    echo "Erro ao criar tabela de log: " . htmlspecialchars($e->getMessage());
} 