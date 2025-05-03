<?php
session_start();
require_once '../MODEL/conexao.php';

// Verifica se o ID do pedido foi fornecido
if (!isset($_GET['pedido_id'])) {
    die('ID do pedido não fornecido');
}

$pedido_id = $_GET['pedido_id'];

try {
    // Busca informações do pedido e seus itens
    $stmt = $conn->prepare("
        SELECT 
            p.idpedido,
            p.idmesa,
            p.data,
            p.hora_pedido,
            p.status,
            p.valor_total,
            p.observacao,
            m.nome as mesa_nome,
            m.id_mesa as mesa_numero
        FROM pedido p
        LEFT JOIN mesas m ON p.idmesa = m.id_mesa
        WHERE p.idpedido = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Erro ao preparar consulta: " . $conn->error);
    }
    
    $stmt->bind_param("i", $pedido_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao executar consulta: " . $stmt->error);
    }
    
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

    if (!$stmt) {
        throw new Exception("Erro ao preparar consulta de itens: " . $conn->error);
    }

    $stmt->bind_param("i", $pedido_id);

    if (!$stmt->execute()) {
        throw new Exception("Erro ao executar consulta de itens: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $itens = [];
    while ($item = $result->fetch_assoc()) {
        // Processar ingredientes
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

        // Formatar o item igual ao carrinho
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

// Carregar configurações do estabelecimento
$config = require_once __DIR__ . '/../CONFIG/estabelecimento.php';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cupom Fiscal - Pedido #<?php echo $pedido_id; ?></title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 10px;
            width: <?php echo $config['printer']['width']; ?>mm;
            font-size: 12px;
            line-height: 1.2;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 10px;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .info-block {
            margin-bottom: 10px;
        }
        .item {
            margin-bottom: 5px;
        }
        .total {
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
        }
        .small-text {
            font-size: 10px;
        }
        @media print {
            body {
                width: 80mm;
                margin: 0;
                padding: 0;
            }
        }
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
        CUPOM FISCAL<br>
        Pedido: #<?php echo str_pad($pedido_id, 6, '0', STR_PAD_LEFT); ?><br>
        Data: <?php echo date('d/m/Y', strtotime($pedido['data'])) . ' ' . $pedido['hora_pedido']; ?><br>
        Mesa: <?php echo $pedido['mesa_numero']; ?>
    </div>

    <div class="divider"></div>

    <div class="items">
        <?php foreach ($itens as $item): ?>
        <div class="item">
            <?php
            $nomeProduto = $item['produto']['nome'];
            if ((isset($item['tamanho']) && $item['tamanho'] === 'mini') && stripos($nomeProduto, 'mini ') !== 0) {
                $nomeProduto = 'Mini ' . $nomeProduto;
            }
            ?>
            <?php echo $item['quantidade']; ?>x <?php echo htmlspecialchars($nomeProduto); ?><br>
            <?php if (!empty($item['ingredientes'])): ?>
                <span class="small-text">
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
            <span class="small-text">
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
        // Imprime automaticamente quando a página carregar
        window.onload = function() {
            window.print();
            // Fecha a janela após a impressão (opcional)
            // window.onafterprint = function() { window.close(); };
        };
    </script>
</body>
</html> 