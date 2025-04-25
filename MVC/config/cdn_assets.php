<?php

/**
 * CDN and Local Assets Configuration
 * This file manages both CDN and local assets to ensure HTTPS usage
 */

return [
    // CDN Assets
    'cdn' => [
        'css' => [
            'bootstrap' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css',
            'fontawesome' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
            'google_fonts' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap'
        ],
        'js' => [
            'jquery' => 'https://code.jquery.com/jquery-3.6.0.min.js',
            'bootstrap_bundle' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js',
            'sweetalert2' => 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js'
        ]
    ],

    // Local Assets (relative to MVC/COMMON)
    'local' => [
        'css' => [
            'animate' => 'css/animate.min.css',
            'datepicker' => 'css/bootstrap-datepicker.css',
            'sb_admin' => 'css/sb-admin-2.min.css',
            'custom' => 'css/custom.css',
            'sidebar' => 'css/sidebar-fix.css',
            'login' => 'css/login-style.css'
        ],
        'js' => [
            'jquery_easing' => 'vendor/jquery-easing/jquery.easing.min.js',
            'sb_admin' => 'js/sb-admin-2.min.js',
            'chart' => 'vendor/chart.js/Chart.min.js',
            'datepicker' => 'js/bootstrap-datepicker.min.js',
            'datepicker_ptbr' => 'js/bootstrap-datepicker.pt-BR.min.js'
        ],
        'img' => [
            'favicon' => 'img/beer.png',
            'logo' => 'img/logo.png'
        ],
        'vendor' => [
            'fontawesome' => 'vendor/fontawesome-free/css/all.min.css',
            'bootstrap' => 'vendor/bootstrap/css/bootstrap.min.css',
            'jquery' => 'vendor/jquery/jquery.min.js',
            'bootstrap_bundle' => 'vendor/bootstrap/js/bootstrap.bundle.min.js'
        ]
    ]
]; 