<?php
include_once(__DIR__ . '/../MODEL/conexao.php');
$status_options = ['Todos', 'Pendente', 'Em Preparo', 'Saiu para Entrega', 'Entregue', 'Cancelado'];
$selected_status = isset($_GET['status']) ? $_GET['status'] : 'Todos';
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 text-end">
            <a href="?view=gerar_pedido_delivery" class="btn btn-success">
                <i class="fas fa-plus-circle"></i> Novo Pedido Delivery
            </a>
        </div>
    </div>
    <form method="get" action="" class="row g-2 align-items-center mb-3" onsubmit="this.action='?view=delivery&status='+document.getElementById('status').value;">
        <input type="hidden" name="view" value="delivery">
        <div class="col-auto">
            <label for="status" class="form-label mb-0">Filtrar por status:</label>
        </div>
        <div class="col-auto">
            <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                <?php foreach ($status_options as $opt): ?>
                    <option value="<?php echo $opt; ?>" <?php if ($selected_status == $opt) echo 'selected'; ?>><?php echo $opt; ?></option>
                <?php endforeach; ?>
            </select>
</div>
    </form>
    <div id="delivery-list">
<?php
        $where = "(p.tipo = 'delivery' OR p.endereco_entrega IS NOT NULL)";
        if ($selected_status && $selected_status != 'Todos') {
            $where .= " AND p.status = '" . mysqli_real_escape_string($conn, $selected_status) . "'";
        }
        $sql = "SELECT p.*, c.nome as cliente_nome, c.tel1 as cliente_tel, c.endereco as cliente_endereco, c.bairro as cliente_bairro, c.cidade as cliente_cidade, c.estado as cliente_estado
                FROM pedido p
                LEFT JOIN clientes c ON p.cliente_id = c.id
                WHERE $where
                ORDER BY p.data DESC, p.hora_pedido DESC";
        $result = mysqli_query($conn, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            echo '<div class="container-fluid mb-4"><h3>Pedidos Delivery</h3>';
            echo '<div class="row">';
            while ($pedido = mysqli_fetch_assoc($result)) {
                echo '<div class="col-md-6 col-lg-4 mb-3">';
                echo '<div class="card shadow">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">Pedido #'.$pedido['idpedido'].' <span class="badge bg-info">'.htmlspecialchars($pedido['status']).'</span>';
                echo ' <button class="btn btn-secondary btn-sm ms-2" onclick="imprimirPedidoDelivery('.$pedido['idpedido'].')"><i class="fas fa-print"></i></button>';
                echo '</h5>';
                echo '<p><strong>Cliente:</strong> '.htmlspecialchars($pedido['cliente_nome'] ?? $pedido['nome_cliente'] ?? '-').'</p>';
                echo '<p><strong>Telefone:</strong> '.htmlspecialchars($pedido['cliente_tel'] ?? $pedido['telefone'] ?? '-').'</p>';
                echo '<p><strong>Endereço:</strong> '.htmlspecialchars($pedido['endereco_entrega'] ?? $pedido['cliente_endereco'] ?? '-');
                if (!empty($pedido['cliente_bairro'])) echo ', Bairro: '.htmlspecialchars($pedido['cliente_bairro']);
                if (!empty($pedido['cliente_cidade'])) echo ', '.htmlspecialchars($pedido['cliente_cidade']);
                if (!empty($pedido['cliente_estado'])) echo ' - '.htmlspecialchars($pedido['cliente_estado']);
                echo '</p>';
                if (!empty($pedido['ponto_referencia'])) echo '<p><strong>Referência:</strong> '.htmlspecialchars($pedido['ponto_referencia']).'</p>';
                if (!empty($pedido['taxa_entrega'])) echo '<p><strong>Taxa de Entrega:</strong> R$ '.number_format($pedido['taxa_entrega'],2,',','.').'</p>';
                if (!empty($pedido['forma_pagamento'])) echo '<p><strong>Pagamento:</strong> '.htmlspecialchars($pedido['forma_pagamento']);
                if (!empty($pedido['troco_para'])) echo ' (Troco para R$ '.number_format($pedido['troco_para'],2,',','.').')';
                echo '</p>';
                echo '<p><strong>Valor Total:</strong> R$ '.number_format($pedido['valor_total'],2,',','.').'</p>';
                echo '<p><strong>Data/Hora:</strong> '.htmlspecialchars($pedido['data']).' '.htmlspecialchars($pedido['hora_pedido']).'</p>';
                if ($pedido['status'] != 'Entregue' && $pedido['status'] != 'Finalizado' && $pedido['status'] != 'Cancelado') {
                    echo '<button class="btn btn-success btn-sm mt-2 w-100" onclick="marcarEntregue('.$pedido['idpedido'].')"><i class="fas fa-check"></i> Marcar como Entregue</button>';
                }
                echo '</div></div></div>';
            }
            echo '</div></div>';
        } else {
            echo '<div class="container-fluid mb-4"><h5>Nenhum pedido de delivery encontrado.</h5></div>';
        }
        ?>
	</div>
</div>
<script>
function marcarEntregue(pedidoId) {
    if (!confirm('Marcar pedido como Entregue?')) return;
    fetch('MVC/MODEL/atualizar_status_pedido.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `pedido_id=${pedidoId}&status=Entregue`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
} else {
            alert('Erro ao atualizar status: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(() => alert('Erro ao atualizar status.'));
}
// Função para imprimir pedido delivery
function imprimirPedidoDelivery(pedidoId) {
    window.open('MVC/VIEWS/imprimir_pedido_delivery.php?pedido_id=' + pedidoId, '_blank');
}
</script>
