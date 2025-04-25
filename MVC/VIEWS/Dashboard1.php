<?php
include_once(__DIR__ . "/include_conexao.php");
$config = Config::getInstance();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .dashboard-header {
            padding: 20px 0;
            margin-bottom: 30px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .quick-actions {
            margin-bottom: 30px;
        }
        
        .stats-card {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: white;
            transition: transform 0.2s;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .mesa-card {
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .mesa-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .mesa-livre { 
            background-color: var(--success-color);
        }
        
        .mesa-ocupada { 
            background-color: var(--danger-color);
        }
        
        .mesa-atendendo { 
            background-color: var(--warning-color);
        }

        .mesa-entregue {
            background-color: var(--info-color);
        }

        .mesa-cancelada {
            background-color: var(--success-color);
        }

        .mesa-fechada {
            background-color: var(--success-color);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .btn {
            border-radius: 5px;
            padding: 8px 16px;
        }
        
        .btn-lg {
            padding: 12px 24px;
        }

        .pedido-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1050;
        }

        .pedido-modal-content {
            position: relative;
            background: white;
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 8px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .close-modal {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            cursor: pointer;
            z-index: 1;
        }

        .pedido-card {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .pedido-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }

        .produto-linha {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .produto-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .produto-nome {
            font-weight: 500;
            font-size: 1.1em;
        }

        .item-pedido {
            padding: 12px;
            border: 1px solid #eee;
            border-radius: 6px;
            margin-bottom: 12px;
            background-color: #fafafa;
        }

        .detalhes-pedido {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #eee;
        }

        .ingredientes-container {
            margin: 6px 0;
        }

        .ingredientes, .observacao {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 4px 0;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .ingredientes.text-danger {
            background-color: #fff5f5;
            color: #dc3545;
        }

        .ingredientes.text-success {
            background-color: #f0fff4;
            color: #28a745;
        }

        .observacao {
            background-color: #f8f9fa;
            color: #6c757d;
            font-style: italic;
        }

        /* Estilos para modal de seleção de tipo de pedido */
        .mesa-card, .delivery-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .mesa-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,123,255,0.2);
        }

        .delivery-card:hover {
            border-color: var(--success-color);
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(40,167,69,0.2);
        }

        .mesa-card .fa-chair {
            color: var(--primary-color);
        }

        .delivery-card .fa-motorcycle {
            color: var(--success-color);
        }

        .modal-dialog-centered {
            display: flex;
            align-items: center;
            min-height: calc(100% - 1rem);
        }

        @media (min-width: 576px) {
            .modal-dialog-centered {
                min-height: calc(100% - 3.5rem);
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <!-- Header Section -->
    <div class="row dashboard-header">
        <div class="col-md-8">
            <h2 class="text-gray-800">Dashboard do Restaurante</h2>
            <p class="text-muted">Bem-vindo ao sistema de gerenciamento de pedidos</p>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-success btn-lg" onclick="novoGeral()">
                <i class="fas fa-plus-circle"></i> Novo Pedido
            </button>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="stats-card bg-primary" onclick="window.location.href='<?php echo $config->url('?view=mesas'); ?>'">
                <h5>Mesas</h5>
                <div class="text-center">
                    <i class="fas fa-chair fa-2x mb-2"></i>
                    <p class="mb-0">Gerenciar Mesas</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card bg-success" onclick="window.location.href='<?php echo $config->url('?view=delivery'); ?>'">
                <h5>Delivery</h5>
                <div class="text-center">
                    <i class="fas fa-motorcycle fa-2x mb-2"></i>
                    <p class="mb-0">Pedidos Delivery</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card bg-info" onclick="window.location.href='index.php?view=gerenciar_produtos'">
                <h5>Produtos</h5>
                <div class="text-center">
                    <i class="fas fa-box fa-2x mb-2"></i>
                    <p class="mb-0">Gerenciar Produtos</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card bg-warning" onclick="window.location.href='index.php?view=relatorios'">
                <h5>Relatórios</h5>
                <div class="text-center">
                    <i class="fas fa-chart-bar fa-2x mb-2"></i>
                    <p class="mb-0">Ver Relatórios</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card bg-secondary" onclick="window.location.href='<?php echo $config->url('?view=gerenciar_categorias'); ?>'">
                <h5>Categorias</h5>
                <div class="text-center">
                    <i class="fas fa-tags fa-2x mb-2"></i>
                    <p class="mb-0">Gerenciar Categorias</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stats-card bg-danger" onclick="window.location.href='index.php?view=pedidos'">
                <h5>Pedidos</h5>
                <div class="text-center">
                    <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                    <p class="mb-0">Gerenciar Pedidos</p>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code Access Button -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Acesso Rápido</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <a href="<?php echo $config->url('?view=qr_codes'); ?>" class="btn btn-info btn-block">
                                    <i class="fas fa-qrcode fa-2x"></i>
                                    <span class="ml-2">QR Codes de Acesso</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status das Mesas -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Status das Mesas</h6>
                    <button class="btn btn-outline-primary btn-sm" onclick="atualizarMesas()">
                        <i class="fas fa-sync-alt"></i> Atualizar
                    </button>
                </div>
                <div class="card-body">
                    <div class="row" id="mesas-container">
                        <?php
                        $sql = "SELECT m.id, m.id_mesa, m.nome, m.status as mesa_status,
                                p.idpedido, p.valor_total, p.status as pedido_status, p.data, p.hora_pedido
                                FROM mesas m 
                                LEFT JOIN (
                                    SELECT idmesa, idpedido, valor_total, status, data, hora_pedido
                                    FROM pedido
                                    WHERE status NOT IN ('Finalizado', 'Cancelado')
                                ) p ON m.id_mesa = p.idmesa 
                                ORDER BY m.id_mesa ASC";
                        $mesas = mysqli_query($conn, $sql);

                        while($mesa = mysqli_fetch_assoc($mesas)) {
                            $status_class = '';
                            $status_text = '';
                            $pedido_info = '';
                            
                            if (!$mesa['idpedido']) {
                                $status_class = 'mesa-livre';
                                $status_text = 'Livre';
                            } else {
                                switch($mesa['pedido_status']) {
                                    case 'Entregue':
                                    case 'Entregue (Mesa)':
                                        $status_class = 'mesa-entregue';
                                        $status_text = 'Entregue';
                                        break;
                                    case 'Cancelado':
                                        $status_class = 'mesa-livre';
                                        $status_text = 'Livre';
                                        break;
                                    case 'Finalizado':
                                        $status_class = 'mesa-livre';
                                        $status_text = 'Livre';
                                        break;
                                    default:
                                        $status_class = 'mesa-ocupada';
                                        $status_text = 'Ocupada';
                                }

                                if ($mesa['idpedido']) {
                                    $pedido_info = '<p class="mb-1">Pedido #' . $mesa['idpedido'] . '</p>' .
                                                  '<p class="mb-2">R$ ' . number_format((float)$mesa['valor_total'], 2, ',', '.') . '</p>';
                                }
                            }
                            ?>
                            <div class="col-xl-2 col-md-3 col-sm-4 mb-3">
                                <div class="card mesa-card <?php echo $status_class; ?> text-white" 
                                     onclick="abrirPedidoMesa(<?php echo $mesa['id_mesa']; ?>)">
                                    <div class="card-body text-center">
                                        <h4 class="mb-2">Mesa <?php echo $mesa['id_mesa']; ?></h4>
                                        <p class="mb-2"><?php echo $status_text; ?></p>
                                        <?php echo $pedido_info; ?>
                                        <button class="btn btn-light btn-sm btn-block">
                                            <?php echo !$mesa['idpedido'] || $mesa['pedido_status'] == 'Finalizado' || $mesa['pedido_status'] == 'Cancelado' ? 
                                                '<i class="fas fa-plus-circle"></i> Fazer Pedido' : 
                                                '<i class="fas fa-eye"></i> Ver Pedido'; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Pedido -->
<div id="pedidoModal" class="pedido-modal">
    <div class="pedido-modal-content">
        <span class="close-modal" onclick="fecharModal()">&times;</span>
        <div id="pedidoConteudo"></div>
    </div>
</div>

<!-- Modal de Novo Pedido -->
<div class="modal fade" id="novoPedidoModal" tabindex="-1" aria-labelledby="novoPedidoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="novoPedidoModalLabel">Selecione o Tipo de Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card h-100 mesa-card" onclick="selecionarTipoPedido('mesa')" style="cursor: pointer;">
                            <div class="card-body text-center">
                                <i class="fas fa-chair fa-3x mb-3 text-primary"></i>
                                <h5 class="card-title">Mesa</h5>
                                <p class="card-text">Pedido para consumo no local</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 delivery-card" onclick="selecionarTipoPedido('delivery')" style="cursor: pointer;">
                            <div class="card-body text-center">
                                <i class="fas fa-motorcycle fa-3x mb-3 text-success"></i>
                                <h5 class="card-title">Delivery</h5>
                                <p class="card-text">Pedido para entrega</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function atualizarMesas() {
    // Primeiro, chamar o endpoint para corrigir o status das mesas
    fetch('MVC/MODEL/corrigir_status_mesas.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na resposta do servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('Mesas atualizadas:', data.mesas_atualizadas);
                // Atualizar apenas o container de mesas via AJAX
                fetch(window.location.href)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newMesasContainer = doc.getElementById('mesas-container');
                        if (newMesasContainer) {
                            document.getElementById('mesas-container').innerHTML = newMesasContainer.innerHTML;
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao atualizar visualização:', error);
                    });
            } else {
                console.error('Erro ao corrigir status das mesas:', data.message);
            }
        })
        .catch(error => {
            console.error('Erro ao atualizar mesas:', error);
        });
}

function abrirPedidoMesa(mesa_id) {
    if (event.target.closest('button').innerText.includes('Ver Pedido')) {
        // Buscar o ID do pedido da mesa
        const pedidoElement = event.target.closest('.card-body').querySelector('p.mb-1');
        if (pedidoElement) {
            const pedidoId = pedidoElement.innerText.replace('Pedido #', '');
            verPedido(pedidoId);
        }
    } else {
        window.location.href = '<?php echo $config->url("?view=gerar_pedido&mesa="); ?>' + mesa_id;
    }
}

function verPedido(pedidoId) {
    const modal = document.getElementById('pedidoModal');
    const conteudo = document.getElementById('pedidoConteudo');
    
    // Buscar detalhes do pedido via AJAX
    fetch(`MVC/MODEL/buscar_pedido.php?pedido_id=${pedidoId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Erro ao fazer parse do JSON:', e);
                    console.error('Resposta recebida:', text);
                    throw new Error('Erro ao processar resposta do servidor');
                }
            });
        })
        .then(data => {
            if (data.success) {
                console.log('Dados recebidos:', data);
                let html = `
                    <div class="pedido-card expanded">
                        <div class="pedido-header">
                            <div>
                                <strong>Pedido #${pedidoId}</strong>
                                <div class="text-muted">
                                    ${data.pedido.data} ${data.pedido.hora_pedido}
                                </div>
                                <div class="text-muted">
                                    Mesa: ${data.pedido.idmesa}
                                </div>
                                <div class="text-muted">
                                    Status: ${data.pedido.status}
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="btn-group">
                                    ${data.pedido.status === 'Entregue' ? `
                                        <button onclick="fecharPedido(${pedidoId})" class="btn btn-success btn-sm">
                                            <i class="fas fa-check-circle"></i> Fechar Pedido
                                        </button>
                                    ` : `
                                        <button onclick="entregarPedido(${pedidoId})" class="btn btn-info btn-sm">
                                            <i class="fas fa-utensils"></i> Entregar Pedido
                                        </button>
                                    `}
                                    <button onclick="converterParaDelivery(${pedidoId})" class="btn btn-primary btn-sm ms-2">
                                        <i class="fas fa-motorcycle"></i> Fazer Delivery
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="pedido-content" style="display: block;">`;
                
                data.itens.forEach(item => {
                    html += `
                        <div class="item-pedido">
                            <div class="produto-linha">
                                <div class="produto-info">
                                    <span class="produto-nome">${item.produto}</span>
                                    <span class="badge bg-secondary">${item.quantidade}x</span>
                                </div>
                                <div class="text-end">
                                    R$ ${parseFloat(item.valor_total).toFixed(2)}
                                </div>
                            </div>`;
                    
                    if (item.ingredientes_com) {
                        const ingredientesCom = typeof item.ingredientes_com === 'string' ? 
                            item.ingredientes_com.split(',').map(i => i.trim()).filter(i => i) : 
                            Array.isArray(item.ingredientes_com) ? item.ingredientes_com : [];
                        
                        if (ingredientesCom.length > 0) {
                            html += `
                                <div class="ingredientes-container">
                                    <div class="ingredientes text-success">
                                        <i class="fas fa-plus-circle"></i>
                                        <span>COM: ${ingredientesCom.join(', ')}</span>
                                    </div>
                                </div>`;
                        }
                    }
                    
                    if (item.ingredientes_sem) {
                        const ingredientesSem = typeof item.ingredientes_sem === 'string' ? 
                            item.ingredientes_sem.split(',').map(i => i.trim()).filter(i => i) : 
                            Array.isArray(item.ingredientes_sem) ? item.ingredientes_sem : [];
                        
                        if (ingredientesSem.length > 0) {
                            html += `
                                <div class="ingredientes-container">
                                    <div class="ingredientes text-danger">
                                        <i class="fas fa-minus-circle"></i>
                                        <span>SEM: ${ingredientesSem.join(', ')}</span>
                                    </div>
                                </div>`;
                        }
                    }
                    
                    if (item.observacao && item.observacao.trim()) {
                        html += `
                            <div class="observacao">
                                <i class="fas fa-info-circle"></i>
                                <span>Observação: ${item.observacao}</span>
                            </div>`;
                    }
                    
                    html += `</div>`;
                });
                
                html += `
                            <div class="valor-total mt-3 text-end">
                                <strong>Total: R$ ${parseFloat(data.pedido.valor_total).toFixed(2)}</strong>
                            </div>
                        </div>
                    </div>`;
                
                conteudo.innerHTML = html;
                modal.style.display = 'block';
            } else {
                alert('Erro ao carregar pedido');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar pedido');
        });
}

function fecharModal() {
    document.getElementById('pedidoModal').style.display = 'none';
}

function fecharPedido(pedidoId) {
    if (confirm('Tem certeza que deseja fechar este pedido?')) {
        fetch('MVC/MODEL/atualizar_status_pedido.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `pedido_id=${pedidoId}&status=Finalizado`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fecharModal();
                atualizarMesas();
            } else {
                alert('Erro ao fechar pedido: ' + (data.message || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao fechar pedido');
        });
    }
}

function entregarPedido(pedidoId) {
    if (confirm('Confirmar entrega do pedido?')) {
        fetch('MVC/MODEL/atualizar_status_pedido.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `pedido_id=${pedidoId}&status=Entregue`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fecharModal();
                atualizarMesas();
            } else {
                alert('Erro ao entregar pedido: ' + (data.message || 'Erro desconhecido'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao entregar pedido');
        });
    }
}

// Fechar modal quando clicar fora dele
window.onclick = function(event) {
    const modal = document.getElementById('pedidoModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

function novoGeral() {
    // Abrir modal de seleção de tipo de pedido
    $('#novoPedidoModal').modal('show');
}

function selecionarTipoPedido(tipo) {
    $('#novoPedidoModal').modal('hide');
    if (tipo === 'mesa') {
        // Redirecionar para seleção de mesa
        window.location.href = '<?php echo $config->url("?view=mesas"); ?>';
    } else if (tipo === 'delivery') {
        // Redirecionar para página de pedido delivery
        window.location.href = '<?php echo $config->url("?view=gerar_pedido_delivery"); ?>';
    }
}

// Atualizar mesas a cada 60 segundos (aumentado de 30 para 60 para reduzir a carga)
const intervalId = setInterval(atualizarMesas, 60000);

// Limpar o intervalo quando a página for fechada/navegada
window.addEventListener('beforeunload', () => {
    clearInterval(intervalId);
});

// Primeira atualização após 3 segundos para garantir que a página carregou completamente
setTimeout(atualizarMesas, 3000);

function converterParaDelivery(pedidoId) {
    Swal.fire({
        title: 'Dados do Delivery',
        html: `
            <form id="deliveryForm" class="text-start">
                <div class="mb-3">
                    <label class="form-label">Nome do Cliente</label>
                    <input type="text" class="form-control" id="nomeCliente">
                </div>
                <div class="mb-3">
                    <label class="form-label">Telefone</label>
                    <input type="text" class="form-control" id="telefone">
                </div>
                <div class="mb-3">
                    <label class="form-label">Endereço</label>
                    <textarea class="form-control" id="endereco" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ponto de Referência</label>
                    <input type="text" class="form-control" id="referencia">
                </div>
                <div class="mb-3">
                    <label class="form-label">Taxa de Entrega</label>
                    <input type="number" class="form-control" id="taxaEntrega" step="0.01" min="0" value="0.00">
                </div>
                <div class="mb-3">
                    <label class="form-label">Forma de Pagamento</label>
                    <select class="form-control" id="formaPagamento">
                        <option value="">Selecione...</option>
                        <option value="Dinheiro">Dinheiro</option>
                        <option value="Cartão - Débito">Cartão - Débito</option>
                        <option value="Cartão - Crédito">Cartão - Crédito</option>
                        <option value="PIX">PIX</option>
                    </select>
                </div>
                <div class="mb-3" id="trocoContainer" style="display: none;">
                    <label class="form-label">Troco para quanto?</label>
                    <input type="number" class="form-control" id="troco" step="0.01" min="0">
                </div>
            </form>
        `,
        confirmButtonText: 'Confirmar Delivery',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        didOpen: () => {
            document.getElementById('formaPagamento').addEventListener('change', function() {
                document.getElementById('trocoContainer').style.display = 
                    this.value === 'Dinheiro' ? 'block' : 'none';
            });
        },
        preConfirm: () => {
            const formaPagamento = document.getElementById('formaPagamento').value;
            const troco = document.getElementById('troco').value;
            
            return {
                pedido_id: pedidoId,
                nome_cliente: document.getElementById('nomeCliente').value,
                telefone: document.getElementById('telefone').value,
                endereco_entrega: document.getElementById('endereco').value,
                ponto_referencia: document.getElementById('referencia').value || '',
                taxa_entrega: parseFloat(document.getElementById('taxaEntrega').value) || 0,
                forma_pagamento: formaPagamento,
                troco_para: troco ? parseFloat(troco) : 0
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const dadosDelivery = result.value;
            
            // Enviar dados para o novo endpoint
            fetch('MVC/MODEL/converter_para_delivery.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(dadosDelivery)
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error('Erro ao converter pedido: ' + text);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Pedido convertido para delivery com sucesso!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        fecharModal();
                        atualizarMesas();
                    });
                } else {
                    throw new Error(data.message || 'Erro ao converter pedido');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: error.message
                });
            });
        }
    });
}
</script>

<!-- Adicionar SweetAlert2 para modais mais bonitos -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>
