<?php
header('Content-Type: application/json');
include_once(__DIR__ . "/conexao.php");

try {
    // Obter e validar ID do pedido
    $pedido_id = isset($_POST['pedido_id']) ? intval($_POST['pedido_id']) : null;
    
    if (!$pedido_id) {
        throw new Exception("ID do pedido não fornecido");
    }
    
    // Verificar se o pedido existe e obter a mesa
    $query = "SELECT idmesa FROM pedido WHERE idpedido = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $pedido_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao verificar pedido");
    }
    
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) === 0) {
        throw new Exception("Pedido não encontrado");
    }
    
    $pedido = mysqli_fetch_assoc($result);
    $mesa_id = $pedido['idmesa'];
    
    // Iniciar transação
    mysqli_begin_transaction($conn);
    
    // Excluir itens do pedido
    $query = "DELETE FROM pedido_itens WHERE pedido_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $pedido_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao excluir itens do pedido");
    }
    
    // Excluir pedido
    $query = "DELETE FROM pedido WHERE idpedido = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $pedido_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao excluir pedido");
    }
    
    // Liberar mesa
    $query = "UPDATE mesas SET status = 1 WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $mesa_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao atualizar status da mesa");
    }
    
    // Commit da transação
    mysqli_commit($conn);
    
    echo json_encode(array(
        'success' => true,
        'message' => 'Pedido excluído com sucesso'
    ));

} catch (Exception $e) {
    // Rollback em caso de erro
    if (isset($conn) && mysqli_ping($conn)) {
        mysqli_rollback($conn);
    }
    
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage()
    ));
}

mysqli_close($conn);
?> 