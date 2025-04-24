<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

echo json_encode([
    'success' => true,
    'message' => 'API funcionando',
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'request_uri' => $_SERVER['REQUEST_URI'],
    'script_name' => $_SERVER['SCRIPT_NAME'],
    'php_self' => $_SERVER['PHP_SELF'],
    'query_string' => $_SERVER['QUERY_STRING'] ?? '',
    'http_host' => $_SERVER['HTTP_HOST'],
    'server_software' => $_SERVER['SERVER_SOFTWARE']
]);
?> 