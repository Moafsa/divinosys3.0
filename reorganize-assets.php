<?php

/**
 * Script to reorganize assets while maintaining compatibility
 * Run this script from the project root
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define paths
define('ROOT_PATH', __DIR__);
define('ASSETS_PATH', ROOT_PATH . '/MVC/COMMON');

// Create asset directories if they don't exist
$directories = [
    'css',
    'js',
    'img',
    'vendor',
    'fonts',
    'uploads'
];

// Create directories
foreach ($directories as $dir) {
    $path = ASSETS_PATH . '/' . $dir;
    if (!file_exists($path)) {
        mkdir($path, 0755, true);
        echo "Created directory: {$path}\n";
    }
}

// Source directories to consolidate from
$sourceDirs = [
    ROOT_PATH . '/assets' => ASSETS_PATH,
    ROOT_PATH . '/MVC/COMMON/ASSETS' => ASSETS_PATH,
];

// Function to copy directory recursively
function copyDir($src, $dst) {
    if (!is_dir($src)) return false;
    
    $dir = opendir($src);
    if (!file_exists($dst)) {
        mkdir($dst, 0755, true);
    }
    
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;
            
            if (is_dir($srcPath)) {
                copyDir($srcPath, $dstPath);
            } else {
                if (!file_exists($dstPath) || md5_file($srcPath) !== md5_file($dstPath)) {
                    copy($srcPath, $dstPath);
                    echo "Copied: {$srcPath} -> {$dstPath}\n";
                }
            }
        }
    }
    closedir($dir);
    return true;
}

// Move files from source directories to MVC/COMMON
foreach ($sourceDirs as $src => $dst) {
    if (!file_exists($src)) {
        echo "Warning: Source directory not found: {$src}\n";
        continue;
    }
    
    if (copyDir($src, $dst)) {
        echo "Successfully copied {$src} to {$dst}\n";
    } else {
        echo "Error copying {$src}\n";
    }
}

// Update assets configuration
$assetsConfig = <<<PHP
<?php

/**
 * Asset configuration file
 * This file manages the asset URLs and ensures they are served over HTTPS
 */

// Base URL configuration
\$protocol = 'https';  // Always use HTTPS
\$baseUrl = \$protocol . '://' . \$_SERVER['HTTP_HOST'];

// Asset paths configuration
return [
    // CSS files
    'css' => [
        'animate' => \$baseUrl . '/MVC/COMMON/css/animate.min.css',
        'datepicker' => \$baseUrl . '/MVC/COMMON/css/bootstrap-datepicker.css',
        'fontawesome' => \$baseUrl . '/MVC/COMMON/vendor/fontawesome-free/css/all.min.css',
        'sb-admin' => \$baseUrl . '/MVC/COMMON/css/sb-admin-2.min.css',
        'custom' => \$baseUrl . '/MVC/COMMON/css/custom.css',
        'sidebar' => \$baseUrl . '/MVC/COMMON/css/sidebar-fix.css',
        'bootstrap' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css'
    ],
    
    // JavaScript files
    'js' => [
        'jquery' => 'https://code.jquery.com/jquery-3.6.0.min.js',
        'bootstrap-bundle' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js',
        'jquery-easing' => \$baseUrl . '/MVC/COMMON/vendor/jquery-easing/jquery.easing.min.js',
        'sb-admin' => \$baseUrl . '/MVC/COMMON/js/sb-admin-2.min.js',
        'chart' => \$baseUrl . '/MVC/COMMON/vendor/chart.js/Chart.min.js',
        'bootstrap' => \$baseUrl . '/MVC/COMMON/vendor/bootstrap/js/bootstrap.min.js',
        'datepicker' => \$baseUrl . '/MVC/COMMON/js/bootstrap-datepicker.min.js',
        'datepicker-ptbr' => \$baseUrl . '/MVC/COMMON/js/bootstrap-datepicker.pt-BR.min.js'
    ],
    
    // Images
    'img' => [
        'favicon' => \$baseUrl . '/MVC/COMMON/img/beer.png'
    ]
];
PHP;

file_put_contents(ROOT_PATH . '/MVC/config/assets.php', $assetsConfig);
echo "Updated assets configuration file\n";

// Create .htaccess for assets directory
$htaccess = <<<HTACCESS
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Force HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Allow access to assets
    <FilesMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$">
        Order Allow,Deny
        Allow from all
    </FilesMatch>
    
    # Prevent directory listing
    Options -Indexes
</IfModule>

# Cache control
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
    ExpiresByType application/x-javascript "access plus 1 month"
    ExpiresByType image/x-icon "access plus 1 year"
    ExpiresDefault "access plus 2 days"
</IfModule>
HTACCESS;

file_put_contents(ASSETS_PATH . '/.htaccess', $htaccess);
echo "Created .htaccess file for assets directory\n";

// Create symbolic links for backward compatibility
if (file_exists(ROOT_PATH . '/assets')) {
    rename(ROOT_PATH . '/assets', ROOT_PATH . '/assets.bak');
    symlink(ASSETS_PATH, ROOT_PATH . '/assets');
    echo "Created symbolic link for /assets\n";
}

echo "\nAsset reorganization complete!\n";
echo "All assets are now consolidated in MVC/COMMON while maintaining compatibility with existing paths.\n";
?> 