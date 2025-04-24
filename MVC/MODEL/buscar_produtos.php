<?php
// Prevenir qualquer saída antes dos headers
if (ob_get_level()) ob_end_clean();
ob_start();

// Desabilitar exibição de erros
error_reporting(0);
ini_set('display_errors', 0);

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Definir constante do caminho base
define('BASE_PATH', dirname(dirname(dirname(__FILE__))));

// Adicionar log no início do arquivo
error_log("Iniciando busca de produtos");

try {
    require_once BASE_PATH . "/MVC/MODEL/conexao.php";
    require_once BASE_PATH . "/MVC/MODEL/config.php";
    $config = Config::getInstance();

    // Verificar se a conexão foi estabelecida
    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new Exception("Conexão com o banco de dados não está disponível");
    }

    // Obter dados da requisição
    $input = file_get_contents('php://input');
    error_log("Input recebido: " . $input);

    if (!empty($input)) {
        $postData = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Erro ao decodificar JSON: " . json_last_error_msg());
            $postData = null;
        } else {
            error_log("Dados POST decodificados: " . json_encode($postData));
        }
    } else {
        $postData = null;
        error_log("Nenhum dado POST recebido");
    }

    // Obter parâmetros de busca
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $postData) {
        $termo = isset($postData['query']) ? trim($postData['query']) : '';
        $categoria = isset($postData['categoria']) && $postData['categoria'] !== '' ? intval($postData['categoria']) : null;
    } else {
        $termo = isset($_GET['termo']) ? trim($_GET['termo']) : '';
        $categoria = isset($_GET['categoria']) && $_GET['categoria'] !== '' ? intval($_GET['categoria']) : null;
    }

    // Validar e sanitizar o termo de busca
    $termo = filter_var($termo, FILTER_SANITIZE_STRING);
    error_log("Termo de busca após sanitização: '$termo'");
    error_log("Categoria após sanitização: " . ($categoria === null ? 'null' : $categoria));

    error_log("Parâmetros de busca: termo='$termo', categoria=" . ($categoria ?? 'null'));

    // Preparar a query base
    $sql = "SELECT 
        p.id,
        p.nome,
        p.descricao,
        p.codigo,
        p.preco_normal,
        p.imagem,
        c.nome as categoria_nome
    FROM produtos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    WHERE 1=1";

    $params = [];
    $types = "";

    // Adicionar condições de busca
    if (!empty($termo)) {
        // Busca por código exato
        if (is_numeric($termo)) {
            $sql .= " AND (p.codigo = ? OR p.nome LIKE ? OR p.descricao LIKE ?)";
            $searchTerm = "%{$termo}%";
            $params[] = $termo; // Para código exato
            $params[] = $searchTerm; // Para nome
            $params[] = $searchTerm; // Para descrição
            $types .= "sss";
            error_log("Buscando por código exato: $termo");
        } else {
            // Busca por nome ou descrição
            $sql .= " AND (p.nome LIKE ? OR p.descricao LIKE ? OR p.codigo LIKE ?)";
            $searchTerm = "%{$termo}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
            error_log("Buscando por termo: $searchTerm");
        }
    }

    // Filtro por categoria
    if ($categoria) {
        $sql .= " AND p.categoria_id = ?";
        $params[] = $categoria;
        $types .= "i";
    }

    // Ordenação e limite
    $sql .= " ORDER BY p.nome ASC LIMIT 50";

    error_log("SQL Query: " . $sql);
    error_log("Parâmetros: " . json_encode($params));

    // Prepare and execute query
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception("Erro ao preparar consulta: " . mysqli_error($conn));
    }

    // Bind parameters if any
    if (!empty($params)) {
        if (!mysqli_stmt_bind_param($stmt, $types, ...$params)) {
            throw new Exception("Erro ao vincular parâmetros: " . mysqli_stmt_error($stmt));
        }
    }

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao executar consulta: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        throw new Exception("Erro ao obter resultados: " . mysqli_error($conn));
    }

    $produtos = [];
    $count = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        // Format prices
        $preco_normal = number_format((float)$row['preco_normal'], 2, '.', '');

        // Build product array
        $produtos[] = [
            'id' => (int)$row['id'],
            'nome' => $row['nome'],
            'descricao' => $row['descricao'],
            'codigo' => $row['codigo'],
            'preco_normal' => $preco_normal,
            'imagem' => $row['imagem'] ? $config->url($row['imagem']) : null,
            'categoria' => $row['categoria_nome']
        ];
        $count++;
    }

    error_log("Produtos encontrados: " . $count);

    $response = [
        'success' => true,
        'produtos' => $produtos,
        'total' => $count,
        'busca' => [
            'termo' => $termo,
            'categoria' => $categoria
        ],
        'tempo_execucao' => number_format(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 4)
    ];

    // Garantir que não haja saída antes
    while (ob_get_level()) ob_end_clean();
    
    // Enviar resposta
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;

} catch (Exception $e) {
    // Garantir que não haja saída antes
    while (ob_get_level()) ob_end_clean();
    
    // Log do erro
    error_log("Erro em buscar_produtos.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Enviar resposta de erro
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar produtos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;

} finally {
    if (isset($stmt)) mysqli_stmt_close($stmt);
    if (isset($conn)) mysqli_close($conn);
}
?> 