<?php
require_once '../config.php';
require_once '../conexao.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['telefone'])) {
        throw new Exception('Telefone não informado');
    }

    $telefone = preg_replace('/[^0-9]/', '', $_GET['telefone']);
    
    // Validar tamanho mínimo do telefone
    if (strlen($telefone) < 8) {
        throw new Exception('Telefone deve ter no mínimo 8 dígitos');
    }
    
    $sql = "SELECT id, nome, tel1, tel2, endereco, referencia 
            FROM clientes 
            WHERE tel1 LIKE ? OR tel2 LIKE ?";
            
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception('Erro ao preparar consulta: ' . mysqli_error($conn));
    }

    $param = "%$telefone%";
    mysqli_stmt_bind_param($stmt, 'ss', $param, $param);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Erro ao executar consulta: ' . mysqli_error($conn));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    $clientes = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $clientes[] = array(
            'id' => (int)$row['id'],
            'nome' => htmlspecialchars($row['nome']),
            'telefone' => $row['tel1'] ? preg_replace('/[^0-9]/', '', $row['tel1']) : preg_replace('/[^0-9]/', '', $row['tel2']),
            'telefone_alternativo' => $row['tel2'] ? preg_replace('/[^0-9]/', '', $row['tel2']) : preg_replace('/[^0-9]/', '', $row['tel1']),
            'endereco' => htmlspecialchars($row['endereco']),
            'referencia' => htmlspecialchars($row['referencia'])
        );
    }
    
    echo json_encode(array(
        'success' => true,
        'clientes' => $clientes
    ));

} catch (Exception $e) {
    error_log('Erro em buscar_cliente.php: ' . $e->getMessage());
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage()
    ));
} 