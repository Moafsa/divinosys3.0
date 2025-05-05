<?php
session_start();
require_once '../MODEL/conexao.php';

if (!isset($_GET['pedido_id'])) {
    die('ID do pedido não fornecido');
}

$pedido_id = intval($_GET['pedido_id']);

try {
    // Buscar dados do pedido
    $stmt = $conn->prepare("
        SELECT 
            p.idpedido,
            p.idmesa,
            p.data,
            p.hora_pedido,
            p.status,
            p.valor_total,
            p.observacao,
            p.endereco_entrega,
            p.taxa_entrega,
            p.forma_pagamento,
            p.troco_para,
            p.ponto_referencia,
            m.nome as mesa_nome,
            m.id_mesa as mesa_numero,
            c.nome as cliente_nome,
            c.tel1 as cliente_tel,
            c.endereco as cliente_endereco,
            c.bairro as cliente_bairro,
            c.cidade as cliente_cidade,
            c.estado as cliente_estado
        FROM pedido p
        LEFT JOIN mesas m ON p.idmesa = m.id_mesa
        LEFT JOIN clientes c ON p.cliente_id = c.id
        WHERE p.idpedido = ?
    ");
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pedido = $result->fetch_assoc();
    if (!$pedido) {
        throw new Exception("Pedido não encontrado");
    }

    // Buscar itens do pedido
    $stmt = $conn->prepare("
        SELECT 
            pi.pedido_id,
            pi.produto_id as id,
            pi.quantidade,
            pi.valor_unitario as preco,
            pi.valor_total,
            pi.observacao,
            pi.tamanho,
            p.nome,
            GROUP_CONCAT(
                CASE 
                    WHEN pii.incluido = 1 THEN CONCAT('com:', i.nome)
                    WHEN pii.incluido = 0 THEN CONCAT('sem:', i.nome)
                    ELSE NULL 
                END
                SEPARATOR '|'
            ) as ingredientes_str
        FROM pedido_itens pi
        JOIN produtos p ON pi.produto_id = p.id
        LEFT JOIN pedido_item_ingredientes pii ON pi.id = pii.pedido_item_id
        LEFT JOIN ingredientes i ON pii.ingrediente_id = i.id
        WHERE pi.pedido_id = ?
        GROUP BY pi.id
    ");
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $itens = [];
    while ($item = $result->fetch_assoc()) {
        $ingredientes = [];
        if (!empty($item['ingredientes_str'])) {
            $ingredientes_array = explode('|', $item['ingredientes_str']);
            foreach ($ingredientes_array as $ing) {
                if (empty($ing)) continue;
                list($tipo, $nome) = explode(':', $ing);
                $ingredientes[] = [
                    'nome' => $nome,
                    'tipo' => $tipo
                ];
            }
        }
        $itens[] = [
            'produto' => [
                'id' => $item['id'],
                'nome' => $item['nome'],
                'preco' => $item['preco']
            ],
            'quantidade' => $item['quantidade'],
            'observacao' => $item['observacao'],
            'valor_total' => $item['valor_total'],
            'ingredientes' => $ingredientes,
            'tamanho' => $item['tamanho'] ?? 'normal'
        ];
    }
} catch (Exception $e) {
    die('Erro ao buscar dados: ' . $e->getMessage());
}

$config = require_once __DIR__ . '/../CONFIG/estabelecimento.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cupom Delivery - Pedido #<?php echo $pedido_id; ?></title>
    <style>
        @page { margin: 0; }
        body {
            font-family: Arial, Verdana, sans-serif;
            margin: 0;
            padding: 10px;
            width: <?php echo $config['printer']['width'] ?? 80; ?>mm;
            font-size: 17px;
            line-height: 1.2;
        }
        .header, .footer { text-align: center; margin-bottom: 10px; }
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        .info-block { margin-bottom: 10px; }
        .item { margin-bottom: 5px; }
        .item-main { font-weight: bold; }
        .produto-nome { font-weight: bold; }
        .item-detail { font-size: 17px; font-family: 'Times New Roman', Times, serif; }
        .mesa-detail { font-size: 17px; font-weight: bold; }
        .total { font-weight: bold; text-align: right; margin-top: 10px; font-size: 21px; }
        .small-text { font-size: 11px; }
        @media print { body { width: 80mm; margin: 0; padding: 0; } }
    </style>
</head>
<body>
    <div class="header">
        <strong><?php echo $config['nome_estabelecimento']; ?></strong><br>
        CNPJ: <?php echo $config['cnpj']; ?><br>
        <?php echo $config['endereco']; ?><br>
        Tel: <?php echo $config['telefone']; ?><br>
        <?php echo $config['site']; ?>
        <?php if (!empty($config['messages']['header'])): ?>
        <br><?php echo $config['messages']['header']; ?>
        <?php endif; ?>
    </div>
    <div class="divider"></div>
    <div class="info-block">
        <strong>CUPOM DELIVERY</strong><br>
        Pedido: #<?php echo str_pad($pedido_id, 6, '0', STR_PAD_LEFT); ?><br>
        Data: <?php echo date('d/m/Y', strtotime($pedido['data'])) . ' ' . $pedido['hora_pedido']; ?><br>
        <?php if (!empty($pedido['mesa_numero'])): ?>Mesa: <span class="mesa-detail"><?php echo $pedido['mesa_numero']; ?></span><br><?php endif; ?>
        <strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['cliente_nome'] ?? ''); ?><br>
        <strong>Telefone:</strong> <?php echo htmlspecialchars($pedido['cliente_tel'] ?? ''); ?><br>
        <strong>Endereço:</strong> <?php echo htmlspecialchars($pedido['cliente_endereco'] ?? ''); ?><br>
        <?php if (!empty($pedido['ponto_referencia'])): ?>
        <strong>Referência:</strong> <?php echo htmlspecialchars($pedido['ponto_referencia']); ?><br>
        <?php endif; ?>
        <?php if (!empty($pedido['taxa_entrega'])): ?>
        <strong>Taxa de Entrega:</strong> R$ <?php echo number_format($pedido['taxa_entrega'],2,',','.'); ?><br>
        <?php endif; ?>
        <?php if (!empty($pedido['forma_pagamento'])): ?>
        <strong>Pagamento:</strong> <?php echo htmlspecialchars($pedido['forma_pagamento']); ?>
        <?php if (!empty($pedido['troco_para'])): ?> (Troco para R$ <?php echo number_format($pedido['troco_para'],2,',','.'); ?>)<?php endif; ?><br>
        <?php endif; ?>
    </div>
    <div class="divider"></div>
    <div class="items">
        <?php foreach ($itens as $item): ?>
        <div class="item">
            <?php
            $nomeProduto = $item['produto']['nome'];
            if ((isset($item['tamanho']) && strtolower($item['tamanho']) === 'mini') && stripos($nomeProduto, 'mini ') !== 0) {
                $nomeProduto = 'Mini ' . $nomeProduto;
            }
            ?>
            <span class="item-main"><?php echo $item['quantidade']; ?>x <span class="produto-nome"><?php echo htmlspecialchars($nomeProduto); ?></span></span><br>
            <?php if (!empty($item['ingredientes'])): ?>
                <span class="item-detail">
                <?php
                $com = [];
                $sem = [];
                foreach ($item['ingredientes'] as $ing) {
                    if ($ing['tipo'] === 'com') {
                        $com[] = $ing['nome'];
                    } else if ($ing['tipo'] === 'sem') {
                        $sem[] = $ing['nome'];
                    }
                }
                if (!empty($com)) {
                    echo "COM: " . implode(', ', $com) . "<br>";
                }
                if (!empty($sem)) {
                    echo "SEM: " . implode(', ', $sem) . "<br>";
                }
                ?>
                </span>
            <?php endif; ?>
            <span class="item-detail">
                <?php 
                if (!empty($item['observacao'])) {
                    echo "OBS: " . $item['observacao'] . "<br>";
                }
                ?>
            </span>
            R$ <?php echo number_format($item['produto']['preco'], 2, ',', '.'); ?> UN
            R$ <?php echo number_format($item['valor_total'], 2, ',', '.'); ?>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="divider"></div>
    <div class="total">
        TOTAL: R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?>
    </div>
    <div class="divider"></div>
    <div class="footer">
        <?php echo $config['messages']['footer'] ?? ''; ?><br>
        <span class="small-text">
            <?php echo date('d/m/Y H:i:s'); ?><br>
            PDV v1.0
        </span>
    </div>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html> 