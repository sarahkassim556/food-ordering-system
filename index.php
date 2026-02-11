<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config/database.php';
require_once 'config/cors.php';

// Simple router
$request_uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove base path
$base_path = '/food-ordering-system/backend';
$request_uri = str_replace($base_path, '', $request_uri);

// Parse query parameters
$url_parts = parse_url($request_uri);
$path = $url_parts['path'];
$query_params = isset($url_parts['query']) ? $url_parts['query'] : '';

// Route the request
switch ($path) {
    case '/api/auth/register':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        if ($method === 'POST') {
            echo $controller->register();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case '/api/auth/login':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        if ($method === 'POST') {
            echo $controller->login();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case '/api/products':
        require_once 'controllers/ProductController.php';
        $controller = new ProductController();
        if ($method === 'GET') {
            // Check for search or category filter
            parse_str($query_params, $params);
            if (isset($params['search'])) {
                echo $controller->search($params['search']);
            } elseif (isset($params['category'])) {
                echo $controller->getByCategory($params['category']);
            } else {
                echo $controller->getAllProducts();
            }
            }
        } elseif ($method === 'POST') {
            echo $controller->create();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case '/api/categories':
        require_once 'controllers/CategoryController.php';
        $controller = new CategoryController();
        if ($method === 'GET') {
            echo $controller->getAllCategories();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case '/api/cart':
        require_once 'controllers/CartController.php';
        $controller = new CartController();
        switch ($method) {
            case 'GET':
                echo $controller->getCart();
                break;
            case 'POST':
                echo $controller->addToCart();
                break;
            case 'PUT':
                echo $controller->updateCartItem();
                break;
            case 'DELETE':
                echo $controller->clearCart();
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case '/api/orders':
        require_once 'controllers/OrderController.php';
        $controller = new OrderController();
        if ($method === 'POST') {
            echo $controller->placeOrder();
        } elseif ($method === 'GET') {
            echo $controller->getUserOrders();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;

    case '/api/admin/orders':
        require_once 'controllers/OrderController.php';
        $controller = new OrderController();
        if ($method === 'GET') {
            echo $controller->getAllOrders();
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;

    // Cart routes commented out as we are moving to LocalStorage for simplicity
    /*
    case '/api/cart':
       // ...
    */

        
    default:
        // Check for product details route
        if (preg_match('/^\/api\/products\/(\d+)$/', $path, $matches)) {
            require_once 'controllers/ProductController.php';
            $controller = new ProductController();
            if ($method === 'GET') {
                echo $controller->getProduct($matches[1]);
            } elseif ($method === 'PUT') {
                echo $controller->update($matches[1]);
            } elseif ($method === 'DELETE') {
                echo $controller->delete($matches[1]);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
        } elseif (preg_match('/^\/api\/admin\/orders\/(\d+)$/', $path, $matches)) {
            require_once 'controllers/OrderController.php';
            $controller = new OrderController();
            if ($method === 'PUT') {
                echo $controller->updateOrderStatus($matches[1]);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
        }
        break;
}
?>