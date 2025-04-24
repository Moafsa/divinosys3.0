<?php
// Prevenir qualquer saída
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

// Incluir arquivo de configuração se ainda não foi incluído
if (!class_exists('Config')) {
    require_once(ROOT_PATH . "/MVC/MODEL/config.php");
}

// Inicializar a configuração
$config = Config::getInstance();

// Verificar login apenas se não estivermos na página de login
$current_script = basename($_SERVER['SCRIPT_NAME']);
$is_login_page = $current_script === 'index.php' && empty($_GET['view']);

if (!$is_login_page && (!isset($_SESSION['login']) || $_SESSION['login'] !== true)) {
    if (ob_get_level()) ob_end_clean();
    $_SESSION['msg'] = "<div class='alert alert-danger'>Acesso não autorizado! Faça login primeiro.</div>";
    header("Location: " . $config->getLoginUrl());
    exit;
}

// Garantir que temos uma conexão com o banco de dados
global $conn;
if (!isset($conn)) {
    require_once(ROOT_PATH . "/MVC/MODEL/conexao.php");
} 