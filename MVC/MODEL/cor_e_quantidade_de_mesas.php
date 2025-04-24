<?php
session_start();
include_once 'config.php';
include_once 'conexao.php';

// Verificar se a conexão está ativa
if (!isset($conn) || !$conn) {
    $_SESSION['msg'] = "<div class='alert alert-danger'>Erro de conexão com o banco de dados</div>";
    header("Location: " . url('?view=Dashboard1'));
    exit;
}

// Verificar se os dados do formulário foram enviados
if (isset($_POST['cor']) || isset($_POST['mesas'])) {
    
    // Definir o charset para UTF-8
    mysqli_set_charset($conn, "utf8");
    
    try {
        // Processar a cor
        if (isset($_POST['cor']) && $_POST['cor'] != "") {
            $cor_input = $_POST['cor'];
            
            // Mapear valores para cores
            $cores = [
                '1' => 'success',
                '2' => 'danger',
                '3' => 'warning',
                '4' => 'info',
                '5' => 'primary'
            ];
            
            if (isset($cores[$cor_input])) {
                $cor = $cores[$cor_input];
                
                // Atualizar a cor usando prepared statement
                $stmt = mysqli_prepare($conn, "UPDATE cor SET cor = ? WHERE id = 1");
                mysqli_stmt_bind_param($stmt, "s", $cor);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Erro ao atualizar a cor: " . mysqli_stmt_error($stmt));
                }
                
                $_SESSION['cor'] = $cor;
                mysqli_stmt_close($stmt);
            }
        }
        
        // Processar a quantidade de mesas
        if (isset($_POST['mesas']) && $_POST['mesas'] != "") {
            $mesas = (int)$_POST['mesas'];
            
            // Verificar a última mesa usando prepared statement
            $stmt = mysqli_prepare($conn, "SELECT id_mesa FROM mesas ORDER BY id DESC LIMIT 1");
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $ultima_mesa = mysqli_fetch_assoc($result);
            $i = isset($ultima_mesa['id_mesa']) ? (int)$ultima_mesa['id_mesa'] : 0;
            mysqli_stmt_close($stmt);
            
            // Preparar statements para inserção e exclusão
            $stmt_insert = mysqli_prepare($conn, "INSERT INTO mesas (id_mesa, status) VALUES (?, 1)");
            $stmt_delete = mysqli_prepare($conn, "DELETE FROM mesas WHERE id_mesa = ?");
            
            // Adicionar novas mesas
            if ($mesas > $i) {
                while ($mesas > $i) {
                    $i++;
                    mysqli_stmt_bind_param($stmt_insert, "i", $i);
                    if (!mysqli_stmt_execute($stmt_insert)) {
                        throw new Exception("Erro ao adicionar mesa: " . mysqli_stmt_error($stmt_insert));
                    }
                }
            } 
            // Remover mesas excedentes
            elseif ($mesas < $i) {
                while ($mesas < $i) {
                    mysqli_stmt_bind_param($stmt_delete, "i", $i);
                    if (!mysqli_stmt_execute($stmt_delete)) {
                        throw new Exception("Erro ao remover mesa: " . mysqli_stmt_error($stmt_delete));
                    }
                    $i--;
                }
            }
            
            mysqli_stmt_close($stmt_insert);
            mysqli_stmt_close($stmt_delete);
        }
        
        $_SESSION['msg'] = "<div class='alert alert-success'>Configurações atualizadas com sucesso!</div>";
        
    } catch (Exception $e) {
        error_log("Erro nas configurações: " . $e->getMessage());
        $_SESSION['msg'] = "<div class='alert alert-danger'>Erro ao atualizar configurações: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Redirecionar para a página inicial
header("Location: " . url('?view=Dashboard1'));
exit;
?>