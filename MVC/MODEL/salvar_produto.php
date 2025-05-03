<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Iniciar transação
        mysqli_begin_transaction($conn);

        // Validar e sanitizar os dados
        $nome = mysqli_real_escape_string($conn, $_POST['nome']);
        $categoria_id = (int)$_POST['categoria_id'];
        $preco_normal = (float)str_replace(',', '.', $_POST['preco_normal']);
        $preco_mini = isset($_POST['preco_mini']) && $_POST['preco_mini'] !== '' ? (float)str_replace(',', '.', $_POST['preco_mini']) : null;
        $descricao = mysqli_real_escape_string($conn, $_POST['descricao'] ?? '');
        
        // Processar upload da imagem
        $imagem = null;
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../uploads/produtos/';
            
            // Criar diretório se não existir
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Gerar nome único para o arquivo
            $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            $imagem = uniqid() . '.' . $ext;
            
            // Mover arquivo
            if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_dir . $imagem)) {
                throw new Exception("Erro ao fazer upload da imagem");
            }
        }

        // Preparar a query
        $sql = "INSERT INTO produtos (nome, categoria_id, preco_normal, preco_mini, descricao, imagem) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);

        // Vincular os parâmetros
        mysqli_stmt_bind_param($stmt, 'siddss', $nome, $categoria_id, $preco_normal, $preco_mini, $descricao, $imagem);

        // Executar a query
        if (mysqli_stmt_execute($stmt)) {
            $produto_id = mysqli_insert_id($conn);

            // Preparar dados do estoque
            $estoque_atual = !empty($_POST['estoque_atual']) ? (int)$_POST['estoque_atual'] : 0;
            $estoque_minimo = !empty($_POST['estoque_minimo']) ? (int)$_POST['estoque_minimo'] : 0;
            $preco_custo = !empty($_POST['preco_custo']) ? (float)str_replace(',', '.', $_POST['preco_custo']) : null;
            $marca = !empty($_POST['marca']) ? mysqli_real_escape_string($conn, $_POST['marca']) : null;

            // Criar registro de estoque
            $sql_estoque = "INSERT INTO estoque (produto_id, estoque_atual, estoque_minimo, preco_custo, marca) VALUES (?, ?, ?, ?, ?)";
            $stmt_estoque = mysqli_prepare($conn, $sql_estoque);
            mysqli_stmt_bind_param($stmt_estoque, 'iiids', $produto_id, $estoque_atual, $estoque_minimo, $preco_custo, $marca);
            
            if (!mysqli_stmt_execute($stmt_estoque)) {
                // Se falhar, remove a imagem se foi feito upload
                if ($imagem && file_exists($upload_dir . $imagem)) {
                    unlink($upload_dir . $imagem);
                }
                throw new Exception("Erro ao cadastrar estoque: " . mysqli_stmt_error($stmt_estoque));
            }

            // Commit da transação
            mysqli_commit($conn);
            $_SESSION['msg'] = "<div class='alert alert-success'>Produto cadastrado com sucesso!</div>";
        } else {
            throw new Exception("Erro ao cadastrar produto: " . mysqli_stmt_error($stmt));
        }

    } catch (Exception $e) {
        // Rollback em caso de erro
        mysqli_rollback($conn);
        
        error_log("Erro ao cadastrar produto: " . $e->getMessage());
        $_SESSION['msg'] = "<div class='alert alert-danger'>Erro ao cadastrar produto: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

header('Location: ../../?view=gerenciar_produtos');
exit; 