<?php
// Garantir que a sessão está iniciada
if (session_status() === PHP_SESSION_NONE) {
session_start();
}

// Prevenir qualquer saída antes dos headers
if (ob_get_level()) ob_end_clean();
ob_start();

// Desabilitar exibição de erros
define('DISPLAY_ERRORS', false);
error_reporting(0);

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Definir constante do caminho base
define('BASE_PATH', dirname(dirname(dirname(__FILE__))));

// Garantir que não há saída anterior
while (ob_get_level()) {
    ob_end_clean();
}

try {
    require_once BASE_PATH . "/MVC/MODEL/conexao.php";
    
    // Verificar método da requisição
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    // Obter e validar dados do pedido
    $json = file_get_contents('php://input');
    if (!$json) {
        throw new Exception('Dados do pedido não fornecidos');
    }

    $data = json_decode($json, true);
    if (!$data) {
        throw new Exception('Erro ao decodificar JSON: ' . json_last_error_msg());
    }

    // Log para debug
    error_log("Dados recebidos: " . print_r($data, true));

    if (!isset($data['mesa_id']) || !isset($data['items']) || !is_array($data['items'])) {
        throw new Exception('Dados do pedido inválidos');
    }

    $mesa_id = intval($data['mesa_id']);
    $items = $data['items'];

    if (empty($items)) {
        throw new Exception('Nenhum item no pedido');
    }

    // Validar mesa
    $stmt_mesa = mysqli_prepare($conn, "SELECT id_mesa FROM mesas WHERE id_mesa = ?");
    if (!$stmt_mesa) {
        throw new Exception("Erro ao preparar consulta da mesa: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt_mesa, 'i', $mesa_id);
    
    if (!mysqli_stmt_execute($stmt_mesa)) {
        throw new Exception("Erro ao executar consulta da mesa: " . mysqli_stmt_error($stmt_mesa));
    }
    
    mysqli_stmt_store_result($stmt_mesa);
    
    if (mysqli_stmt_num_rows($stmt_mesa) === 0) {
        throw new Exception('Mesa não encontrada');
    }
    mysqli_stmt_close($stmt_mesa);

    // Iniciar transação
    mysqli_begin_transaction($conn);

    try {
        // Criar pedido principal
        $data_pedido = date('Y-m-d');
        $hora_pedido = date('H:i:s');
        $status = 'Pendente';
        
        $sql_pedido = "INSERT INTO pedido (idmesa, data, hora_pedido, status) VALUES (?, ?, ?, ?)";
        $stmt_pedido = mysqli_prepare($conn, $sql_pedido);
        if (!$stmt_pedido) {
            throw new Exception("Erro ao preparar inserção do pedido: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt_pedido, 'isss', $mesa_id, $data_pedido, $hora_pedido, $status);
        
        if (!mysqli_stmt_execute($stmt_pedido)) {
            throw new Exception("Erro ao inserir pedido: " . mysqli_stmt_error($stmt_pedido));
        }

        $pedido_id = mysqli_insert_id($conn);

        // Inserir itens do pedido
        $sql_item = "INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, valor_unitario, valor_total, observacao, tamanho) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_item = mysqli_prepare($conn, $sql_item);
        if (!$stmt_item) {
            throw new Exception("Erro ao preparar inserção de item: " . mysqli_error($conn));
        }

        $total_pedido = 0;

        foreach ($items as $item) {
            if (!isset($item['produto']) || !isset($item['produto']['id']) || !isset($item['quantidade'])) {
                throw new Exception("Dados do item inválidos: " . json_encode($item));
            }

            $produto_id = intval($item['produto']['id']);
            $quantidade = intval($item['quantidade']);
            $valor_unitario = floatval($item['produto']['preco']);
            $valor_total = $valor_unitario * $quantidade;
            $observacao = isset($item['observacao']) ? $item['observacao'] : '';
            $tamanho = isset($item['tamanho']) ? $item['tamanho'] : 'normal';
            
            // Validar dados
            if ($produto_id <= 0) throw new Exception("ID do produto inválido");
            if ($quantidade <= 0) throw new Exception("Quantidade inválida");
            if ($valor_unitario <= 0) throw new Exception("Preço inválido");
            
            error_log("Inserindo item: produto_id=$produto_id, quantidade=$quantidade, valor_unitario=$valor_unitario, valor_total=$valor_total, observacao=$observacao");
            
            mysqli_stmt_bind_param($stmt_item, 'iidddss', $pedido_id, $produto_id, $quantidade, $valor_unitario, $valor_total, $observacao, $tamanho);
            
            if (!mysqli_stmt_execute($stmt_item)) {
                throw new Exception("Erro ao inserir item do pedido: " . mysqli_stmt_error($stmt_item));
            }

            $total_pedido += $valor_total;
            $item_id = mysqli_insert_id($conn);

            // Inserir ingredientes do item se houver
            if (!empty($item['ingredientes'])) {
                error_log("=== Processando ingredientes do item ===");
                error_log("Item ID: " . $item_id);
                error_log("Ingredientes recebidos: " . json_encode($item['ingredientes']));
                
                $ingredientes_com = [];
                $ingredientes_sem = [];
                
                foreach ($item['ingredientes'] as $ingrediente) {
                    if (!isset($ingrediente['id']) || !isset($ingrediente['tipo'])) {
                        error_log("ERRO: Ingrediente inválido: " . json_encode($ingrediente));
                        continue;
                    }
                    
                    error_log("Processando ingrediente: " . json_encode($ingrediente));
                    
                    // Separar ingredientes por tipo
                    if ($ingrediente['tipo'] === 'com') {
                        $ingredientes_com[] = $ingrediente['id'];
                        error_log("Adicionado aos ingredientes COM: " . $ingrediente['id']);
                    } else if ($ingrediente['tipo'] === 'sem') {
                        $ingredientes_sem[] = $ingrediente['id'];
                        error_log("Adicionado aos ingredientes SEM: " . $ingrediente['id']);
                    }
                }
                
                error_log("Ingredientes COM finais: " . json_encode($ingredientes_com));
                error_log("Ingredientes SEM finais: " . json_encode($ingredientes_sem));
                error_log("=====================================");

                // Inserir ingredientes no banco de dados
                $sql_ingrediente = "INSERT INTO pedido_item_ingredientes (pedido_item_id, ingrediente_id, incluido) VALUES (?, ?, ?)";
                $stmt_ingrediente = mysqli_prepare($conn, $sql_ingrediente);
                if (!$stmt_ingrediente) {
                    error_log("ERRO: Falha ao preparar statement de ingredientes: " . mysqli_error($conn));
                    throw new Exception("Erro ao preparar inserção de ingrediente");
                }

                // Inserir ingredientes COM
                foreach ($ingredientes_com as $ingrediente_id) {
                    error_log("Inserindo ingrediente COM: item_id=$item_id, ingrediente_id=$ingrediente_id");
                    mysqli_stmt_bind_param($stmt_ingrediente, 'iii', $item_id, $ingrediente_id, $incluido_sim);
                    $incluido_sim = 1;
                    
                    if (!mysqli_stmt_execute($stmt_ingrediente)) {
                        error_log("ERRO: Falha ao inserir ingrediente COM: " . mysqli_stmt_error($stmt_ingrediente));
                        // Não lançar exceção para não quebrar todo o pedido por um ingrediente
                    }
                }

                // Inserir ingredientes SEM
                foreach ($ingredientes_sem as $ingrediente_id) {
                    error_log("Inserindo ingrediente SEM: item_id=$item_id, ingrediente_id=$ingrediente_id");
                    mysqli_stmt_bind_param($stmt_ingrediente, 'iii', $item_id, $ingrediente_id, $incluido_nao);
                    $incluido_nao = 0;
                    
                    if (!mysqli_stmt_execute($stmt_ingrediente)) {
                        error_log("ERRO: Falha ao inserir ingrediente SEM: " . mysqli_stmt_error($stmt_ingrediente));
                        // Não lançar exceção para não quebrar todo o pedido por um ingrediente
                    }
                }

                mysqli_stmt_close($stmt_ingrediente);
            }
        }

        // Atualizar total do pedido
        $sql_update = "UPDATE pedido SET valor_total = ? WHERE idpedido = ?";
        $stmt_update = mysqli_prepare($conn, $sql_update);
        if (!$stmt_update) {
            throw new Exception("Erro ao preparar atualização do pedido: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt_update, 'di', $total_pedido, $pedido_id);
        
        if (!mysqli_stmt_execute($stmt_update)) {
            throw new Exception("Erro ao atualizar total do pedido: " . mysqli_stmt_error($stmt_update));
        }

        // Atualizar status da mesa
        $sql_mesa = "UPDATE mesas SET status = 2 WHERE id_mesa = ?";
        $stmt_mesa = mysqli_prepare($conn, $sql_mesa);
        if (!$stmt_mesa) {
            throw new Exception("Erro ao preparar atualização da mesa: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt_mesa, 'i', $mesa_id);
        
        if (!mysqli_stmt_execute($stmt_mesa)) {
            throw new Exception("Erro ao atualizar status da mesa: " . mysqli_stmt_error($stmt_mesa));
        }

        // Commit da transação
        mysqli_commit($conn);

        // Limpar qualquer saída anterior e sessão do carrinho
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        if (isset($_SESSION['carrinho'][$mesa_id])) {
            unset($_SESSION['carrinho'][$mesa_id]);
        }

        // Retornar sucesso
        echo json_encode([
            'success' => true,
            'message' => 'Pedido finalizado com sucesso',
            'pedido_id' => $pedido_id,
            'total' => $total_pedido,
            'print_receipt' => isset($data['print_receipt']) ? $data['print_receipt'] : false
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        // Rollback em caso de erro
        if (isset($conn)) {
        mysqli_rollback($conn);
        }

        // Log detalhado do erro
        error_log("Erro ao finalizar pedido: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        error_log("Dados recebidos: " . (isset($json) ? $json : 'N/A'));

        // Garantir que não há saída anterior
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Enviar resposta de erro
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao finalizar pedido: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } finally {
        // Fechar statements
        if (isset($stmt_item)) mysqli_stmt_close($stmt_item);
        if (isset($stmt_pedido)) mysqli_stmt_close($stmt_pedido);
        if (isset($stmt_update)) mysqli_stmt_close($stmt_update);
        if (isset($stmt_mesa)) mysqli_stmt_close($stmt_mesa);
        
        // Fechar conexão
        if (isset($conn)) mysqli_close($conn);
    }

} catch (Exception $e) {
    error_log("Erro em finalizar_pedido.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Limpar qualquer saída anterior
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao finalizar pedido: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);

} finally {
    if (isset($stmt_pedido)) mysqli_stmt_close($stmt_pedido);
    if (isset($stmt_item)) mysqli_stmt_close($stmt_item);
    if (isset($stmt_update)) mysqli_stmt_close($stmt_update);
    if (isset($stmt_mesa)) mysqli_stmt_close($stmt_mesa);
    if (isset($conn)) mysqli_close($conn);
} 