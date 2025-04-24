<?php
session_start();
require_once 'conexao.php';

if (isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        
        $sql = "SELECT p.*, c.nome as categoria_nome 
                FROM produtos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.id = ?";
                
        $stmt = $PDO->prepare($sql);
        $stmt->execute([$id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            echo json_encode([
                'success' => true,
                'produto' => $produto
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Produto não encontrado'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao buscar produto: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID do produto não fornecido'
    ]);
} 