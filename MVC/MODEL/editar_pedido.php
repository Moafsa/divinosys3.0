<?php
require_once __DIR__ . '/conexao.php';
header('Content-Type: application/json');
$conn = $GLOBALS['conn'];

$pedido_id = isset($_POST['pedido_id']) ? intval($_POST['pedido_id']) : 0;
$mesa = isset($_POST['mesa']) ? intval($_POST['mesa']) : 0;
$produtos = isset($_POST['produto']) ? $_POST['produto'] : [];
$quantidades = isset($_POST['quantidade']) ? $_POST['quantidade'] : [];
$observacoes = isset($_POST['observacao']) ? $_POST['observacao'] : [];

if (!$pedido_id || !$mesa || empty($produtos)) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos', 'debug' => compact('pedido_id','mesa','produtos')]);
    exit;
}

// Atualizar mesa do pedido
if (!mysqli_query($conn, "UPDATE pedido SET idmesa = $mesa WHERE idpedido = $pedido_id")) {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar mesa', 'debug' => mysqli_error($conn)]);
    exit;
}
// Remover itens antigos
if (!mysqli_query($conn, "DELETE FROM pedido_itens WHERE pedido_id = $pedido_id")) {
    echo json_encode(['success' => false, 'message' => 'Erro ao remover itens antigos', 'debug' => mysqli_error($conn)]);
    exit;
}
// Inserir novos itens
for ($i = 0; $i < count($produtos); $i++) {
    $produto = mysqli_real_escape_string($conn, $produtos[$i]);
    $quantidade = intval($quantidades[$i]);
    $observacao = mysqli_real_escape_string($conn, $observacoes[$i]);
    // Buscar id do produto pelo nome
    $res = mysqli_query($conn, "SELECT id FROM produtos WHERE nome = '$produto' LIMIT 1");
    if (!$res) {
        echo json_encode(['success' => false, 'message' => 'Erro ao buscar produto', 'produto' => $produto, 'debug' => mysqli_error($conn)]);
        exit;
    }
    $row = mysqli_fetch_assoc($res);
    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado', 'produto' => $produto]);
        exit;
    }
    $produto_id = $row['id'];
    if (!mysqli_query($conn, "INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, observacao) VALUES ($pedido_id, $produto_id, $quantidade, '$observacao')")) {
        echo json_encode(['success' => false, 'message' => 'Erro ao inserir item', 'produto' => $produto, 'debug' => mysqli_error($conn)]);
        exit;
    }
}
echo json_encode(['success' => true]); 