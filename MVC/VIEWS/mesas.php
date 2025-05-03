<?php
// Verificar se o usuário está logado
if (!isset($_SESSION["login"]) || $_SESSION["login"] != 1) {
    header("Location: " . $config->url());
    exit;
}

$config = Config::getInstance();

// Buscar todos os pedidos abertos agrupados por mesa
$query = "SELECT m.id_mesa, m.nome, m.status as mesa_status,
               p.idpedido, p.valor_total, p.status as pedido_status, p.data, p.hora_pedido
        FROM mesas m
        LEFT JOIN pedido p ON m.id_mesa = p.idmesa AND p.status NOT IN ('Finalizado', 'Cancelado')
        ORDER BY m.id_mesa ASC, p.data ASC, p.hora_pedido ASC";
$result = mysqli_query($conn, $query);

$mesas_pedidos = array();
while($row = mysqli_fetch_assoc($result)) {
    $id_mesa = $row['id_mesa'];
    if (!isset($mesas_pedidos[$id_mesa])) {
        $mesas_pedidos[$id_mesa] = [
            'nome' => $row['nome'],
            'mesa_status' => $row['mesa_status'],
            'pedidos' => []
        ];
    }
    if ($row['idpedido']) {
        $mesas_pedidos[$id_mesa]['pedidos'][] = [
            'idpedido' => $row['idpedido'],
            'valor_total' => $row['valor_total'],
            'status' => $row['pedido_status'],
            'data' => $row['data'],
            'hora_pedido' => $row['hora_pedido']
        ];
    }
}
// Ordenar mesas numericamente
uksort($mesas_pedidos, function($a, $b) { return (int)$a - (int)$b; });

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
        <?php foreach ($mesas_pedidos as $id_mesa => $mesa):
            $tem_pedidos = count($mesa['pedidos']) > 0;
            $status_class = $tem_pedidos ? 'mesa-ocupada' : 'mesa-livre';
            $status_text = $tem_pedidos ? 'Ocupada' : 'Livre';
        ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow h-100 py-2 mesa-card <?php echo $status_class; ?>">
                <div class="card-body">
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        Mesa <?php echo $id_mesa; ?>
                    </div>
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Status: <?php echo $status_text; ?>
                    </div>
                    <?php if ($tem_pedidos): ?>
                        <div class="mb-2">
                            <?php $total = 0; foreach($mesa['pedidos'] as $pedido): $total += $pedido['valor_total']; ?>
                                <div class="d-flex justify-content-between align-items-center mb-1 bg-light text-dark rounded px-2 py-1">
                                    <span>Pedido #<?php echo $pedido['idpedido']; ?></span>
                                    <span>R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></span>
                                    <button class="btn btn-info btn-sm ms-2" onclick="verPedido(<?php echo $pedido['idpedido']; ?>)">
                                        <i class="fas fa-eye"></i> Ver Pedido
                                    </button>
                                </div>
                            <?php endforeach; ?>
                            <div class="fw-bold mt-2">Total: R$ <?php echo number_format($total, 2, ',', '.'); ?></div>
                            <button class="btn btn-success btn-sm mt-2" onclick="fecharTodosPedidos(<?php echo $id_mesa; ?>)"><i class="fas fa-check"></i> Fechar Todos</button>
                        </div>
                    <?php endif; ?>
                    <a href="<?php echo $config->url("?view=gerar_pedido&mesa=") . $id_mesa; ?>" class="btn btn-primary btn-sm mt-2">
                        <i class="fas fa-plus-circle"></i> Fazer Pedido
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal de Pedido -->
<div id="pedidoModal" class="pedido-modal">
    <div class="pedido-modal-content">
        <span class="close-modal" onclick="fecharModal()">&times;</span>
        <div id="pedidoConteudo"></div>
    </div>
</div>

<!-- Modal de Edição de Pedido -->
<div id="editarPedidoModal" class="pedido-modal">
    <div class="pedido-modal-content" style="max-width: 700px;">
        <span class="close-modal" onclick="fecharEditarPedidoModal()">&times;</span>
        <h4>Editar Pedido</h4>
        <form id="formEditarPedido">
            <div class="form-group mb-2">
                <label for="editarMesa">Mesa</label>
                <select id="editarMesa" name="mesa" class="form-control"></select>
            </div>
            <div id="itensPedidoEdit"></div>
            <button type="button" class="btn btn-primary btn-sm mt-2" onclick="adicionarItemEdit()">Adicionar Item</button>
            <div class="text-end mt-3">
                <button type="button" class="btn btn-success" onclick="salvarEdicaoPedido()">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script>
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
                                    ${data.pedido.status === 'Entregue' || data.pedido.status === 'Entregue (Mesa)' ? `
                                        <button onclick="fecharPedido(${pedidoId})" class="btn btn-success btn-sm">
                                            <i class="fas fa-check-circle"></i> Fechar Pedido
                                        </button>
                                    ` : (data.pedido.status === 'Ocupada' || data.pedido.status === 'Pendente') ? `
                                        <button onclick="entregarPedido(${pedidoId})" class="btn btn-info btn-sm">
                                            <i class="fas fa-utensils"></i> Entregar Pedido
                                        </button>
                                    ` : ''}
                                    <button onclick="editarPedido(${pedidoId})" class="btn btn-warning btn-sm ms-2">
                                        <i class="fas fa-edit"></i> Editar Pedido
                                    </button>
                                    <button onclick="imprimirPedido(${pedidoId})" class="btn btn-secondary btn-sm ms-2">
                                        <i class="fas fa-print"></i> Imprimir Pedido
                                    </button>
                                    <button onclick="excluirPedido(${pedidoId})" class="btn btn-danger btn-sm ms-2">
                                        <i class="fas fa-trash"></i> Excluir
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="pedido-content" style="display: block;">`;
                
                data.itens.forEach(item => {
                    let nomeProduto = item.produto;
                    if (item.tamanho && item.tamanho.toLowerCase() === 'mini' && !nomeProduto.toLowerCase().startsWith('mini ')) {
                        nomeProduto = 'Mini ' + nomeProduto;
                    }
                    html += `
                        <div class="item-pedido">
                            <div class="produto-linha">
                                <div class="produto-info">
                                    <span class="produto-nome">${item.quantidade}x ${nomeProduto}</span>
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
                location.reload();
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

function editarPedido(pedidoId) {
    // Buscar dados do pedido e mesas disponíveis
    Promise.all([
        fetch(`MVC/MODEL/buscar_pedido.php?pedido_id=${pedidoId}`).then(r => r.json()),
        fetch('MVC/MODEL/buscar_mesas.php').then(r => r.json())
    ]).then(([pedidoData, mesasData]) => {
        if (!pedidoData.success) return alert('Erro ao buscar pedido');
        if (!mesasData.success) return alert('Erro ao buscar mesas');
        // Preencher select de mesas
        const selectMesa = document.getElementById('editarMesa');
        selectMesa.innerHTML = mesasData.mesas.map(m => `<option value="${m.id_mesa}" ${m.id_mesa == pedidoData.pedido.idmesa ? 'selected' : ''}>Mesa ${m.id_mesa}</option>`).join('');
        // Preencher itens do pedido
        const itensDiv = document.getElementById('itensPedidoEdit');
        itensDiv.innerHTML = pedidoData.itens.map((item, idx) => renderItemEdit(item, idx)).join('');
        // Exibir modal
        document.getElementById('editarPedidoModal').style.display = 'block';
        // Guardar id do pedido em edição
        document.getElementById('formEditarPedido').setAttribute('data-pedido-id', pedidoId);
    }).catch(() => alert('Erro ao carregar dados para edição.'));
}

function fecharEditarPedidoModal() {
    document.getElementById('editarPedidoModal').style.display = 'none';
}

function renderItemEdit(item, idx) {
    return `<div class="card mb-2 p-2" data-idx="${idx}">
        <div class="row align-items-center">
            <div class="col-5">
                <input type="text" class="form-control form-control-sm" name="produto[]" value="${item.produto}" readonly>
            </div>
            <div class="col-2">
                <input type="number" class="form-control form-control-sm" name="quantidade[]" value="${item.quantidade}" min="1">
            </div>
            <div class="col-3">
                <input type="text" class="form-control form-control-sm" name="observacao[]" value="${item.observacao || ''}" placeholder="Observação">
            </div>
            <div class="col-2 text-end">
                <button type="button" class="btn btn-danger btn-sm" onclick="removerItemEdit(${idx})">Remover</button>
            </div>
        </div>
    </div>`;
}

function adicionarItemEdit() {
    // Aqui futuramente buscar produtos do backend
    const idx = document.querySelectorAll('#itensPedidoEdit .card').length;
    const novoItem = { produto: '', quantidade: 1, observacao: '' };
    const div = document.createElement('div');
    div.innerHTML = renderItemEdit(novoItem, idx);
    document.getElementById('itensPedidoEdit').appendChild(div.firstChild);
}

function removerItemEdit(idx) {
    const card = document.querySelector(`#itensPedidoEdit .card[data-idx='${idx}']`);
    if (card) card.remove();
}

function salvarEdicaoPedido() {
    const form = document.getElementById('formEditarPedido');
    const formData = new FormData(form);
    formData.append('pedido_id', form.getAttribute('data-pedido-id'));
    fetch('MVC/MODEL/editar_pedido.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            fecharEditarPedidoModal();
            location.reload();
        } else {
            alert('Erro ao salvar edição: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(() => alert('Erro ao salvar edição.'));
}

// Fechar modal quando clicar fora dele
window.onclick = function(event) {
    const modal = document.getElementById('pedidoModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

// Adicionar função de impressão
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
            location.reload();
        } else {
            alert('Erro ao excluir pedido: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(() => alert('Erro ao excluir pedido.'));
}

function fecharTodosPedidos(idMesa) {
    if (!confirm('Tem certeza que deseja fechar todos os pedidos abertos desta mesa?')) return;
    fetch('MVC/MODEL/fechar_todos_pedidos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_mesa=${idMesa}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erro ao fechar pedidos: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(() => alert('Erro ao fechar pedidos.'));
}
</script>