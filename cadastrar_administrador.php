<?php
// Configurações iniciais
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/cadastro_admin.log');

// Definir constantes e incluir arquivos necessários
define('ROOT_PATH', dirname(__FILE__));
define('MODEL_PATH', ROOT_PATH . '/MVC/MODEL');

require_once MODEL_PATH . '/config.php';
require_once MODEL_PATH . '/conexao.php';

// Função para log
function logDebug($message) {
    error_log(date('Y-m-d H:i:s') . " - " . $message . "\n");
}

$mensagem = '';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    logDebug("Recebendo POST com os dados:");
    logDebug("POST data: " . print_r($_POST, true));
    
    try {
        // Conectar ao banco
        $conn = new mysqli('localhost', 'root', '', 'pdv');
        
        if ($conn->connect_error) {
            throw new Exception("Erro de conexão: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8");
        logDebug("Conexão com banco estabelecida");
        
        // Validar dados
        $login = trim($_POST['login'] ?? '');
        $senha = trim($_POST['senha'] ?? '');
        $pergunta = trim($_POST['pergunta'] ?? '');
        $resposta = trim($_POST['resposta'] ?? '');
        $nivel = isset($_POST['nivel']) ? (int)$_POST['nivel'] : 1;

        logDebug("Dados validados: login=$login, nivel=$nivel");

        if (empty($login) || empty($senha) || empty($pergunta) || empty($resposta)) {
            throw new Exception("Todos os campos são obrigatórios");
        }

        // Verificar login existente
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE login = ?");
        if (!$stmt) {
            throw new Exception("Erro ao preparar verificação: " . $conn->error);
        }
        
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            throw new Exception("Este login já está em uso");
        }
        $stmt->close();

        logDebug("Login disponível, preparando para inserir");

        // Criar hash da senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Inserir usuário
        $stmt = $conn->prepare("INSERT INTO usuarios (login, senha, nivel, pergunta, resposta) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Erro ao preparar insert: " . $conn->error);
        }

        logDebug("Preparando para inserir com os valores: login=$login, nivel=$nivel, pergunta definida=" . (!empty($pergunta)));
        
        // Corrigido: agora temos 5 tipos (ssis) para 5 parâmetros
        $stmt->bind_param("ssiss", $login, $senha_hash, $nivel, $pergunta, $resposta);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao inserir: " . $stmt->error);
        }

        if ($stmt->affected_rows > 0) {
            logDebug("Usuário inserido com sucesso!");
            $mensagem = "<div class='alert alert-success'>Administrador criado com sucesso!</div>";
            header("Location: index.php");
            exit;
        } else {
            throw new Exception("Nenhum registro foi inserido");
        }

    } catch (Exception $e) {
        logDebug("ERRO: " . $e->getMessage());
        $mensagem = "<div class='alert alert-danger'>" . htmlspecialchars($e->getMessage()) . "</div>";
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Cadastrar Administrador - Divinosys 1.0</title>
	
	<!-- Google Fonts -->
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<!-- Font Awesome -->
	<link href="MVC/COMMON/VENDOR/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
	<link href="<?php echo assets('css/login-style.css'); ?>" rel="stylesheet">
	
	<style>
		.login-container {
			max-width: 480px;
		}
		
		select.form-control {
			appearance: none;
			-webkit-appearance: none;
			background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12' fill='none'%3E%3Cpath d='M2.5 4L6 7.5L9.5 4' stroke='%239C9C9C' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
			background-repeat: no-repeat;
			background-position: right 1rem center;
			padding-right: 2.5rem;
		}
		
		textarea.form-control {
			min-height: 100px;
			resize: vertical;
			padding: 0.875rem 1rem;
		}
		
		.btn-back {
			background-color: transparent;
			border: 1px solid #EA1D2C;
			color: #EA1D2C;
			margin-top: 1rem;
		}
		
		.btn-back:hover {
			background-color: rgba(234, 29, 44, 0.1);
		}
	</style>
</head>
<body>
	<div class="login-container">
		<div class="login-card">
			<div class="login-logo">
				<h1>Criar Administrador</h1>
				<p>Cadastre um novo administrador para o sistema</p>
			</div>
			
			<?php if (!empty($mensagem)) echo $mensagem; ?>
			
			<form method="POST" action="">
				<div class="form-group">
					<label for="login">Login</label>
					<div class="input-icon">
						<i class="fas fa-user"></i>
						<input type="text" class="form-control" id="login" name="login" placeholder="Digite o login" required value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>">
					</div>
				</div>
				
				<div class="form-group">
					<label for="senha">Senha</label>
					<div class="input-icon">
						<i class="fas fa-lock"></i>
						<input type="password" class="form-control" id="senha" name="senha" placeholder="Digite a senha" required>
					</div>
				</div>
				
				<div class="form-group">
					<label for="nivel">Nível de Acesso</label>
					<div class="input-icon">
						<i class="fas fa-user-shield"></i>
						<select class="form-control" id="nivel" name="nivel">
							<option value="1" <?php echo (!isset($_POST['nivel']) || $_POST['nivel'] == 1) ? 'selected' : ''; ?>>Administrador</option>
							<option value="2" <?php echo (isset($_POST['nivel']) && $_POST['nivel'] == 2) ? 'selected' : ''; ?>>Gerente</option>
							<option value="3" <?php echo (isset($_POST['nivel']) && $_POST['nivel'] == 3) ? 'selected' : ''; ?>>Funcionário</option>
						</select>
					</div>
				</div>
				
				<div class="form-group">
					<label for="pergunta">Pergunta de Segurança</label>
					<div class="input-icon">
						<i class="fas fa-question"></i>
						<textarea class="form-control" id="pergunta" name="pergunta" placeholder="Escreva uma pergunta que só você saiba responder" required><?php echo isset($_POST['pergunta']) ? htmlspecialchars($_POST['pergunta']) : ''; ?></textarea>
					</div>
				</div>
				
				<div class="form-group">
					<label for="resposta">Resposta</label>
					<div class="input-icon">
						<i class="fas fa-key"></i>
						<input type="text" class="form-control" id="resposta" name="resposta" placeholder="Digite a resposta" required value="<?php echo isset($_POST['resposta']) ? htmlspecialchars($_POST['resposta']) : ''; ?>">
					</div>
				</div>
				
				<button type="submit" class="btn-login">Cadastrar</button>
				<a href="index.php" class="btn-login btn-back">Voltar</a>
			</form>
		</div>
	</div>
</body>
</html>