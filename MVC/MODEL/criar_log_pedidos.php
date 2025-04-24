<?php
// Define o caminho raiz
define('ROOT_PATH', dirname(dirname(dirname(__FILE__))));

// Inclui o arquivo de configuração usando caminho absoluto
require_once ROOT_PATH . '/MVC/MODEL/config.php';
require_once ROOT_PATH . '/MVC/MODEL/conexao.php';

// SQL para criar a tabela
$sql = "CREATE TABLE IF NOT EXISTS log_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    idpedido INT NOT NULL,
    status_anterior VARCHAR(50),
    novo_status VARCHAR(50),
    usuario VARCHAR(100),
    data_alteracao DATETIME NOT NULL,
    FOREIGN KEY (idpedido) REFERENCES pedido(idpedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Executa o SQL
if (mysqli_query($conn, $sql)) {
    echo "Tabela log_pedidos criada com sucesso!";
} else {
    echo "Erro ao criar tabela: " . mysqli_error($conn);
}
?> 