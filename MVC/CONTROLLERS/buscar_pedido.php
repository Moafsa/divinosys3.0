<?php

$sql = "SELECT p.*, pi.*, pr.nome as produto 
        FROM pedido p 
        JOIN pedido_itens pi ON p.id = pi.pedido_id 
        JOIN produtos pr ON pi.produto_id = pr.id 
        WHERE p.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pedidoId);
$stmt->execute();
$result = $stmt->get_result();

$pedido = null;
$itens = array();

while ($row = $result->fetch_assoc()) {
    error_log("Dados do item: " . print_r($row, true));
    
    if (!$pedido) {
        $pedido = array(
            'id' => $row['id'],
            'idmesa' => $row['idmesa'],
            'data' => $row['data'],
            'hora_pedido' => $row['hora_pedido'],
            'status' => $row['status']
        );
    }
    
    $item = array(
        'id' => $row['id'],
        'produto_id' => $row['produto_id'],
        'produto' => $row['produto'],
        'quantidade' => $row['quantidade'],
        'valor_unitario' => $row['valor_unitario'],
        'valor_total' => $row['valor_total'],
        'observacao' => $row['observacao'],
        'ingredientes_com' => $row['ingredientes_com'],
        'ingredientes_sem' => $row['ingredientes_sem'],
        'tamanho' => isset($row['tamanho']) ? $row['tamanho'] : 'normal'
    );
    
    error_log("Item processado: " . print_r($item, true));
    $itens[] = $item;
}

$response = array(
    'success' => true,
    'pedido' => $pedido,
    'itens' => $itens
);

error_log("Resposta final: " . print_r($response, true));
header('Content-Type: application/json');
echo json_encode($response); 