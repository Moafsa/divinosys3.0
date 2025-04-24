<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $id = $_POST['id'];
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'];
        $preco = str_replace(',', '.', $_POST['preco']);
        $categoria_id = $_POST['categoria_id'];
        $status = isset($_POST['status']) ? 1 : 0;

        // Handle image upload if a new image was provided
        $imagem = null;
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/produtos/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileInfo = pathinfo($_FILES['imagem']['name']);
            $extension = strtolower($fileInfo['extension']);
            
            // Validate file extension
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($extension, $allowedExtensions)) {
                throw new Exception('Formato de imagem inválido. Apenas JPG, JPEG, PNG e GIF são permitidos.');
            }

            // Generate unique filename
            $filename = uniqid() . '.' . $extension;
            $targetFile = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $targetFile)) {
                // Delete old image if exists
                $sql = "SELECT imagem FROM produtos WHERE id = ?";
                $stmt = $PDO->prepare($sql);
                $stmt->execute([$id]);
                $oldImage = $stmt->fetchColumn();

                if ($oldImage && file_exists($uploadDir . $oldImage)) {
                    unlink($uploadDir . $oldImage);
                }

                $imagem = $filename;
            }
        }

        // Update product in database
        if ($imagem) {
            $sql = "UPDATE produtos SET nome = ?, descricao = ?, preco = ?, categoria_id = ?, status = ?, imagem = ? WHERE id = ?";
            $params = [$nome, $descricao, $preco, $categoria_id, $status, $imagem, $id];
        } else {
            $sql = "UPDATE produtos SET nome = ?, descricao = ?, preco = ?, categoria_id = ?, status = ? WHERE id = ?";
            $params = [$nome, $descricao, $preco, $categoria_id, $status, $id];
        }

        $stmt = $PDO->prepare($sql);
        $stmt->execute($params);

        $_SESSION['success'] = "Produto atualizado com sucesso!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Erro ao atualizar produto: " . $e->getMessage();
    }
}

header('Location: ../../?view=gerenciar_produtos');
exit;