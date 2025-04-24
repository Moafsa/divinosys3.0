<?php
header('Content-Type: application/json');
include_once(__DIR__ . "/conexao.php");

try {
    // Parâmetros de paginação e filtros
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $per_page = isset($_GET['per_page']) ? max(1, min(100, intval($_GET['per_page']))) : 10;
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $mesa_id = isset($_GET['mesa_id']) ? intval($_GET['mesa_id']) : null;
    $data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : null;
    $data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : null;

    // Construir query base
    $query = "
        SELECT 
            p.idpedido,
            p.idmesa,
            p.status,
            p.data,
            p.hora_pedido,
            p.delivery,
            p.cliente,
            p.gorjeta,
            m.id_mesa as numero_mesa,
            COALESCE(SUM(ip.quantidade * pr.preco_normal), 0) as valor_total
        FROM pedido p
        LEFT JOIN mesas m ON p.idmesa = m.id
        LEFT JOIN pedido_itens ip ON p.idpedido = ip.pedido_id
        LEFT JOIN produtos pr ON ip.produto_id = pr.id
    ";

    $where_conditions = array();
    $params = array();
    $types = "";

    // Adicionar filtros
    if ($status) {
        $where_conditions[] = "p.status = ?";
        $params[] = $status;
        $types .= "s";
    }

    if ($mesa_id) {
        $where_conditions[] = "p.idmesa = ?";
        $params[] = $mesa_id;
        $types .= "i";
    }

    if ($data_inicio) {
        $where_conditions[] = "p.data >= ?";
        $params[] = $data_inicio;
        $types .= "s";
    }

    if ($data_fim) {
        $where_conditions[] = "p.data <= ?";
        $params[] = $data_fim;
        $types .= "s";
    }

    // Adicionar WHERE se houver condições
    if (!empty($where_conditions)) {
        $query .= " WHERE " . implode(" AND ", $where_conditions);
    }

    // Agrupar por pedido
    $query .= " GROUP BY p.idpedido, p.idmesa, p.status, p.data, p.hora_pedido, p.delivery, p.cliente, p.gorjeta, m.id_mesa";

    // Contar total de registros
    $count_query = "SELECT COUNT(DISTINCT p.idpedido) as total FROM pedido p";
    if (!empty($where_conditions)) {
        $count_query .= " WHERE " . implode(" AND ", $where_conditions);
    }

    $stmt = mysqli_prepare($conn, $count_query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao contar registros");
    }

    $total_result = mysqli_stmt_get_result($stmt);
    $total_row = mysqli_fetch_assoc($total_result);
    $total_records = $total_row['total'];

    // Calcular total de páginas
    $total_pages = ceil($total_records / $per_page);
    $offset = ($page - 1) * $per_page;

    // Adicionar paginação
    $query .= " ORDER BY p.data DESC, p.hora_pedido DESC LIMIT ? OFFSET ?";
    $types .= "ii";
    $params[] = $per_page;
    $params[] = $offset;

    // Executar query principal
    $stmt = mysqli_prepare($conn, $query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao listar pedidos");
    }

    $result = mysqli_stmt_get_result($stmt);
    $pedidos = array();

    while ($row = mysqli_fetch_assoc($result)) {
        // Buscar itens do pedido
        $query_itens = "
            SELECT 
                ip.produto_id,
                ip.quantidade,
                p.nome as produto_nome,
                p.preco_normal,
                p.preco_mini
            FROM pedido_itens ip
            JOIN produtos p ON ip.produto_id = p.id
            WHERE ip.pedido_id = ?
        ";

        $stmt_itens = mysqli_prepare($conn, $query_itens);
        mysqli_stmt_bind_param($stmt_itens, "i", $row['idpedido']);
        
        if (!mysqli_stmt_execute($stmt_itens)) {
            throw new Exception("Erro ao buscar itens do pedido");
        }

        $result_itens = mysqli_stmt_get_result($stmt_itens);
        $itens = array();

        while ($item = mysqli_fetch_assoc($result_itens)) {
            $itens[] = array(
                'produto_id' => intval($item['produto_id']),
                'produto_nome' => $item['produto_nome'],
                'quantidade' => intval($item['quantidade']),
                'preco_normal' => floatval($item['preco_normal']),
                'preco_mini' => floatval($item['preco_mini']),
                'subtotal' => floatval($item['preco_normal']) * intval($item['quantidade'])
            );
        }

        $pedidos[] = array(
            'id' => intval($row['idpedido']),
            'mesa_id' => intval($row['idmesa']),
            'mesa_numero' => intval($row['numero_mesa']),
            'status' => $row['status'],
            'data' => $row['data'],
            'hora_pedido' => $row['hora_pedido'],
            'delivery' => $row['delivery'],
            'cliente' => $row['cliente'],
            'gorjeta' => floatval($row['gorjeta']),
            'valor_total' => floatval($row['valor_total']),
            'itens' => $itens
        );
    }

    echo json_encode(array(
        'success' => true,
        'data' => array(
            'pedidos' => $pedidos,
            'pagination' => array(
                'total_records' => intval($total_records),
                'total_pages' => $total_pages,
                'current_page' => $page,
                'per_page' => $per_page
            )
        )
    ));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage()
    ));
}

mysqli_close($conn);
?> 