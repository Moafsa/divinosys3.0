<?php
	session_start();
	
	include_once 'conexao.php';
require_once 'config.php';

// Initialize configuration
$config = Config::getInstance();

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
	
	// Start transaction
	mysqli_begin_transaction($conn);
	
	// Prepare data for product update
	$id = mysqli_real_escape_string($conn, $_POST['id']);
	$codigo = mysqli_real_escape_string($conn, $_POST['codigo']);
	$nome = mysqli_real_escape_string($conn, $_POST['nome']);
	$descricao = mysqli_real_escape_string($conn, $_POST['detalhes']);
	$preco_normal = str_replace(',', '.', mysqli_real_escape_string($conn, $_POST['preco_venda']));
	$preco_mini = isset($_POST['preco_mini']) ? str_replace(',', '.', mysqli_real_escape_string($conn, $_POST['preco_mini'])) : null;
	
	// Update product
	$stmt = mysqli_prepare($conn, "UPDATE produtos SET 
		codigo = ?,
		categoria_id = ?,
		nome = ?,
		descricao = ?,
		preco_normal = ?,
		preco_mini = ?
		WHERE id = ?");
	
	mysqli_stmt_bind_param($stmt, 'sissddi', 
		$codigo,
		$categoria_id,
		$nome,
		$descricao,
		$preco_normal,
		$preco_mini,
		$id
	);

	if (!mysqli_stmt_execute($stmt)) {
		throw new Exception("Erro ao atualizar produto: " . mysqli_stmt_error($stmt));
	}
	mysqli_stmt_close($stmt);

	// Prepare data for stock update
	$estoque_atual = !empty($_POST['estoque_atual']) ? str_replace(',', '.', mysqli_real_escape_string($conn, $_POST['estoque_atual'])) : 0;
	$estoque_minimo = !empty($_POST['estoque_minimo']) ? str_replace(',', '.', mysqli_real_escape_string($conn, $_POST['estoque_minimo'])) : 0;
	$preco_custo = !empty($_POST['preco_custo']) ? str_replace(',', '.', mysqli_real_escape_string($conn, $_POST['preco_custo'])) : null;
	$marca = !empty($_POST['marca']) ? mysqli_real_escape_string($conn, $_POST['marca']) : null;
	$fornecedor = !empty($_POST['fornecedor']) ? mysqli_real_escape_string($conn, $_POST['fornecedor']) : null;
	$data_compra = !empty($_POST['data_compra']) ? date('Y-m-d', strtotime(str_replace('/', '-', $_POST['data_compra']))) : null;
	$data_validade = !empty($_POST['data_validade']) ? date('Y-m-d', strtotime(str_replace('/', '-', $_POST['data_validade']))) : null;
	$unidade = !empty($_POST['unidade']) ? mysqli_real_escape_string($conn, $_POST['unidade']) : null;
	$observacoes = !empty($_POST['observacoes']) ? mysqli_real_escape_string($conn, $_POST['observacoes']) : null;

	// Check if stock record exists
	$stmt = mysqli_prepare($conn, "SELECT id FROM estoque WHERE produto_id = ?");
	mysqli_stmt_bind_param($stmt, 'i', $id);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	$estoque_exists = mysqli_fetch_assoc($result);
	mysqli_stmt_close($stmt);

	if ($estoque_exists) {
		// Update existing stock record
		$stmt = mysqli_prepare($conn, "UPDATE estoque SET 
			estoque_atual = ?,
			estoque_minimo = ?,
			preco_custo = ?,
			marca = ?,
			fornecedor = ?,
			data_compra = ?,
			data_validade = ?,
			unidade = ?,
			observacoes = ?
			WHERE produto_id = ?");
		
		mysqli_stmt_bind_param($stmt, 'ddsssssssi', 
			$estoque_atual,
			$estoque_minimo,
			$preco_custo,
			$marca,
			$fornecedor,
			$data_compra,
			$data_validade,
			$unidade,
			$observacoes,
			$id
		);
	} else {
		// Insert new stock record
		$stmt = mysqli_prepare($conn, "INSERT INTO estoque (
			produto_id,
			estoque_atual,
			estoque_minimo,
			preco_custo,
			marca,
			fornecedor,
			data_compra,
			data_validade,
			unidade,
			observacoes
		) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		
		mysqli_stmt_bind_param($stmt, 'iddssssssss', 
			$id,
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
	}

	if (!mysqli_stmt_execute($stmt)) {
		throw new Exception("Erro ao atualizar informações de estoque: " . mysqli_stmt_error($stmt));
	}
	mysqli_stmt_close($stmt);

	// Commit transaction
	mysqli_commit($conn);

	$_SESSION['msg'] = "<div class='alert alert-success' role='alert'>Produto atualizado com sucesso!<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";

} catch (Exception $e) {
	// Rollback transaction
	mysqli_rollback($conn);
	
	error_log("Erro ao atualizar produto: " . $e->getMessage());
	$_SESSION['msg'] = "<div class='alert alert-danger' role='alert'>Erro ao atualizar produto: " . htmlspecialchars($e->getMessage()) . "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
} finally {
	mysqli_close($conn);
	header("Location: " . $config->url('?view=tabela'));
	exit;
}