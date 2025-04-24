<?php
header('Content-Type: application/json');

require_once '../MODEL/config.php';
require_once '../MODEL/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idpedido = isset($_POST['idpedido']) ? intval($_POST['idpedido']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    
    if ($idpedido > 0 && !empty($status)) {
        $query = "UPDATE pedido SET status = ? WHERE idpedido = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $status, $idpedido);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => false, 'error' => 'Parâmetros inválidos']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
}
?> 