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

try {
    require_once BASE_PATH . "/MVC/MODEL/conexao.php";

    // GET request - retornar carrinho atual
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $mesa_id = isset($_GET['mesa_id']) ? intval($_GET['mesa_id']) : null;
    
    if (!$mesa_id) {
            throw new Exception("ID da mesa não fornecido");
    }

    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }

    if (!isset($_SESSION['carrinho'][$mesa_id])) {
        $_SESSION['carrinho'][$mesa_id] = [];
    }

        echo json_encode([
            'success' => true,
            'carrinho' => $_SESSION['carrinho'][$mesa_id]
        ]);
        exit;
    }

    // POST request - adicionar item ao carrinho
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data) {
            throw new Exception("Dados inválidos");
        }

        if (!isset($data['mesa_id']) || !isset($data['produto']) || !isset($data['quantidade'])) {
            throw new Exception("Dados incompletos");
        }

        $mesa_id = intval($data['mesa_id']);
        $produto = $data['produto'];
        $quantidade = intval($data['quantidade']);
        $ingredientes = isset($data['ingredientes']) ? $data['ingredientes'] : [];

        // Validar dados
        if ($mesa_id <= 0) throw new Exception("Mesa inválida");
        if ($quantidade <= 0) throw new Exception("Quantidade inválida");
        if (!isset($produto['id']) || !isset($produto['nome']) || !isset($produto['preco'])) {
            throw new Exception("Dados do produto incompletos");
        }

        // Validar preço máximo
        if ($produto['preco'] > 10000) { // R$ 10.000 como limite máximo
            throw new Exception("Preço do produto inválido");
        }

        // Limpar e validar dados do produto
        $produto_limpo = [
            'id' => intval($produto['id']),
            'nome' => strip_tags(trim($produto['nome'])),
            'preco' => floatval($produto['preco'])
        ];

        // Validar ingredientes
        if (!empty($ingredientes)) {
            $ingredientes_limpos = [];
            foreach ($ingredientes as $ingrediente) {
                if (!isset($ingrediente['id']) || !isset($ingrediente['nome']) || !isset($ingrediente['tipo'])) {
                    throw new Exception("Dados de ingrediente inválidos");
                }
                $ingredientes_limpos[] = [
                    'id' => intval($ingrediente['id']),
                    'nome' => strip_tags(trim($ingrediente['nome'])),
                    'tipo' => strip_tags(trim($ingrediente['tipo'])),
                    'preco_adicional' => isset($ingrediente['preco_adicional']) ? floatval($ingrediente['preco_adicional']) : 0
                ];
            }
            $ingredientes = $ingredientes_limpos;
        }

        // Inicializar array do carrinho se não existir
        if (!isset($_SESSION['carrinho'])) {
            $_SESSION['carrinho'] = [];
        }

        if (!isset($_SESSION['carrinho'][$mesa_id])) {
            $_SESSION['carrinho'][$mesa_id] = [];
        }

        // Adicionar item ao carrinho com dados limpos
        $_SESSION['carrinho'][$mesa_id][] = [
            'produto' => $produto_limpo,
            'quantidade' => $quantidade,
            'ingredientes' => $ingredientes,
            'observacao' => isset($data['observacao']) ? strip_tags(trim($data['observacao'])) : '',
            'tamanho' => isset($data['tamanho']) ? strip_tags(trim($data['tamanho'])) : null,
            'data_adicao' => date('Y-m-d H:i:s')
        ];

        echo json_encode([
            'success' => true,
            'message' => 'Item adicionado ao carrinho',
            'carrinho' => $_SESSION['carrinho'][$mesa_id]
        ]);
        exit;
    }

    // DELETE request - remover item do carrinho
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data) {
            throw new Exception("Dados inválidos");
        }

        if (!isset($data['mesa_id']) || !isset($data['index'])) {
            throw new Exception("Dados incompletos");
        }

        $mesa_id = intval($data['mesa_id']);
        $index = intval($data['index']);

        // Validar dados
        if ($mesa_id <= 0) throw new Exception("Mesa inválida");
        if ($index < 0) throw new Exception("Índice inválido");

        // Verificar se o carrinho existe
        if (!isset($_SESSION['carrinho']) || !isset($_SESSION['carrinho'][$mesa_id])) {
            throw new Exception("Carrinho não encontrado");
        }

        // Verificar se o índice existe
        if (!isset($_SESSION['carrinho'][$mesa_id][$index])) {
            throw new Exception("Item não encontrado no carrinho");
        }

        // Remover item
        array_splice($_SESSION['carrinho'][$mesa_id], $index, 1);

        echo json_encode([
            'success' => true,
            'message' => 'Item removido do carrinho',
            'carrinho' => $_SESSION['carrinho'][$mesa_id]
        ]);
        exit;
    }

    throw new Exception("Método não suportado");

} catch (Exception $e) {
    error_log(sprintf(
        "Erro em carrinho.php: %s. Dados recebidos: %s",
        $e->getMessage(),
        json_encode([
            'method' => $_SERVER['REQUEST_METHOD'],
            'data' => isset($data) ? $data : null,
            'session' => isset($_SESSION['carrinho']) ? count($_SESSION['carrinho']) : 0
        ])
    ));
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}