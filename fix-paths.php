<?php

function fixPaths($directory) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory)
    );

    $extensions = ['php', 'css', 'js', 'html'];
    $patterns = [
        // Fix case sensitivity in paths
        '/MVC\/COMMON/i' => 'MVC/COMMON',
        '/MVC\/MODEL/i' => 'MVC/MODEL',
        '/MVC\/VIEW/i' => 'MVC/VIEW',
        '/MVC\/CONTROLLER/i' => 'MVC/CONTROLLER',
        '/\/IMG\//i' => '/img/',
        '/\/CSS\//i' => '/CSS/',
        '/\/JS\//i' => '/JS/',
        '/\/VENDOR\//i' => '/VENDOR/',
        
        // Convert HTTP to HTTPS
        'http:\/\/divinosys\.conext\.click' => 'https://divinosys.conext.click',
        
        // Fix specific paths
        '/mvc\/common/i' => 'MVC/COMMON',
        '/mvc\/model/i' => 'MVC/MODEL',
        '/mvc\/view/i' => 'MVC/VIEW',
        '/mvc\/controller/i' => 'MVC/CONTROLLER',
        
        // Fix image paths specifically
        '\/IMG\/beer\.png' => '/img/beer.png',
        '\/IMG\/User\.png' => '/img/User.png'
    ];

    foreach ($files as $file) {
        if ($file->isFile()) {
            $ext = strtolower(pathinfo($file->getPathname(), PATHINFO_EXTENSION));
            if (in_array($ext, $extensions)) {
                $content = file_get_contents($file->getPathname());
                $originalContent = $content;
                
                foreach ($patterns as $pattern => $replacement) {
                    $content = preg_replace('/' . $pattern . '/', $replacement, $content);
                }
                
                if ($content !== $originalContent) {
                    file_put_contents($file->getPathname(), $content);
                    echo "Fixed: " . $file->getPathname() . "\n";
                }
            }
        }
    }
}

// Execute the fix
$baseDir = __DIR__;
echo "Starting path fixes...\n";
fixPaths($baseDir);
echo "Path fixes completed.\n"; 