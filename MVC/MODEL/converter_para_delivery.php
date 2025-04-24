<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Define o caminho raiz
define('ROOT_PATH', dirname(dirname(dirname(__FILE__))));

// Inclui o arquivo de configuração usando caminho absoluto
require_once ROOT_PATH . '/MVC/MODEL/config.php';
require_once ROOT_PATH . '/MVC/MODEL/conexao.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['pedido_id'])) {
        throw new Exception('ID do pedido não informado');
    }

    $conn = conectar();
    $conn->begin_transaction();

    // Dados do pedido
    $pedido_id = $data['pedido_id'];
    $nome_cliente = isset($data['nome_cliente']) ? $data['nome_cliente'] : '';
    $telefone = isset($data['telefone']) ? preg_replace('/[^0-9]/', '', $data['telefone']) : '';
    $endereco_entrega = isset($data['endereco']) ? $data['endereco'] : '';
    $ponto_referencia = isset($data['ponto_referencia']) ? $data['ponto_referencia'] : '';
    $forma_pagamento = isset($data['forma_pagamento']) ? $data['forma_pagamento'] : '';
    $troco_para = isset($data['troco_para']) ? floatval($data['troco_para']) : 0;
    $taxa_entrega = isset($data['taxa_entrega']) ? floatval($data['taxa_entrega']) : 0;
    $observacoes = isset($data['observacoes']) ? $data['observacoes'] : '';

    // Atualizar o pedido para delivery
    $stmt = $conn->prepare("
        UPDATE pedido SET
            tipo = 'delivery',
            nome_cliente = ?,
            telefone_cliente = ?,
            endereco_entrega = ?,
            ponto_referencia = ?,
            taxa_entrega = ?,
            forma_pagamento = ?,
            troco_para = ?,
            observacoes = ?
        WHERE idpedido = ?
    ");

    $stmt->bind_param(
        "ssssdsdsi",
        $nome_cliente,
        $telefone,
        $endereco_entrega, 
        $ponto_referencia,
        $taxa_entrega,
        $forma_pagamento,
        $troco_para,
        $observacoes,
        $pedido_id
    );

    if (!$stmt->execute()) {
        throw new Exception("Erro ao converter pedido para delivery: " . $stmt->error);
    }

    // Liberar a mesa
    $stmt = $conn->prepare("UPDATE mesas SET status = 'Livre' WHERE id_mesa = (SELECT idmesa FROM pedido WHERE idpedido = ?)");
    $stmt->bind_param("i", $data['pedido_id']);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao liberar mesa: " . $stmt->error);
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Pedido convertido para delivery com sucesso'
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    
    error_log("Erro ao converter pedido para delivery: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) mysqli_close($conn);
} 