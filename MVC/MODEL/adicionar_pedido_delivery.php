<?php
require_once '../config.php';
require_once '../conexao.php';

header('Content-Type: application/json');

try {
    // Validar dados básicos do pedido
    if (!isset($_POST['produto_id']) || !isset($_POST['quantidade'])) {
        throw new Exception('Produto e quantidade são obrigatórios');
    }

    // Obter dados do POST com valores padrão para campos opcionais
    $produto_id = intval($_POST['produto_id']);
    $quantidade = intval($_POST['quantidade']);
    $tamanho = isset($_POST['tamanho']) ? $_POST['tamanho'] : '';
    $observacoes = isset($_POST['observacoes']) ? $_POST['observacoes'] : '';
    $valor = isset($_POST['valor']) ? floatval($_POST['valor']) : 0;
    $nome_cliente = isset($_POST['nome_cliente']) ? $_POST['nome_cliente'] : '';
    $telefone = isset($_POST['telefone']) ? $_POST['telefone'] : '';
    $endereco = isset($_POST['endereco']) ? $_POST['endereco'] : '';
    $referencia = isset($_POST['referencia']) ? $_POST['referencia'] : '';
    $taxa_entrega = isset($_POST['taxa_entrega']) ? floatval($_POST['taxa_entrega']) : 0;
    $forma_pagamento = isset($_POST['forma_pagamento']) ? $_POST['forma_pagamento'] : '';
    $troco_para = isset($_POST['troco_para']) ? floatval($_POST['troco_para']) : 0;

    // Verificar se o produto existe e está ativo
    $stmt = mysqli_prepare($conn, "SELECT nome, status FROM produtos WHERE id = ?");
    if (!$stmt) {
        throw new Exception('Erro ao preparar consulta de produto');
    }

    mysqli_stmt_bind_param($stmt, 'i', $produto_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Erro ao verificar produto');
    }

    $result = mysqli_stmt_get_result($stmt);
    $produto = mysqli_fetch_assoc($result);

    if (!$produto) {
        throw new Exception('Produto não encontrado');
    }

    if ($produto['status'] != 1) {
        throw new Exception('Produto indisponível');
    }

    // Calcular valor total
    $valor_total = $valor * $quantidade;

    // Adicionar taxa de entrega se fornecida
    if ($taxa_entrega > 0) {
        $valor_total += $taxa_entrega;
    }

    // Inserir pedido
    $data = date('Y-m-d');
    $hora = date('H:i:s');
    $status = 'Pendente';
    $delivery = 1; // Marca como pedido delivery

    $sql = "INSERT INTO pedido (
        produto_id, 
        nome_produto,
        quantidade, 
        tamanho,
        valor_unitario,
        valor_total,
        observacoes,
        data,
        hora_pedido,
        status,
        delivery,
        nome_cliente,
        telefone_cliente,
        endereco_entrega,
        referencia,
        taxa_entrega,
        forma_pagamento,
        troco_para
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception('Erro ao preparar inserção do pedido');
    }

    mysqli_stmt_bind_param($stmt, 'isisddssssissssdsd',
        $produto_id,
        $produto['nome'],
        $quantidade,
        $tamanho,
        $valor,
        $valor_total,
        $observacoes,
        $data,
        $hora,
        $status,
        $delivery,
        $nome_cliente,
        $telefone,
        $endereco,
        $referencia,
        $taxa_entrega,
        $forma_pagamento,
        $troco_para
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Erro ao inserir pedido');
    }

    $pedido_id = mysqli_insert_id($conn);

    echo json_encode(array(
        'success' => true,
        'message' => 'Pedido adicionado com sucesso',
        'pedido_id' => $pedido_id
    ));

} catch (Exception $e) {
    error_log('Erro em adicionar_pedido_delivery.php: ' . $e->getMessage());
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage()
    ));
} 