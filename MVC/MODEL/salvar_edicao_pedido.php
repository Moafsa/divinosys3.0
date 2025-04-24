<?php
// Garantir que a sessão está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado
if (!isset($_SESSION["login"]) || $_SESSION["login"] != 1) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autorizado']);
    exit;
}

// Incluir o arquivo de conexão
include_once(__DIR__ . "/../VIEWS/include_conexao.php");

// Verificar se os dados foram enviados
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    // Validar dados obrigatórios
    $required_fields = ['idpedido', 'quantidade', 'valor', 'status', 'idmesa'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || $_POST[$field] === '') {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        throw new Exception('Campos obrigatórios não preenchidos: ' . implode(', ', $missing_fields));
    }

    // Iniciar transação
    mysqli_begin_transaction($conn);

    // Sanitizar e validar dados
    $idpedido = filter_var($_POST['idpedido'], FILTER_SANITIZE_NUMBER_INT);
    $quantidade = filter_var($_POST['quantidade'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $valor = filter_var($_POST['valor'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $valor_total = filter_var($_POST['valor_total'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $observacao = filter_var($_POST['observacao'], FILTER_SANITIZE_STRING);
    $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);
    $idmesa = filter_var($_POST['idmesa'], FILTER_SANITIZE_NUMBER_INT);

    // Validações adicionais
    if ($quantidade <= 0) {
        throw new Exception('A quantidade deve ser maior que zero');
    }

    if ($valor <= 0) {
        throw new Exception('O valor deve ser maior que zero');
    }

    $status_validos = ['Pendente', 'Em Preparo', 'Pronto', 'Entregue', 'Cancelado'];
    if (!in_array($status, $status_validos)) {
        throw new Exception('Status inválido');
    }

    // Verificar se a mesa existe
    $stmt = mysqli_prepare($conn, "SELECT id_mesa FROM mesas WHERE id_mesa = ?");
    mysqli_stmt_bind_param($stmt, "i", $idmesa);
    mysqli_stmt_execute($stmt);
    if (!mysqli_stmt_fetch($stmt)) {
        throw new Exception('Mesa não encontrada');
    }
    
    // Atualizar pedido
    $stmt = mysqli_prepare($conn, "
        UPDATE pedido 
        SET quantidade = ?, 
            valor = ?, 
            valor_total = ?, 
            observacao = ?, 
            status = ?, 
            idmesa = ?,
            data_atualizacao = NOW()
        WHERE idpedido = ?
    ");
    
    mysqli_stmt_bind_param($stmt, "dddssii", 
        $quantidade, $valor, $valor_total, $observacao, $status, $idmesa, $idpedido
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao atualizar pedido: " . mysqli_stmt_error($stmt));
    }

    // Processar ingredientes
    if (isset($_POST['ingredientes_info'])) {
        $ingredientes_info = json_decode($_POST['ingredientes_info'], true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Erro ao processar informações dos ingredientes');
        }

        // Limpar ingredientes existentes
        $stmt = mysqli_prepare($conn, "DELETE FROM pedido_ingredientes WHERE pedido_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $idpedido);
        mysqli_stmt_execute($stmt);

        // Inserir novos ingredientes
        if (!empty($ingredientes_info)) {
            $stmt = mysqli_prepare($conn, "
                INSERT INTO pedido_ingredientes (pedido_id, ingrediente_id, quantidade, removido) 
                VALUES (?, ?, ?, ?)
            ");

            foreach ($ingredientes_info as $ingrediente_id => $info) {
                $removido = !$info['selecionado'];
                $quantidade_ing = $info['quantidade'];
                
                mysqli_stmt_bind_param($stmt, "iiis", 
                    $idpedido, $ingrediente_id, $quantidade_ing, $removido
                );
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Erro ao atualizar ingrediente: " . mysqli_stmt_error($stmt));
                }
            }
        }
    }

    // Registrar log de alteração
    $usuario = $_SESSION['usuario'] ?? 'Sistema';
    $stmt = mysqli_prepare($conn, "
        INSERT INTO log_pedidos (pedido_id, usuario, acao, detalhes, data_hora)
        VALUES (?, ?, 'EDICAO', ?, NOW())
    ");
    
    $detalhes = json_encode([
        'quantidade' => $quantidade,
        'valor' => $valor,
        'status' => $status,
        'observacao' => $observacao
    ]);
    
    mysqli_stmt_bind_param($stmt, "iss", $idpedido, $usuario, $detalhes);
    mysqli_stmt_execute($stmt);

    // Commit da transação
    mysqli_commit($conn);
    
    echo json_encode([
        'success' => true,
        'message' => 'Pedido atualizado com sucesso'
    ]);

} catch (Exception $e) {
    // Rollback em caso de erro
    mysqli_rollback($conn);
    
    error_log("Erro ao editar pedido #$idpedido: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao salvar pedido: ' . $e->getMessage()
    ]);
}

mysqli_close($conn); 