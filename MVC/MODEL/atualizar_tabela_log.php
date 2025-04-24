<?php
include_once 'conexao.php';

try {
    // Primeiro, vamos dropar a tabela antiga se ela existir
    $drop_sql = "DROP TABLE IF EXISTS log_pedidos";
    if (!mysqli_query($conn, $drop_sql)) {
        throw new Exception("Erro ao remover tabela antiga: " . mysqli_error($conn));
    }

    // Agora vamos criar a nova tabela com a estrutura correta
    $create_sql = "CREATE TABLE log_pedidos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        idpedido INT NOT NULL,
        status_anterior VARCHAR(50),
        status_novo VARCHAR(50) NOT NULL,
        data_alteracao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        usuario VARCHAR(100),
        INDEX idx_pedido (idpedido)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if (!mysqli_query($conn, $create_sql)) {
        throw new Exception("Erro ao criar nova tabela de log: " . mysqli_error($conn));
    }

    echo json_encode([
        'success' => true,
        'message' => 'Tabela de log atualizada com sucesso!'
    ]);

} catch (Exception $e) {
    error_log("Erro ao atualizar tabela de log: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => "Erro ao atualizar tabela de log: " . $e->getMessage()
    ]);
}

mysqli_close($conn);
?> 