<?php
session_start();

include_once 'conexao.php';

$idpedido = mysqli_real_escape_string($conn, $_POST['idpedido']);

$exclude_table = "DELETE FROM pedido WHERE idpedido = '$idpedido'";	
$produto_excluido = mysqli_query($conn, $exclude_table);

if(mysqli_affected_rows($conn) > 0){
    $_SESSION['msg'] = "<div class='alert alert-success'>Pedido excluído com sucesso!</div>";
    echo json_encode(['success' => true]);
} else {
    $_SESSION['msg'] = "<div class='alert alert-danger'>Erro ao excluir Pedido!</div>";
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir pedido']);
}

$conn->close();
?>