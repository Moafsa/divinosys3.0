<?php
session_start();
include_once 'conexao.php';
include_once 'config.php';

$config = Config::getInstance();

// Debug - Log do início do script
error_log("Iniciando salvar_categoria.php");
error_log("Session: " . print_r($_SESSION, true));

// Verificar se está logado
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    $_SESSION['msg'] = "<div class='alert alert-danger'>Acesso não autorizado! Faça login primeiro.</div>";
    header("Location: " . $config->url(''));
    exit;
}

// Verificar se é uma edição ou nova categoria
$id = isset($_POST['id']) ? intval($_POST['id']) : null;
$nome = trim($_POST['nome']);

// Debug - Log dos dados recebidos
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

// Validar nome
if (empty($nome)) {
    $_SESSION['msg'] = "<div class='alert alert-danger'>O nome da categoria é obrigatório!</div>";
    header("Location: " . $config->url('?view=gerenciar_categorias'));
    exit;
}

try {
    // Processar upload da imagem
    $imagem_path = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['imagem']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            throw new Exception("Tipo de arquivo não permitido. Use apenas: " . implode(', ', $allowed));
        }
        
        // Criar diretório se não existir
        $base_path = dirname(dirname(dirname(__FILE__)));
        $upload_dir = $base_path . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'categorias' . DIRECTORY_SEPARATOR;
        
        error_log("Base path: " . $base_path);
        error_log("Upload directory: " . $upload_dir);
        
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception("Erro ao criar diretório de upload: " . $upload_dir);
            }
            chmod($upload_dir, 0777);
        }
        
        // Debug - Log do diretório
        error_log("Permissões do diretório: " . substr(sprintf('%o', fileperms($upload_dir)), -4));
        
        // Gerar nome único para o arquivo
        $new_filename = uniqid() . '.' . $ext;
        $imagem_path = 'uploads/categorias/' . $new_filename;
        $full_path = $upload_dir . $new_filename;
        
        // Debug - Log dos caminhos
        error_log("Caminho relativo para DB: " . $imagem_path);
        error_log("Caminho completo do arquivo: " . $full_path);
        
        // Verificar permissões
        if (!is_writable($upload_dir)) {
            throw new Exception("Diretório sem permissão de escrita: " . $upload_dir);
        }
        
        // Mover arquivo
        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $full_path)) {
            $error = error_get_last();
            throw new Exception("Erro ao fazer upload da imagem: " . ($error ? $error['message'] : 'Erro desconhecido'));
        }
        
        // Verificar se o arquivo foi criado
        if (!file_exists($full_path)) {
            throw new Exception("Arquivo não foi criado após o upload: " . $full_path);
        }
        
        // Ajustar permissões do arquivo
        chmod($full_path, 0666);
        
        // Debug - Log do resultado
        error_log("Arquivo criado com sucesso: " . $full_path);
        error_log("Permissões do arquivo: " . substr(sprintf('%o', fileperms($full_path)), -4));
    }
    
    // Preparar query
    if ($id) {
        // Atualizar categoria existente
        if ($imagem_path) {
            $query = "UPDATE categorias SET nome = ?, imagem = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ssi", $nome, $imagem_path, $id);
        } else {
            $query = "UPDATE categorias SET nome = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "si", $nome, $id);
        }
    } else {
        // Inserir nova categoria
        $query = "INSERT INTO categorias (nome, imagem) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $nome, $imagem_path);
    }
    
    // Debug - Log da query
    error_log("Query: " . $query);
    error_log("Nome: " . $nome);
    error_log("Imagem path: " . $imagem_path);
    error_log("ID: " . $id);
    
    // Executar query
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao salvar categoria: " . mysqli_error($conn));
    }
    
    $_SESSION['swal_msg'] = json_encode([
        'title' => 'Sucesso!',
        'text' => $id ? 'Categoria atualizada com sucesso!' : 'Categoria criada com sucesso!',
        'icon' => 'success'
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao salvar categoria: " . $e->getMessage());
    $_SESSION['swal_msg'] = json_encode([
        'title' => 'Erro!',
        'text' => $e->getMessage(),
        'icon' => 'error'
    ]);
}

// Debug - Log do redirecionamento
error_log("Redirecionando para: " . $config->url('?view=gerenciar_categorias'));

header("Location: " . $config->url('?view=gerenciar_categorias'));
exit; 