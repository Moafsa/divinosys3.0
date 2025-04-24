<?php
// Prevent any output before headers
if (ob_get_level()) ob_end_clean();
ob_start();

// Configurações iniciais
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('error_log', dirname(__FILE__) . '/login.log');

// Função para log
function logDebug($message) {
    error_log(date('Y-m-d H:i:s') . " - " . $message);
}

logDebug("=== Iniciando processo de login ===");

// Definir constantes
define('ROOT_PATH', dirname(dirname(dirname(__FILE__))));
require_once ROOT_PATH . "/MVC/MODEL/config.php";
require_once ROOT_PATH . "/MVC/MODEL/conexao.php";

// Iniciar sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Verificar se o formulário foi enviado
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        logDebug("Recebida requisição POST");
        
        // Validar e sanitizar inputs
        $login = filter_var($_POST['login'], FILTER_SANITIZE_STRING);
        $senha = $_POST['senha'] ?? '';

        logDebug("Tentativa de login para usuário: " . $login);

        if (empty($login) || empty($senha)) {
            throw new Exception("Por favor, preencha todos os campos.");
        }

        // Verificar conexão com o banco
        if (!isset($conn) || !($conn instanceof mysqli)) {
            logDebug("Tentando estabelecer nova conexão com o banco");
            $dbConfig = Config::get('db');
            $conn = new mysqli(
                $dbConfig['host'],
                $dbConfig['user'],
                $dbConfig['pass'],
                $dbConfig['name']
            );

            if ($conn->connect_error) {
                throw new Exception("Erro de conexão: " . $conn->connect_error);
            }

            $conn->set_charset("utf8");
        }

        // Consultar usuário
        $stmt = $conn->prepare("SELECT id, senha, nivel FROM usuarios WHERE login = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Erro ao preparar consulta: " . $conn->error);
        }

        $stmt->bind_param("s", $login);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao executar consulta: " . $stmt->error);
        }

        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            logDebug("Usuário encontrado, verificando senha");
            
            // Verificar senha
            if (password_verify($senha, $row['senha'])) {
                logDebug("Senha correta, criando sessão");
                
                // Limpar qualquer redirecionamento anterior
                if (isset($_SESSION['last_redirect'])) {
                    unset($_SESSION['last_redirect']);
                }
                
                // Login bem sucedido
                $_SESSION['login'] = true;
                $_SESSION['usuario'] = $login;
                $_SESSION['nivel'] = $row['nivel'];
                $_SESSION['id_usuario'] = $row['id'];

                // Limpar qualquer mensagem de erro anterior
                if (isset($_SESSION['msg'])) {
                    unset($_SESSION['msg']);
                }

                logDebug("Sessão criada com sucesso. Redirecionando...");

                // Redirecionar para o dashboard
                $config = Config::getInstance();
                header("Location: " . $config->url('index.php?view=Dashboard1'));
                exit();
            } else {
                logDebug("Senha incorreta para o usuário: " . $login);
                throw new Exception("Senha incorreta.");
            }
        } else {
            logDebug("Usuário não encontrado: " . $login);
            throw new Exception("Usuário não encontrado.");
        }

        $stmt->close();
    } else {
        throw new Exception("Método de requisição inválido.");
    }
} catch (Exception $e) {
    logDebug("Erro no processo de login: " . $e->getMessage());
    $_SESSION['msg'] = "<div class='alert alert-danger'>" . htmlspecialchars($e->getMessage()) . "</div>";
    $config = Config::getInstance();
    header("Location: " . $config->url('index.php'));
    exit();
} finally {
    // Garantir que a conexão seja fechada
    if (isset($stmt)) {
        $stmt->close();
    }
    
    // Limpar o buffer de saída
    while (ob_get_level()) ob_end_clean();
}
?>