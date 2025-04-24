<?php
header('Content-Type: application/json');

// Define o caminho raiz
define('ROOT_PATH', dirname(dirname(dirname(__FILE__))));

// Habilita exibição de erros para debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inclui o arquivo de configuração usando caminho absoluto
require_once ROOT_PATH . '/MVC/MODEL/config.php';
require_once ROOT_PATH . '/MVC/MODEL/conexao.php';

// Get database configuration
$config = Config::getInstance();
$dbConfig = $config->get('db');

// Enable error logging
error_log("Iniciando atualização de pedido");

// Verificar se é uma requisição para atualizar tipo/observação
if (isset($_POST['tipo']) && isset($_POST['observacao']) && isset($_POST['pedido_id'])) {
    try {
        // Use existing connection from conexao.php
        global $conn;
        
        if (!$conn || !mysqli_ping($conn)) {
            // If connection is not available, create a new one
            $conn = mysqli_connect(
                $dbConfig['host'],
                $dbConfig['user'],
                $dbConfig['pass'],
                $dbConfig['name']
            );
        }
        
        if (!$conn) {
            throw new Exception("Database connection failed: " . mysqli_connect_error());
        }
        error_log("Conexão com banco de dados estabelecida");

        // Log dos dados recebidos
        error_log("Dados recebidos para atualização de delivery:");
        error_log("tipo: " . $_POST['tipo']);
        error_log("observacao: " . $_POST['observacao']);
        error_log("pedido_id: " . $_POST['pedido_id']);

        // Atualizar tipo e observação do pedido
        $stmt = mysqli_prepare($conn, "UPDATE pedido SET tipo = ?, observacao = ? WHERE idpedido = ?");
        if (!$stmt) {
            throw new Exception("Erro ao preparar query: " . mysqli_error($conn));
        }

        $tipo = $_POST['tipo'];
        $observacao = $_POST['observacao'];
        $pedido_id = $_POST['pedido_id'];

        mysqli_stmt_bind_param($stmt, "ssi", $tipo, $observacao, $pedido_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Erro ao atualizar pedido: " . mysqli_stmt_error($stmt));
        }

        // Se for delivery, atualizar status da mesa para livre
        if ($tipo === 'delivery') {
            $stmt = mysqli_prepare($conn, "UPDATE mesas m 
                                         JOIN pedido p ON m.id_mesa = p.idmesa 
                                         SET m.status = 1 
                                         WHERE p.idpedido = ?");
            if (!$stmt) {
                throw new Exception("Erro ao preparar query de atualização da mesa: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "i", $pedido_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Erro ao atualizar status da mesa: " . mysqli_stmt_error($stmt));
            }
        }

        echo json_encode(['success' => true]);
        exit;
    } catch (Exception $e) {
        error_log("Erro ao atualizar pedido: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

// Se chegou aqui, é uma requisição para atualizar status
// Receive data via POST in JSON format
$input = file_get_contents('php://input');
error_log("Dados recebidos para atualização de status: " . $input);

$data = json_decode($input, true);
error_log("Dados decodificados: " . print_r($data, true));

// Validate required data
if (!isset($data['idpedido']) || !isset($data['status'])) {
    error_log("Dados obrigatórios faltando para atualização de status");
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Validate status
$statusPermitidos = ['Pendente', 'Em Preparo', 'Pronto', 'Finalizado', 'Cancelado'];
if (!in_array($data['status'], $statusPermitidos)) {
    error_log("Status inválido: " . $data['status']);
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status value']);
    exit;
}

try {
    // Use existing connection from conexao.php
    global $conn;
    
    if (!$conn || !mysqli_ping($conn)) {
        // If connection is not available, create a new one
        $conn = mysqli_connect(
            $dbConfig['host'],
            $dbConfig['user'],
            $dbConfig['pass'],
            $dbConfig['name']
        );
    }
    
    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }
    error_log("Conexão com banco de dados estabelecida");

    // Check if order exists
    $stmt = mysqli_prepare($conn, "SELECT status FROM pedido WHERE idpedido = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $data['idpedido']);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    error_log("Consulta de pedido executada");

    if (mysqli_num_rows($result) === 0) {
        throw new Exception("Order not found: " . $data['idpedido']);
    }

    $row = mysqli_fetch_assoc($result);
    $statusAnterior = $row['status'];
    error_log("Status anterior: " . $statusAnterior);

    // Start transaction
    mysqli_begin_transaction($conn);
    error_log("Transação iniciada");

    // Get user from session or set as 'Sistema'
    $usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Sistema';

    // Log status change
    $stmtLog = mysqli_prepare($conn, "INSERT INTO log_pedidos (idpedido, status_anterior, novo_status, usuario, data_alteracao) VALUES (?, ?, ?, ?, NOW())");
    if (!$stmtLog) {
        throw new Exception("Prepare log failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmtLog, "isss", $data['idpedido'], $statusAnterior, $data['status'], $usuario);
    if (!mysqli_stmt_execute($stmtLog)) {
        throw new Exception("Execute log failed: " . mysqli_stmt_error($stmtLog));
    }
    error_log("Log registrado");

    // Update order status
    $stmt = mysqli_prepare($conn, "UPDATE pedido SET status = ? WHERE idpedido = ?");
    if (!$stmt) {
        throw new Exception("Prepare update failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "si", $data['status'], $data['idpedido']);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Execute update failed: " . mysqli_stmt_error($stmt));
    }
    error_log("Status atualizado");

    // If status is "Finalizado", release the table
    if ($data['status'] === 'Finalizado') {
        $stmtTable = mysqli_prepare($conn, "UPDATE mesa SET status = 'Livre' WHERE idmesa = (SELECT idmesa FROM pedido WHERE idpedido = ?)");
        if (!$stmtTable) {
            throw new Exception("Prepare table update failed: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmtTable, "i", $data['idpedido']);
        if (!mysqli_stmt_execute($stmtTable)) {
            throw new Exception("Execute table update failed: " . mysqli_stmt_error($stmtTable));
        }
        error_log("Mesa liberada");
    }

    // Update order items if provided
    if (isset($data['itens']) && is_array($data['itens'])) {
        // First, delete existing items
        $stmtDelete = mysqli_prepare($conn, "DELETE FROM pedido_produto WHERE idpedido = ?");
        mysqli_stmt_bind_param($stmtDelete, "i", $data['idpedido']);
        mysqli_stmt_execute($stmtDelete);

        // Then insert new items
        $stmtInsert = mysqli_prepare($conn, "INSERT INTO pedido_produto (idpedido, idproduto, quantidade, valor_unitario, valor_total, ingredientes_sem, ingredientes_com, observacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($data['itens'] as $item) {
            $valorTotal = $item['quantidade'] * $item['valor_unitario'];
            $ingredientesSem = isset($item['ingredientes_sem']) ? implode(',', $item['ingredientes_sem']) : '';
            $ingredientesCom = isset($item['ingredientes_com']) ? implode(',', $item['ingredientes_com']) : '';
            $observacoes = isset($item['observacao']) ? $item['observacao'] : '';

            mysqli_stmt_bind_param($stmtInsert, "iidddsss", 
                $data['idpedido'],
                $item['idproduto'],
                $item['quantidade'],
                $item['valor_unitario'],
                $valorTotal,
                $ingredientesSem,
                $ingredientesCom,
                $observacoes
            );
            mysqli_stmt_execute($stmtInsert);
        }
    }

    // Commit transaction
    mysqli_commit($conn);
    error_log("Transação finalizada com sucesso");
    
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("Erro na atualização: " . $e->getMessage());
    if (isset($conn)) {
        mysqli_rollback($conn);
        error_log("Transação revertida");
    }
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update order: ' . $e->getMessage()]);
} finally {
    // Não fechamos a conexão aqui pois ela pode ser usada em outras partes do sistema
}
?> 