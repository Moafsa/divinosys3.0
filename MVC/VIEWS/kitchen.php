<?php
// Verificar se o usuário está logado e tem permissão de cozinha
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'kitchen') {
    header('Location: ' . url('index.php'));
    exit;
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Painel da Cozinha</h1>
    </div>

    <div class="row">
        <!-- Pedidos Pendentes -->
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Pedidos Pendentes</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="pendingOrders">
                            <thead>
                                <tr>
                                    <th>Mesa/Delivery</th>
                                    <th>Pedido</th>
                                    <th>Horário</th>
                                    <th>Observações</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Os pedidos serão carregados via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Atualizar pedidos a cada 30 segundos
function updateOrders() {
    $.ajax({
        url: '<?php echo url("MVC/CONTROLLER/get_pending_orders.php"); ?>',
        type: 'GET',
        success: function(response) {
            $('#pendingOrders tbody').html(response);
        }
    });
}

// Iniciar atualização automática
$(document).ready(function() {
    updateOrders();
    setInterval(updateOrders, 30000);
});

// Função para atualizar status do pedido
function updateOrderStatus(orderId, status) {
    $.ajax({
        url: '<?php echo url("MVC/CONTROLLER/update_order_status.php"); ?>',
        type: 'POST',
        data: {
            order_id: orderId,
            status: status
        },
        success: function(response) {
            updateOrders();
        }
    });
}
</script> 