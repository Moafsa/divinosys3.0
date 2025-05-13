<?php
require_once 'conexao.php';
header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

// Busca o caminho da imagem
$sql = "SELECT caminho FROM imagens_movimentacoes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$img = $result->fetch_assoc();

if (!$img) {
    echo json_encode(['success' => false, 'message' => 'Imagem não encontrada']);
    exit;
}

// Exclui do disco
if (file_exists($img['caminho'])) {
    unlink($img['caminho']);
}

// Exclui do banco
$sql = "DELETE FROM imagens_movimentacoes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

echo json_encode(['success' => true]); 