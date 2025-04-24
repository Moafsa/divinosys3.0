<?php
header('Content-Type: application/json');
include_once(__DIR__ . "/conexao.php");

try {
    // Iniciar transação
    mysqli_begin_transaction($conn);

    // Primeiro, vamos identificar mesas que estão marcadas como ocupadas mas não têm pedidos ativos
    $query = "
        UPDATE mesas m 
        LEFT JOIN (
            SELECT idmesa, COUNT(*) as pedidos_ativos
            FROM pedido 
            WHERE status NOT IN ('Finalizado', 'Entregue', 'Cancelado', 'Fechado')
            GROUP BY idmesa
        ) p ON m.id_mesa = p.idmesa
        SET m.status = 1
        WHERE m.status IN (2, 3) 
        AND (p.pedidos_ativos IS NULL OR p.pedidos_ativos = 0)
    ";

    if (!mysqli_query($conn, $query)) {
        throw new Exception("Erro ao atualizar status das mesas: " . mysqli_error($conn));
    }

    $mesas_atualizadas = mysqli_affected_rows($conn);

    // Commit da transação
    mysqli_commit($conn);

    echo json_encode([
        'success' => true,
        'message' => "Status das mesas corrigido com sucesso",
        'mesas_atualizadas' => $mesas_atualizadas
    ]);

} catch (Exception $e) {
    // Rollback em caso de erro
    mysqli_rollback($conn);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn); 