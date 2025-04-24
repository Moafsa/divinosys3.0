<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Teste do Sistema</h1>";

try {
    $conn = mysqli_connect('db', 'pdv', 'pdv123', 'pdv');
    echo "<p style='color: green'>✓ Conexão com banco de dados OK!</p>";
    
    $tables = mysqli_query($conn, "SHOW TABLES");
    if ($tables) {
        echo "<h2>Tabelas encontradas:</h2><ul>";
        while ($table = mysqli_fetch_array($tables)) {
            echo "<li>" . $table[0] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange'>⚠ Nenhuma tabela encontrada</p>";
    }
    
    mysqli_close($conn);
} catch (Exception $e) {
    echo "<p style='color: red'>✗ Erro: " . $e->getMessage() . "</p>";
}

phpinfo(); 