<?php

session_start();

include_once "conexao.php";
require_once "config.php";

// Initialize configuration
$config = Config::getInstance();

try {
    // Validação dos dados recebidos
    $required_fields = ['pedido', 'preco_venda', 'quantidade', 'id_mesa'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Campo obrigatório não preenchido: {$field}");
        }
    }

    // Sanitização dos dados
    $pedido = htmlspecialchars($_POST['pedido']);
    $preco_venda = filter_var($_POST['preco_venda'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $quantidade = filter_var($_POST['quantidade'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $id_mesa = filter_var($_POST['id_mesa'], FILTER_SANITIZE_NUMBER_INT);
    $cliente = isset($_POST['cliente']) ? htmlspecialchars($_POST['cliente']) : '';
    $observacoes = isset($_POST['observacoes']) ? htmlspecialchars($_POST['observacoes']) : '';

    // Calcula o valor total
    $valor_total = $preco_venda * $quantidade;

    // Prepara e executa a inserção
    $stmt = $mysqli->prepare("INSERT INTO pedido (id_mesa, cliente, pedido, preco_venda, quantidade, valor_total, observacoes, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Em Preparo')");
    
    if (!$stmt) {
        throw new Exception("Erro ao preparar a consulta: " . $mysqli->error);
    }

    $stmt->bind_param("issddds", $id_mesa, $cliente, $pedido, $preco_venda, $quantidade, $valor_total, $observacoes);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao executar a consulta: " . $stmt->error);
    }

    $stmt->close();
    
    // Atualiza o status da mesa para ocupada
    $stmt_mesa = $mysqli->prepare("UPDATE mesas SET status = 'Ocupada' WHERE num_mesa = ?");
    
    if (!$stmt_mesa) {
        throw new Exception("Erro ao preparar a atualização da mesa: " . $mysqli->error);
    }

    $stmt_mesa->bind_param("i", $id_mesa);
    
    if (!$stmt_mesa->execute()) {
        throw new Exception("Erro ao atualizar o status da mesa: " . $stmt_mesa->error);
    }

    $stmt_mesa->close();
    $mysqli->close();

    $_SESSION['msg'] = "Pedido adicionado com sucesso!";
    header("Location: " . Config::url("?view=novo_pedido&mesa=" . $id_mesa . "&cliente=" . urlencode($cliente)));
    exit();

} catch (Exception $e) {
    // Log do erro
    error_log("Erro ao adicionar pedido: " . $e->getMessage());
    
    // Mensagem amigável para o usuário
    $_SESSION['msg_erro'] = "Não foi possível adicionar o pedido. Por favor, tente novamente.";
    
    // Redireciona de volta mantendo os dados da mesa e cliente
    header("Location: " . Config::url("?view=novo_pedido&mesa=" . $_POST['id_mesa'] . "&cliente=" . urlencode($_POST['cliente'] ?? '')));
    exit();
}

?>