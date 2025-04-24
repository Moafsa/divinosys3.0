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

// Adicionar log no início do arquivo
error_log("Iniciando busca de categorias");

try {
    require_once BASE_PATH . "/MVC/MODEL/conexao.php";
    require_once BASE_PATH . "/MVC/MODEL/config.php";

    // Log da query
    error_log("Executando query de categorias");
    
    // Query to get all categories with ordering by priority
    $sql = "SELECT id, nome 
            FROM categorias 
            ORDER BY 
                CASE nome 
                    WHEN 'XIS' THEN 1
                    WHEN 'Cachorro-Quente' THEN 2
                    WHEN 'Bauru' THEN 3
                    WHEN 'PF e A La Minuta' THEN 4
                    WHEN 'Torrada' THEN 5
                    WHEN 'Rodízio' THEN 6
                    WHEN 'Porções' THEN 7
                    WHEN 'Bebidas' THEN 8
                    WHEN 'Bebidas Alcoólicas' THEN 9
                    ELSE 10
                END,
                nome ASC";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log("Erro ao preparar consulta: " . mysqli_error($conn));
        throw new Exception("Erro ao preparar consulta: " . mysqli_error($conn));
    }

    if (!mysqli_stmt_execute($stmt)) {
        error_log("Erro ao executar consulta: " . mysqli_stmt_error($stmt));
        throw new Exception("Erro ao executar consulta: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        error_log("Erro ao obter resultado: " . mysqli_error($conn));
        throw new Exception("Erro ao obter resultado: " . mysqli_error($conn));
    }

    $categories = [];
    $count = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = [
            'id' => intval($row['id']),
            'nome' => $row['nome']
        ];
        $count++;
    }

    error_log("Categorias encontradas: " . $count);

    // Adicionar headers de cache
    $etag = md5(json_encode($categories));
    header('ETag: "' . $etag . '"');
    header('Cache-Control: public, max-age=300'); // Cache por 5 minutos

    $response = [
        'success' => true,
        'categories' => $categories,
        'total' => $count,
        'cached' => false
    ];

    // Garantir que não haja saída antes
    if (ob_get_length()) ob_clean();
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;

} catch (Exception $e) {
    error_log("Erro em buscar_categorias.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Garantir que não haja saída antes
    if (ob_get_length()) ob_clean();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar categorias: ' . $e->getMessage()
    ]);
    exit;
} finally {
    if (isset($stmt)) mysqli_stmt_close($stmt);
    if (isset($conn)) mysqli_close($conn);
} 