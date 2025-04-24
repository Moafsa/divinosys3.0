<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $id = $_POST['id'];
        
        $sql = "DELETE FROM produtos WHERE id = ?";
        $stmt = $PDO->prepare($sql);
        $stmt->execute([$id]);

        $_SESSION['success'] = "Produto excluído com sucesso!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erro ao excluir produto: " . $e->getMessage();
    }
}

header('Location: ../../?view=gerenciar_produtos');
exit; 