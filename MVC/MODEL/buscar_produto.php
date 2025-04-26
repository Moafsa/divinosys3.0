<?php
require_once __DIR__ . '/conexao.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid product ID']);
    exit;
}

$sql = "SELECT p.id, p.nome, p.categoria_id, p.preco_normal, p.descricao, p.imagem, e.estoque_atual, e.estoque_minimo, e.preco_custo, e.marca FROM produtos p LEFT JOIN estoque e ON p.id = e.produto_id WHERE p.id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . mysqli_error($conn)]);
    exit;
}
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$produto = mysqli_fetch_assoc($result);

if (!$produto) {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found']);
    exit;
}

// Garantir que todos os campos estejam presentes
$produto = array_merge([
    'id' => '',
    'nome' => '',
    'categoria_id' => '',
    'preco_normal' => '',
    'descricao' => '',
    'imagem' => '',
    'estoque_atual' => '',
    'estoque_minimo' => '',
    'preco_custo' => '',
    'marca' => ''
], $produto);

echo json_encode($produto); 