<?php
require_once 'MVC/COMMON/header.php';
require_once 'MVC/MODEL/conexao.php';

// Get products with low stock
$sql = "SELECT p.nome, e.estoque_atual, e.estoque_minimo
        FROM produtos p 
        LEFT JOIN estoque e ON p.id = e.produto_id
        WHERE e.estoque_atual <= e.estoque_minimo
        ORDER BY p.nome";
$result = mysqli_query($conn, $sql);

// Store data for export
$produtos_baixo_estoque = array();
while ($row = mysqli_fetch_assoc($result)) {
    $produtos_baixo_estoque[] = $row;
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h2 class="text-danger mb-0">Produtos com Estoque Baixo!</h2>
        <button class="btn btn-success" id="exportarExcel">
            <i class="fas fa-file-excel"></i> Exportar Relatório
        </button>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <?php foreach ($produtos_baixo_estoque as $produto): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                        <p class="card-text">
                            Estoque atual: <span class="text-danger"><?php echo (int)$produto['estoque_atual']; ?></span> unidades<br>
                            Estoque mínimo: <?php echo (int)$produto['estoque_minimo']; ?> unidades
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($produtos_baixo_estoque)): ?>
                <div class="alert alert-success text-center">
                    <i class="fas fa-check-circle"></i> Não há produtos com estoque baixo!
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- SheetJS -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
document.getElementById('exportarExcel').addEventListener('click', function() {
    // Preparar dados para exportação
    var dados = [
        ['Nome do Produto', 'Estoque Atual', 'Estoque Mínimo'] // Cabeçalho
    ];

    // Adicionar dados dos produtos
    <?php foreach ($produtos_baixo_estoque as $produto): ?>
    dados.push([
        '<?php echo addslashes($produto['nome']); ?>',
        <?php echo (int)$produto['estoque_atual']; ?>,
        <?php echo (int)$produto['estoque_minimo']; ?>
    ]);
    <?php endforeach; ?>

    // Criar planilha
    var ws = XLSX.utils.aoa_to_sheet(dados);
    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Estoque Baixo");

    // Gerar arquivo
    XLSX.writeFile(wb, 'relatorio_estoque_baixo.xlsx');
});
</script>

<?php require_once 'MVC/COMMON/footer.php'; ?>