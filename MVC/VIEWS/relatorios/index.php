<?php
error_log("Carregando view de relatórios");

// Check if user is logged in
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Divino Lanches</title>
    
    <!-- CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css" rel="stylesheet">
    
    <style>
        .card-dashboard {
            transition: transform 0.2s;
        }
        .card-dashboard:hover {
            transform: translateY(-5px);
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.3rem 0.5rem;
        }
        #salesChart {
            max-height: 220px !important;
            height: 220px !important;
        }
    </style>
</head>
<body>

<div class="container-fluid p-4">
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="view" value="relatorios">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days')); ?>">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                           value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); ?>">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All</option>
                        <option value="Pending" <?php echo isset($_GET['status']) && $_GET['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Completed" <?php echo isset($_GET['status']) && $_GET['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="Cancelled" <?php echo isset($_GET['status']) && $_GET['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card card-dashboard bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Orders</h6>
                    <h2 class="mb-0"><?php echo $summary['total_orders']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-dashboard bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Amount</h6>
                    <h2 class="mb-0">R$ <?php echo number_format($summary['total_amount'], 2, '.', ','); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-dashboard bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Delivery</h6>
                    <h2 class="mb-0"><?php echo $summary['total_delivery']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-dashboard bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Table Service</h6>
                    <h2 class="mb-0"><?php echo $summary['total_table']; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Daily Sales</h5>
            <canvas id="salesChart" style="max-height:220px;height:220px;"></canvas>
        </div>
    </div>

    <!-- Best Selling Products -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Best Selling Products</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bestSellingProducts as $product): ?>
                                <tr>
                                    <td><?php echo $product['name']; ?></td>
                                    <td><?php echo $product['total_quantity']; ?></td>
                                    <td>R$ <?php echo number_format($product['total_amount'], 2, '.', ','); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Order Status</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td>Completed</td>
                                    <td><span class="badge bg-success"><?php echo $summary['completed_orders']; ?></span></td>
                                </tr>
                                <tr>
                                    <td>Pending</td>
                                    <td><span class="badge bg-warning"><?php echo $summary['pending_orders']; ?></span></td>
                                </tr>
                                <tr>
                                    <td>Cancelled</td>
                                    <td><span class="badge bg-danger"><?php echo $summary['cancelled_orders']; ?></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title">Orders List</h5>
                <div>
                    <button class="btn btn-sm btn-outline-secondary" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Attendant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo $order['order_id']; ?></td>
                            <td><?php echo $order['customer']; ?></td>
                            <td><?php echo date('m/d/Y', strtotime($order['date'])); ?></td>
                            <td><?php echo $order['order_time']; ?></td>
                            <td>
                                <span class="badge status-badge bg-<?php 
                                    echo $order['status'] == 'Completed' ? 'success' : 
                                        ($order['status'] == 'Pending' ? 'warning' : 'danger'); 
                                ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </td>
                            <td><?php echo $order['delivery'] ? 'Delivery' : 'Table'; ?></td>
                            <td>R$ <?php echo number_format($order['total_amount'], 2, '.', ','); ?></td>
                            <td><?php echo $order['attendant']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Agrupamento por Mesa -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Pedidos por Mesa</h5>
            <?php 
            $total_geral = 0;
            $pedidos_por_mesa = [];
            foreach ($orders as $order) {
                $mesa = $order['table_number'] ?? 'Sem Mesa';
                if (!isset($pedidos_por_mesa[$mesa])) $pedidos_por_mesa[$mesa] = [];
                $pedidos_por_mesa[$mesa][] = $order;
                $total_geral += $order['total_amount'];
            }
            ?>
            <?php foreach ($pedidos_por_mesa as $mesa => $pedidos): ?>
                <div class="mb-3 p-2 border rounded bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>Mesa <?php echo htmlspecialchars($mesa); ?></strong>
                        <span class="fw-bold">Total: R$ <?php echo number_format(array_sum(array_column($pedidos, 'total_amount')), 2, ',', '.'); ?></span>
                    </div>
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Pedido</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                    <th>Hora</th>
                                    <th>Valor</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td>#<?php echo $pedido['order_id']; ?></td>
                                    <td><span class="badge status-badge bg-<?php 
                                        echo $pedido['status'] == 'Completed' ? 'success' : 
                                            ($pedido['status'] == 'Pending' ? 'warning' : ($pedido['status'] == 'Cancelled' ? 'danger' : 'info')); 
                                    ?>"><?php echo $pedido['status']; ?></span></td>
                                    <td><?php echo date('d/m/Y', strtotime($pedido['date'])); ?></td>
                                    <td><?php echo $pedido['order_time']; ?></td>
                                    <td>R$ <?php echo number_format($pedido['total_amount'], 2, ',', '.'); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="verPedido(<?php echo $pedido['order_id']; ?>)"><i class="fas fa-eye"></i> Ver Pedido</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="mt-3 text-end">
                <strong>Total Geral: R$ <?php echo number_format($total_geral, 2, ',', '.'); ?></strong>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Pedido -->
<div id="pedidoModal" class="pedido-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div class="pedido-modal-content" style="background:#fff;max-width:600px;width:90vw;margin:auto;position:relative;top:10vh;border-radius:8px;padding:20px;">
        <span class="close-modal" onclick="fecharModal()" style="position:absolute;right:20px;top:20px;font-size:24px;cursor:pointer;">&times;</span>
        <div id="pedidoConteudo"></div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>

<script>
// Debug dos dados do gráfico
console.log('Labels:', <?php echo json_encode($chartData['labels']); ?>);
console.log('Amounts:', <?php echo json_encode($chartData['amounts']); ?>);
console.log('Orders:', <?php echo json_encode($chartData['orders']); ?>);

// Chart configuration
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chartData['labels']); ?>,
        datasets: [{
            label: 'Total Amount (R$)',
            data: <?php echo json_encode($chartData['amounts']); ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1,
            yAxisID: 'y'
        }, {
            label: 'Number of Orders',
            data: <?php echo json_encode($chartData['orders']); ?>,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        stacked: false,
        plugins: {
            title: {
                display: true,
                text: 'Daily Sales and Orders'
            }
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Amount (R$)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Number of Orders'
                },
                grid: {
                    drawOnChartArea: false
                }
            }
        }
    }
});

// Export functions
function exportToPDF() {
    // Get current filter values
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    const status = document.querySelector('select[name="status"]').value;

    // Build URL with parameters
    let url = window.location.pathname + '?view=relatorios&action=exportToPDF';
    if (startDate) url += `&start_date=${startDate}`;
    if (endDate) url += `&end_date=${endDate}`;
    if (status) url += `&status=${status}`;

    // Open in new tab to prevent navigation issues
    window.open(url, '_blank');
}

function exportToExcel() {
    // Get current filter values
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    const status = document.querySelector('select[name="status"]').value;

    // Build URL with parameters
    let url = window.location.pathname + '?view=relatorios&action=exportToExcel';
    if (startDate) url += `&start_date=${startDate}`;
    if (endDate) url += `&end_date=${endDate}`;
    if (status) url += `&status=${status}`;

    // Open in new tab to prevent navigation issues
    window.open(url, '_blank');
}

function verPedido(pedidoId) {
    const modal = document.getElementById('pedidoModal');
    const conteudo = document.getElementById('pedidoConteudo');
    fetch(`MVC/MODEL/buscar_pedido.php?pedido_id=${pedidoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = `<div class='pedido-card expanded'><div class='pedido-header'><div><strong>Pedido #${pedidoId}</strong><div class='text-muted'>${data.pedido.data} ${data.pedido.hora_pedido}</div><div class='text-muted'>Mesa: ${data.pedido.idmesa}</div><div class='text-muted'>Status: ${data.pedido.status}</div></div></div><div class='pedido-content' style='display: block;'>`;
                data.itens.forEach(item => {
                    let nomeProduto = item.produto;
                    if (item.tamanho && item.tamanho.toLowerCase() === 'mini' && !nomeProduto.toLowerCase().startsWith('mini ')) {
                        nomeProduto = 'Mini ' + nomeProduto;
                    }
                    html += `<div class='item-pedido'><div class='produto-linha'><div class='produto-info'><span class='produto-nome'>${item.quantidade}x ${nomeProduto}</span></div><div>R$ ${parseFloat(item.valor_total || (item.quantidade * item.valor_unitario)).toFixed(2)}</div></div><div class='detalhes-pedido'>`;
                    if (item.ingredientes_sem) {
                        const ingredientesSem = typeof item.ingredientes_sem === 'string' ? item.ingredientes_sem.split(',').map(i => i.trim()).filter(i => i) : Array.isArray(item.ingredientes_sem) ? item.ingredientes_sem : [];
                        if (ingredientesSem.length > 0) {
                            html += `<div class='ingredientes-container'><div class='ingredientes text-danger'><i class='fas fa-minus-circle'></i><span>SEM: ${ingredientesSem.join(', ')}</span></div></div>`;
                        }
                    }
                    if (item.ingredientes_com) {
                        const ingredientesCom = typeof item.ingredientes_com === 'string' ? item.ingredientes_com.split(',').map(i => i.trim()).filter(i => i) : Array.isArray(item.ingredientes_com) ? item.ingredientes_com : [];
                        if (ingredientesCom.length > 0) {
                            html += `<div class='ingredientes-container'><div class='ingredientes text-success'><i class='fas fa-plus-circle'></i><span>COM: ${ingredientesCom.join(', ')}</span></div></div>`;
                        }
                    }
                    if (item.observacao && item.observacao.trim()) {
                        html += `<div class='observacao'><i class='fas fa-info-circle'></i><span>Observação: ${item.observacao}</span></div>`;
                    }
                    html += `</div></div>`;
                });
                html += `<div class='valor-total'>Total: R$ ${parseFloat(data.pedido.valor_total).toFixed(2)}</div>`;
                html += `<div class='mt-3 text-end'>
                    <button class='btn btn-secondary btn-sm me-2' onclick='imprimirPedido(${pedidoId})'><i class='fas fa-print'></i> Imprimir Pedido</button>
                    <button class='btn btn-danger btn-sm' onclick='excluirPedido(${pedidoId})'><i class='fas fa-trash'></i> Excluir Pedido</button>
                </div>`;
                html += `</div></div>`;
                conteudo.innerHTML = html;
                modal.style.display = 'flex';
            } else {
                alert('Erro ao carregar pedido');
            }
        })
        .catch(() => alert('Erro ao carregar pedido.'));
}

function imprimirPedido(pedidoId) {
    window.open(`MVC/VIEWS/imprimir_pedido.php?pedido_id=${pedidoId}`, '_blank');
}

function excluirPedido(pedidoId) {
    if (!confirm('Tem certeza que deseja excluir este pedido? Esta ação não pode ser desfeita.')) return;
    fetch('MVC/CONTROLLER/excluir_pedido.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: `idpedido=${pedidoId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            fecharModal();
            location.reload();
        } else {
            alert('Erro ao excluir pedido: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(() => alert('Erro ao excluir pedido.'));
}

function fecharModal() {
    document.getElementById('pedidoModal').style.display = 'none';
}
</script>

</body>
</html> 