<?php
header('Content-Type: application/json');

// Prevenir acesso direto
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die(json_encode(['success' => false, 'message' => 'Acesso não permitido']));
}

// Verificar se o ID do pedido foi fornecido
if (!isset($_POST['idpedido']) || empty($_POST['idpedido'])) {
    die(json_encode(['success' => false, 'message' => 'ID do pedido não fornecido']));
}

require_once '../MODEL/conexao.php';

try {
    file_put_contents('/tmp/excluir_pedido_debug.log', date('c')." POST: ".print_r($_POST, true), FILE_APPEND);
    $idpedido = (int)$_POST['idpedido'];
    
    // Buscar id da mesa antes de deletar o pedido
    $query = "SELECT idmesa FROM pedido WHERE idpedido = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idpedido);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $idmesa = $row ? (int)$row['idmesa'] : null;
    
    // Iniciar transação
    mysqli_begin_transaction($conn);
    
    // Excluir ingredientes dos itens do pedido
    $query = "DELETE pii FROM pedido_item_ingredientes pii 
              INNER JOIN pedido_itens pi ON pii.pedido_item_id = pi.id 
              WHERE pi.pedido_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idpedido);
    mysqli_stmt_execute($stmt);
    
    // Excluir itens do pedido
    $query = "DELETE FROM pedido_itens WHERE pedido_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idpedido);
    mysqli_stmt_execute($stmt);
    
    // Excluir o pedido
    $query = "DELETE FROM pedido WHERE idpedido = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idpedido);
    mysqli_stmt_execute($stmt);
    
    // Atualizar status da mesa para Livre (1) se idmesa encontrado
    if ($idmesa) {
        $query = "UPDATE mesas SET status = 1 WHERE id_mesa = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $idmesa);
        mysqli_stmt_execute($stmt);
    }
    
    // Commit da transação
    mysqli_commit($conn);
    
    echo json_encode(['success' => true, 'message' => 'Pedido excluído com sucesso']);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    mysqli_rollback($conn);
    error_log("Erro ao excluir pedido: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
    }
} 