<?php
// Garantir que a sessão está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevenir qualquer saída antes dos headers
ob_clean();

// Habilitar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

    // Iniciar transação
    mysqli_begin_transaction($conn);

    try {
        $cliente_id = null;
        $nome_cliente = isset($data['nome_cliente']) ? $data['nome_cliente'] : '';
        $telefone = isset($data['telefone']) ? preg_replace('/[^0-9]/', '', $data['telefone']) : '';
        $endereco_entrega = isset($data['endereco_entrega']) ? $data['endereco_entrega'] : '';
        $ponto_referencia = isset($data['ponto_referencia']) ? $data['ponto_referencia'] : '';
        $taxa_entrega = isset($data['taxa_entrega']) ? floatval($data['taxa_entrega']) : 0;
        $forma_pagamento = isset($data['forma_pagamento']) ? $data['forma_pagamento'] : '';
        $troco_para = isset($data['troco_para']) ? floatval($data['troco_para']) : 0;
        
        // Se tiver telefone, tenta identificar ou cadastrar o cliente
        if (!empty($telefone)) {
            // Verificar se o cliente já existe usando tel1 ou tel2
            $stmt = mysqli_prepare($conn, "SELECT id FROM clientes WHERE tel1 = ? OR tel2 = ?");
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta de cliente: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, 'ss', $telefone, $telefone);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Erro ao executar consulta de cliente: " . mysqli_stmt_error($stmt));
            }
            
            $result = mysqli_stmt_get_result($stmt);
            
            if ($cliente = mysqli_fetch_assoc($result)) {
                $cliente_id = $cliente['id'];
                
                // Atualizar dados do cliente
                if (!empty($endereco_entrega)) {
                    $stmt = mysqli_prepare($conn, "
                        UPDATE clientes 
                        SET nome = ?,
                            endereco = ?, 
                            ponto_referencia = ?
                        WHERE id = ?
                    ");
                    
                    if (!$stmt) {
                        throw new Exception("Erro ao preparar atualização de cliente: " . mysqli_error($conn));
                    }
                    
                    mysqli_stmt_bind_param($stmt, 'sssi', 
                        $nome_cliente,
                        $endereco_entrega,
                        $ponto_referencia,
                        $cliente_id
                    );
                    
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception("Erro ao atualizar cliente: " . mysqli_stmt_error($stmt));
                    }
                }
            } else {
                // Cadastrar novo cliente
                $stmt = mysqli_prepare($conn, "
                    INSERT INTO clientes (
                        nome, 
                        tel1,
                        endereco, 
                        ponto_referencia
                    ) VALUES (?, ?, ?, ?)
                ");
                
                if (!$stmt) {
                    throw new Exception("Erro ao preparar inserção de cliente: " . mysqli_error($conn));
                }
                
                mysqli_stmt_bind_param($stmt, 'ssss',
                    $nome_cliente,
                    $telefone,
                    $endereco_entrega,
                    $ponto_referencia
                );
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Erro ao inserir cliente: " . mysqli_stmt_error($stmt));
                }
                
                $cliente_id = mysqli_insert_id($conn);
            }
        }

        // Calcular valor total do pedido
        $valor_total = 0;
        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                $quantidade = isset($item['quantidade']) ? intval($item['quantidade']) : 1;
                $valor_unitario = isset($item['produto']['preco']) ? floatval($item['produto']['preco']) : 0;
                $valor_total += $valor_unitario * $quantidade;
            }
        }
        // Adicionar taxa de entrega ao valor total
        $valor_total += $taxa_entrega;

        // Criar pedido principal
        $data_pedido = date('Y-m-d');
        $hora_pedido = date('H:i:s');
        $status = 'Pendente';
        $delivery = 1;
        
        $sql_pedido = "INSERT INTO pedido (
            cliente_id,
            cliente, 
            telefone_cliente, 
            endereco_entrega, 
            taxa_entrega,
            forma_pagamento,
            troco_para,
            ponto_referencia,
            data, 
            hora_pedido, 
            status,
            delivery,
            valor_total
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt_pedido = mysqli_prepare($conn, $sql_pedido);
        if (!$stmt_pedido) {
            throw new Exception("Erro ao preparar inserção de pedido: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt_pedido, 'isssdsdssssis',
            $cliente_id,
            $nome_cliente,
            $telefone,
            $endereco_entrega,
            $taxa_entrega,
            $forma_pagamento,
            $troco_para,
            $ponto_referencia,
            $data_pedido,
            $hora_pedido,
            $status,
            $delivery,
            $valor_total
        );
        
        if (!mysqli_stmt_execute($stmt_pedido)) {
            throw new Exception("Erro ao inserir pedido: " . mysqli_stmt_error($stmt_pedido));
        }
        
        $pedido_id = mysqli_insert_id($conn);

        // Inserir itens do pedido
        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                $produto_id = isset($item['produto']['id']) ? intval($item['produto']['id']) : 0;
                $quantidade = isset($item['quantidade']) ? intval($item['quantidade']) : 1;
                $valor_unitario = isset($item['produto']['preco']) ? floatval($item['produto']['preco']) : 0;
                $valor_total_item = $valor_unitario * $quantidade;
                $observacao = isset($item['observacao']) ? $item['observacao'] : '';
                $tamanho = isset($item['tamanho']) ? $item['tamanho'] : 'normal';
                error_log("[DEBUG] Salvando item do pedido delivery: produto_id={$produto_id}, quantidade={$quantidade}, tamanho={$tamanho}");
                
                $sql_item = "INSERT INTO pedido_itens (
                    pedido_id, 
                    produto_id, 
                    quantidade, 
                    valor_unitario, 
                    valor_total, 
                    observacao,
                    tamanho
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";

                $stmt_item = mysqli_prepare($conn, $sql_item);
                if (!$stmt_item) {
                    throw new Exception("Erro ao preparar inserção de item: " . mysqli_error($conn));
                }
                
                mysqli_stmt_bind_param($stmt_item, 'iidddss', 
                    $pedido_id, 
                    $produto_id, 
                    $quantidade, 
                    $valor_unitario, 
                    $valor_total_item, 
                    $observacao,
                    $tamanho
                );
                
                if (!mysqli_stmt_execute($stmt_item)) {
                    throw new Exception("Erro ao inserir item: " . mysqli_stmt_error($stmt_item));
                }
                
                $item_id = mysqli_insert_id($conn);

                // Inserir ingredientes do item se houver
                if (!empty($item['ingredientes'])) {
                    foreach ($item['ingredientes'] as $ingrediente) {
                        if (isset($ingrediente['id'])) {
                            $incluido = isset($ingrediente['tipo']) && $ingrediente['tipo'] === 'com' ? 1 : 0;
                            
                            $sql_ingrediente = "INSERT INTO pedido_item_ingredientes (
                                pedido_item_id, 
                                ingrediente_id, 
                                incluido
                            ) VALUES (?, ?, ?)";

                            $stmt_ingrediente = mysqli_prepare($conn, $sql_ingrediente);
                            if (!$stmt_ingrediente) {
                                throw new Exception("Erro ao preparar inserção de ingrediente: " . mysqli_error($conn));
                            }
                            
                            mysqli_stmt_bind_param($stmt_ingrediente, 'iii', 
                                $item_id, 
                                $ingrediente['id'], 
                                $incluido
                            );
                            
                            if (!mysqli_stmt_execute($stmt_ingrediente)) {
                                throw new Exception("Erro ao inserir ingrediente: " . mysqli_stmt_error($stmt_ingrediente));
                            }
                        }
                    }
                }
            }
        }

        // Commit da transação
        mysqli_commit($conn);

        // Limpar carrinho de delivery
        unset($_SESSION['carrinho_delivery']);

        // Retornar sucesso
        echo json_encode([
            'success' => true,
            'message' => 'Pedido finalizado com sucesso',
            'pedido_id' => $pedido_id
        ]);

    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }

} catch (Exception $e) {
    error_log("Erro ao finalizar pedido delivery: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) mysqli_close($conn);
    ob_end_flush();
} 