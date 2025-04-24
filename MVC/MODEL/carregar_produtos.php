<?php
include_once(__DIR__ . "/config.php");
include_once(__DIR__ . "/conexao.php");

if (!isset($_GET['categoria'])) {
    echo '<p class="text-center text-muted">Categoria não especificada</p>';
    exit;
}

$categoria = mysqli_real_escape_string($conn, $_GET['categoria']);

$query = "SELECT * FROM produtos WHERE categoria = ? ORDER BY nome ASC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $categoria);
mysqli_stmt_execute($stmt);
$produtos = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($produtos) > 0) {
    while ($produto = mysqli_fetch_assoc($produtos)) {
        ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card produto-card" onclick='abrirModalProduto(<?php echo json_encode($produto); ?>)'>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars($produto['descricao']); ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-primary">R$ <?php echo number_format($produto['preco_normal'], 2, ',', '.'); ?></span>
                        <?php if($produto['preco_mini'] > 0) { ?>
                            <span class="text-success">Mini: R$ <?php echo number_format($produto['preco_mini'], 2, ',', '.'); ?></span>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
} else {
    echo '<div class="col-12"><p class="text-center text-muted">Nenhum produto encontrado nesta categoria</p></div>';
} 