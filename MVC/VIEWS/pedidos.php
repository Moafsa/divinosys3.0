<?php
// Garantir que ROOT_PATH está definido
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(dirname(__FILE__))));
}

// Garantir que a classe Config está disponível
if (!class_exists('Config')) {
    require_once ROOT_PATH . '/MVC/MODEL/config.php';
}

// Garantir que temos uma conexão com o banco de dados
global $conn;
if (!isset($conn)) {
    require_once ROOT_PATH . '/MVC/MODEL/conexao.php';
}

// Incluir o controller
require_once ROOT_PATH . '/MVC/CONTROLLER/pedidos_controller.php';

// Inicializa o controlador
$pedidosController = new PedidosController($conn);

// Obtém os filtros da URL
$status = isset($_GET['status']) ? $_GET['status'] : '';
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d');
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');
$mesa = isset($_GET['mesa']) ? $_GET['mesa'] : '';
$delivery = isset($_GET['delivery']) ? $_GET['delivery'] : '';

// Obtém a página atual
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 12;

// Busca os pedidos com base nos filtros
$pedidos = $pedidosController->buscarPedidos($status, $data_inicio, $data_fim, $mesa, $delivery, $pagina, $por_pagina);
$total_pedidos = $pedidosController->contarPedidos($status, $data_inicio, $data_fim, $mesa, $delivery);

// Calcula o total de páginas
$total_paginas = ceil($total_pedidos / $por_pagina);

// Agrupar pedidos por status
$pedidos_por_status = array(
    'Pendente' => array(),
    'Em Preparo' => array(),
    'Pronto' => array(),
    'Saiu para Entrega' => array(),
    'Entregue (Mesa)' => array(),
    'Entregue (Delivery)' => array()
);

foreach ($pedidos as $pedido) {
    $status = $pedido['status'];
    // Modificar o status para diferenciar entre mesa e delivery
    if ($status === 'Entregue') {
        $status = $pedido['delivery'] ? 'Entregue (Delivery)' : 'Entregue (Mesa)';
    }
    
    if (array_key_exists($status, $pedidos_por_status)) {
        if (!isset($pedidos_por_status[$status][$pedido['idpedido']])) {
            $pedidos_por_status[$status][$pedido['idpedido']] = array(
                'pedido' => $pedido,
                'itens' => array()
            );
        }
        // Adiciona o item ao pedido
        $pedidos_por_status[$status][$pedido['idpedido']]['itens'][] = $pedido;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - PDV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .pipeline-scroll-container {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch; /* Para melhor scroll no iOS */
            scrollbar-width: thin; /* Para Firefox */
            padding-bottom: 15px; /* Espaço para a scrollbar */
        }

        /* Estilização da scrollbar para Chrome/Safari */
        .pipeline-scroll-container::-webkit-scrollbar {
            height: 8px;
        }

        .pipeline-scroll-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .pipeline-scroll-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .pipeline-scroll-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .pipeline-container {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            min-width: max-content; /* Garante que as colunas não encolham */
        }
        
        .pipeline-column {
            flex: 0 0 300px; /* Largura fixa de 300px, não encolhe nem cresce */
            margin: 0 10px;
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
            max-height: calc(100vh - 200px); /* Altura máxima com scroll vertical */
            overflow-y: auto;
        }
        
        .pipeline-header {
            position: sticky;
            top: 0;
            z-index: 1;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
            color: white;
        }
        
        .pendente-header {
            background-color: #ffc107;
        }
        
        .em-preparo-header {
            background-color: #17a2b8;
        }
        
        .pronto-header {
            background-color: #28a745;
        }
        
        .saiu-para-entrega-header {
            background-color: #007bff;
        }

        .entregue-mesa-header {
            background-color: #6f42c1;
        }

        .entregue-delivery-header {
            background-color: #20c997;
        }
        
        .pedido-card {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .pedido-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .pedido-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0;
            position: relative;
        }

        .pedido-header::after {
            content: '\f107';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 0;
            transition: transform 0.3s ease;
        }

        .pedido-card.expanded .pedido-header::after {
            transform: rotate(180deg);
        }
        
        .pedido-content {
            display: none;
            margin-top: 1rem;
            border-top: 1px solid #eee;
            padding-top: 1rem;
        }

        .pedido-card.expanded .pedido-content {
            display: block;
        }
        
        .item-pedido {
            padding: 10px;
            border-bottom: 1px solid #eee;
            margin-bottom: 10px;
        }
        
        .produto-linha {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .produto-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .produto-nome {
            font-weight: 500;
        }
        
        .valor-total {
            text-align: right;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
            border-top: 1px solid #dee2e6;
        }
        
        .ingredientes-lista {
            margin-left: 25px;
            font-size: 0.9em;
            margin-top: 5px;
        }
        
        .text-danger {
            color: #dc3545;
        }
        
        .text-success {
            color: #28a745;
        }
        
        .observacao {
            margin-left: 25px;
            margin-top: 5px;
            color: #666;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .observacao i {
            font-size: 0.8em;
            color: #999;
        }
        
        .delete-item {
            color: #dc3545;
            cursor: pointer;
            padding: 0.25rem;
            margin-left: 0.5rem;
        }

        .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }
        
        .ingredientes-container {
            margin-top: 8px;
            font-size: 0.9em;
        }
        
        .ingredientes, .observacao {
            display: flex;
            align-items: center;
            margin: 4px 0;
            padding: 4px 8px;
            border-radius: 4px;
        }
        
        .ingredientes.sem {
            background-color: #ffebee;
            color: #d32f2f;
        }
        
        .ingredientes.com {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .observacao {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .ingredientes i, .observacao i {
            margin-right: 8px;
        }
        
        .ingredientes span, .observacao span {
            flex: 1;
            word-break: break-word;
        }

        .valor-total-header {
            margin-top: 0.5rem;
            font-weight: bold;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
    <!-- Filtros -->
        <div class="card my-3">
        <div class="card-body">
                <form method="GET" class="row g-3">
                <input type="hidden" name="view" value="pedidos">
                    <div class="col-md-3">
                        <label for="data_inicio" class="form-label">Data Início</label>
                        <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo $data_inicio; ?>">
                    </div>
                <div class="col-md-3">
                        <label for="data_fim" class="form-label">Data Fim</label>
                        <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo $data_fim; ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="mesa" class="form-label">Mesa</label>
                        <input type="number" class="form-control" id="mesa" name="mesa" value="<?php echo $mesa; ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="delivery" class="form-label">Delivery</label>
                        <select name="delivery" id="delivery" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" <?php echo $delivery == '1' ? 'selected' : ''; ?>>Sim</option>
                            <option value="0" <?php echo $delivery == '0' ? 'selected' : ''; ?>>Não</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                    </div>
                </form>
                    </div>
                </div>
                
        <!-- Pipeline de Pedidos -->
        <div class="pipeline-scroll-container">
            <div class="pipeline-container">
                <?php foreach ($pedidos_por_status as $status => $pedidos_status): ?>
                    <div class="pipeline-column">
                        <div class="pipeline-header <?php echo strtolower(str_replace(' ', '-', $status)); ?>-header">
                            <h5 class="mb-0"><?php echo $status; ?></h5>
                        </div>
                        <?php foreach ($pedidos_status as $pedido_id => $dados): ?>
                            <div class="pedido-card" onclick="togglePedido(this, event)">
                                <div class="pedido-header">
                                    <div>
                                        <h6 class="mb-0">
                                            <?php if ($dados['pedido']['delivery']): ?>
                                                <i class="fas fa-motorcycle me-1"></i>
                                            <?php else: ?>
                                                <i class="fas fa-utensils me-1"></i>
                                                Mesa <?php echo $dados['pedido']['idmesa']; ?>
                                            <?php endif; ?>
                                            #<?php echo $pedido_id; ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($dados['pedido']['data'] . ' ' . $dados['pedido']['hora_pedido'])); ?>
                                        </small>
                                        <div class="valor-total-header">
                                            R$ <?php echo number_format($dados['pedido']['valor_total'], 2, ',', '.'); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="pedido-content">
                                    <?php foreach ($dados['itens'] as $item): ?>
                                        <div class="item-pedido">
                                            <div class="produto-linha">
                                                <div class="produto-info">
                                                    <span><?= htmlspecialchars($item['quantidade']) ?>x</span>
                                                    <span class="produto-nome">
                                                        <?php 
                                                        $nome_produto = $item['produto'];
                                                        if (strpos($nome_produto, '(') === false && isset($item['pessoas']) && $item['pessoas'] > 0) {
                                                            $nome_produto .= " ({$item['pessoas']} PESSOAS)";
                                                        }
                                                        echo htmlspecialchars($nome_produto);
                                                        ?>
                                                    </span>
                                                </div>
                                                <span>R$ <?= number_format($item['valor_unitario'], 2, ',', '.') ?></span>
                                            </div>
                                            
                                            <?php if (!empty($item['ingredientes_sem']) || !empty($item['ingredientes_com']) || !empty($item['observacao'])) : ?>
                                                <div class="ingredientes-container">
                                                    <?php if (!empty($item['ingredientes_sem'])) : ?>
                                                        <div class="ingredientes sem">
                                                            <i class="fas fa-minus-circle"></i>
                                                            <span>SEM: <?php echo htmlspecialchars($item['ingredientes_sem']); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($item['ingredientes_com'])) : ?>
                                                        <div class="ingredientes com">
                                                            <i class="fas fa-plus-circle"></i>
                                                            <span>COM: <?php echo htmlspecialchars($item['ingredientes_com']); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($item['observacao'])) : ?>
                                                        <div class="observacao">
                                                            <i class="fas fa-info-circle"></i>
                                                            <span>OBS: <?php echo htmlspecialchars($item['observacao']); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="btn-group">
                                            <?php if ($status == 'Pendente'): ?>
                                                <button class="btn btn-sm btn-info" onclick="atualizarStatus(<?php echo $pedido_id; ?>, 'Em Preparo')">
                                                    <i class="fas fa-utensils me-1"></i> Iniciar Preparo
                                                </button>
                                            <?php elseif ($status == 'Em Preparo'): ?>
                                                <button class="btn btn-sm btn-success" onclick="atualizarStatus(<?php echo $pedido_id; ?>, 'Pronto')">
                                                    <i class="fas fa-check-circle me-1"></i> Marcar Pronto
                                                </button>
                                            <?php elseif ($status == 'Pronto'): ?>
                                                <?php if ($dados['pedido']['delivery']): ?>
                                                    <button class="btn btn-sm btn-primary" onclick="atualizarStatus(<?php echo $pedido_id; ?>, 'Saiu para Entrega')">
                                                        <i class="fas fa-motorcycle me-1"></i> Saiu para Entrega
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-success" onclick="atualizarStatus(<?php echo $pedido_id; ?>, 'Entregue')">
                                                        <i class="fas fa-check me-1"></i> Entregar na Mesa
                                                    </button>
                                                <?php endif; ?>
                                            <?php elseif ($status == 'Saiu para Entrega'): ?>
                                                <button class="btn btn-sm btn-success" onclick="atualizarStatus(<?php echo $pedido_id; ?>, 'Entregue')">
                                                    <i class="fas fa-check me-1"></i> Confirmar Entrega
                                                </button>
                                            <?php endif; ?>

                                            <?php if (!in_array($status, ['Entregue (Mesa)', 'Entregue (Delivery)'])): ?>
                                                <button class="btn btn-sm btn-danger ms-2" onclick="atualizarStatus(<?php echo $pedido_id; ?>, 'Cancelado')">
                                                    <i class="fas fa-times me-1"></i> Cancelar
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        <div class="valor-total">
                                            R$ <?php echo number_format($dados['pedido']['valor_total'], 2, ',', '.'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        function togglePedido(element, event) {
            // Previne que o clique em botões dentro do card ative o toggle
            if (event.target.closest('.btn-group')) {
                return;
            }
            element.classList.toggle('expanded');
        }

        function atualizarStatus(pedidoId, novoStatus) {
            // Previne a propagação do clique para não fechar o card
            event.stopPropagation();
            
            console.log('Atualizando status:', { pedidoId, novoStatus });
            
            fetch('MVC/MODEL/atualizar_status_pedido.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `pedido_id=${pedidoId}&status=${novoStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao atualizar status: ' + (data.message || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao atualizar status. Verifique o console para mais detalhes.');
            });
        }

        // Atualiza a página a cada 30 segundos
        setInterval(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html> 