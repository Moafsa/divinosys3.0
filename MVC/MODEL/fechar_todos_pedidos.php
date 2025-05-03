<?php
require_once __DIR__ . '/../MODEL/conexao.php';
header('Content-Type: application/json');

$id_mesa = isset($_POST['id_mesa']) ? (int)$_POST['id_mesa'] : 0;
if (!$id_mesa) {
    echo json_encode(['success' => false, 'message' => 'Mesa inválida']);
    exit;
}

try {
    mysqli_begin_transaction($conn);
    $sql = "UPDATE pedido SET status = 'Finalizado' WHERE idmesa = ? AND status NOT IN ('Finalizado', 'Cancelado')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id_mesa);
    mysqli_stmt_execute($stmt);
    mysqli_commit($conn);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 