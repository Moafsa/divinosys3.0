<?php
header('Content-Type: application/json');
include_once(__DIR__ . "/conexao.php");

try {
    // Obter dados do corpo da requisição
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['idmesa']) || !isset($data['itens']) || empty($data['itens'])) {
        throw new Exception("Dados incompletos");
    }

    $mesa_id = intval($data['idmesa']);
    $itens = $data['itens'];
    
    // Validar se a mesa existe e está livre
    $query = "SELECT status FROM mesas WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $mesa_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao verificar mesa");
    }
    
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) === 0) {
        throw new Exception("Mesa não encontrada");
    }
    
    $mesa = mysqli_fetch_assoc($result);
    if ($mesa['status'] !== 'Livre') {
        throw new Exception("Mesa não está livre");
    }
    
    // Iniciar transação
    mysqli_begin_transaction($conn);
    
    // Criar pedido
    $query = "INSERT INTO pedido (idmesa, status, data_criacao) VALUES (?, 'Em Andamento', NOW())";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $mesa_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao criar pedido");
    }
    
    $pedido_id = mysqli_insert_id($conn);
    
    // Inserir itens do pedido
    $query = "INSERT INTO pedido_itens (pedido_id, produto_id, quantidade) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    
    foreach ($itens as $item) {
        if (!isset($item['produto_id']) || !isset($item['quantidade'])) {
            throw new Exception("Dados de item incompletos");
        }
        
        $produto_id = intval($item['produto_id']);
        $quantidade = intval($item['quantidade']);
        
        mysqli_stmt_bind_param($stmt, "iii", $pedido_id, $produto_id, $quantidade);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Erro ao inserir item do pedido");
        }
    }
    
    // Atualizar status da mesa
    $query = "UPDATE mesas SET status = 'Ocupada' WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $mesa_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao atualizar status da mesa");
    }
    
    // Commit da transação
    mysqli_commit($conn);
    
    echo json_encode(array(
        'success' => true,
        'message' => 'Pedido criado com sucesso',
        'pedido_id' => $pedido_id
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