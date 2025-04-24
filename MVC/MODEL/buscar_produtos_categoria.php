<?php
// Garantir que a sessão está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir o arquivo de conexão
include_once(__DIR__ . "/../VIEWS/include_conexao.php");

// Verificar se o ID da categoria foi fornecido
if (!isset($_GET['categoria_id']) || empty($_GET['categoria_id'])) {
echo json_encode(['error' => 'ID da categoria não fornecido']);
    exit;
}

$categoria_id = filter_var($_GET['categoria_id'], FILTER_SANITIZE_NUMBER_INT);

try {
    // Buscar produtos da categoria
    $stmt = mysqli_prepare($conn, "SELECT * FROM produtos WHERE categoria_id = ? ORDER BY nome");
    mysqli_stmt_bind_param($stmt, "i", $categoria_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Construir HTML dos produtos
    $html = '';
    while ($produto = mysqli_fetch_assoc($result)) {
    $imagem = $produto['imagem'] ? $produto['imagem'] : 'MVC/COMMON/img/no-image.jpg';
        $html .= '
        <div class="col-md-4 mb-4">
            <div class="product-card" onclick="selecionarProduto(' . $produto['id'] . ')">
                <img src="' . htmlspecialchars($imagem) . '" 
                     class="product-img" 
                     alt="' . htmlspecialchars($produto['nome']) . '"
             onerror="this.src=\'MVC/COMMON/img/no-image.jpg\'">
                <div class="product-info">
    <h5>' . htmlspecialchars($produto['nome']) . '</h5>
                <p class="text-muted small mb-2">' . htmlspecialchars($produto['descricao']) . '</p>
                    <div class="product-price">
                        R$ ' . number_format($produto['preco_normal'], 2, ',', '.') . '
                    </div>
                </div>
            </div>
        </div>';
    }

    echo $html;

} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao buscar produtos: ' . $e->getMessage()]);
}
?> 