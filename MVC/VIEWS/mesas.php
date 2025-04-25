<?php
// Verificar se o usuário está logado
if (!isset($_SESSION["login"]) || $_SESSION["login"] != 1) {
    header("Location: " . $config->url());
    exit;
}

$config = Config::getInstance();

// Buscar mesas e seus pedidos ativos do banco de dados
$query = "SELECT m.*, 
          p.idpedido, 
          p.valor_total, 
          p.data, 
          p.hora_pedido,
          p.status as status_pedido
          FROM mesas m
          LEFT JOIN pedido p ON m.id_mesa = p.idmesa 
          AND p.status NOT IN ('Finalizado', 'Entregue', 'Entregue (Mesa)', 'Entregue (Delivery)', 'Cancelado')
          ORDER BY m.id_mesa";
$result = mysqli_query($conn, $query);

// Array para armazenar os itens dos pedidos
$itens_pedidos = array();

// Buscar itens dos pedidos ativos
$pedidos_ids = array();
while ($row = mysqli_fetch_assoc($result)) {
    if ($row['idpedido']) {
        $pedidos_ids[] = $row['idpedido'];
    }
}
mysqli_data_seek($result, 0);

if (!empty($pedidos_ids)) {
    $ids = implode(',', $pedidos_ids);
    $query_itens = "SELECT pi.*, p.nome as produto
                    FROM pedido_itens pi 
                    JOIN produtos p ON pi.produto_id = p.id 
                    WHERE pi.pedido_id IN ($ids)";
    $result_itens = mysqli_query($conn, $query_itens);
    
    while ($item = mysqli_fetch_assoc($result_itens)) {
        if (!isset($itens_pedidos[$item['pedido_id']])) {
            $itens_pedidos[$item['pedido_id']] = array();
        }
        $itens_pedidos[$item['pedido_id']][] = $item;
    }
}
?>

<style>
.mesa-card {
    transition: all 0.3s ease;
}

.mesa-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
}

.mesa-livre {
    border-left: 4px solid #28a745 !important;
}

.mesa-ocupada {
    border-left: 4px solid #dc3545 !important;
}

.mesa-atendendo {
    border-left: 4px solid #ffc107 !important;
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

.valor-total {
    text-align: right;
    padding: 1rem 0;
    margin-top: 1rem;
    border-top: 2px solid #eee;
    font-weight: bold;
    font-size: 1.2em;
    color: #2c3e50;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
    margin-left: 0.5rem;
}

.text-muted {
    font-size: 0.9em;
    margin: 2px 0;
}
</style>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Gerenciamento de Mesas</h1>
    
    <div class="row">
        <?php while ($mesa = mysqli_fetch_assoc($result)): 
            $status_class = '';
            switch($mesa["status"]) {
                case 'Livre':
                    $status_class = 'mesa-livre';
                    break;
                case 'Ocupada':
                    $status_class = 'mesa-ocupada';
                    break;
                case 'Atendendo':
                    $status_class = 'mesa-atendendo';
                    break;
            }
        ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100 py-2 mesa-card <?php echo $status_class; ?>">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    Mesa <?php echo $mesa["id_mesa"]; ?>
                                </div>
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Status: <?php echo $mesa["status"]; ?>
                                </div>
                                <?php if ($mesa['idpedido']): ?>
                                    <div class="text-xs text-muted mt-2">
                                        Pedido #<?php echo $mesa['idpedido']; ?><br>
                                        <?php echo date('d/m/Y H:i', strtotime($mesa['data'] . ' ' . $mesa['hora_pedido'])); ?><br>
                                        R$ <?php echo number_format($mesa['valor_total'], 2, ',', '.'); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-auto">
                                <?php if ($mesa['idpedido']): ?>
                                    <button onclick="verPedido(<?php echo $mesa['idpedido']; ?>)" 
                                            class="btn btn-info btn-sm mb-2">
                                        <i class="fas fa-eye"></i> Ver Pedido
                                    </button>
                                    <br>
                                    <button onclick="fecharPedido(<?php echo $mesa['idpedido']; ?>)" 
                                            class="btn btn-success btn-sm">
                                        <i class="fas fa-check-circle"></i> Fechar Pedido
                                    </button>
                                <?php else: ?>
                                    <a href="<?php echo $config->url("?view=gerar_pedido&mesa=" . $mesa["id_mesa"]); ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus-circle"></i> Fazer Pedido
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Modal de Pedido -->
<div id="pedidoModal" class="pedido-modal">
    <div class="pedido-modal-content">
        <span class="close-modal" onclick="fecharModal()">&times;</span>
        <div id="pedidoConteudo"></div>
    </div>
</div>

<script>
function verPedido(pedidoId) {
    const modal = document.getElementById('pedidoModal');
    const conteudo = document.getElementById('pedidoConteudo');
    
    // Buscar detalhes do pedido via AJAX
    fetch('<?php echo api_url("buscar_pedido.php"); ?>?pedido_id=' + pedidoId)
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
                                    <button onclick="fecharPedido(${pedidoId})" class="btn btn-success btn-sm">
                                        <i class="fas fa-check-circle"></i> Fechar Pedido
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="pedido-content" style="display: block;">`;
                
                data.itens.forEach(item => {
                    console.log('Processando item:', item);
                    html += `
                        <div class="item-pedido">
                            <div class="produto-linha">
                                <div class="produto-info">
                                    <span class="produto-nome">${item.quantidade}x ${item.produto}</span>
                                </div>
                                <div>
                                    R$ ${parseFloat(item.valor_total || (item.quantidade * item.valor_unitario)).toFixed(2)}
                                </div>
                            </div>
                            <div class="detalhes-pedido">`;
                    
                    // Debug dos ingredientes
                    console.log('Ingredientes SEM:', item.ingredientes_sem);
                    console.log('Ingredientes COM:', item.ingredientes_com);
                    
                    // Verifica se ingredientes_sem existe e não está vazio
                    if (item.ingredientes_sem) {
                        console.log('Tipo de ingredientes_sem:', typeof item.ingredientes_sem);
                        const ingredientesSem = typeof item.ingredientes_sem === 'string' ? 
                            item.ingredientes_sem.split(',').map(i => i.trim()).filter(i => i) : 
                            Array.isArray(item.ingredientes_sem) ? item.ingredientes_sem : [];
                        
                        console.log('ingredientesSem processados:', ingredientesSem);
                        
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
                    
                    // Verifica se ingredientes_com existe e não está vazio
                    if (item.ingredientes_com) {
                        console.log('Tipo de ingredientes_com:', typeof item.ingredientes_com);
                        const ingredientesCom = typeof item.ingredientes_com === 'string' ? 
                            item.ingredientes_com.split(',').map(i => i.trim()).filter(i => i) : 
                            Array.isArray(item.ingredientes_com) ? item.ingredientes_com : [];
                        
                        console.log('ingredientesCom processados:', ingredientesCom);
                        
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
                    
                    // Verifica se há observação e não está vazia
                    if (item.observacao && item.observacao.trim()) {
                        html += `
                            <div class="observacao">
                                <i class="fas fa-info-circle"></i>
                                <span>Observação: ${item.observacao}</span>
                            </div>`;
                    }
                    
                    html += `</div></div>`;
                });
                
                html += `
                            <div class="valor-total">
                                Total: R$ ${parseFloat(data.pedido.valor_total).toFixed(2)}
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
        fetch('<?php echo api_url("atualizar_status_pedido.php"); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `pedido_id=${pedidoId}&status=Entregue`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
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

// Fechar modal quando clicar fora dele
window.onclick = function(event) {
    const modal = document.getElementById('pedidoModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>