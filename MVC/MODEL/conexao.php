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
                error_log("=== Database Connection Debug ===");
                error_log("Host: " . $host);
                error_log("Database: " . $dbname);
                error_log("User: " . $user);
                error_log("Current Directory: " . __DIR__);
                error_log("Document Root: " . $_SERVER['DOCUMENT_ROOT']);
                error_log("Environment Variables:");
                error_log("DB_HOST: " . getenv('DB_HOST'));
                error_log("DB_DATABASE: " . getenv('DB_DATABASE'));
                error_log("DB_USERNAME: " . getenv('DB_USERNAME'));

                // Test DNS resolution
                $ip = gethostbyname($host);
                error_log("Resolved IP for {$host}: " . $ip);
                if ($ip === $host) {
                    error_log("WARNING: Could not resolve hostname");
                }

                // Create PDO connection with extended timeout
                $dsn = "mysql:host={$host};dbname={$dbname};connect_timeout=10";
                self::$instance = new PDO(
                    $dsn,
                    $user,
                    $pass,
                    array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                        PDO::ATTR_TIMEOUT => 10,
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    )
                );
                
                error_log("=== Database connection successful ===");
            } catch(PDOException $e) {
                error_log("Connection Error Details: " . $e->getMessage());
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