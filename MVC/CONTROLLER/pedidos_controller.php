<?php
class PedidosController {
    private $conn;
    private $table = 'pedido';

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function buscarPedidos($status = '', $data_inicio = '', $data_fim = '', $mesa = '', $delivery = '', $pagina = 1, $por_pagina = 12) {
        try {
            $offset = ($pagina - 1) * $por_pagina;
            
            $query = "SELECT 
                p.idpedido,
                p.idmesa,
                p.cliente,
                p.delivery,
                p.data,
                p.hora_pedido,
                p.status,
                p.valor_total,
                p.observacao as observacao_pedido,
                p.usuario_id,
                i.quantidade,
                i.valor_unitario,
                i.valor_total as valor_item,
                i.observacao,
                i.tamanho,
                i.id as item_id,
                pr.nome as produto,
                m.id_mesa,
                GROUP_CONCAT(DISTINCT CASE WHEN pii.incluido = 0 THEN ing.nome END) as ingredientes_sem,
                GROUP_CONCAT(DISTINCT CASE WHEN pii.incluido = 1 THEN ing.nome END) as ingredientes_com
            FROM pedido p 
            LEFT JOIN mesas m ON p.idmesa = m.id_mesa 
            LEFT JOIN pedido_itens i ON p.idpedido = i.pedido_id
            LEFT JOIN produtos pr ON i.produto_id = pr.id
            LEFT JOIN pedido_item_ingredientes pii ON i.id = pii.pedido_item_id
            LEFT JOIN ingredientes ing ON pii.ingrediente_id = ing.id
            WHERE DATE(p.data) BETWEEN ? AND ?";
            
            $params = [$data_inicio ?: date('Y-m-d'), $data_fim ?: date('Y-m-d')];
            $types = "ss";
            
            if ($status) {
                $query .= " AND p.status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            if ($mesa) {
                $query .= " AND p.idmesa = ?";
                $params[] = $mesa;
                $types .= "i";
            }
            
            if ($delivery !== '') {
                $query .= " AND p.delivery = ?";
                $params[] = $delivery;
                $types .= "i";
            }
            
            $query .= " GROUP BY p.idpedido, i.id, p.idmesa, p.cliente, p.delivery, p.data, p.hora_pedido, p.status, p.valor_total, p.observacao, p.usuario_id, i.quantidade, i.valor_unitario, i.valor_total, i.observacao, i.tamanho, i.id, pr.nome, m.id_mesa ORDER BY p.data DESC, p.hora_pedido DESC LIMIT ? OFFSET ?";
            $params[] = $por_pagina;
            $params[] = $offset;
            $types .= "ii";
            
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . mysqli_error($this->conn));
            }
            
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Erro ao executar query: " . mysqli_stmt_error($stmt));
            }
            
            $result = mysqli_stmt_get_result($stmt);
            if (!$result) {
                throw new Exception("Erro ao obter resultado: " . mysqli_error($this->conn));
            }
            
            $pedidos = [];
            while ($row = mysqli_fetch_assoc($result)) {
                // Debug dos dados
                error_log("Dados do pedido " . $row['idpedido'] . ":");
                error_log("Ingredientes: " . print_r($row['ingredientes_sem'] . ';' . $row['ingredientes_com'], true));
                error_log("Observação do item: " . print_r($row['observacao'], true));
                error_log("Dados completos: " . print_r($row, true));
                
                $pedidos[] = $row;
            }
            
            mysqli_stmt_close($stmt);
            return $pedidos;
            
        } catch (Exception $e) {
            error_log("Erro ao buscar pedidos: " . $e->getMessage());
            throw $e;
        }
    }

    public function contarPedidos($status = '', $data_inicio = '', $data_fim = '', $mesa = '', $delivery = '') {
        try {
            $query = "SELECT COUNT(DISTINCT p.idpedido) as total 
                     FROM pedido p 
                     WHERE DATE(p.data) BETWEEN ? AND ?";
            
            $params = [$data_inicio ?: date('Y-m-d'), $data_fim ?: date('Y-m-d')];
            $types = "ss";
            
            if ($status) {
                $query .= " AND p.status = ?";
                $params[] = $status;
                $types .= "s";
            }
            
            if ($mesa) {
                $query .= " AND p.idmesa = ?";
                $params[] = $mesa;
                $types .= "i";
            }
            
            if ($delivery !== '') {
                $query .= " AND p.delivery = ?";
                $params[] = $delivery;
                $types .= "i";
            }
            
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                throw new Exception("Erro ao preparar query de contagem: " . mysqli_error($this->conn));
            }
            
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Erro ao executar query de contagem: " . mysqli_stmt_error($stmt));
            }
            
            $result = mysqli_stmt_get_result($stmt);
            if (!$result) {
                throw new Exception("Erro ao obter resultado da contagem: " . mysqli_error($this->conn));
            }
            
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            
            return isset($row['total']) ? (int)$row['total'] : 0;
            
        } catch (Exception $e) {
            error_log("Erro ao contar pedidos: " . $e->getMessage());
            throw $e;
        }
    }
}
?> 