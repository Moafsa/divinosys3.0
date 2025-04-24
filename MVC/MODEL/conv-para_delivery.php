<?php
header('Content-Type: application/json');

require_once 'conexao.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['pedido_id'])) {
        throw new Exception('ID do pedido não fornecido');
    }

    $conn = conectar();
    $conn->begin_transaction();

    // Atualizar o pedido para delivery
    $stmt = $conn->prepare("UPDATE pedido SET 
        tipo = 'delivery',
        nome_cliente = ?,
        telefone_cliente = ?,
        endereco_entrega = ?,
        ponto_referencia = ?,
        taxa_entrega = ?,
        forma_pagamento = ?,
        troco_para = ?
        WHERE idpedido = ?");

    $stmt->bind_param(
        "ssssdsdi",
        $data['nome_cliente'],
        $data['telefone'],
        $data['endereco_entrega'],
        $data['ponto_referencia'],
        $data['taxa_entrega'],
        $data['forma_pagamento'],
        $data['troco_para'],
        $data['pedido_id']
    );

    if (!$stmt->execute()) {
        throw new Exception("Erro ao atualizar pedido: " . $stmt->error);
    }

    // Liberar a mesa
    $stmt = $conn->prepare("UPDATE mesas SET status = 'Livre' WHERE id_mesa = (SELECT idmesa FROM pedido WHERE idpedido = ?)");
    $stmt->bind_param("i", $data['pedido_id']);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao liberar mesa: " . $stmt->error);
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 