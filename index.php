<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration
require_once __DIR__ . '/config/database.php';

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

// Remove base path
$base_path = '/food-ordering-system/backend';
$path = str_replace($base_path, '', $request_uri);
$path = trim($path, '/');

// Remove index.php if present
if (strpos($path, 'index.php') === 0) {
    $path = substr($path, strlen('index.php'));
    $path = trim($path, '/');
}

// Remove query string
$path = explode('?', $path)[0];

// Define routes
$routes = [
    'GET' => [
        '' => 'home',
        'api/test' => 'test',
        'api/users' => 'getUsers',
         'api/products' => 'getProducts',
    ],
    'POST' => [
        'api/register' => 'register',
        'api/login' => 'login',
    ]

];

// Handle the request
if (isset($routes[$method][$path])) {
    $action = $routes[$method][$path];
    
    switch ($action) {
        case 'home':
            echo json_encode([
                'success' => true,
                'message' => 'foof-ordering-system API v1.0',
                'endpoints' => [
                    'GET /api/test' => 'Test API connection',
                    'POST /api/register' => 'Register new user',
                    'POST /api/login' => 'Login user',
                    'GET /api/users' => 'Get all users (admin)'
                ]
            ]);
            break;
            
        case 'test':
            echo json_encode([
                'success' => true,
                'message' => 'API is working perfectly!',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'getUsers':
            require_once __DIR__ . '/controllers/UserController.php';
            $controller = new UserController();
            $controller->getAll();
            break;
            
        case 'register':
            require_once __DIR__ . '/controllers/AuthController.php';
            $controller = new AuthController();
            $controller->register();
            break;
            
        case 'login':
            require_once __DIR__ . '/controllers/AuthController.php';
            $controller = new AuthController();
            $controller->login();
            break;

        case 'getProducts':
            require_once __DIR__ . '/controllers/ProductController.php';
            $controller = new ProductController();
            $controller->getAll();
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Action not implemented']);
    }
} else {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => 'Route not found',
        'requested' => $path ?: '(empty)',
        'method' => $method,
        'available_routes' => $routes[$method] ?? []
    ]);
}
?>