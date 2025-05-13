<?php
// Prevent any output before headers
ob_start();

// Detect if this is a JSON endpoint
$is_json_endpoint = defined('IS_JSON_ENDPOINT') || 
    stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false || 
    stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false ||
    $_SERVER['REQUEST_METHOD'] === 'POST' ||
    $_SERVER['REQUEST_METHOD'] === 'DELETE' ||
    $_SERVER['REQUEST_METHOD'] === 'PUT';

// Configure error display based on endpoint type
if ($is_json_endpoint) {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Set default timezone
date_default_timezone_set('America/Sao_Paulo');

// Database connection settings
$servidor = "db"; // Docker service name from docker-compose.yml
$usuario = "divino";
$senha = "divino123";
$dbname = "divinosys";

// Debug: Print environment variables
error_log("=== DEBUG: Variáveis de Ambiente ===");
error_log("DB_HOST: " . ($servidor ?: 'não definido'));
error_log("DB_USER: " . ($usuario ?: 'não definido'));
error_log("DB_NAME: " . ($dbname ?: 'não definido'));
error_log("DB_PASS: " . (empty($senha) ? 'vazio' : 'definido'));

// Log connection attempt
error_log("=== Iniciando nova tentativa de conexão ===");
error_log("Servidor: " . $servidor);
error_log("Banco: " . $dbname);

// Database connection
global $conn;
$conn = new mysqli($servidor, $usuario, $senha, $dbname);

// If connection fails, display error message
if ($conn->connect_error) {
    echo "<div style='color: red; font-weight: bold; text-align: center; margin-top: 20px;'>";
    echo "Connection Error: Could not connect to database.";
    echo "</div>";
    error_log("Main connection failure: " . $conn->connect_error);
    // Don't kill execution to allow page to load with appropriate message
} else {
    // Set charset to UTF-8
    mysqli_set_charset($conn, "utf8");

    // Set timezone to UTC in MySQL and handle conversion in PHP
    try {
        mysqli_query($conn, "SET time_zone = '+00:00'");
        error_log("Timezone do MySQL configurado para UTC com sucesso!");
    } catch (Exception $e) {
        error_log("Aviso: Usando timezone padrão do MySQL: " . $e->getMessage());
    }

    // Test if we can actually execute queries
    $test_query = mysqli_query($conn, "SELECT 1");
    if (!$test_query) {
        throw new Exception("Erro ao executar query de teste: " . mysqli_error($conn));
    }
    error_log("Query de teste executada com sucesso!");

    // Define constant to indicate successful connection
    define('DB_CONNECTION_SUCCESS', true);
    error_log("=== Conexão estabelecida com sucesso! ===");
}

// Function to safely close database connection
function closeConnection() {
    global $conn;
    static $already_closed = false;
    
    if (!$already_closed && isset($conn) && $conn instanceof mysqli && !($conn->connect_errno)) {
        try {
            @mysqli_close($conn);
            $already_closed = true;
            unset($conn);
        } catch (Exception $e) {
            error_log("Erro ao fechar conexão: " . $e->getMessage());
        }
    }
}
?>