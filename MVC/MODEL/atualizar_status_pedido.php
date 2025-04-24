<?php
session_start();
header('Content-Type: application/json');

// Incluir arquivo de configuração
require_once __DIR__ . '/config.php';

// Incluir o arquivo de conexão
require_once __DIR__ . '/conexao.php';

try {
    // Validar dados recebidos
    $pedido_id = isset($_POST['pedido_id']) ? intval($_POST['pedido_id']) : null;
    $novo_status = isset($_POST['status']) ? $_POST['status'] : null;
    $usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Sistema';

    // Debug log
    error_log("Recebido pedido_id: " . print_r($pedido_id, true));
    error_log("Recebido novo_status: " . print_r($novo_status, true));
    error_log("Usuário: " . print_r($usuario, true));

    if (!$pedido_id || !$novo_status) {
        error_log("Dados incompletos - pedido_id: $pedido_id, novo_status: $novo_status");
        throw new Exception("Dados incompletos");
    }

    // Validar status
    $status_validos = [
        'Pendente',
        'Em Preparo',
        'Pronto',
        'Saiu para Entrega',
        'Entregue',
        'Entregue (Mesa)',
        'Entregue (Delivery)',
        'Cancelado',
        'Finalizado'
    ];
    
    if (!in_array($novo_status, $status_validos)) {
        throw new Exception('Status inválido');
    }

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Get current status before update
        $stmt = mysqli_prepare($conn, "SELECT status FROM pedido WHERE idpedido = ?");
        if (!$stmt) {
            error_log("Erro ao preparar query para obter status atual: " . mysqli_error($conn));
            throw new Exception("Erro ao preparar query para obter status atual");
        }
        mysqli_stmt_bind_param($stmt, "i", $pedido_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Erro ao executar query para obter status atual: " . mysqli_stmt_error($stmt));
            throw new Exception("Error getting current status: " . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_bind_result($stmt, $status_anterior);
        if (!mysqli_stmt_fetch($stmt)) {
            error_log("Pedido não encontrado: $pedido_id");
            throw new Exception("Pedido não encontrado");
        }
        mysqli_stmt_close($stmt);

        error_log("Status anterior: $status_anterior");

        // Update order status
        $stmt = mysqli_prepare($conn, "UPDATE pedido SET status = ? WHERE idpedido = ?");
        if (!$stmt) {
            error_log("Erro ao preparar query de atualização: " . mysqli_error($conn));
            throw new Exception("Erro ao preparar query de atualização");
        }
        mysqli_stmt_bind_param($stmt, "si", $novo_status, $pedido_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Erro ao executar atualização: " . mysqli_stmt_error($stmt));
            throw new Exception("Error updating status: " . mysqli_stmt_error($stmt));
        }
        
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        if ($affected_rows === 0) {
            error_log("Nenhuma linha atualizada para o pedido: $pedido_id");
            throw new Exception("Nenhuma linha atualizada");
        }
        mysqli_stmt_close($stmt);

        error_log("Status atualizado com sucesso. Linhas afetadas: $affected_rows");

        // Log the status change
        $stmt = mysqli_prepare($conn, "INSERT INTO log_pedidos (idpedido, status_anterior, novo_status, usuario, data_alteracao) VALUES (?, ?, ?, ?, NOW())");
        if (!$stmt) {
            error_log("Erro ao preparar query de log: " . mysqli_error($conn));
            throw new Exception("Erro ao preparar query de log");
        }
        mysqli_stmt_bind_param($stmt, "isss", $pedido_id, $status_anterior, $novo_status, $usuario);
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Erro ao inserir log: " . mysqli_stmt_error($stmt));
            throw new Exception("Error logging status change: " . mysqli_stmt_error($stmt));
        }

        $log_id = mysqli_insert_id($conn);
        error_log("Log inserido com sucesso. ID: $log_id");

        mysqli_stmt_close($stmt);

        // Se o pedido foi finalizado, atualizar status da mesa para livre
        if ($novo_status === 'Finalizado' || $novo_status === 'Entregue' || $novo_status === 'Entregue (Mesa)' || $novo_status === 'Entregue (Delivery)') {
            error_log("Atualizando status da mesa - Pedido: $pedido_id");
            
            // Define o status da mesa baseado no novo status do pedido
            $mesa_status = ($novo_status === 'Entregue' || $novo_status === 'Entregue (Mesa)' || $novo_status === 'Entregue (Delivery)') ? 2 : 1;
            
            $stmt = mysqli_prepare($conn, "
                UPDATE mesas m
                JOIN pedido p ON m.id_mesa = p.idmesa
                SET m.status = ?
                WHERE p.idpedido = ? AND p.idmesa IS NOT NULL
            ");

            if (!$stmt) {
                error_log("Erro ao preparar query de atualização da mesa: " . mysqli_error($conn));
                throw new Exception("Erro ao preparar query de atualização da mesa");
            }

            mysqli_stmt_bind_param($stmt, "ii", $mesa_status, $pedido_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                error_log("Erro ao atualizar status da mesa: " . mysqli_stmt_error($stmt));
                throw new Exception("Erro ao atualizar status da mesa: " . mysqli_stmt_error($stmt));
            }

            $affected_tables = mysqli_stmt_affected_rows($stmt);
            error_log("Mesas atualizadas: $affected_tables");
            mysqli_stmt_close($stmt);
        }

        // Commit transaction
        mysqli_commit($conn);

        echo json_encode([
            'success' => true,
            'message' => 'Status atualizado com sucesso',
            'novo_status' => $novo_status
        ]);

    } catch (Exception $e) {
        // Rollback on error
        mysqli_rollback($conn);
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?> 