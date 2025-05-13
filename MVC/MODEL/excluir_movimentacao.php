<?php
require_once 'conexao.php';
require_once 'FinanceiroModel.php';
header('Content-Type: application/json');

try {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit;
    }

    $conn->begin_transaction();

    // Buscar informações da movimentação antes de excluir
    $sql_mov = "SELECT tipo, conta_id, valor, status FROM movimentacoes_financeiras WHERE id = ?";
    $stmt_mov = $conn->prepare($sql_mov);
    $stmt_mov->bind_param('i', $id);
    $stmt_mov->execute();
    $movimentacao = $stmt_mov->get_result()->fetch_assoc();

    // Excluir parcelas vinculadas
    $sql_parcelas = 'DELETE FROM parcelas_financeiras WHERE movimentacao_id = ?';
    $stmt_parcelas = $conn->prepare($sql_parcelas);
    if (!$stmt_parcelas) {
        throw new Exception('Erro ao preparar exclusão de parcelas: ' . $conn->error);
    }
    $stmt_parcelas->bind_param('i', $id);
    if (!$stmt_parcelas->execute()) {
        throw new Exception('Erro ao excluir parcelas: ' . $stmt_parcelas->error);
    }

    // Agora excluir a movimentação
    $sql = 'DELETE FROM movimentacoes_financeiras WHERE id = ?';
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Erro ao preparar statement: ' . $conn->error);
    }
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        throw new Exception('Erro ao excluir movimentação: ' . $stmt->error);
    }

    // Se a movimentação estava paga, reverter o saldo da conta
    if ($movimentacao && $movimentacao['status'] == 'pago') {
        $model = new FinanceiroModel($conn);
        // Inverte o tipo para reverter o saldo (se era receita vira despesa e vice-versa)
        $tipo_reverso = $movimentacao['tipo'] == 'receita' ? 'despesa' : 'receita';
        if (!$model->updateSaldoConta($movimentacao['conta_id'], $movimentacao['valor'], $tipo_reverso)) {
            throw new Exception('Erro ao atualizar saldo da conta');
        }
    }

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    error_log('Exceção ao excluir movimentação: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
} 