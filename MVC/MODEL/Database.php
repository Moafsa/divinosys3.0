<?php
class Database {
    private static $instance = null;
    private $connection = null;
    private $config = null;

    private function __construct() {
        $this->loadConfig();
        $this->connect();
    }

    private function loadConfig() {
        $this->config = require __DIR__ . '/../config/database.php';
    }

    private function connect() {
        try {
            // Create connection
            $this->connection = mysqli_connect(
                $this->config['host'],
                $this->config['user'],
                $this->config['password'],
                $this->config['database'],
                $this->config['port']
            );

            if (!$this->connection) {
                throw new Exception("Connection failed: " . mysqli_connect_error());
            }

            // Set charset
            if (!mysqli_set_charset($this->connection, $this->config['charset'])) {
                throw new Exception("Error setting charset: " . mysqli_error($this->connection));
            }

            // Set timezone to UTC
            mysqli_query($this->connection, "SET time_zone = '+00:00'");

            error_log("Database connection established successfully");
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw $e;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql) {
        $result = mysqli_query($this->connection, $sql);
        if ($result === false) {
            $error = mysqli_error($this->connection);
            error_log("Query failed: " . $error);
            throw new Exception("Query failed: " . $error);
        }
        return $result;
    }

    public function escape($string) {
        return mysqli_real_escape_string($this->connection, $string);
    }

    public function close() {
        if ($this->connection) {
            mysqli_close($this->connection);
            $this->connection = null;
        }
    }

    public function __destruct() {
        $this->close();
    }
} 