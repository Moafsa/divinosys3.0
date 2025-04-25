<?php
// Prevent any output before headers
if (ob_get_level()) ob_end_clean();
ob_start();

// Configurações do Sistema
class Config {
    private static $instance = null;
    private $config = [];
    private $baseUrl;

    private function __construct() {
        // Detecta o ambiente automaticamente
        $this->detectEnvironment();
        
        // Configurações do banco de dados
        $this->config['db'] = [
            'host' => 'mysql',
            'user' => 'root',
            'pass' => '122334Qw!!Conext',
            'name' => 'pdv_db'
        ];

        // Configurações de URL
$this->baseUrl = $this->detectBaseUrl();
        $this->config['assets_url'] = rtrim($this->baseUrl, '/') . '/assets';
        
        // Log para debug
        error_log("Base URL detectada: " . $this->baseUrl);
    }

    private function detectEnvironment() {
        $server_name = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $this->config['environment'] = ($server_name === 'localhost' || $server_name === '127.0.0.1') 
            ? 'development' 
            : 'production';
    }

    private function detectBaseUrl() {
        // Protocol (http or https)
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        
        // Host (domain) with port
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost:8080';
        
        // Base path
        $baseUrl = "{$protocol}://{$host}";
        
        // Debug logs
        error_log("Host detected: {$host}");
        error_log("Base URL: {$baseUrl}");
        
        return $baseUrl;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function get($key = null) {
        $instance = self::getInstance();
        if ($key === null) {
            return $instance->config;
        }
        return $instance->config[$key] ?? null;
    }

    public function getBaseUrl() {
        return $this->baseUrl;
    }

    public function url($path = '') {
        // Remove barras iniciais e finais
        $path = trim($path, '/');
        
        // Se o caminho estiver vazio, retorna a URL base
        if (empty($path)) {
            return rtrim($this->baseUrl, '/');
        }
        
        // Monta a URL completa
        $url = rtrim($this->baseUrl, '/') . '/' . $path;
        
        // Log para debug
        error_log("URL Gerada para {$path}: {$url}");
        
        return $url;
    }

    public function redirect($path = '') {
        header('Location: ' . $this->url($path));
        exit;
    }

    // URLs específicas do sistema
    public function getDashboardUrl() {
        return $this->url('index.php?view=Dashboard1');
    }

    public function getConfigUrl() {
        return $this->url('MVC/MODEL/configuracao.php');
    }

    public function getLoginUrl() {
        return $this->url('index.php');
    }

    public function getAssetsUrl($path = '') {
        $path = trim($path, '/');
        return rtrim($this->baseUrl, '/') . '/assets/' . $path;
    }

    public static function assets($path = '') {
        return self::getInstance()->getAssetsUrl($path);
    }
}

// Função global para facilitar o uso
if (!function_exists('url')) {
    function url($path = '') {
        return Config::getInstance()->url($path);
    }
}

// Função global para assets
if (!function_exists('assets')) {
    function assets($path = '') {
        return Config::getInstance()->assets($path);
    }
}

// Inicializa a configuração
Config::getInstance();
?> 