<?php
session_start();

// Limpar qualquer output anterior e iniciar buffer
if (ob_get_level()) ob_end_clean();
ob_start();

// Configurações iniciais
ini_set('display_errors', 0);
error_reporting(0);

// Definir constantes
define('ROOT_PATH', dirname(__FILE__));
require_once ROOT_PATH . "/MVC/MODEL/config.php";
require_once ROOT_PATH . "/MVC/MODEL/conexao.php";

$login = isset($_POST['login']) ? trim(filter_var($_POST['login'], FILTER_SANITIZE_STRING)) : null;

if ($login == null) {?>

  <!DOCTYPE html>
  <html lang="pt-br">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Divinosys 1.0</title>

    <link href="MVC/COMMON/CSS/animate.min.css" rel="stylesheet"/>
    <link href="MVC/COMMON/CSS/bootstrap-datepicker.css" rel="stylesheet"/>
    <link href="MVC/COMMON/VENDOR/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="MVC/COMMON/CSS/sb-admin-2.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="MVC/COMMON/IMG/beer.png">

    <style>
      body {
        background-color: #000000;
        font-family: 'Inter', sans-serif;
        min-height: 100vh;
        display: flex;
        align-items: center;
        margin: 0;
      }
      .container {
        max-width: 100%;
        padding: 20px;
      }
      .card {
        background-color: #1E1E1E;
        border: none;
        border-radius: 16px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
      }
      .card-body {
        padding: 2rem;
      }
      h1 {
        color: #EA1D2C !important;
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
      }
      .h4 {
        color: #FFFFFF !important;
        font-size: 1.25rem;
        margin-bottom: 1rem;
      }
      h6 {
        color: #9C9C9C;
        font-size: 0.875rem;
        margin-bottom: 2rem;
      }
      .form-control {
        background-color: #2D2D2D;
        border: 1px solid #3D3D3D;
        border-radius: 8px;
        color: #FFFFFF;
        padding: 1rem;
        height: auto;
        font-size: 1rem;
      }
      .form-control:focus {
        background-color: #2D2D2D;
        border-color: #EA1D2C;
        color: #FFFFFF;
        box-shadow: 0 0 0 2px rgba(234, 29, 44, 0.2);
      }
      .form-control::placeholder {
        color: #9C9C9C;
      }
      .btn-danger {
        background-color: #EA1D2C;
        border: none;
        border-radius: 8px;
        padding: 1rem;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
      }
      .btn-danger:hover {
        background-color: #D01424;
        transform: translateY(-1px);
      }
      .small {
        color: #EA1D2C;
        text-decoration: none;
        font-size: 0.875rem;
        transition: color 0.3s ease;
      }
      .small:hover {
        color: #D01424;
        text-decoration: none;
      }
      .alert {
        border-radius: 8px;
        margin-bottom: 1rem;
      }
      .custom-checkbox {
        display: none;
      }
      @media (max-width: 768px) {
        .card-body {
          padding: 1.5rem;
        }
        h1 {
          font-size: 1.75rem;
        }
        .container {
          padding: 15px;
        }
      }
    </style>
  </head>

  <body>
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-xl-5 col-lg-6 col-md-8 col-sm-10 col-12">
          <div class="card">
            <div class="card-body">
              <div class="text-center">
                <h1>Recuperação de senha</h1>
                <h6>Digite seu login para recuperar sua senha</h6>
              </div>

              <form method="POST" action="">
                <div class="form-group">
                  <input class="form-control" name="login" id="login" placeholder="Digite seu login" required>
                </div>

                <div class="text-center mb-4" id="mensagem">
                  <?php if (isset($_SESSION['msg'])) {echo $_SESSION['msg']; unset($_SESSION['msg']); }?>
                </div>

                <button type="submit" class="btn btn-danger btn-block">
                  Buscar
                </button>

                <div class="text-center mt-4">
                  <a class="small" href="index.php">Voltar para o login</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script type="text/javascript">
      var var1 = document.getElementById("mensagem");
      setTimeout(function() {var1.style.visibility = "hidden";}, 5000);
    </script>
  </body>

  </html>


<?php
}else{
    try {
        // Verificar se o login existe
        $stmt = mysqli_prepare($conn, "SELECT id, pergunta, resposta FROM usuarios WHERE login = ?");
        if (!$stmt) {
            throw new Exception("Erro ao preparar consulta: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "s", $login);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Erro ao executar consulta: " . mysqli_stmt_error($stmt));
        }

        $result = mysqli_stmt_get_result($stmt);
        $usuario = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$usuario) {
            throw new Exception("Usuário não encontrado!");
        }

        $pergunta = $usuario['pergunta'];
        $resposta_correta = $usuario['resposta'];
        $id_usuario = $usuario['id'];

        // Se uma resposta foi enviada
        $responder = isset($_POST['responder']) ? trim($_POST['responder']) : null;
        if ($responder !== null) {
            if (strtolower($responder) === strtolower($resposta_correta)) {
                // Gerar nova senha aleatória
                $nova_senha = bin2hex(random_bytes(4)); // 8 caracteres
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

                // Atualizar senha no banco
                $stmt = mysqli_prepare($conn, "UPDATE usuarios SET senha = ? WHERE id = ?");
                if (!$stmt) {
                    throw new Exception("Erro ao preparar atualização: " . mysqli_error($conn));
                }

                mysqli_stmt_bind_param($stmt, "si", $senha_hash, $id_usuario);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Erro ao atualizar senha: " . mysqli_stmt_error($stmt));
                }
                mysqli_stmt_close($stmt);

                $_SESSION['msg'] = "<div class='alert alert-success' role='alert'>Sua nova senha é: " . htmlspecialchars($nova_senha) . "<br>Por favor, anote-a e faça login.</div>";
                header("Location: index.php");
                exit;
            } else {
                $_SESSION['msg'] = "<div class='alert alert-danger' role='alert'>Resposta incorreta! Tente novamente.</div>";
                header("Location: recuperar_senha.php");
                exit;
            }
        }

        // ... existing code para exibir o formulário com a pergunta ...

    } catch (Exception $e) {
        error_log("Erro na recuperação de senha: " . $e->getMessage());
        $_SESSION['msg'] = "<div class='alert alert-danger' role='alert'>" . htmlspecialchars($e->getMessage()) . "</div>";
        header("Location: recuperar_senha.php");
        exit;
    } finally {
        if (isset($conn)) {
            mysqli_close($conn);
    }
  }
}
?>