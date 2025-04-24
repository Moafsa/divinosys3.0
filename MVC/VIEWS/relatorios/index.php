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
            <canvas id="salesChart"></canvas>
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
</script>

</body>
</html> 