<?php

/**
 * Front Controller — Entry point for all requests
 * Loads configuration, autoloader, and dispatches routes
 */

// Load configuration
require_once dirname(__DIR__) . '/config/config.php';

// Load Composer autoloader
$autoloader = APP_ROOT . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
} else {
    // Fallback: manual class autoloading
    spl_autoload_register(function ($class) {
        // App namespace
        if (str_starts_with($class, 'App\\')) {
            $path = APP_ROOT . '/' . str_replace('\\', '/', lcfirst(substr($class, 0, 4)) . substr($class, 4)) . '.php';
            // Convert App\ to app/
            $path = APP_ROOT . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }

        // KHQR namespace
        if (str_starts_with($class, 'KHQR\\')) {
            $path = APP_ROOT . '/khqr/src/' . str_replace('\\', '/', substr($class, 5)) . '.php';
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }
    });
}

// Parse request URI — strip base path
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = BASE_PATH;
$uri = $requestUri;

if (str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
}

// Ensure URI starts with /
if (empty($uri) || $uri === false) {
    $uri = '/';
}
if ($uri[0] !== '/') {
    $uri = '/' . $uri;
}

// Remove trailing slash (except root)
if ($uri !== '/' && str_ends_with($uri, '/')) {
    $uri = rtrim($uri, '/');
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Load routes and dispatch
$router = require APP_ROOT . '/routes/web.php';
$router->dispatch($method, $uri);
