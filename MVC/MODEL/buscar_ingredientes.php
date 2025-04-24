<?php
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
header('Access-Control-Allow-Methods: GET, OPTIONS');
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
    
    // Log do início da requisição
    error_log("Iniciando busca de ingredientes - produto_id: " . ($_GET['produto_id'] ?? 'não fornecido'));
    
    // Validar parâmetro produto_id
    if (!isset($_GET['produto_id'])) {
        throw new Exception("ID do produto não fornecido");
    }

    $produto_id = filter_var($_GET['produto_id'], FILTER_VALIDATE_INT);
    if ($produto_id === false) {
        throw new Exception("ID do produto inválido");
    }

    error_log("Produto ID validado: " . $produto_id);

    // Verificar conexão
    if (!isset($conn) || !$conn) {
        throw new Exception("Erro de conexão com o banco de dados");
    }

    // Primeiro verificar se o produto existe
    $sql_check = "SELECT id, nome FROM produtos WHERE id = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    if (!$stmt_check) {
        error_log("Erro ao preparar consulta de verificação: " . mysqli_error($conn));
        throw new Exception("Erro ao preparar consulta de verificação");
    }

    mysqli_stmt_bind_param($stmt_check, 'i', $produto_id);
    if (!mysqli_stmt_execute($stmt_check)) {
        error_log("Erro ao executar consulta de verificação: " . mysqli_stmt_error($stmt_check));
        throw new Exception("Erro ao executar consulta de verificação");
    }

    $result_check = mysqli_stmt_get_result($stmt_check);
    if (!$result_check || mysqli_num_rows($result_check) === 0) {
        error_log("Produto não encontrado: " . $produto_id);
        throw new Exception("Produto não encontrado");
    }

    error_log("Produto encontrado, buscando ingredientes");

    // Buscar TODOS os ingredientes, marcando quais são do produto e quais não são
    $sql = "SELECT 
        i.id,
        i.nome,
        COALESCE(i.preco_adicional, 0) as preco_adicional,
        CASE WHEN pi.produto_id IS NOT NULL THEN 1 ELSE 0 END as padrao
    FROM ingredientes i
    LEFT JOIN produto_ingredientes pi ON i.id = pi.ingrediente_id AND pi.produto_id = ?
    ORDER BY i.nome";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("Erro ao preparar consulta de ingredientes: " . mysqli_error($conn));
        throw new Exception("Erro ao preparar consulta de ingredientes");
    }

    mysqli_stmt_bind_param($stmt, 'i', $produto_id);

    if (!mysqli_stmt_execute($stmt)) {
        error_log("Erro ao executar consulta de ingredientes: " . mysqli_stmt_error($stmt));
        throw new Exception("Erro ao executar consulta de ingredientes");
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        error_log("Erro ao obter resultado dos ingredientes: " . mysqli_error($conn));
        throw new Exception("Erro ao obter resultado dos ingredientes");
    }

    $ingredients = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ingredients[] = [
            'id' => (int)$row['id'],
            'nome' => $row['nome'],
            'preco_adicional' => (float)$row['preco_adicional'],
            'padrao' => (bool)$row['padrao']
        ];
    }

    error_log("Total de ingredientes encontrados: " . count($ingredients));

    // Limpar qualquer saída anterior
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Criar o array de resposta
    $response = [
        'success' => true,
        'ingredients' => $ingredients,
        'total' => count($ingredients)
    ];

    // Converter para JSON e enviar
    $json = json_encode($response, JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        error_log("Erro ao codificar JSON: " . json_last_error_msg());
        throw new Exception("Erro ao codificar JSON");
    }

    echo $json;
    exit;

} catch (Exception $e) {
    error_log("Erro em buscar_ingredientes.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Limpar qualquer saída anterior
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
} finally {
    if (isset($stmt_check)) mysqli_stmt_close($stmt_check);
    if (isset($stmt)) mysqli_stmt_close($stmt);
    if (isset($conn)) mysqli_close($conn);
} 