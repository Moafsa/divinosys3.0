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

class Conexao {
    private static $instance;

    public static function getConn() {
        if (!isset(self::$instance)) {
            try {
                // Get environment variables with fallbacks
                $host = getenv('DB_HOST') ?: 'mysql';
                $dbname = getenv('DB_DATABASE') ?: 'pdv_db';
                $user = getenv('DB_USERNAME') ?: 'root';
                $pass = getenv('DB_PASSWORD') ?: '122334Qw!!Conext';

                // Log connection attempt
                error_log("=== Attempting database connection ===");
                error_log("Host: " . $host);
                error_log("Database: " . $dbname);
                error_log("User: " . $user);

                // Create PDO connection
                self::$instance = new PDO(
                    "mysql:host={$host};dbname={$dbname}",
                    $user,
                    $pass,
                    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
                );

                // Configure PDO
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
                
                // Set timezone
                self::$instance->exec("SET time_zone = '+00:00'");
                
                error_log("=== Database connection successful ===");
            } catch(PDOException $e) {
                error_log("Connection Error: " . $e->getMessage());
                if (getenv('APP_DEBUG') == 'true') {
                    echo "Connection Error: " . $e->getMessage();
                } else {
                    echo "Connection Error: Could not connect to database.";
                }
                die();
            }
        }
        return self::$instance;
    }
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