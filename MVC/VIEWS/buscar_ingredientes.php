<?php
// Desabilitar exibição de erros
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Garantir que nenhuma saída foi enviada antes
if (headers_sent($filename, $linenum)) {
    error_log("Headers já foram enviados em $filename:$linenum");
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor'
    ]);
    exit;
}

// Limpar qualquer saída anterior
if (ob_get_level()) ob_end_clean();

// Definir cabeçalho como JSON
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Incluir arquivo de conexão
require_once(__DIR__ . "/../MODEL/config.php");
require_once(__DIR__ . "/../MODEL/conexao.php");

try {
    // Verificar se é uma requisição AJAX
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        throw new Exception('Requisição inválida');
    }

    // Verificar se o ID do produto foi fornecido
    if (!isset($_GET['produto_id'])) {
        throw new Exception('ID do produto não fornecido');
    }

    $produto_id = filter_var($_GET['produto_id'], FILTER_SANITIZE_NUMBER_INT);

    if (!$produto_id) {
        throw new Exception('ID do produto inválido');
    }

    // Buscar ingredientes do produto
    $query = "
        SELECT i.*, pi.quantidade as quantidade_padrao
        FROM produtos_ingredientes pi
        JOIN ingredientes i ON i.id = pi.ingrediente_id
        WHERE pi.produto_id = ?
        ORDER BY i.nome
    ";

    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception('Erro ao preparar consulta: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "i", $produto_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Erro ao executar consulta: ' . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    if ($result === false) {
        throw new Exception('Erro ao obter resultado da consulta');
    }

    $ingredientes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ingredientes[] = [
            'id' => (int)$row['id'],
            'nome' => $row['nome'],
            'quantidade_padrao' => (int)$row['quantidade_padrao'],
            'unidade_medida' => $row['unidade_medida']
        ];
    }

    echo json_encode([
        'success' => true,
        'ingredientes' => $ingredientes
    ]);

} catch (Exception $e) {
    error_log("Erro em buscar_ingredientes.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    // Fechar a conexão se estiver aberta
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
}
?> 