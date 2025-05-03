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
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Definir constante do caminho base
define('BASE_PATH', dirname(dirname(dirname(__FILE__))));

// Iniciar sessão
session_start();

// Inicializar carrinho de delivery se não existir
if (!isset($_SESSION['carrinho_delivery'])) {
    $_SESSION['carrinho_delivery'] = array();
}

try {
    // Obter o método da requisição
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            // Retornar o carrinho atual
            echo json_encode([
                'success' => true,
                'carrinho' => $_SESSION['carrinho_delivery']
            ]);
            break;

        case 'POST':
            // Adicionar item ao carrinho
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                throw new Exception("Dados inválidos");
            }

            // Validar dados necessários
            if (!isset($data['produto']) || !isset($data['quantidade'])) {
                throw new Exception("Dados do produto incompletos");
            }

            // Adicionar item ao carrinho
            $_SESSION['carrinho_delivery'][] = [
                'produto' => $data['produto'],
                'quantidade' => $data['quantidade'],
                'ingredientes' => isset($data['ingredientes']) ? $data['ingredientes'] : [],
                'observacao' => isset($data['observacao']) ? $data['observacao'] : '',
                'valor_total' => $data['valor_total'],
                'tamanho' => isset($data['tamanho']) ? $data['tamanho'] : null
            ];

            echo json_encode([
                'success' => true,
                'message' => 'Item adicionado ao carrinho',
                'carrinho' => $_SESSION['carrinho_delivery']
            ]);
            break;

        case 'DELETE':
            // Remover item do carrinho
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['index'])) {
                throw new Exception("Índice do item não especificado");
            }

            $index = $data['index'];
            
            if (isset($_SESSION['carrinho_delivery'][$index])) {
                array_splice($_SESSION['carrinho_delivery'], $index, 1);
                echo json_encode([
                    'success' => true,
                    'message' => 'Item removido do carrinho',
                    'carrinho' => $_SESSION['carrinho_delivery']
                ]);
            } else {
                throw new Exception("Item não encontrado no carrinho");
            }
            break;

        default:
            throw new Exception("Método não suportado");
    }

} catch (Exception $e) {
    error_log(sprintf(
        "Erro em carrinho.php: %s. Dados recebidos: %s",
        $e->getMessage(),
        json_encode([
            'method' => $_SERVER['REQUEST_METHOD'],
            'data' => isset($data) ? $data : null,
            'session' => isset($_SESSION['carrinho_delivery']) ? count($_SESSION['carrinho_delivery']) : 0
        ])
    ));
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    // Garantir que todo o output foi enviado
    ob_end_flush();
}