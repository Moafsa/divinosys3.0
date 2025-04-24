<?php
$idpedido = $_POST['idpedido'];

include_once "conexao.php";


	$exclude_table = "DELETE FROM pedido WHERE idpedido = '$idpedido'";	
	$pedido_excluido = mysqli_query($conn, $exclude_table);

	header("Location: " . url('?view=pedidos_delivery'));
?>