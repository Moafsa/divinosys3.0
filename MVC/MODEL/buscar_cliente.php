<?php
require_once '../config.php';
require_once '../conexao.php';

header('Content-Type: application/json');

try {
    if (isset($_GET['telefone'])) {
        $telefone = preg_replace('/[^0-9]/', '', $_GET['telefone']);
        if (strlen($telefone) < 8) {
            throw new Exception('Telefone deve ter no mínimo 8 dígitos');
        }
        $sql = "SELECT * FROM clientes WHERE tel1 LIKE ? OR tel2 LIKE ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception('Erro ao preparar consulta: ' . mysqli_error($conn));
        }
        $param = "%$telefone%";
        mysqli_stmt_bind_param($stmt, 'ss', $param, $param);
    } elseif (isset($_GET['nome'])) {
        $nome = '%' . trim($_GET['nome']) . '%';
        $sql = "SELECT * FROM clientes WHERE nome LIKE ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception('Erro ao preparar consulta: ' . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 's', $nome);
    } else {
        throw new Exception('Informe o nome ou telefone para buscar');
    }

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Erro ao executar consulta: ' . mysqli_error($conn));
    }
    $result = mysqli_stmt_get_result($stmt);
    $clientes = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $clientes[] = array(
            'id' => (int)$row['id'],
            'nome' => htmlspecialchars($row['nome']),
            'tel1' => $row['tel1'],
            'tel2' => $row['tel2'],
            'endereco' => htmlspecialchars($row['endereco']),
            'bairro' => htmlspecialchars($row['bairro']),
            'cidade' => htmlspecialchars($row['cidade']),
            'estado' => htmlspecialchars($row['estado']),
            'complemento' => htmlspecialchars($row['complemento']),
            'cep' => htmlspecialchars($row['cep']),
            'referencia' => htmlspecialchars($row['referencia']),
            'email' => htmlspecialchars($row['email']),
            'cpf_cnpj' => htmlspecialchars($row['cpf_cnpj']),
            'rg' => htmlspecialchars($row['rg']),
            'condominio' => htmlspecialchars($row['condominio']),
            'bloco' => htmlspecialchars($row['bloco']),
            'apartamento' => htmlspecialchars($row['apartamento']),
            'local_entrega' => htmlspecialchars($row['local_entrega']),
            'observacoes' => htmlspecialchars($row['observacoes'])
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