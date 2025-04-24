<?php
include_once 'conexao.php';

try {
    // Verificar se a coluna já existe
    $check_column = "SHOW COLUMNS FROM categorias LIKE 'imagem'";
    $result = mysqli_query($conn, $check_column);
    
    if (mysqli_num_rows($result) == 0) {
        // Adicionar a coluna imagem
        $add_column = "ALTER TABLE categorias ADD COLUMN imagem VARCHAR(255)";
        if (mysqli_query($conn, $add_column)) {
            echo "Coluna 'imagem' adicionada com sucesso!";
        } else {
            throw new Exception("Erro ao adicionar coluna: " . mysqli_error($conn));
        }
    } else {
        echo "A coluna 'imagem' já existe.";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?> 