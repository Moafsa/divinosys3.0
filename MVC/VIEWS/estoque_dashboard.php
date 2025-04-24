<?php
require_once 'MVC/COMMON/header.php';
require_once 'MVC/MODEL/conexao.php';

// Get stock data
$sql = "SELECT p.nome, p.preco_normal, e.estoque_atual, e.estoque_minimo, e.preco_custo, e.marca,
               c.nome as categoria_nome
        FROM produtos p 
        LEFT JOIN estoque e ON p.id = e.produto_id
        LEFT JOIN categorias c ON p.categoria_id = c.id
        ORDER BY p.nome";
$result = mysqli_query($conn, $sql);

$produtos = array();
$labels = array();
$estoque_atual = array();
$estoque_minimo = array();
$produtos_baixo_estoque = array();

while ($row = mysqli_fetch_assoc($result)) {
    $produtos[] = $row;
    
    // Data for charts
    $labels[] = $row['nome'];
    $estoque_atual[] = (int)$row['estoque_atual'];
    $estoque_minimo[] = (int)$row['estoque_minimo'];
    
    // Check for low stock
    if ($row['estoque_atual'] <= $row['estoque_minimo']) {
        $produtos_baixo_estoque[] = $row;
    }
}

// Helper function to safely escape HTML
function escape($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard de Estoque</h1>
        <div>
            <button class="btn btn-success" id="exportLowStock">
                <i class="fas fa-file-excel"></i> Exportar Baixo Estoque
            </button>
        </div>
    </div>

    <!-- Alert for low stock -->
    <?php if (count($produtos_baixo_estoque) > 0): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        Existem <?php echo count($produtos_baixo_estoque); ?> produtos com estoque baixo!
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Stock Level Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Níveis de Estoque</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="stockLevelsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Status Pie Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status do Estoque</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4">
                        <canvas id="stockStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Produtos com Estoque Baixo</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="lowStockTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Categoria</th>
                            <th>Marca</th>
                            <th>Estoque Atual</th>
                            <th>Estoque Mínimo</th>
                            <th>Preço de Custo</th>
                            <th>Preço de Venda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos_baixo_estoque as $produto): ?>
                            <tr>
                                <td><?php echo escape($produto['nome']); ?></td>
                                <td><?php echo escape($produto['categoria_nome']); ?></td>
                                <td><?php echo escape($produto['marca']); ?></td>
                                <td class="text-danger"><?php echo (int)$produto['estoque_atual']; ?></td>
                                <td><?php echo (int)$produto['estoque_minimo']; ?></td>
                                <td>R$ <?php echo number_format((float)$produto['preco_custo'], 2, ',', '.'); ?></td>
                                <td>R$ <?php echo number_format((float)$produto['preco_normal'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- DataTables JavaScript -->
<script src="MVC/COMMON/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="MVC/COMMON/vendor/datatables/dataTables.bootstrap4.min.js"></script>
<!-- SheetJS (XLSX) -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#lowStockTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json"
        }
    });

    // Bar Chart - Stock Levels
    var ctx = document.getElementById("stockLevelsChart");
    var stockLevelsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: "Estoque Atual",
                backgroundColor: "rgba(78, 115, 223, 0.8)",
                data: <?php echo json_encode($estoque_atual); ?>
            },
            {
                label: "Estoque Mínimo",
                backgroundColor: "rgba(231, 74, 59, 0.8)",
                data: <?php echo json_encode($estoque_minimo); ?>
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Pie Chart - Stock Status
    var ctxPie = document.getElementById("stockStatusChart");
    var stockStatusChart = new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: ["Estoque Normal", "Estoque Baixo"],
            datasets: [{
                data: [
                    <?php 
                    echo count($produtos) - count($produtos_baixo_estoque) . ', ' . 
                         count($produtos_baixo_estoque);
                    ?>
                ],
                backgroundColor: ['#1cc88a', '#e74a3b'],
                hoverBackgroundColor: ['#17a673', '#be2617'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Export to Excel
    $('#exportLowStock').click(function() {
        var data = [];
        
        // Add header
        data.push([
            'Produto',
            'Categoria',
            'Marca',
            'Estoque Atual',
            'Estoque Mínimo',
            'Preço de Custo',
            'Preço de Venda'
        ]);
        
        // Add data
        <?php foreach ($produtos_baixo_estoque as $produto): ?>
        data.push([
            '<?php echo addslashes($produto['nome']); ?>',
            '<?php echo addslashes($produto['categoria_nome']); ?>',
            '<?php echo addslashes($produto['marca']); ?>',
            <?php echo (int)$produto['estoque_atual']; ?>,
            <?php echo (int)$produto['estoque_minimo']; ?>,
            <?php echo (float)$produto['preco_custo']; ?>,
            <?php echo (float)$produto['preco_normal']; ?>
        ]);
        <?php endforeach; ?>
        
        // Create workbook
        var ws = XLSX.utils.aoa_to_sheet(data);
        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Estoque Baixo");
        
        // Generate file
        XLSX.writeFile(wb, 'estoque_baixo.xlsx');
    });
});
</script>

<?php require_once 'MVC/COMMON/footer.php'; ?> 