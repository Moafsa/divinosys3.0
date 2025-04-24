<?php
session_start();
include_once 'conexao.php';
include_once 'config.php';

$config = Config::getInstance();

// Verificar se está logado
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    $_SESSION['msg'] = "<div class='alert alert-danger'>Acesso não autorizado! Faça login primeiro.</div>";
    header("Location: " . $config->url(''));
    exit;
}

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['swal_msg'] = json_encode([
        'title' => 'Erro!',
        'text' => 'ID da categoria não fornecido ou inválido',
        'icon' => 'error'
    ]);
    header("Location: " . $config->url('?view=gerenciar_categorias'));
    exit;
}

$id = intval($_GET['id']);

try {
    // Primeiro, buscar a imagem da categoria
    $query = "SELECT imagem FROM categorias WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao buscar categoria: " . mysqli_error($conn));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    $categoria = mysqli_fetch_assoc($result);
    
    // Excluir a categoria
    $query = "DELETE FROM categorias WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao excluir categoria: " . mysqli_error($conn));
    }
    
    // Se a categoria tinha uma imagem, excluir o arquivo
    if ($categoria && $categoria['imagem']) {
        $imagem_path = dirname(dirname(dirname(__FILE__))) . '/' . $categoria['imagem'];
        if (file_exists($imagem_path)) {
            unlink($imagem_path);
        }
    }
    
    $_SESSION['swal_msg'] = json_encode([
        'title' => 'Sucesso!',
        'text' => 'Categoria excluída com sucesso!',
        'icon' => 'success'
    ]);
    
} catch (Exception $e) {
    $_SESSION['swal_msg'] = json_encode([
        'title' => 'Erro!',
        'text' => $e->getMessage(),
        'icon' => 'error'
    ]);
}

header("Location: " . $config->url('?view=gerenciar_categorias'));
exit;
?> 