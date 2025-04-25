<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/www/html/php_errors.log');

// Log directory information
error_log("Current directory: " . __DIR__);
error_log("Document root: " . $_SERVER['DOCUMENT_ROOT']);

// Limpar qualquer output anterior e iniciar buffer
if (ob_get_level()) ob_end_clean();
ob_start();

// Configurações iniciais
ini_set('display_errors', 1);
ini_set('default_charset', 'UTF-8');
error_reporting(E_ALL);

// Definir constantes
define('DEFAULT_VIEW', 'Dashboard1');
define('SYSTEM_CLASS_PATH', 'MVC/CLASSES/system.class.php');
define('ROOT_PATH', dirname(__FILE__));

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir arquivos necessários
require_once ROOT_PATH . "/MVC/MODEL/config.php";
require_once ROOT_PATH . "/MVC/MODEL/conexao.php";

try {
    // Definir o charset para UTF-8
    if (!mysqli_set_charset($conn, "utf8")) {
        throw new Exception("Erro ao definir charset UTF-8");
    }

    // Consultar a cor do sistema usando prepared statement
    $select_table = "SELECT cor FROM cor WHERE id = ?";
    $stmt = mysqli_prepare($conn, $select_table);
    
    if (!$stmt) {
        throw new Exception("Erro ao preparar consulta: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $id);
    $id = 1;
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao executar consulta: " . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    $_SESSION['cor'] = ($row = mysqli_fetch_assoc($result)) ? $row['cor'] : 'danger';
    
    mysqli_stmt_close($stmt);

} catch (Exception $e) {
    error_log("Erro no index.php: " . $e->getMessage());
    $_SESSION['cor'] = 'danger';
}

// Verificar se é uma requisição de login
$is_login_request = empty($_GET['view']);

// Verificar se o usuário está logado
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    // Se não estiver logado e não for uma requisição de login, mostrar o formulário
    if (!$is_login_request) {
        $_SESSION['msg'] = "<div class='alert alert-danger'>Acesso não autorizado! Faça login primeiro.</div>";
        header("Location: " . Config::getInstance()->url('index.php'));
        exit;
    }
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Divinosys 1.0</title>

  <!-- Bootstrap CSS -->
  <link href="<?php echo assets('vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="<?php echo assets('vendor/fontawesome-free/css/all.min.css'); ?>" rel="stylesheet">
  <!-- Custom Login CSS -->
  <link href="<?php echo assets('css/login-style.css'); ?>" rel="stylesheet"/>
  <link rel="shortcut icon" href="<?php echo assets('img/beer.png'); ?>">
</head>

<body>
  <div class="login-container">
    <div class="login-card">
      <div class="login-logo">
        <h1>Divinosys 1.0</h1>
        <p>Sistema de Gestão de Pedidos</p>
      </div>
      
      <form method="POST" action="<?php echo url('MVC/MODEL/login.php'); ?>">
        <div class="form-group">
          <label for="login">Login</label>
          <div class="input-icon">
            <i class="fas fa-user"></i>
            <input type="text" class="form-control" name="login" id="login" placeholder="Digite seu login" required>
          </div>
        </div>
        
        <div class="form-group">
          <label for="senha">Senha</label>
          <div class="input-icon">
            <i class="fas fa-lock"></i>
            <input type="password" class="form-control" name="senha" id="senha" placeholder="Digite sua senha" required>
          </div>
        </div>

        <div class="text-center" id="mensagem">
          <?php 
          if (isset($_SESSION['msg'])) {
            echo $_SESSION['msg'];
            unset($_SESSION['msg']);
          }
          ?>
        </div>

        <button type="submit" class="btn btn-login">
          Entrar
        </button>
      </form>

      <div class="login-links">
        <a href="<?php echo url('recuperar_senha.php'); ?>">Esqueceu a senha?</a>
        <br>
        <a href="<?php echo url('cadastrar_administrador.php'); ?>">Cadastrar Administrador</a>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="<?php echo assets('vendor/jquery/jquery.min.js'); ?>"></script>
  <script src="<?php echo assets('vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
</body>
</html>

<?php 
} else {
    try {
        // Usuário logado, carrega o sistema
        if (!file_exists(SYSTEM_CLASS_PATH)) {
            throw new Exception("Arquivo do sistema não encontrado: " . SYSTEM_CLASS_PATH);
        }
        
        require_once(SYSTEM_CLASS_PATH);

        // Validar e sanitizar o parâmetro view
        $view = DEFAULT_VIEW;
        if (isset($_GET['view'])) {
            $allowedViews = require_once(ROOT_PATH . "/MVC/CONFIG/views.php");
            $requestedView = trim(filter_var($_GET['view'], FILTER_SANITIZE_STRING));
            if (in_array($requestedView, $allowedViews)) {
                $view = $requestedView;
            }
        }

        $system = new System($view);
        
    } catch (Exception $e) {
        error_log("Erro ao carregar sistema: " . $e->getMessage());
        header("Location: " . url('error.php?msg=' . urlencode($e->getMessage())));
        exit;
    }
}
?>