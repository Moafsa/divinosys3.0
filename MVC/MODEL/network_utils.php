<?php

class NetworkUtils {
    public static function getLocalIP() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            $output = [];
            exec('ipconfig', $output);
            foreach ($output as $line) {
                if (strpos($line, 'IPv4') !== false) {
                    if (preg_match('/192\.168\.\d+\.\d+/', $line, $matches)) {
                        return $matches[0];
                    }
                }
            }
        } else {
            // Linux - usa ip addr para pegar o IP
            $output = [];
            exec('ip addr | grep "inet " | grep -v "127.0.0.1"', $output);
            foreach ($output as $line) {
                if (preg_match('/inet\s+(\d+\.\d+\.\d+\.\d+)/', $line, $matches)) {
                    if (strpos($matches[1], '192.168.') === 0) {
                        return $matches[1];
                    }
                }
            }
            // Se não encontrou IP 192.168.*, pega o primeiro IP não-localhost
            foreach ($output as $line) {
                if (preg_match('/inet\s+(\d+\.\d+\.\d+\.\d+)/', $line, $matches)) {
                    return $matches[1];
                }
            }
        }
        
        // Fallback para o IP do servidor
        return $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
    }

    public static function generateAccessURL($type = 'default') {
        $ip = self::getLocalIP();
        $port = $_SERVER['SERVER_PORT'] ?? '8080';
        
        // Generate specific URLs based on user type
        switch ($type) {
            case 'kitchen':
                return "http://{$ip}:{$port}/?view=kitchen";
            case 'waiter':
                return "http://{$ip}:{$port}/?view=waiter";
            case 'cashier':
                return "http://{$ip}:{$port}/?view=Dashboard1";
            default:
                return "http://{$ip}:{$port}/";
        }
    }
} 