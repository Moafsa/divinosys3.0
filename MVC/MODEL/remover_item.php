<?php
header('Content-Type: application/json');
include_once(__DIR__ . "/conexao.php");

try {
    // Validar ID do pedido
    $pedido_id = isset($_POST['id']) ? intval($_POST['id']) : null;

    if (!$pedido_id) {
        throw new Exception("ID do pedido não fornecido");
    }

    // Verificar se o pedido existe e não está finalizado
    $stmt = mysqli_prepare($conn, "
        SELECT p.*, pr.nome as produto_nome 
        FROM pedido p
        JOIN produtos pr ON p.id_produto = pr.id
        WHERE p.idpedido = ?
    ");
    
    mysqli_stmt_bind_param($stmt, "i", $pedido_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        throw new Exception("Pedido não encontrado ou já finalizado");
    }

    $pedido = mysqli_fetch_assoc($result);
    $mesa_id = $pedido['idmesa'];

    // Remover o pedido
    $stmt = mysqli_prepare($conn, "DELETE FROM pedido WHERE idpedido = ?");
    mysqli_stmt_bind_param($stmt, "i", $pedido_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao remover pedido");
    }

    // Verificar se ainda existem pedidos para esta mesa
    $stmt = mysqli_prepare($conn, "
        SELECT p.*, pr.nome as produto_nome 
        FROM pedido p
        JOIN produtos pr ON p.id_produto = pr.id
        WHERE p.idmesa = ? AND p.status != 'Finalizado'
        ORDER BY p.created_at DESC
    ");
    
    mysqli_stmt_bind_param($stmt, "i", $mesa_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    // Se não houver mais pedidos, atualizar status da mesa para livre
    if ($row['total_pedidos'] == 0) {
        $stmt = mysqli_prepare($conn, "UPDATE mesas SET status = 1 WHERE idmesa = ?");
        mysqli_stmt_bind_param($stmt, "i", $mesa_id);
        mysqli_stmt_execute($stmt);
    }

    echo json_encode(array(
        'success' => true,
        'message' => 'Item removido com sucesso'
    ));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage()
    ));
}

mysqli_close($conn);
?> 