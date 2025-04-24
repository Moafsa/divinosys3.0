<?php
return [
    'host' => getenv('DB_HOST') ?: 'mysql',
    'database' => getenv('DB_DATABASE') ?: 'pdv_db',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '122334Qw!!Conext',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]; 