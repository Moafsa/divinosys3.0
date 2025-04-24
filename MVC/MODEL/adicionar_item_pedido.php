<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION["login"]) || $_SESSION["login"] != 1) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autorizado']);
    exit;
}

include_once "conexao.php";

try {
    // Validar dados necessários
    $required_fields = ['novo_produto_id', 'nova_quantidade', 'novo_valor', 'idmesa'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Campo obrigatório não fornecido: {$field}");
        }
    }

    // Sanitizar dados
    $produto_id = filter_var($_POST['novo_produto_id'], FILTER_SANITIZE_NUMBER_INT);
    $quantidade = filter_var($_POST['nova_quantidade'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $valor_unitario = filter_var($_POST['novo_valor'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $idmesa = filter_var($_POST['idmesa'], FILTER_SANITIZE_NUMBER_INT);
    $observacao = isset($_POST['nova_observacao']) ? htmlspecialchars($_POST['nova_observacao']) : '';
    
    // Calcular valor total
    $valor_total = $quantidade * $valor_unitario;

    // Iniciar transação
    mysqli_begin_transaction($conn);

    try {
        // Buscar nome do produto
        $stmt = mysqli_prepare($conn, "SELECT nome FROM produtos WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $produto_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $produto = mysqli_fetch_assoc($result);
        
        if (!$produto) {
            throw new Exception("Produto não encontrado");
        }

        // Inserir novo pedido
        $query = "INSERT INTO pedido (produto, quantidade, valor, valor_total, status, observacao, idmesa, usuario) 
                 VALUES (?, ?, ?, ?, 'Pendente', ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            throw new Exception("Erro ao preparar query de inserção: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "sdddssi", 
            $produto['nome'],
            $quantidade,
            $valor_unitario,
            $valor_total,
            $observacao,
            $idmesa,
            $_SESSION["usuario"]
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Erro ao inserir pedido: " . mysqli_stmt_error($stmt));
        }

        $idpedido = mysqli_insert_id($conn);

        // Inserir ingredientes removidos
        if (isset($_POST['ingredientes_removidos'])) {
            $ingredientes_removidos = json_decode($_POST['ingredientes_removidos']);
            if ($ingredientes_removidos) {
                $stmt = mysqli_prepare($conn, "INSERT INTO pedido_ingredientes_removidos (idpedido, ingrediente_id) VALUES (?, ?)");
                foreach ($ingredientes_removidos as $ingrediente_id) {
                    mysqli_stmt_bind_param($stmt, "ii", $idpedido, $ingrediente_id);
                    mysqli_stmt_execute($stmt);
                }
            }
        }

        // Registrar no log
        $query_log = "INSERT INTO log_pedidos (idpedido, status_anterior, status_novo, data_alteracao, usuario) 
                     VALUES (?, NULL, 'Pendente', NOW(), ?)";
        
        $stmt = mysqli_prepare($conn, $query_log);
        mysqli_stmt_bind_param($stmt, "is", $idpedido, $_SESSION["usuario"]);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Erro ao registrar log: " . mysqli_stmt_error($stmt));
        }

        // Commit da transação
        mysqli_commit($conn);

        echo json_encode([
            'success' => true,
            'message' => 'Item adicionado com sucesso',
            'idpedido' => $idpedido
        ]);

    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }

} catch (Exception $e) {
    error_log("Erro ao adicionar item ao pedido: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn); 