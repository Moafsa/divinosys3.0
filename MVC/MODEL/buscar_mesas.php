<?php
require_once __DIR__ . '/conexao.php';
header('Content-Type: application/json');
$conn = $GLOBALS['conn'];
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco']);
    exit;
}
$mesas = array();
$res = mysqli_query($conn, "SELECT id_mesa FROM mesas ORDER BY id_mesa");
if (!$res) {
    echo json_encode(['success' => false, 'message' => 'Erro na consulta SQL']);
    exit;
}
while ($row = mysqli_fetch_assoc($res)) {
    $mesas[] = [
        'id_mesa' => $row['id_mesa'],
        'nome' => 'Mesa ' . $row['id_mesa']
    ];
}
echo json_encode(['success' => true, 'mesas' => $mesas]); 