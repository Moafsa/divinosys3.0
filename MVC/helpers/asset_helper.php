<?php

/**
 * Asset Helper Functions
 * These functions help manage and serve assets securely over HTTPS
 */

if (!function_exists('get_asset')) {
    /**
     * Get the full URL for an asset
     * 
     * @param string $type The type of asset (css, js, img, vendor)
     * @param string $name The name of the asset as defined in the config
     * @param bool $use_cdn Whether to prefer CDN version if available
     * @return string The full URL of the asset
     */
    function get_asset($type, $name, $use_cdn = true) {
        static $assets = null;
        static $cdn_assets = null;
        
        if ($assets === null) {
            $assets = require_once __DIR__ . '/../config/assets.php';
        }
        
        if ($cdn_assets === null) {
            $cdn_assets = require_once __DIR__ . '/../config/cdn_assets.php';
        }
        
        // Check CDN first if preferred
        if ($use_cdn && isset($cdn_assets['cdn'][$type][$name])) {
            return $cdn_assets['cdn'][$type][$name];
        }
        
        // Then check local assets
        if (isset($cdn_assets['local'][$type][$name])) {
            $base_url = rtrim($assets['base_url'], '/');
            $asset_path = ltrim($cdn_assets['local'][$type][$name], '/');
            return "{$base_url}/MVC/COMMON/{$asset_path}";
        }
        
        // Fallback to old config
        if (isset($assets[$type][$name])) {
            return $assets[$type][$name];
        }
        
        error_log("Asset not found: {$type}/{$name}");
        return '';
    }
}

if (!function_exists('css_asset')) {
    /**
     * Get a CSS asset URL
     */
    function css_asset($name, $use_cdn = true) {
        return get_asset('css', $name, $use_cdn);
    }
}

if (!function_exists('js_asset')) {
    /**
     * Get a JavaScript asset URL
     */
    function js_asset($name, $use_cdn = true) {
        return get_asset('js', $name, $use_cdn);
    }
}

if (!function_exists('img_asset')) {
    /**
     * Get an image asset URL
     */
    function img_asset($name) {
        return get_asset('img', $name, false);
    }
}

if (!function_exists('vendor_asset')) {
    /**
     * Get a vendor asset URL
     */
    function vendor_asset($name, $use_cdn = true) {
        return get_asset('vendor', $name, $use_cdn);
    }
}

if (!function_exists('ensure_https')) {
    /**
     * Ensure a URL uses HTTPS
     */
    function ensure_https($url) {
        if (empty($url)) return '';
        return preg_replace('/^http:/', 'https:', $url);
    }
} 