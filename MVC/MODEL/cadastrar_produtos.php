<?php
	
	session_start();

	include_once 'conexao.php';
	require_once 'config.php';

	// Initialize configuration
	$config = Config::getInstance();

	//recebe o valor que vem da tag [<input name="nome" type="text" class="form-control" id="recipient-name">]
	//recebe o valor que vem da tag [<textarea name="detalhes" class="form-control" id="detalhes-text"></textarea>]
	//recebe o valor que vem da tag invisivel [<input name="id" type="hidden" id="id_Produto">]
	
	try {
		// Get category ID from name
		$categoria = mysqli_real_escape_string($conn, strtoupper($_POST['categoria']));
		$stmt = mysqli_prepare($conn, "SELECT id FROM categorias WHERE nome = ?");
		mysqli_stmt_bind_param($stmt, 's', $categoria);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		$categoria_row = mysqli_fetch_assoc($result);
		
		if (!$categoria_row) {
			throw new Exception("Categoria não encontrada");
		}
		
		$categoria_id = $categoria_row['id'];
		
		// Prepare data for product insertion
		$codigo = mysqli_real_escape_string($conn, $_POST['codigo']);
		$nome = mysqli_real_escape_string($conn, $_POST['nome']);
		$descricao = mysqli_real_escape_string($conn, $_POST['detalhes']);
		$preco_normal = str_replace(',', '.', mysqli_real_escape_string($conn, $_POST['preco_venda']));
		$preco_mini = isset($_POST['preco_mini']) ? str_replace(',', '.', mysqli_real_escape_string($conn, $_POST['preco_mini'])) : null;
		
		// Start transaction
		mysqli_begin_transaction($conn);
		
		// Insert product
		$stmt = mysqli_prepare($conn, "INSERT INTO produtos (codigo, categoria_id, nome, descricao, preco_normal, preco_mini) VALUES (?, ?, ?, ?, ?, ?)");
		
		mysqli_stmt_bind_param($stmt, 'sissdd', 
			$codigo,
			$categoria_id,
			$nome,
			$descricao,
			$preco_normal,
			$preco_mini
		);

		if (!mysqli_stmt_execute($stmt)) {
			throw new Exception("Erro ao cadastrar produto: " . mysqli_stmt_error($stmt));
		}
		
		$produto_id = mysqli_insert_id($conn);
		mysqli_stmt_close($stmt);

		// Prepare data for stock insertion
		$estoque_atual = !empty($_POST['estoque_atual']) ? str_replace(',', '.', mysqli_real_escape_string($conn, $_POST['estoque_atual'])) : 0;
		$estoque_minimo = !empty($_POST['estoque_minimo']) ? str_replace(',', '.', mysqli_real_escape_string($conn, $_POST['estoque_minimo'])) : 0;
		$preco_custo = !empty($_POST['preco_custo']) ? str_replace(',', '.', mysqli_real_escape_string($conn, $_POST['preco_custo'])) : null;
		$marca = !empty($_POST['marca']) ? mysqli_real_escape_string($conn, $_POST['marca']) : null;
		$fornecedor = !empty($_POST['fornecedor']) ? mysqli_real_escape_string($conn, $_POST['fornecedor']) : null;
		$data_compra = !empty($_POST['data_compra']) ? date('Y-m-d', strtotime(str_replace('/', '-', $_POST['data_compra']))) : null;
		$data_validade = !empty($_POST['data_validade']) ? date('Y-m-d', strtotime(str_replace('/', '-', $_POST['data_validade']))) : null;
		$unidade = !empty($_POST['unidade']) ? mysqli_real_escape_string($conn, $_POST['unidade']) : null;
		$observacoes = !empty($_POST['observacoes']) ? mysqli_real_escape_string($conn, $_POST['observacoes']) : null;

		// Insert stock information
		$stmt = mysqli_prepare($conn, "INSERT INTO estoque (produto_id, estoque_atual, estoque_minimo, preco_custo, marca, fornecedor, data_compra, data_validade, unidade, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		
		mysqli_stmt_bind_param($stmt, 'idddsssssss', 
			$produto_id,
			$estoque_atual,
			$estoque_minimo,
			$preco_custo,
			$marca,
			$fornecedor,
			$data_compra,
			$data_validade,
			$unidade,
			$observacoes
		);

		if (!mysqli_stmt_execute($stmt)) {
			throw new Exception("Erro ao cadastrar informações de estoque: " . mysqli_stmt_error($stmt));
		}

		mysqli_stmt_close($stmt);
		
		// Commit transaction
		mysqli_commit($conn);

		$_SESSION['msg'] = "<div class='alert alert-success' role='alert'>Produto cadastrado com sucesso!<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";

	} catch (Exception $e) {
		// Rollback transaction
		mysqli_rollback($conn);
		
		error_log("Erro ao cadastrar produto: " . $e->getMessage());
		$_SESSION['msg'] = "<div class='alert alert-danger' role='alert'>Erro ao cadastrar produto: " . htmlspecialchars($e->getMessage()) . "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
	} finally {
		mysqli_close($conn);
		header("Location: " . $config->url('?view=tabela'));
		exit;
	}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<title></title>
</head>
<body>
	<?php

	if(mysqli_affected_rows($conn)!=-1){

		echo "<META HTTP-EQUIV=REFRESH CONTENT = '0;URL=http://localhost/Pdv/?view=tabela'>";
		$_SESSION['msg'] = "<div class='alert alert-success' role='alert'>O Produto foi Cadastrado com Sucesso<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
	}else{

		echo "<META HTTP-EQUIV=REFRESH CONTENT = '0;URL=http://localhost/Pdv/?view=tabela'>";	
		$_SESSION['msg'] = "<div class='alert alert-danger' role='alert'>Erro ao cadastrar Produto <button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
	}?>

</body>
</html>

<?php $conn->close(); ?>