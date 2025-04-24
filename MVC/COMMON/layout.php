<?php
// Verificar se há uma sessão ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se é uma página que não precisa de header/footer (como impressão)
$no_layout_pages = ['imprimir_pedido', 'imprimir_cozinha'];
$current_view = isset($_GET['view']) ? $_GET['view'] : '';

if (!in_array($current_view, $no_layout_pages)) {
    include_once(__DIR__ . "/header.php");
}
?>

<!-- Page Content -->
<div class="container-fluid">
    <?php 
    // Mensagem de feedback do sistema
    if (isset($_SESSION['msg'])) {
        echo $_SESSION['msg'];
        unset($_SESSION['msg']);
    }
    
    // Conteúdo da view
    if (isset($content)) {
        echo $content;
    }
    ?>
</div>

<?php
if (!in_array($current_view, $no_layout_pages)) {
    include_once(__DIR__ . "/footer.php");
}
?> 