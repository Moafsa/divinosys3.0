<?php
$resumo = $data['resumo'];
$movimentacoes_por_categoria = $data['movimentacoes_por_categoria'];
$fluxo_caixa = $data['fluxo_caixa'];
$data_inicio = $data['data_inicio'];
$data_fim = $data['data_fim'];
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Relatórios Financeiros</h1>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="form-inline">
                <input type="hidden" name="view" value="financeiro">
                <input type="hidden" name="action" value="relatorios">
                
                <input type="date" class="form-control mr-2" name="data_inicio" value="<?php echo $data_inicio; ?>" placeholder="Data Início">
                <input type="date" class="form-control mr-2" name="data_fim" value="<?php echo $data_fim; ?>" placeholder="Data Fim">

                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>
        </div>
    </div>

    <!-- Resumo -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Receitas (Pagas)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                R$ <?php echo number_format($resumo['receitas_pagas'], 2, ',', '.'); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Despesas (Pagas)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                R$ <?php echo number_format($resumo['despesas_pagas'], 2, ',', '.'); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Receitas (Pendentes)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                R$ <?php echo number_format($resumo['receitas_pendentes'], 2, ',', '.'); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Despesas (Pendentes)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                R$ <?php echo number_format($resumo['despesas_pendentes'], 2, ',', '.'); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <!-- Gráfico de Receitas x Despesas -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Receitas x Despesas</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar">
                        <canvas id="receitasDespesasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Categorias -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Categorias</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4">
                        <canvas id="categoriasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Movimentações por Categoria -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Movimentações por Categoria</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Tipo</th>
                            <th>Valor Total</th>
                            <th>Quantidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movimentacoes_por_categoria as $mov): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($mov['categoria_nome']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $mov['tipo'] == 'receita' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($mov['tipo']); ?>
                                </span>
                            </td>
                            <td class="text-right">
                                R$ <?php echo number_format($mov['valor_total'], 2, ',', '.'); ?>
                            </td>
                            <td class="text-center"><?php echo $mov['quantidade']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabela de Fluxo de Caixa -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Fluxo de Caixa</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Receitas</th>
                            <th>Despesas</th>
                            <th>Saldo do Dia</th>
                            <th>Saldo Acumulado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fluxo_caixa as $fluxo): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($fluxo['data'])); ?></td>
                            <td class="text-success text-right">
                                R$ <?php echo number_format($fluxo['receitas'], 2, ',', '.'); ?>
                            </td>
                            <td class="text-danger text-right">
                                R$ <?php echo number_format($fluxo['despesas'], 2, ',', '.'); ?>
                            </td>
                            <td class="text-right <?php echo $fluxo['saldo_dia'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                R$ <?php echo number_format($fluxo['saldo_dia'], 2, ',', '.'); ?>
                            </td>
                            <td class="text-right <?php echo $fluxo['saldo_acumulado'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                R$ <?php echo number_format($fluxo['saldo_acumulado'], 2, ',', '.'); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Gráfico de Receitas x Despesas
var ctx = document.getElementById('receitasDespesasChart').getContext('2d');
var receitasDespesasChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($fluxo_caixa, 'data')); ?>,
        datasets: [{
            label: 'Receitas',
            data: <?php echo json_encode(array_column($fluxo_caixa, 'receitas')); ?>,
            backgroundColor: 'rgba(40, 167, 69, 0.2)',
            borderColor: 'rgba(40, 167, 69, 1)',
            borderWidth: 1
        }, {
            label: 'Despesas',
            data: <?php echo json_encode(array_column($fluxo_caixa, 'despesas')); ?>,
            backgroundColor: 'rgba(220, 53, 69, 0.2)',
            borderColor: 'rgba(220, 53, 69, 1)',
            borderWidth: 1
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

// Gráfico de Categorias
var ctx2 = document.getElementById('categoriasChart').getContext('2d');
var categoriasChart = new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($movimentacoes_por_categoria, 'categoria_nome')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($movimentacoes_por_categoria, 'valor_total')); ?>,
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(23, 162, 184, 0.8)',
                'rgba(0, 123, 255, 0.8)',
                'rgba(111, 66, 193, 0.8)',
                'rgba(102, 16, 242, 0.8)',
                'rgba(214, 51, 108, 0.8)'
            ]
        }]
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
</script> 