<?php
	
	session_start();

	include_once 'conexao.php';

	$id = mysqli_real_escape_string($conn, $_POST['id']);

	$idpedido = mysqli_real_escape_string($conn, $_POST['idpedido']);

	$quantidade = mysqli_real_escape_string($conn, $_POST['quantidade']);

	

	$obs = mysqli_real_escape_string($conn, $_POST['obs']);

	$insert_table = "UPDATE pedido SET 
	quantidade = '$quantidade',
	observacao = '$obs'  WHERE 
	idpedido = '$idpedido'";

	$edita_pedido = mysqli_query($conn, $insert_table);

	$conn->close();

	if(mysqli_affected_rows($conn)!=-1){

		echo "<META HTTP-EQUIV=REFRESH CONTENT = '0;URL=/?view=mesas'>";
		$_SESSION['msg'] = "<div class='alert alert-success'>Pedido editado com sucesso!</div>";
	}else{

		echo "<META HTTP-EQUIV=REFRESH CONTENT = '0;URL=/?view=mesas'>";	
		$_SESSION['msg'] = "<div class='alert alert-danger'>Erro ao editar pedido!</div>";
	}

	?>
