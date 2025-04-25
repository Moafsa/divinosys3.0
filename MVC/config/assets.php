<?php

/**
 * Asset configuration file
 * This file manages the asset URLs and ensures they are served over HTTPS
 */

// Base URL configuration
$protocol = 'https';  // Sempre usar HTTPS
$baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'];

// Asset paths configuration
return [
    // CSS files
    'css' => [
        'animate' => $baseUrl . '/MVC/COMMON/css/animate.min.css',
        'datepicker' => $baseUrl . '/MVC/COMMON/css/bootstrap-datepicker.css',
        'fontawesome' => $baseUrl . '/MVC/COMMON/VENDOR/fontawesome-free/css/all.min.css',
        'sb-admin' => $baseUrl . '/MVC/COMMON/css/sb-admin-2.min.css',
        'custom' => $baseUrl . '/MVC/COMMON/css/custom.css',
        'sidebar' => $baseUrl . '/MVC/COMMON/css/sidebar-fix.css',
        'bootstrap' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css'
    ],
    
    // JavaScript files
    'js' => [
        'jquery' => 'https://code.jquery.com/jquery-3.6.0.min.js',
        'bootstrap-bundle' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js',
        'jquery-easing' => $baseUrl . '/MVC/COMMON/VENDOR/jquery-easing/jquery.easing.min.js',
        'sb-admin' => $baseUrl . '/MVC/COMMON/js/sb-admin-2.min.js',
        'chart' => $baseUrl . '/MVC/COMMON/VENDOR/chart.js/Chart.min.js',
        'bootstrap' => $baseUrl . '/MVC/COMMON/VENDOR/bootstrap/js/bootstrap.min.js',
        'datepicker' => $baseUrl . '/MVC/COMMON/js/bootstrap-datepicker.min.js',
        'datepicker-ptbr' => $baseUrl . '/MVC/COMMON/js/bootstrap-datepicker.pt-BR.min.js'
    ],
    
    // Images
    'img' => [
        'favicon' => $baseUrl . '/MVC/COMMON/img/beer.png'
    ]
]; 