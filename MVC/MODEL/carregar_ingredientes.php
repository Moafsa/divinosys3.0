<?php
include_once(__DIR__ . "/config.php");
include_once(__DIR__ . "/conexao.php");

if (!isset($_GET['produto_id'])) {
    exit;
}

$produto_id = intval($_GET['produto_id']);

// Buscar ingredientes do produto
$query = "SELECT * FROM ingredientes WHERE produto_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $produto_id);
mysqli_stmt_execute($stmt);
$ingredientes = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($ingredientes) > 0) {
    echo '<div class="row">';
    while ($ingrediente = mysqli_fetch_assoc($ingredientes)) {
        ?>
        <div class="col-md-6">
            <div class="custom-control custom-switch ingrediente-toggle">
                <input type="checkbox" class="custom-control-input ingrediente-toggle" 
                       id="ingrediente_<?php echo $ingrediente['id']; ?>"
                       value="<?php echo $ingrediente['id']; ?>"
                       data-nome="<?php echo htmlspecialchars($ingrediente['nome']); ?>"
                       data-tipo="normal">
                <label class="custom-control-label" for="ingrediente_<?php echo $ingrediente['id']; ?>">
                    <?php echo htmlspecialchars($ingrediente['nome']); ?>
                </label>
            </div>
        </div>
        <?php
    }
    echo '</div>';
    echo '<div class="mt-3">';
    echo '<button type="button" class="btn btn-outline-danger btn-sm mr-2" onclick="marcarTodosSem()">Sem Todos</button>';
    echo '<button type="button" class="btn btn-outline-success btn-sm" onclick="marcarTodosExtra()">Extra Todos</button>';
    echo '</div>';
} else {
    echo '<p class="text-muted">Este produto não possui ingredientes personalizáveis.</p>';
}

// Adicionar JavaScript para manipulação dos ingredientes
?>
<script>
function marcarTodosSem() {
    $('.ingrediente-toggle').each(function() {
        $(this).prop('checked', true).data('tipo', 'sem');
        $(this).next('label').text('Sem ' + $(this).data('nome'));
    });
}

function marcarTodosExtra() {
    $('.ingrediente-toggle').each(function() {
        $(this).prop('checked', true).data('tipo', 'extra');
        $(this).next('label').text('Extra ' + $(this).data('nome'));
    });
}

// Alternar entre normal, sem e extra ao clicar
$('.ingrediente-toggle').click(function(e) {
    const checkbox = $(this);
    const label = checkbox.next('label');
    const nome = checkbox.data('nome');
    
    if (!checkbox.prop('checked')) {
        checkbox.data('tipo', 'normal');
        label.text(nome);
        return;
    }
    
    switch(checkbox.data('tipo')) {
        case 'normal':
            checkbox.data('tipo', 'sem');
            label.text('Sem ' + nome);
            break;
        case 'sem':
            checkbox.data('tipo', 'extra');
            label.text('Extra ' + nome);
            break;
        case 'extra':
            checkbox.prop('checked', false);
            checkbox.data('tipo', 'normal');
            label.text(nome);
            break;
    }
    
    e.preventDefault();
});
</script> 