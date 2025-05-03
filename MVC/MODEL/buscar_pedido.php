<?php
// Define que este é um endpoint JSON
define('IS_JSON_ENDPOINT', true);

// Prevenir qualquer saída antes dos headers
if (ob_get_level()) ob_end_clean();
ob_start();

session_start();
header('Content-Type: application/json; charset=utf-8');

// Incluir arquivo de configuração
require_once __DIR__ . '/config.php';

// Incluir o arquivo de conexão
require_once __DIR__ . '/conexao.php';

// Função para limpar e validar strings
function sanitizeString($str) {
    if (!is_string($str)) {
        return $str;
    }
    
    // Converter para UTF-8 se necessário
    if (!mb_check_encoding($str, 'UTF-8')) {
        $str = mb_convert_encoding($str, 'UTF-8', 'auto');
    }
    
    // Remover caracteres invisíveis e inválidos
    $str = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $str);
    
    // Remover BOM se presente
    $str = str_replace("\xEF\xBB\xBF", '', $str);
    
    return $str;
}

// Função para sanitizar array recursivamente
function sanitizeArray($array) {
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = sanitizeArray($value);
        } else {
            $array[$key] = sanitizeString($value);
        }
    }
    return $array;
}

try {
    // Validar dados recebidos
    $pedido_id = isset($_GET['pedido_id']) ? intval($_GET['pedido_id']) : null;

    if (!$pedido_id) {
        throw new Exception("ID do pedido não fornecido");
    }

    // Buscar dados do pedido
    $query = "SELECT p.*, m.id_mesa 
             FROM pedido p 
             LEFT JOIN mesas m ON p.idmesa = m.id_mesa 
             WHERE p.idpedido = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception("Erro ao preparar query do pedido: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $pedido_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao executar query do pedido: " . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    $pedido = mysqli_fetch_assoc($result);
    
    if (!$pedido) {
        throw new Exception("Pedido não encontrado");
    }

    // Buscar itens do pedido
    $query = "SELECT pi.*, p.nome as produto, pi.tamanho,
             GROUP_CONCAT(DISTINCT CASE WHEN pii.incluido = 1 THEN i.nome END) as ingredientes_com,
             GROUP_CONCAT(DISTINCT CASE WHEN pii.incluido = 0 THEN i.nome END) as ingredientes_sem
             FROM pedido_itens pi 
             JOIN produtos p ON pi.produto_id = p.id 
             LEFT JOIN pedido_item_ingredientes pii ON pi.id = pii.pedido_item_id
             LEFT JOIN ingredientes i ON pii.ingrediente_id = i.id
             WHERE pi.pedido_id = ?
             GROUP BY pi.id";
    
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception("Erro ao preparar query dos itens: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $pedido_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao executar query dos itens: " . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    $itens = array();
    
    while ($item = mysqli_fetch_assoc($result)) {
        error_log("=== Processando item do pedido ===");
        error_log("ID do item: " . ($item['id'] ?? 'N/A'));
        error_log("Produto: " . ($item['produto'] ?? 'N/A'));
        error_log("Quantidade: " . ($item['quantidade'] ?? 'N/A'));
        error_log("Ingredientes COM: " . ($item['ingredientes_com'] ?? 'N/A'));
        error_log("Ingredientes SEM: " . ($item['ingredientes_sem'] ?? 'N/A'));
        error_log("Valor unitário: " . ($item['valor_unitario'] ?? 'N/A'));
        error_log("Valor total: " . ($item['valor_total'] ?? 'N/A'));
        error_log("Observação: " . ($item['observacao'] ?? 'N/A'));
        error_log("Dados brutos do item: " . print_r($item, true));
        error_log("================================");
        
        $itens[] = $item;
    }

    // Sanitizar dados
    $pedido = sanitizeArray($pedido);
    $itens = sanitizeArray($itens);

    $response = [
        'success' => true,
        'pedido' => $pedido,
        'itens' => $itens
    ];

    error_log("Resposta completa: " . print_r($response, true));

    // Limpar qualquer saída anterior
    if (ob_get_length()) ob_clean();

    // Codificar resposta
    $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new Exception("Erro ao codificar resposta: " . json_last_error_msg());
    }

    // Fechar a conexão manualmente antes de enviar a resposta
    if (isset($conn)) {
        @mysqli_close($conn);
        unset($conn);
    }

    // Enviar resposta
    echo $json;
    exit;

} catch (Exception $e) {
    // Limpar qualquer saída anterior
    if (ob_get_length()) ob_clean();

    error_log("Erro na busca do pedido: " . $e->getMessage());
    http_response_code(500);
    
    // Fechar a conexão em caso de erro
    if (isset($conn)) {
        @mysqli_close($conn);
        unset($conn);
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?> 