<?php
header('Content-Type: application/json');

$config_file = dirname(__DIR__) . '/CONFIG/estabelecimento.php';
if (!file_exists($config_file)) {
    echo json_encode(['success' => false, 'message' => 'Configuração não encontrada']);
    exit;
}

$config = require $config_file;

// Montar resposta
$response = [
    'success' => true,
    'data' => [
        'nome_estabelecimento' => $config['nome_estabelecimento'] ?? '',
        'cnpj' => $config['cnpj'] ?? '',
        'endereco' => $config['endereco'] ?? '',
        'telefone' => $config['telefone'] ?? '',
        'site' => $config['site'] ?? '',
        'mensagem_header' => $config['messages']['header'] ?? '',
        'mensagem_footer' => $config['messages']['footer'] ?? '',
        'logo' => $config['logo'] ?? '',
    ]
];
echo json_encode($response); 