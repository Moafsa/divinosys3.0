<?php
session_start();
include_once 'conexao.php';
include_once 'config.php';

$config = Config::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cor = isset($_POST['cor']) ? $_POST['cor'] : null;
    
    if ($cor) {
        try {
            // Primeiro, verifica se a tabela existe
            $checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'configuracoes'");
            if (mysqli_num_rows($checkTable) == 0) {
                // Criar a tabela se não existir
                mysqli_query($conn, "
                    CREATE TABLE IF NOT EXISTS configuracoes (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        chave VARCHAR(50) NOT NULL UNIQUE,
                        valor TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )
                ");
            }

            // Tenta atualizar, se não existir, insere
            $stmt = mysqli_prepare($conn, "
                INSERT INTO configuracoes (chave, valor) 
                VALUES ('cor', ?) 
                ON DUPLICATE KEY UPDATE valor = ?
            ");
            mysqli_stmt_bind_param($stmt, 'ss', $cor, $cor);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['msg'] = "<div class='alert alert-success'>Cor atualizada com sucesso!</div>";
            } else {
                throw new Exception(mysqli_error($conn));
            }
            
            mysqli_stmt_close($stmt);
        } catch (Exception $e) {
            error_log("Erro ao salvar configuração: " . $e->getMessage());
            $_SESSION['msg'] = "<div class='alert alert-danger'>Erro ao salvar configuração: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Redireciona para o Dashboard após salvar
$config->redirect('?view=Dashboard1'); 