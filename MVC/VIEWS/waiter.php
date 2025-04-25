<?php
// Verificar se o usuário está logado e tem permissão de garçom
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'waiter') {
    header('Location: ' . url('index.php'));
    exit;
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Painel do Garçom</h1>
    </div>

    <div class="row">
        <!-- Mesas Ativas -->
        <div class="col-12 col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Mesas Ativas</h6>
                </div>
                <div class="card-body">
                    <div class="row" id="activeTables">
                        <!-- As mesas serão carregadas via AJAX -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Pedidos Prontos -->
        <div class="col-12 col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Pedidos Prontos para Entrega</h6>
                </div>
                <div class="card-body">
                    <div class="list-group" id="readyOrders">
                        <!-- Os pedidos prontos serão carregados via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Novo Pedido -->
    <div class="modal fade" id="newOrderModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Pedido</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="newOrderForm">
                        <input type="hidden" id="tableNumber" name="table_number">
                        <div class="form-group">
                            <label>Produtos</label>
                            <select class="form-control" id="products" multiple>
                                <!-- Produtos serão carregados via AJAX -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Observações</label>
                            <textarea class="form-control" name="observations"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="submitOrder()">Enviar Pedido</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Atualizar mesas e pedidos a cada 30 segundos
function updateTables() {
    $.ajax({
        url: '<?php echo url("MVC/CONTROLLER/get_active_tables.php"); ?>',
        type: 'GET',
        success: function(response) {
            $('#activeTables').html(response);
        }
    });
}

function updateReadyOrders() {
    $.ajax({
        url: '<?php echo url("MVC/CONTROLLER/get_ready_orders.php"); ?>',
        type: 'GET',
        success: function(response) {
            $('#readyOrders').html(response);
        }
    });
}

// Carregar produtos no modal
function loadProducts() {
    $.ajax({
        url: '<?php echo url("MVC/CONTROLLER/get_products.php"); ?>',
        type: 'GET',
        success: function(response) {
            $('#products').html(response);
        }
    });
}

// Abrir modal de novo pedido
function openNewOrder(tableNumber) {
    $('#tableNumber').val(tableNumber);
    loadProducts();
    $('#newOrderModal').modal('show');
}

// Enviar novo pedido
function submitOrder() {
    $.ajax({
        url: '<?php echo url("MVC/CONTROLLER/submit_order.php"); ?>',
        type: 'POST',
        data: $('#newOrderForm').serialize(),
        success: function(response) {
            $('#newOrderModal').modal('hide');
            updateTables();
            updateReadyOrders();
        }
    });
}

// Marcar pedido como entregue
function markAsDelivered(orderId) {
    $.ajax({
        url: '<?php echo url("MVC/CONTROLLER/mark_order_delivered.php"); ?>',
        type: 'POST',
        data: { order_id: orderId },
        success: function(response) {
            updateReadyOrders();
        }
    });
}

// Iniciar atualizações automáticas
$(document).ready(function() {
    updateTables();
    updateReadyOrders();
    setInterval(function() {
        updateTables();
        updateReadyOrders();
    }, 30000);
});
</script> 