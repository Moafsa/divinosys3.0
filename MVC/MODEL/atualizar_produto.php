<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Iniciar transação
        mysqli_begin_transaction($conn);

        // Validar e sanitizar os dados
        $id = (int)$_POST['id'];
        $nome = mysqli_real_escape_string($conn, $_POST['nome']);
        $categoria_id = (int)$_POST['categoria_id'];
        $preco_normal = (float)str_replace(',', '.', $_POST['preco_normal']);
        $preco_mini = isset($_POST['preco_mini']) && $_POST['preco_mini'] !== '' ? (float)str_replace(',', '.', $_POST['preco_mini']) : null;
        $descricao = mysqli_real_escape_string($conn, $_POST['descricao'] ?? '');

        // Verificar se o produto existe e obter imagem atual
        $check_sql = "SELECT imagem FROM produtos WHERE id = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, 'i', $id);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        $produto = mysqli_fetch_assoc($result);

        if (!$produto) {
            throw new Exception("Produto não encontrado");
        }

        // Processar upload da nova imagem
        $imagem = $produto['imagem']; // Mantém a imagem atual por padrão
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../uploads/produtos/';
            
            // Criar diretório se não existir
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Gerar nome único para o arquivo
            $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            $nova_imagem = uniqid() . '.' . $ext;
            
            // Mover arquivo
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_dir . $nova_imagem)) {
                // Remover imagem antiga se existir
                if ($imagem && file_exists($upload_dir . $imagem)) {
                    unlink($upload_dir . $imagem);
                }
                $imagem = $nova_imagem;
            } else {
                throw new Exception("Erro ao fazer upload da imagem");
            }
        }

        // Preparar a query de atualização do produto
        $sql = "UPDATE produtos SET 
                nome = ?, 
                categoria_id = ?, 
                preco_normal = ?, 
                preco_mini = ?,
                descricao = ?,
                imagem = ?
                WHERE id = ?";
                
        $stmt = mysqli_prepare($conn, $sql);

        // Vincular os parâmetros
        mysqli_stmt_bind_param($stmt, 'siddssi', 
            $nome, 
            $categoria_id, 
            $preco_normal, 
            $preco_mini,
            $descricao,
            $imagem,
            $id
        );

        // Executar a query
        if (mysqli_stmt_execute($stmt)) {
            // Preparar dados do estoque
            $estoque_atual = !empty($_POST['estoque_atual']) ? (int)$_POST['estoque_atual'] : 0;
            $estoque_minimo = !empty($_POST['estoque_minimo']) ? (int)$_POST['estoque_minimo'] : 0;
            $preco_custo = !empty($_POST['preco_custo']) ? (float)str_replace(',', '.', $_POST['preco_custo']) : null;
            $marca = !empty($_POST['marca']) ? mysqli_real_escape_string($conn, $_POST['marca']) : null;

            // Verificar se já existe registro de estoque
            $check_estoque = "SELECT id FROM estoque WHERE produto_id = ?";
            $stmt_check = mysqli_prepare($conn, $check_estoque);
            mysqli_stmt_bind_param($stmt_check, 'i', $id);
            mysqli_stmt_execute($stmt_check);
            $result_estoque = mysqli_stmt_get_result($stmt_check);
            $estoque_row = mysqli_fetch_assoc($result_estoque);

            if ($estoque_row) {
                // Atualizar estoque existente
                $sql_estoque = "UPDATE estoque SET 
                               estoque_atual = ?,
                               estoque_minimo = ?,
                               preco_custo = ?,
                               marca = ?
                               WHERE produto_id = ?";
                $stmt_estoque = mysqli_prepare($conn, $sql_estoque);
                mysqli_stmt_bind_param($stmt_estoque, 'iidss', 
                    $estoque_atual,
                    $estoque_minimo,
                    $preco_custo,
                    $marca,
                    $id
                );
            } else {
                // Criar novo registro de estoque
                $sql_estoque = "INSERT INTO estoque 
                               (produto_id, estoque_atual, estoque_minimo, preco_custo, marca) 
                               VALUES (?, ?, ?, ?, ?)";
                $stmt_estoque = mysqli_prepare($conn, $sql_estoque);
                mysqli_stmt_bind_param($stmt_estoque, 'iidss', 
                    $id,
                    $estoque_atual,
                    $estoque_minimo,
                    $preco_custo,
                    $marca
                );
            }

            if (!mysqli_stmt_execute($stmt_estoque)) {
                throw new Exception("Erro ao atualizar estoque: " . mysqli_stmt_error($stmt_estoque));
            }

            // Commit da transação
            mysqli_commit($conn);
            $_SESSION['msg'] = "<div class='alert alert-success'>Produto atualizado com sucesso!</div>";
        } else {
            throw new Exception("Erro ao atualizar produto: " . mysqli_stmt_error($stmt));
        }

    } catch (Exception $e) {
        // Rollback em caso de erro
        mysqli_rollback($conn);
        
        error_log("Erro ao atualizar produto: " . $e->getMessage());
        $_SESSION['msg'] = "<div class='alert alert-danger'>Erro ao atualizar produto: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

header('Location: ../../?view=gerenciar_produtos');
exit; 