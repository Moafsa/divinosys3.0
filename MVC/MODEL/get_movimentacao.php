<?php
require_once 'conexao.php';
require_once 'FinanceiroModel.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}
$model = new FinanceiroModel($conn);
$movs = $model->getMovimentacoes(['id' => $id]);
if ($movs && count($movs) > 0) {
    echo json_encode(['success' => true, 'data' => $movs[0]]);
} else {
    echo json_encode(['success' => false, 'message' => 'Movimentação não encontrada']);
} 