<?php
class RelatoriosModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getOrdersByPeriod($start_date, $end_date, $status = null) {
        $sql = "SELECT 
                    p.idpedido as order_id,
                    p.cliente as customer,
                    p.data as date,
                    p.hora_pedido as order_time,
                    CASE 
                        WHEN p.status = 'Finalizado' THEN 'Completed'
                        WHEN p.status = 'Pendente' THEN 'Pending'
                        WHEN p.status = 'Cancelado' THEN 'Cancelled'
                        ELSE p.status
                    END as status,
                    p.valor_total as total_amount,
                    p.tipo as type,
                    p.delivery,
                    u.login as attendant
                FROM pedido p
                LEFT JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.data BETWEEN ? AND ?";
        
        if ($status) {
            $sql .= " AND CASE 
                        WHEN p.status = 'Finalizado' THEN 'Completed'
                        WHEN p.status = 'Pendente' THEN 'Pending'
                        WHEN p.status = 'Cancelado' THEN 'Cancelled'
                        ELSE p.status
                    END = ?";
        }
        
        $sql .= " ORDER BY p.data DESC, p.hora_pedido DESC";
        
        $stmt = $this->conn->prepare($sql);
        
        if ($status) {
            $stmt->bind_param("sss", $start_date, $end_date, $status);
        } else {
            $stmt->bind_param("ss", $start_date, $end_date);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getSalesSummary($start_date, $end_date) {
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN status = 'Finalizado' THEN 1 ELSE 0 END) as completed_orders,
                    SUM(CASE WHEN status = 'Cancelado' THEN 1 ELSE 0 END) as cancelled_orders,
                    SUM(CASE WHEN status = 'Pendente' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(valor_total) as total_amount,
                    SUM(CASE WHEN delivery = 1 THEN 1 ELSE 0 END) as total_delivery,
                    SUM(CASE WHEN tipo = 'mesa' THEN 1 ELSE 0 END) as total_table
                FROM pedido
                WHERE data BETWEEN ? AND ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getBestSellingProducts($start_date, $end_date, $limit = 10) {
        $sql = "SELECT 
                    p.nome as name,
                    SUM(pi.quantidade) as total_quantity,
                    SUM(pi.valor_total) as total_amount
                FROM pedido_itens pi
                JOIN produtos p ON pi.produto_id = p.id
                JOIN pedido pe ON pi.pedido_id = pe.idpedido
                WHERE pe.data BETWEEN ? AND ?
                    AND pe.status = 'Finalizado'
                GROUP BY p.id
                ORDER BY total_quantity DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $start_date, $end_date, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getDailySales($start_date, $end_date) {
        $sql = "SELECT 
                    data as date,
                    COUNT(*) as total_orders,
                    COALESCE(SUM(valor_total), 0) as total_amount
                FROM pedido
                WHERE data BETWEEN ? AND ?
                GROUP BY data
                ORDER BY data ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Preencher dias faltantes com zeros
        $allDates = [];
        $current = new DateTime($start_date);
        $end = new DateTime($end_date);
        
        while ($current <= $end) {
            $currentDate = $current->format('Y-m-d');
            $allDates[$currentDate] = [
                'date' => $currentDate,
                'total_orders' => 0,
                'total_amount' => 0
            ];
            $current->modify('+1 day');
        }
        
        // Preencher com dados reais onde existem
        foreach ($result as $row) {
            $allDates[$row['date']] = $row;
        }
        
        // Ordenar por data
        ksort($allDates);
        
        return array_values($allDates);
    }
} 