<?php
include_once(__DIR__ . '/../MODEL/conexao.php');
// Função para normalizar strings removendo acentuação e convertendo para minúsculo
function normalize($str) {
    return strtolower(
        preg_replace('/[ç]/u','c',
            preg_replace('/[úùûü]/u','u',
                preg_replace('/[óòõôö]/u','o',
                    preg_replace('/[íìîï]/u','i',
                        preg_replace('/[éèêë]/u','e',
                            preg_replace('/[áàãâä]/u','a', $str)
                        )
                    )
                )
            )
        )
    );
}
// Buscar todos os pedidos delivery
        $sql = "SELECT p.*, c.nome as cliente_nome, c.tel1 as cliente_tel, c.endereco as cliente_endereco, c.bairro as cliente_bairro, c.cidade as cliente_cidade, c.estado as cliente_estado
                FROM pedido p
                LEFT JOIN clientes c ON p.cliente_id = c.id
        WHERE (p.tipo = 'delivery' OR p.endereco_entrega IS NOT NULL)
                ORDER BY p.data DESC, p.hora_pedido DESC";
        $result = mysqli_query($conn, $sql);
$pedidos = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($pedido = mysqli_fetch_assoc($result)) {
        $pedidos[] = $pedido;
    }
}
$status_cols = [
    'Pendente' => 'Pendente',
    'Em Preparo' => 'Em Preparo',
    'Saiu para Entrega' => 'Saiu para Entrega',
    'Entregue' => 'Entregue',
    'Cancelado' => 'Cancelado'
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pedidos Delivery</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f7f7f7; margin: 0; font-family: 'Segoe UI', Arial, sans-serif; }
        .delivery-header { background: #fff; padding: 32px 0 18px 0; border-bottom: 1px solid #e3e6f0; margin-bottom: 0; }
        .delivery-title { font-size: 2.2rem; font-weight: 800; color: #222; letter-spacing: -1px; margin-left: 32px; }
        .btn-novo { background: #21c77a; color: #fff; border: none; border-radius: 22px; font-weight: 700; font-size: 1.1em; padding: 12px 32px; margin-right: 32px; cursor: pointer; transition: background 0.15s; }
        .btn-novo:hover { background: #179e5c; }
        .search-bar { width: 100%; max-width: 400px; margin: 24px auto 0 auto; display: flex; justify-content: center; }
        .search-bar input { width: 100%; padding: 12px 18px; border-radius: 22px; border: 1px solid #ddd; font-size: 1.1em; outline: none; }
        .pipeline-board { display: flex; gap: 24px; overflow-x: auto; padding: 32px 16px 32px 16px; max-width: 100vw; }
        .pipeline-col { background: #f9f9f9; border-radius: 16px; min-width: 320px; max-width: 340px; flex: 1 1 320px; padding: 0 10px 18px 10px; box-shadow: 0 2px 12px rgba(44,62,80,0.06); display: flex; flex-direction: column; }
        .pipeline-col-title { font-size: 1.15em; font-weight: 700; color: #555; padding: 18px 0 12px 0; text-align: center; letter-spacing: 0.5px; }
        .card-delivery { background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(44,62,80,0.10); padding: 18px 14px 14px 14px; margin-bottom: 18px; border: none; display: flex; flex-direction: column; gap: 8px; }
        .card-title { font-size: 1.1rem; font-weight: 700; color: #e21a1a; margin-bottom: 4px; display: flex; align-items: center; justify-content: space-between; }
        .badge-status { font-size: 0.98em; font-weight: 600; border-radius: 12px; padding: 4px 14px; color: #fff; margin-left: 8px; letter-spacing: 0.5px; }
        .badge-status[data-status="Pendente"] { background: #e21a1a; }
        .badge-status[data-status="Em Preparo"] { background: #ff9800; }
        .badge-status[data-status="Saiu para Entrega"] { background: #1976d2; }
        .badge-status[data-status="Entregue"] { background: #43a047; }
        .badge-status[data-status="Cancelado"] { background: #757575; }
        .info-row { font-size: 1em; color: #444; margin-bottom: 2px; }
        .info-label { font-weight: 600; color: #888; margin-right: 4px; }
        .valor-total { font-size: 1.13em; font-weight: 800; color: #43a047; margin: 6px 0 2px 0; }
        .data-hora { font-size: 0.98em; color: #888; margin-bottom: 6px; }
        .btn-group {
            display: flex;
            gap: 6px;
            margin-top: 10px;
            flex-wrap: wrap;
            width: 100%;
            justify-content: stretch;
        }
        .btn-ifood {
            border-radius: 22px;
            font-weight: 500;
            font-size: 0.95em;
            padding: 7px 0;
            min-width: 0;
            max-width: 100%;
            white-space: nowrap;
        }
        .btn-ifood i {
            font-size: 1em;
            margin-right: 5px;
        }
        .btn-ifood-eye { background: #1976d2; color: #fff; }
        .btn-ifood-eye:hover, .btn-ifood-eye:focus { background: #125ea2; box-shadow: 0 4px 16px rgba(25,118,210,0.13); }
        .btn-ifood-print { background: #757575; color: #fff; }
        .btn-ifood-print:hover, .btn-ifood-print:focus { background: #555; box-shadow: 0 4px 16px rgba(117,117,117,0.13); }
        .btn-ifood-done { background: #43a047; color: #fff; }
        .btn-ifood-done:hover, .btn-ifood-done:focus { background: #2e7031; box-shadow: 0 4px 16px rgba(67,160,71,0.13); }
        .btn-ifood-delete {
            background: #e53935;
            color: #fff;
        }
        .btn-ifood-delete:hover, .btn-ifood-delete:focus {
            background: #b71c1c;
            box-shadow: 0 4px 16px rgba(229,57,53,0.13);
        }
        @media (max-width: 900px) {
            .pipeline-board { flex-direction: column; gap: 0; }
            .pipeline-col { max-width: 100vw; min-width: 95vw; margin-bottom: 24px; }
            .btn-group { flex-direction: column; gap: 6px; }
            .btn-ifood { width: 100%; font-size: 1em; padding: 10px 0; }
        }
        @media (max-width: 600px) {
            .pipeline-board { 
                padding: 0 !important; 
                overflow-x: hidden !important;
            }
            .pipeline-col { 
                width: 100% !important; 
                min-width: 0 !important; 
                max-width: 100% !important; 
                padding: 0 !important; 
                margin: 0 !important;
            }
            .card-delivery { 
                padding: 6px 0 !important; 
                border-radius: 8px !important; 
                margin: 0 !important;
            }
            .btn-group { 
                flex-direction: column !important; 
                gap: 2px !important; 
                padding: 0 !important; 
                margin: 0 !important;
            }
            .btn-ifood {
                width: 100% !important;
                font-size: 0.78em !important;
                padding: 5px 0 !important;
                border-radius: 12px !important;
                font-weight: 500 !important;
                margin: 0 !important;
                flex: none !important;
                min-width: 0 !important;
                max-width: 100% !important;
                letter-spacing: 0 !important;
            }
            .btn-ifood i { font-size: 0.9em !important; }
        }
        /* Modal */
        .pedido-modal { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.55); z-index: 9999; align-items: center; justify-content: center; }
        .pedido-modal.show { display: flex !important; animation: fadeIn 0.2s; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .pedido-modal-content { background: #fff; border-radius: 16px; max-width: 420px; width: 95vw; margin: 0 auto; padding: 32px 20px 18px 20px; box-shadow: 0 8px 32px rgba(44,62,80,0.18); position: relative; max-height: 90vh; overflow-y: auto; animation: modalPop 0.2s; }
        @keyframes modalPop { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .close-modal { position: absolute; right: 18px; top: 14px; font-size: 2rem; color: #888; cursor: pointer; z-index: 2; }
        *,
        *::before,
        *::after {
            box-sizing: border-box !important;
        }
    </style>
</head>
<body>
    <div class="delivery-header" style="display:flex;align-items:center;justify-content:space-between;">
        <span class="delivery-title">Pedidos Delivery</span>
        <a href="?view=gerar_pedido_delivery" class="btn-novo"><i class="fas fa-plus-circle"></i> Novo Pedido Delivery</a>
    </div>
    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Buscar por cliente, telefone, endereço ou pedido..." oninput="filtrarPedidos()">
    </div>
    <div class="pipeline-board" id="pipelineBoard">
        <?php foreach ($status_cols as $status_key => $status_label): ?>
        <div class="pipeline-col" data-status="<?php echo $status_key; ?>">
            <div class="pipeline-col-title"><?php echo $status_label; ?></div>
            <div class="pipeline-col-cards">
            <?php foreach ($pedidos as $pedido): if ($pedido['status'] !== $status_key) continue; ?>
                <?php
                    // Montar string de busca sem acentuação e em minúsculo
                    $search_str = normalize($pedido['idpedido'].' '.($pedido['cliente_nome'] ?? '').' '.($pedido['cliente_tel'] ?? '').' '.($pedido['endereco_entrega'] ?? $pedido['cliente_endereco'] ?? ''));
                ?>
                <div class="card-delivery" data-search="<?php echo htmlspecialchars($search_str); ?>">
                    <div class="card-title">
                        Pedido #<?php echo $pedido['idpedido']; ?>
                        <span class="badge-status" data-status="<?php echo $pedido['status']; ?>"><?php echo $pedido['status']; ?></span>
                    </div>
                    <div class="info-row"><span class="info-label">Cliente:</span> <?php echo htmlspecialchars($pedido['cliente_nome'] ?? $pedido['nome_cliente'] ?? '-'); ?></div>
                    <div class="info-row"><span class="info-label">Telefone:</span> <?php echo htmlspecialchars($pedido['cliente_tel'] ?? $pedido['telefone'] ?? '-'); ?></div>
                    <div class="info-row"><span class="info-label">Endereço:</span> <?php echo htmlspecialchars($pedido['endereco_entrega'] ?? $pedido['cliente_endereco'] ?? '-'); ?></div>
                    <div class="valor-total">Valor Total: R$ <?php echo number_format($pedido['valor_total'],2,',','.'); ?></div>
                    <div class="data-hora"><?php echo htmlspecialchars($pedido['data']).' '.htmlspecialchars($pedido['hora_pedido']); ?></div>
                    <div class="btn-group">
                        <button class="btn-ifood btn-ifood-eye" onclick="verPedido(<?php echo $pedido['idpedido']; ?>)"><i class="fas fa-eye"></i> Ver Pedido</button>
                        <button class="btn-ifood btn-ifood-print" onclick="imprimirPedidoDelivery(<?php echo $pedido['idpedido']; ?>)"><i class="fas fa-print"></i> Imprimir</button>
                        <?php if ($pedido['status'] != 'Entregue' && $pedido['status'] != 'Finalizado' && $pedido['status'] != 'Cancelado'): ?>
                        <button class="btn-ifood btn-ifood-done" onclick="marcarEntregue(<?php echo $pedido['idpedido']; ?>)"><i class="fas fa-check"></i> Marcar como Entregue</button>
                        <?php endif; ?>
                        <button class="btn-ifood btn-ifood-delete" onclick="excluirPedido(<?php echo $pedido['idpedido']; ?>)"><i class="fas fa-trash"></i> Excluir</button>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <!-- Modal de Pedido -->
    <div id="pedidoModal" class="pedido-modal">
        <div class="pedido-modal-content">
            <span class="close-modal" onclick="fecharModal()">&times;</span>
            <div id="pedidoConteudo"></div>
	</div>
</div>
<script>
    function filtrarPedidos() {
        const termo = document.getElementById('searchInput').value.toLowerCase().normalize('NFD').replace(/\p{Diacritic}/gu, '');
        document.querySelectorAll('.card-delivery').forEach(card => {
            const search = card.getAttribute('data-search');
            card.style.display = (!termo || search.includes(termo)) ? '' : 'none';
        });
    }
function marcarEntregue(pedidoId) {
    if (!confirm('Marcar pedido como Entregue?')) return;
    fetch('MVC/MODEL/atualizar_status_pedido.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `pedido_id=${pedidoId}&status=Entregue`
    })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); else alert('Erro ao atualizar status: ' + (data.message || 'Erro desconhecido')); })
        .catch(() => alert('Erro ao atualizar status.'));
    }
    function imprimirPedidoDelivery(pedidoId) {
        window.open('MVC/VIEWS/imprimir_pedido_delivery.php?pedido_id=' + pedidoId, '_blank');
    }
    function verPedido(pedidoId) {
        fetch('MVC/MODEL/buscar_pedido.php?pedido_id=' + pedidoId)
            .then(response => response.json())
            .then(data => {
                if (!data.success) return alert('Erro ao buscar pedido');
                let html = `<div class='pedido-card expanded'><div class='pedido-header'><div><strong>Pedido #${pedidoId}</strong><div class='text-muted'>${data.pedido.data} ${data.pedido.hora_pedido}</div><div class='text-muted'>Status: ${data.pedido.status}</div></div></div><div class='pedido-content' style='display: block;'>`;
                data.itens.forEach(item => {
                    let nomeProduto = item.produto;
                    if (item.tamanho && item.tamanho.toLowerCase() === 'mini' && !nomeProduto.toLowerCase().startsWith('mini ')) {
                        nomeProduto = 'Mini ' + nomeProduto;
                    }
                    html += `<div class='item-pedido'><div class='produto-linha'><div class='produto-info'><span class='produto-nome'>${nomeProduto}</span><span class='badge bg-secondary'>${item.quantidade}x</span></div><div class='text-end'>R$ ${parseFloat(item.valor_total).toFixed(2)}</div></div>`;
                    if (item.ingredientes_com) {
                        const ingredientesCom = typeof item.ingredientes_com === 'string' ? item.ingredientes_com.split(',').map(i => i.trim()).filter(i => i) : Array.isArray(item.ingredientes_com) ? item.ingredientes_com : [];
                        if (ingredientesCom.length > 0) {
                            html += `<div class='ingredientes-container'><div class='ingredientes text-success'><i class='fas fa-plus-circle'></i><span>COM: ${ingredientesCom.join(', ')}</span></div></div>`;
                        }
                    }
                    if (item.ingredientes_sem) {
                        const ingredientesSem = typeof item.ingredientes_sem === 'string' ? item.ingredientes_sem.split(',').map(i => i.trim()).filter(i => i) : Array.isArray(item.ingredientes_sem) ? item.ingredientes_sem : [];
                        if (ingredientesSem.length > 0) {
                            html += `<div class='ingredientes-container'><div class='ingredientes text-danger'><i class='fas fa-minus-circle'></i><span>SEM: ${ingredientesSem.join(', ')}</span></div></div>`;
                        }
                    }
                    if (item.observacao && item.observacao.trim()) {
                        html += `<div class='observacao'><i class='fas fa-info-circle'></i><span>Observação: ${item.observacao}</span></div>`;
                    }
                    html += `</div>`;
                });
                html += `<div class='valor-total mt-3 text-end'><strong>Total: R$ ${parseFloat(data.pedido.valor_total).toFixed(2)}</strong></div></div></div>`;
                document.getElementById('pedidoConteudo').innerHTML = html;
                document.getElementById('pedidoModal').classList.add('show');
            })
            .catch(() => alert('Erro ao buscar detalhes do pedido.'));
    }
    function fecharModal() {
        document.getElementById('pedidoModal').classList.remove('show');
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
</script>
</body>
</html>
