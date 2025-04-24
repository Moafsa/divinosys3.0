<?php

class System {
	private $conn;
	private $maxRedirects = 3;
	private static $redirectCount = 0;
	private $config;

	public function __construct($view) {
		// Prevenir qualquer saída antes dos headers
		if (ob_get_level()) ob_end_clean();
		ob_start();

		// Definir constante ROOT_PATH se ainda não estiver definida
		if (!defined('ROOT_PATH')) {
			define('ROOT_PATH', dirname(dirname(dirname(__FILE__))));
		}

		// Garantir que a sessão está iniciada
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		// Log para debug
		error_log("Iniciando System com view: " . $view);
		error_log("Redirect count: " . self::$redirectCount);
		error_log("Session login status: " . (isset($_SESSION['login']) ? 'true' : 'false'));
		error_log("ROOT_PATH: " . ROOT_PATH);

		// Incluir arquivo de configuração primeiro
		require_once(ROOT_PATH . "/MVC/MODEL/config.php");
		$this->config = Config::getInstance();

		// Verificar se há muitos redirecionamentos
		if (self::$redirectCount >= $this->maxRedirects) {
			error_log("Número máximo de redirecionamentos atingido!");
			if (ob_get_level()) ob_end_clean();
			session_destroy();
			die("Erro: Muitos redirecionamentos. Por favor, limpe os cookies do navegador e tente novamente.");
		}

		// Verificar se há redirecionamento pendente
		if (isset($_SESSION['redirect'])) {
			$redirect = $_SESSION['redirect'];
			unset($_SESSION['redirect']);
			self::$redirectCount++;
			$this->redirect($redirect);
			return;
		}

		// Resetar contador de redirecionamentos se chegou aqui
		self::$redirectCount = 0;

		// Garantir que a conexão está estabelecida antes de qualquer coisa
		$this->setupConnection();
		
		// Verificar se existe um controlador para a view
		$controllerName = ucfirst($view) . 'Controller';
		$controllerPath = ROOT_PATH . "/MVC/CONTROLLER/{$controllerName}.php";
		
		error_log("Verificando controlador: " . $controllerPath);
		
		if (file_exists($controllerPath)) {
			error_log("Controlador encontrado: " . $controllerPath);
			require_once($controllerPath);
			
			if (class_exists($controllerName)) {
				error_log("Instanciando controlador: " . $controllerName);
				$controller = new $controllerName($this->conn);
				
				if (method_exists($controller, 'index')) {
					error_log("Chamando método index do controlador");
					
					// Incluir arquivo de inicialização
					require_once(ROOT_PATH . "/MVC/COMMON/init.php");
					
					// Capturar o conteúdo da view
					ob_start();
					$controller->index();
					$content = ob_get_clean();
					
					// Carregar o layout com o conteúdo da view
					include(ROOT_PATH . "/MVC/COMMON/layout.php");
					return;
				} else {
					error_log("Método index não encontrado no controlador");
				}
			} else {
				error_log("Classe do controlador não encontrada: " . $controllerName);
			}
		} else {
			error_log("Controlador não encontrado: " . $controllerPath);
		}
		
		// Se não houver controlador ou se o controlador falhar, tenta carregar a view diretamente
		$viewPath = ROOT_PATH . "/MVC/VIEWS/{$view}.php";
		error_log("Tentando carregar view diretamente: " . $viewPath);
		
		if (!file_exists($viewPath)) {
			error_log("View não encontrada: " . $viewPath);
			$viewPath = ROOT_PATH . "/MVC/VIEWS/404.php";
			error_log("Redirecionando para 404: " . $viewPath);
		}
		
		// Disponibilizar a conexão globalmente
		global $conn;
		$conn = $this->conn;
		
		try {
			// Incluir arquivo de inicialização
			require_once(ROOT_PATH . "/MVC/COMMON/init.php");
			
			// Capturar o conteúdo da view
			ob_start();
			include($viewPath);
			$content = ob_get_clean();
			
			// Carregar o layout com o conteúdo da view
			include(ROOT_PATH . "/MVC/COMMON/layout.php");
			
		} catch (Exception $e) {
			error_log("Erro ao carregar view: " . $e->getMessage());
			if (ob_get_level()) ob_end_clean();
			$_SESSION['msg'] = "<div class='alert alert-danger'>Erro ao carregar a página: " . htmlspecialchars($e->getMessage()) . "</div>";
			header("Location: " . $this->config->url('index.php?view=error'));
			exit;
		}
	}

	private function setupConnection() {
		if (!isset($this->conn)) {
			require_once(ROOT_PATH . "/MVC/MODEL/conexao.php");
			global $conn;
			$this->conn = $conn;
		}
	}

	private function redirect($url) {
		if (ob_get_level()) ob_end_clean();
		header("Location: " . $url);
		exit;
	}
}