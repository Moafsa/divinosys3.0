<?php

return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_NAME') ?: 'divino_lanches',
    'user' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: '',
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
    'collation' => getenv('DB_COLLATION') ?: 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
]; 