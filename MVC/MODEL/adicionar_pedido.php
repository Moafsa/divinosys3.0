<?php
// Garante que nenhum output foi enviado antes
ob_start();

header('Content-Type: application/json');

// Habilita exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/conexao.php';
require_once 'Database.php';

// Limpa qualquer output anterior
ob_clean();

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Validate required fields
    $required_fields = ['produto_id', 'quantidade', 'tamanho', 'preco'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Campo obrigatório não preenchido: $field");
        }
    }

    // Get POST data
    $produto_id = $_POST['produto_id'];
    $quantidade = $_POST['quantidade'];
    $tamanho = $_POST['tamanho'];
    $observacao = isset($_POST['observacao']) ? $_POST['observacao'] : '';
    $preco = $_POST['preco'];
    $cliente = isset($_POST['cliente']) ? $_POST['cliente'] : '';
    $mesa_id = isset($_POST['mesa_id']) ? $_POST['mesa_id'] : null;
    
    // Delivery specific fields
    $is_delivery = isset($_POST['delivery']) && $_POST['delivery'] == '1';
    $endereco_entrega = isset($_POST['endereco_entrega']) ? $_POST['endereco_entrega'] : null;
    $telefone_cliente = isset($_POST['telefone_cliente']) ? $_POST['telefone_cliente'] : null;
    $taxa_entrega = isset($_POST['taxa_entrega']) ? floatval($_POST['taxa_entrega']) : 0.00;
    $forma_pagamento = isset($_POST['forma_pagamento']) ? $_POST['forma_pagamento'] : null;
    $troco_para = isset($_POST['troco_para']) ? floatval($_POST['troco_para']) : null;

    // Validate delivery fields if it's a delivery order
    if ($is_delivery) {
        if (empty($endereco_entrega)) {
            throw new Exception("Endereço de entrega é obrigatório para pedidos delivery");
        }
        if (empty($telefone_cliente)) {
            throw new Exception("Telefone do cliente é obrigatório para pedidos delivery");
        }
        if (empty($forma_pagamento)) {
            throw new Exception("Forma de pagamento é obrigatória para pedidos delivery");
        }
    }

    // Start transaction
    $conn->beginTransaction();

    // Check if product exists and is active
    $stmt = $conn->prepare("SELECT nome, status FROM produtos WHERE id = ?");
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch();

    if (!$produto) {
        throw new Exception("Produto não encontrado");
    }

    if ($produto['status'] !== 'ativo') {
        throw new Exception("Produto não está disponível");
    }

    // If table is provided, check if it exists and is available
    if ($mesa_id !== null) {
        $stmt = $conn->prepare("SELECT status FROM mesas WHERE id = ?");
        $stmt->execute([$mesa_id]);
        $mesa = $stmt->fetch();

        if (!$mesa) {
            throw new Exception("Mesa não encontrada");
        }
    }

    // Calculate total value
    $valor_total = $quantidade * $preco;
    if ($is_delivery) {
        $valor_total += $taxa_entrega;
    }

    // Insert order
    $stmt = $conn->prepare("
        INSERT INTO pedido (
            cliente, idmesa, produto, quantidade, valor_total, observacao, 
            delivery, endereco_entrega, telefone_cliente, taxa_entrega,
            forma_pagamento, troco_para, status, tipo
    ) VALUES (
            ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?,
            ?, ?, ?, ?
        )
    ");

    $status = 'Pendente';
    $tipo = $is_delivery ? 'delivery' : 'mesa';

    $stmt->execute([
        $cliente, $mesa_id, $produto['nome'], $quantidade, $valor_total, $observacao,
        $is_delivery, $endereco_entrega, $telefone_cliente, $taxa_entrega,
        $forma_pagamento, $troco_para, $status, $tipo
    ]);

    // Commit transaction
    $conn->commit();

    // Return success response
    $response = [
        'status' => 'success',
        'message' => 'Pedido adicionado com sucesso',
        'pedido_id' => $conn->lastInsertId()
    ];
    echo json_encode($response);

} catch (Exception $e) {
    // Rollback transaction if there was an error
    if (isset($conn)) {
        $conn->rollBack();
    }

    // Log error
    error_log("Erro ao adicionar pedido: " . $e->getMessage());
    
    // Return error response
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
    echo json_encode($response);
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}

// Garante que todo o output foi enviado
ob_end_flush();
?> 