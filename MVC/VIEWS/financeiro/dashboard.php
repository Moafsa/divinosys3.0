<?php
global $conn;
if (!isset($conn) || !$conn) {
    require_once __DIR__ . '/../../MODEL/config.php';
    require_once __DIR__ . '/../../MODEL/conexao.php';
}
require_once __DIR__ . '/../../MODEL/FinanceiroModel.php';
$model = new FinanceiroModel($conn);
$categorias = $model->getCategorias();
$contas = $model->getContas();
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard Financeiro</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#movimentacaoModal">
            <i class="fas fa-plus"></i> Nova Movimentação
        </button>
        <div class="d-flex">
            <form class="form-inline mr-2">
                <input type="hidden" name="view" value="financeiro">
                <input type="date" class="form-control mr-2" name="data_inicio" value="<?php echo $data_inicio; ?>">
                <input type="date" class="form-control mr-2" name="data_fim" value="<?php echo $data_fim; ?>">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </form>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Receitas Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Receitas (Pago)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">R$ <?php echo number_format((float)($resumo['total_receitas'] ?? 0), 2, ',', '.'); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Despesas Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Despesas (Pago)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">R$ <?php echo number_format((float)($resumo['total_despesas'] ?? 0), 2, ',', '.'); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Receitas Pendentes Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Receitas Pendentes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">R$ <?php echo number_format((float)($resumo['valor_receitas_pendentes'] ?? 0), 2, ',', '.'); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Despesas Pendentes Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Despesas Pendentes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">R$ <?php echo number_format((float)($resumo['valor_despesas_pendentes'] ?? 0), 2, ',', '.'); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Gráfico de Receitas x Despesas -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Receitas x Despesas</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="receitasDespesasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Categorias -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Categorias</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="categoriasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Últimas Movimentações -->
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Últimas Movimentações</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Descrição</th>
                                    <th>Categoria</th>
                                    <th>Conta</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Imagens</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movimentacoes as $mov): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($mov['data_movimentacao'])); ?></td>
                                    <td><?php echo htmlspecialchars($mov['descricao']); ?></td>
                                    <td><?php echo htmlspecialchars($mov['categoria_nome']); ?></td>
                                    <td><?php echo htmlspecialchars($mov['conta_nome']); ?></td>
                                    <td class="<?php echo $mov['tipo'] == 'receita' ? 'text-success' : 'text-danger'; ?>">
                                        R$ <?php echo number_format($mov['valor'], 2, ',', '.'); ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $mov['status'] == 'pago' ? 'success' : 
                                                ($mov['status'] == 'pendente' ? 'warning' : 'danger'); 
                                        ?>">
                                            <?php echo ucfirst($mov['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        // Buscar também o id da imagem para exclusão
                                        $sql = "SELECT id, caminho FROM imagens_movimentacoes WHERE movimentacao_id = ?";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("i", $mov['id']);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        while ($img = $result->fetch_assoc()):
                                        ?>
                                            <span class="img-thumb-wrapper" style="position:relative; display:inline-block;">
                                                <a href="<?php echo $img['caminho']; ?>" target="_blank">
                                                    <img src="<?php echo $img['caminho']; ?>" style="max-width:40px; max-height:40px; margin:2px; border-radius:4px;">
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger btn-excluir-img" data-img-id="<?php echo $img['id']; ?>" style="position:absolute;top:0;right:0;padding:0 4px;line-height:1;">&times;</button>
                                            </span>
                                        <?php endwhile; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm" onclick="editarMovimentacao(<?php echo $mov['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="excluirMovimentacao(<?php echo $mov['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page level plugins -->
<script src="vendor/chart.js/Chart.min.js"></script>

<script>
// Gráfico de Receitas x Despesas
var ctx = document.getElementById('receitasDespesasChart').getContext('2d');
var receitasDespesasChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Receitas', 'Despesas'],
        datasets: [{
            label: 'Valor (R$)',
            data: [
                <?php echo $resumo['total_receitas']; ?>,
                <?php echo $resumo['total_despesas']; ?>
            ],
            backgroundColor: [
                'rgba(40, 167, 69, 0.2)',
                'rgba(220, 53, 69, 0.2)'
            ],
            borderColor: [
                'rgba(40, 167, 69, 1)',
                'rgba(220, 53, 69, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        maintainAspectRatio: false,
        layout: {
            padding: {
                left: 10,
                right: 25,
                top: 25,
                bottom: 0
            }
        },
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
        labels: ['Receitas', 'Despesas'],
        datasets: [{
            data: [
                <?php echo $resumo['total_receitas']; ?>,
                <?php echo $resumo['total_despesas']; ?>
            ],
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(220, 53, 69, 0.8)'
            ],
            hoverBackgroundColor: [
                'rgba(40, 167, 69, 1)',
                'rgba(220, 53, 69, 1)'
            ],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }]
    },
    options: {
        maintainAspectRatio: false,
        tooltips: {
            backgroundColor: "rgb(255,255,255)",
            bodyFontColor: "#858796",
            borderColor: '#dddfeb',
            borderWidth: 1,
            xPadding: 15,
            yPadding: 15,
            displayColors: false,
            caretPadding: 10,
        },
        legend: {
            display: true
        },
        cutoutPercentage: 80,
    }
});

function editarMovimentacao(id) {
    $.get('MVC/MODEL/get_movimentacao.php', {id: id}, function(resp) {
        if (resp.success) {
            const mov = resp.data;
            $('#movimentacaoModal input[name="valor"]').val(mov.valor);
            $('#movimentacaoModal input[name="descricao"]').val(mov.descricao);
            $('#movimentacaoModal input[name="data_movimentacao"]').val(mov.data_movimentacao);
            atualizarCategoriasModal(mov.tipo, mov.categoria_id);
            $('#movimentacaoModal select[name="conta_id"]').val(mov.conta_id);
            $('#movimentacaoModal select[name="status"]').val(mov.status);
            $('#movimentacaoModal select[name="forma_pagamento"]').val(mov.forma_pagamento);
            if ($('#movimentacaoModal input[name="id"]').length === 0) {
                $('#movimentacaoModal form').append('<input type="hidden" name="id" value="'+mov.id+'">');
            } else {
                $('#movimentacaoModal input[name="id"]').val(mov.id);
            }
            $('#movimentacaoModal').modal('show');
        } else {
            alert('Erro ao buscar movimentação: ' + resp.message);
        }
    }, 'json');
}

function excluirMovimentacao(id) {
    if (confirm('Tem certeza que deseja excluir esta movimentação?')) {
        $.post('MVC/MODEL/excluir_movimentacao.php', {id: id}, function(resp) {
            if (resp.success) {
                // Mostra alerta de sucesso e remove a linha da tabela
                $('<div class="alert alert-success text-center">Movimentação excluída com sucesso!</div>')
                    .insertBefore('.container-fluid').delay(1500).fadeOut(500, function() { location.reload(); });
                $('button[onclick="excluirMovimentacao(' + id + ')"]').closest('tr').fadeOut();
            } else {
                $('<div class="alert alert-danger text-center">Erro ao excluir movimentação: ' + resp.message + '</div>')
                    .insertBefore('.container-fluid').delay(2500).fadeOut(500);
            }
        }, 'json');
    }
}

$(document).on('click', '.btn-excluir-img', function(e) {
    e.preventDefault();
    var btn = $(this);
    var imgId = btn.data('img-id');
    if (confirm('Deseja realmente excluir esta imagem?')) {
        $.post('MVC/MODEL/excluir_imagem_movimentacao.php', {id: imgId}, function(resp) {
            if (resp.success) {
                btn.closest('.img-thumb-wrapper').fadeOut(300, function() { $(this).remove(); });
            } else {
                alert('Erro ao excluir imagem: ' + (resp.message || ''));
            }
        }, 'json');
    }
});
</script>

<?php include __DIR__ . '/movimentacoes-modal.php'; ?> 