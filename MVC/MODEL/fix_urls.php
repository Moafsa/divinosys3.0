<?php
function fix_urls($dir) {
    $files = glob($dir . '/*.php');
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $content = str_replace('/', '/', $content);
        file_put_contents($file, $content);
    }
    
    // Process subdirectories
    $subdirs = glob($dir . '/*', GLOB_ONLYDIR);
    foreach ($subdirs as $subdir) {
        fix_urls($subdir);
    }
}

// Start from the root directory
fix_urls(__DIR__ . '/..');
echo "URLs fixed successfully!\n";
?> 