<?php
class FinanceiroModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Métodos para Categorias
    public function getCategorias($tipo = null) {
        $sql = "SELECT * FROM categorias_financeiras";
        if ($tipo) {
            $sql .= " WHERE tipo = ?";
        }
        $sql .= " ORDER BY nome";
        
        $stmt = $this->conn->prepare($sql);
        if ($tipo) {
            $stmt->bind_param("s", $tipo);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function addCategoria($nome, $tipo, $descricao = null) {
        $sql = "INSERT INTO categorias_financeiras (nome, tipo, descricao) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $nome, $tipo, $descricao);
        return $stmt->execute();
    }

    // Métodos para Contas
    public function getContas() {
        $sql = "SELECT * FROM contas_financeiras ORDER BY nome";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function addConta($dados) {
        $sql = "INSERT INTO contas_financeiras (nome, tipo, saldo_inicial, saldo_atual, banco, agencia, conta) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssddsss", 
            $dados['nome'], 
            $dados['tipo'], 
            $dados['saldo_inicial'], 
            $dados['saldo_inicial'], 
            $dados['banco'], 
            $dados['agencia'], 
            $dados['conta']
        );
        return $stmt->execute();
    }

    public function updateSaldoConta($conta_id, $valor, $tipo) {
        $sql = "UPDATE contas_financeiras SET saldo_atual = saldo_atual " . 
               ($tipo == 'receita' ? '+' : '-') . " ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("di", $valor, $conta_id);
        return $stmt->execute();
    }

    // Métodos para Movimentações
    public function getMovimentacoes($filtros = []) {
        $sql = "SELECT m.*, c.nome as categoria_nome, co.nome as conta_nome 
                FROM movimentacoes_financeiras m 
                JOIN categorias_financeiras c ON m.categoria_id = c.id 
                JOIN contas_financeiras co ON m.conta_id = co.id 
                WHERE 1=1";
        
        $params = [];
        $types = "";

        if (!empty($filtros['id'])) {
            $sql .= " AND m.id = ?";
            $params[] = $filtros['id'];
            $types .= "i";
        }

        if (!empty($filtros['tipo'])) {
            $sql .= " AND m.tipo = ?";
            $params[] = $filtros['tipo'];
            $types .= "s";
        }

        if (!empty($filtros['data_inicio'])) {
            $sql .= " AND m.data_movimentacao >= ?";
            $params[] = $filtros['data_inicio'];
            $types .= "s";
        }

        if (!empty($filtros['data_fim'])) {
            $sql .= " AND m.data_movimentacao <= ?";
            $params[] = $filtros['data_fim'];
            $types .= "s";
        }

        if (!empty($filtros['status'])) {
            $sql .= " AND m.status = ?";
            $params[] = $filtros['status'];
            $types .= "s";
        }

        $sql .= " ORDER BY m.data_movimentacao DESC";

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function addMovimentacao($dados) {
        $this->conn->begin_transaction();
        try {
            $sql = "INSERT INTO movimentacoes_financeiras (tipo, categoria_id, conta_id, valor, data_movimentacao, 
                    descricao, status, forma_pagamento) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("siidssss", 
                $dados['tipo'],
                $dados['categoria_id'],
                $dados['conta_id'],
                $dados['valor'],
                $dados['data_movimentacao'],
                $dados['descricao'],
                $dados['status'],
                $dados['forma_pagamento']
            );
            $stmt->execute();
            $movimentacao_id = $this->conn->insert_id;

            if ($dados['status'] == 'pago') {
                $this->updateSaldoConta($dados['conta_id'], $dados['valor'], $dados['tipo']);
            }

            $this->conn->commit();
            return $movimentacao_id;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function updateMovimentacao($id, $dados) {
        $this->conn->begin_transaction();
        try {
            // Buscar dados da movimentação atual
            $sql = "SELECT tipo, conta_id, valor, status FROM movimentacoes_financeiras WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $movimentacao_atual = $result->fetch_assoc();

            // Atualizar movimentação
            $sql = "UPDATE movimentacoes_financeiras SET 
                    tipo = ?, 
                    categoria_id = ?, 
                    conta_id = ?, 
                    valor = ?, 
                    data_movimentacao = ?, 
                    descricao = ?, 
                    status = ?,
                    forma_pagamento = ?
                    WHERE id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("siidssssi", 
                $dados['tipo'],
                $dados['categoria_id'],
                $dados['conta_id'],
                $dados['valor'],
                $dados['data_movimentacao'],
                $dados['descricao'],
                $dados['status'],
                $dados['forma_pagamento'],
                $id
            );
            $stmt->execute();

            // Se a movimentação antiga estava paga, reverter o saldo
            if ($movimentacao_atual['status'] == 'pago') {
                $this->updateSaldoConta(
                    $movimentacao_atual['conta_id'], 
                    $movimentacao_atual['valor'], 
                    $movimentacao_atual['tipo'] == 'receita' ? 'despesa' : 'receita'
                );
            }

            // Se a nova movimentação está paga, atualizar o saldo
            if ($dados['status'] == 'pago') {
                $this->updateSaldoConta($dados['conta_id'], $dados['valor'], $dados['tipo']);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    // Métodos para Relatórios
    public function getResumoFinanceiro($data_inicio, $data_fim) {
        $sql = "SELECT 
                SUM(CASE WHEN tipo = 'receita' AND status = 'pago' THEN valor ELSE 0 END) as total_receitas,
                SUM(CASE WHEN tipo = 'despesa' AND status = 'pago' THEN valor ELSE 0 END) as total_despesas,
                COUNT(CASE WHEN tipo = 'receita' AND status = 'pendente' THEN 1 END) as receitas_pendentes,
                COUNT(CASE WHEN tipo = 'despesa' AND status = 'pendente' THEN 1 END) as despesas_pendentes,
                SUM(CASE WHEN tipo = 'receita' AND status = 'pendente' THEN valor ELSE 0 END) as valor_receitas_pendentes,
                SUM(CASE WHEN tipo = 'despesa' AND status = 'pendente' THEN valor ELSE 0 END) as valor_despesas_pendentes
                FROM movimentacoes_financeiras 
                WHERE data_movimentacao BETWEEN ? AND ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $data_inicio, $data_fim);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getMovimentacoesPorCategoria($data_inicio, $data_fim) {
        $sql = "SELECT c.nome as categoria, 
                SUM(CASE WHEN m.tipo = 'receita' AND m.status = 'pago' THEN m.valor ELSE 0 END) as receitas,
                SUM(CASE WHEN m.tipo = 'despesa' AND m.status = 'pago' THEN m.valor ELSE 0 END) as despesas
                FROM movimentacoes_financeiras m
                JOIN categorias_financeiras c ON m.categoria_id = c.id
                WHERE m.data_movimentacao BETWEEN ? AND ?
                GROUP BY c.id, c.nome
                ORDER BY c.nome";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $data_inicio, $data_fim);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getFluxoCaixa($data_inicio, $data_fim) {
        $sql = "SELECT 
                DATE(data_movimentacao) as data,
                SUM(CASE WHEN tipo = 'receita' AND status = 'pago' THEN valor ELSE 0 END) as receitas,
                SUM(CASE WHEN tipo = 'despesa' AND status = 'pago' THEN valor ELSE 0 END) as despesas
                FROM movimentacoes_financeiras
                WHERE data_movimentacao BETWEEN ? AND ?
                GROUP BY DATE(data_movimentacao)
                ORDER BY data_movimentacao";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $data_inicio, $data_fim);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
} 