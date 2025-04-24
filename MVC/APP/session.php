<?php
session_start();

// Limpar qualquer output anterior e iniciar buffer
if (ob_get_level()) ob_end_clean();
ob_start();

// Configurações iniciais
ini_set('display_errors', 0);
error_reporting(0);

// Definir constantes
define('ROOT_PATH', dirname(dirname(dirname(__FILE__))));
require_once ROOT_PATH . "/MVC/MODEL/config.php";
require_once ROOT_PATH . "/MVC/MODEL/conexao.php";

try {
    $login = isset($_POST['login']) ? trim(filter_var($_POST['login'], FILTER_SANITIZE_STRING)) : null;
    $senha = isset($_POST['senha']) ? $_POST['senha'] : null;

    if (empty($login) || empty($senha)) {
        throw new Exception("Por favor, preencha todos os campos.");
    }

    // Consultar usuário
    $stmt = mysqli_prepare($conn, "SELECT id, login, senha, nivel FROM usuarios WHERE login = ? LIMIT 1");
    if (!$stmt) {
        error_log("Erro na preparação da query: " . mysqli_error($conn));
        throw new Exception("Erro interno do servidor");
    }

    mysqli_stmt_bind_param($stmt, "s", $login);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Erro na execução da query: " . mysqli_stmt_error($stmt));
        throw new Exception("Erro interno do servidor");
    }

    $result = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$usuario) {
        error_log("Tentativa de login falhou para o usuário: " . $login);
        throw new Exception("Usuário ou senha incorretos");
    }

    // Verifica a senha usando password_verify
    if (password_verify($senha, $usuario['senha'])) {
        // Login bem sucedido
        $_SESSION['loginapp'] = 1;
        $_SESSION['usuarioid'] = $usuario['id'];
        $_SESSION['nivel'] = $usuario['nivel'];
        
        // Atualiza o hash se necessário
        if (password_needs_rehash($usuario['senha'], PASSWORD_DEFAULT)) {
            $novo_hash = password_hash($senha, PASSWORD_DEFAULT);
            $update_stmt = mysqli_prepare($conn, "UPDATE usuarios SET senha = ? WHERE id = ?");
            if ($update_stmt) {
                mysqli_stmt_bind_param($update_stmt, "si", $novo_hash, $usuario['id']);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
            }
        }
        
        header('Location: app_mesas.php');
        exit;
    }
    
    error_log("Senha inválida para o usuário: " . $login);
    throw new Exception("Usuário ou senha incorretos");

} catch (Exception $e) {
    error_log("Erro no login do app: " . $e->getMessage());
    ?>
    <link href="../COMMON/css/bootstrap.min.css" rel="stylesheet"/>
    <h1 class="text-center" style="color: red; padding: 20%;">Acesso Negado!</h1>
    <h4 class="text-center" style="color:white; background: black;">SENHA OU LOGIN INCORRETOS</h4>
    <h5 class="text-center" style="color:white; background: black;">Clique no botão e tente novamente</h5>
    <h1 class="text-center" style="padding: 5%;">
        <button class="text-center btn btn-warning btn-lg" onclick="window.location.href='app_login.php'">Voltar</button>
    </h1>
    <?php
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?>

<script src="../COMMON/js/jquery-3.3.1.slim.min.js"></script>
<script src="../COMMON/js/popper.min.js"></script>
<script src="../COMMON/js/bootstrap.min.js"></script>