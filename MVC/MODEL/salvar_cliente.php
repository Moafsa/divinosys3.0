<?php
require_once '../config.php';
require_once '../conexao.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['nome']) || !isset($_POST['telefone'])) {
        throw new Exception('Nome e telefone são obrigatórios');
    }

    $nome = trim($_POST['nome']);
    $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone']);
    $telefone2 = isset($_POST['telefone2']) ? preg_replace('/[^0-9]/', '', $_POST['telefone2']) : null;
    $endereco = isset($_POST['endereco']) ? trim($_POST['endereco']) : '';
    $referencia = isset($_POST['referencia']) ? trim($_POST['referencia']) : '';

    // Check if client already exists by phone number
    $sql_check = "SELECT id FROM clientes WHERE tel1 = ? OR tel2 = ? OR (? IS NOT NULL AND (tel1 = ? OR tel2 = ?))";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    if (!$stmt_check) {
        throw new Exception('Erro ao preparar consulta de verificação: ' . mysqli_error($conn));
    }

    if ($telefone2) {
        mysqli_stmt_bind_param($stmt_check, 'sssss', $telefone, $telefone, $telefone2, $telefone2, $telefone2);
    } else {
        mysqli_stmt_bind_param($stmt_check, 'sssss', $telefone, $telefone, $telefone2, $telefone2, $telefone2);
    }

    if (!mysqli_stmt_execute($stmt_check)) {
        throw new Exception('Erro ao executar consulta de verificação: ' . mysqli_error($conn));
    }

    $result = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result) > 0) {
        // Update existing client
        $row = mysqli_fetch_assoc($result);
        $client_id = $row['id'];
        
        $sql_update = "UPDATE clientes SET 
                        nome = ?,
                        tel1 = ?,
                        tel2 = ?,
                        endereco = ?,
                        referencia = ?,
                        updated_at = NOW()
                      WHERE id = ?";
                      
        $stmt_update = mysqli_prepare($conn, $sql_update);
        if (!$stmt_update) {
            throw new Exception('Erro ao preparar atualização: ' . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt_update, 'sssssi', 
            $nome, 
            $telefone,
            $telefone2,
            $endereco,
            $referencia,
            $client_id
        );

        if (!mysqli_stmt_execute($stmt_update)) {
            throw new Exception('Erro ao atualizar cliente: ' . mysqli_error($conn));
        }

        echo json_encode(array(
            'success' => true,
            'message' => 'Cliente atualizado com sucesso',
            'client_id' => $client_id
        ));

    } else {
        // Insert new client
        $sql_insert = "INSERT INTO clientes (nome, tel1, tel2, endereco, referencia, created_at, updated_at) 
                       VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
                       
        $stmt_insert = mysqli_prepare($conn, $sql_insert);
        if (!$stmt_insert) {
            throw new Exception('Erro ao preparar inserção: ' . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt_insert, 'sssss', 
            $nome,
            $telefone,
            $telefone2,
            $endereco,
            $referencia
        );

        if (!mysqli_stmt_execute($stmt_insert)) {
            throw new Exception('Erro ao inserir cliente: ' . mysqli_error($conn));
        }

        $client_id = mysqli_insert_id($conn);

        echo json_encode(array(
            'success' => true,
            'message' => 'Cliente cadastrado com sucesso',
            'client_id' => $client_id
        ));
    }

} catch (Exception $e) {
    error_log('Erro em salvar_cliente.php: ' . $e->getMessage());
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage()
    ));
} 