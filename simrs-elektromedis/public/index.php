<?php
require_once '../src/Config/config.php';
require_once '../src/Helpers/view.php';

// Autoload classes
spl_autoload_register(function ($class) {
    $prefix = 'src\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Start the session
session_start();

// Define the routing logic
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Simple routing mechanism
if ($requestUri === '/teknisi' && $requestMethod === 'GET') {
    $controller = new \src\Controller\TeknisiController();
    $controller->index();
} elseif ($requestUri === '/teknisi/add' && $requestMethod === 'GET') {
    $controller = new \src\Controller\TeknisiController();
    $controller->add();
} elseif (preg_match('/^\/teknisi\/edit\/(\d+)$/', $requestUri, $matches) && $requestMethod === 'GET') {
    $controller = new \src\Controller\TeknisiController();
    $controller->edit($matches[1]);
} elseif (preg_match('/^\/teknisi\/delete\/(\d+)$/', $requestUri, $matches) && $requestMethod === 'POST') {
    $controller = new \src\Controller\TeknisiController();
    $controller->delete($matches[1]);
} else {
    http_response_code(404);
    echo "404 Not Found";
}
?>