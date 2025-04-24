<?php
require_once(__DIR__ . "/../VIEWS/include_conexao.php");

try {
    // Ler o arquivo SQL
    $sql = file_get_contents(__DIR__ . '/criar_tabela_produtos_ingredientes.sql');
    
    // Executar o SQL
    if (!mysqli_query($conn, $sql)) {
        throw new Exception("Erro ao criar tabela: " . mysqli_error($conn));
    }
    
    echo "Tabela produtos_ingredientes criada com sucesso!\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?> 