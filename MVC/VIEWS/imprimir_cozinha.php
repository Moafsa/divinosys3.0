<?php
session_start();
require_once '../MODEL/conexao.php';

// Verifica se o ID do pedido foi fornecido
if (!isset($_GET['pedido_id'])) {
    die('ID do pedido não fornecido');
}

$pedido_id = $_GET['pedido_id'];

try {
    // Busca informações do pedido
    $stmt = $pdo->prepare("
        SELECT p.*, pr.nome as produto_nome, pr.descricao
        FROM pedido p
        JOIN produtos pr ON p.id_produto = pr.id
        WHERE p.id_mesa = ? AND p.status != 'Finalizado'
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        die('Pedido não encontrado');
    }

    // Busca os ingredientes do produto
    $stmt = $pdo->prepare("
        SELECT i.nome, i.quantidade as qtd_padrao, pi.quantidade as qtd_pedido
        FROM pedido_ingredientes pi
        JOIN ingredientes i ON pi.ingrediente_id = i.id
        WHERE pi.pedido_id = ?
    ");
    $stmt->execute([$pedido_id]);
    $ingredientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('Erro ao buscar dados: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pedido Cozinha #<?php echo $pedido_id; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 14px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 24px;
            margin: 0;
        }
        .info-block {
            margin-bottom: 15px;
        }
        .info-block h2 {
            margin: 10px 0;
            font-size: 18px;
            border-bottom: 1px solid #ccc;
        }
        .ingredients-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .ingredients-list li {
            padding: 5px 0;
            border-bottom: 1px dashed #ccc;
            font-size: 16px;
        }
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PEDIDO #<?php echo $pedido_id; ?></h1>
        <p>Data: <?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></p>
    </div>

    <div class="info-block">
        <h2><?php echo htmlspecialchars($pedido['produto_nome']); ?></h2>
        <p><strong>Quantidade:</strong> <?php echo $pedido['quantidade']; ?></p>
    </div>

    <div class="info-block">
        <h2>Ingredientes:</h2>
        <ul class="ingredients-list">
            <?php foreach ($ingredientes as $ingrediente): ?>
            <li>
                <?php echo htmlspecialchars($ingrediente['nome']); ?>: 
                <?php echo $ingrediente['qtd_pedido']; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <script>
        // Imprime automaticamente quando a página carregar
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html> 