<?php
/**
 * Component for displaying an order card
 * @param int $pedido_id - The order ID
 * @param array $dados - The order data
 * @param string $status - Current status of the order
 */

// Define next action based on current status
$nextAction = '';
$nextActionClass = '';
$nextActionText = '';

switch ($status) {
    case 'Pendente':
        $nextAction = 'Em Preparo';
        $nextActionClass = 'btn-primary';
        $nextActionText = 'Iniciar Preparo';
        break;
    case 'Em Preparo':
        $nextAction = 'Pronto';
        $nextActionClass = 'btn-success';
        $nextActionText = 'Marcar Pronto';
        break;
    case 'Pronto':
        $nextAction = 'Entregue';
        $nextActionClass = 'btn-info';
        $nextActionText = 'Marcar Entregue';
        break;
    case 'Entregue':
    case 'Cancelado':
        $nextAction = '';
        break;
}
?>

<div class="pedido-card">
    <div class="pedido-header">
        <h6 class="mb-0">Pedido #<?php echo $pedido_id; ?></h6>
        <div class="d-flex align-items-center">
            <small class="me-2"><?php echo date('H:i', strtotime($dados['pedido']['hora_pedido'])); ?></small>
            <div class="btn-group">
                <button class="btn btn-outline-primary btn-sm" onclick="editarPedido(<?php echo $pedido_id; ?>)" title="Editar pedido">
                    <i class="fas fa-pencil-alt"></i>
                </button>
                <?php if ($status !== 'Entregue'): ?>
                    <button class="btn btn-outline-warning btn-sm" onclick="cancelarPedido(<?php echo $pedido_id; ?>)" title="Cancelar pedido">
                        <i class="fas fa-ban"></i>
                    </button>
                <?php endif; ?>
                <button class="btn btn-outline-danger btn-sm" onclick="excluirPedido(<?php echo $pedido_id; ?>)" title="Excluir pedido">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <?php if (!empty($dados['pedido']['cliente'])): ?>
        <div class="mb-2">
            <small class="text-muted">Cliente: <?php echo htmlspecialchars($dados['pedido']['cliente']); ?></small>
        </div>
    <?php endif; ?>

    <?php foreach ($dados['itens'] as $item): ?>
        <div class="item-pedido">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="quantidade"><?php echo $item['quantidade']; ?>x</span>
                    <?php
                    $nomeProduto = $item['produto'];
                    if ((isset($item['tamanho']) && $item['tamanho'] === 'mini') && stripos($nomeProduto, 'mini ') !== 0) {
                        $nomeProduto = 'Mini ' . $nomeProduto;
                    }
                    ?>
                    <span class="produto"><?php echo htmlspecialchars($nomeProduto); ?></span>
                </div>
                <div class="valor">
                    R$ <?php echo number_format($item['valor_total'], 2, ',', '.'); ?>
                </div>
            </div>
            
            <?php if (!empty($item['ingredientes'])): ?>
                <div class="ingredientes-lista">
                    <?php
                    $ingredientes_array = array('sem' => array(), 'com' => array());
                    $ingredientes_raw = array_filter(explode(';', $item['ingredientes']));
                    
                    foreach ($ingredientes_raw as $ingrediente) {
                        $partes = explode('|', trim($ingrediente));
                        if (count($partes) == 2) {
                            list($nome, $tipo) = $partes;
                            $tipo = trim(strtolower($tipo));
                            if (in_array($tipo, ['sem', 'com'])) {
                                $ingredientes_array[$tipo][] = trim($nome);
                            }
                        }
                    }
                    
                    // Debug
                    error_log("Processando ingredientes para item {$item['item_id']}:");
                    error_log("Raw: " . $item['ingredientes']);
                    error_log("Processado: " . print_r($ingredientes_array, true));
                    
                    // Display removed ingredients first
                    if (!empty($ingredientes_array['sem'])): ?>
                        <div class="text-danger small mt-1">
                            <i class="fas fa-minus-circle me-1"></i>
                            Sem: <?php echo implode(', ', array_map('htmlspecialchars', array_unique($ingredientes_array['sem']))); ?>
                        </div>
                    <?php endif;
                    
                    // Then display added ingredients
                    if (!empty($ingredientes_array['com'])): ?>
                        <div class="text-success small mt-1">
                            <i class="fas fa-plus-circle me-1"></i>
                            Com: <?php echo implode(', ', array_map('htmlspecialchars', array_unique($ingredientes_array['com']))); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php 
            // Debug da observação
            error_log("Observação para item {$item['item_id']}: " . print_r($item['observacao_item'], true));
            
            if (!empty($item['observacao_item'])): ?>
                <div class="observacao">
                    <i class="fas fa-comment-alt me-1"></i>
                    <span><?php echo htmlspecialchars($item['observacao_item']); ?></span>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <?php if (!empty($nextAction)): ?>
            <button class="btn btn-sm <?php echo $nextActionClass; ?>" onclick="atualizarStatus(<?php echo $pedido_id; ?>, '<?php echo $nextAction; ?>')">
                <?php echo $nextActionText; ?>
            </button>
        <?php endif; ?>
        <span class="text-muted">R$ <?php echo number_format($dados['pedido']['valor_total'], 2, ',', '.'); ?></span>
    </div>
</div> 