<?php
// Mock request/server variables if needed
$_SERVER['REQUEST_METHOD'] = 'GET';

require_once 'controllers/ProductController.php';

$controller = new ProductController();
$response = $controller->getAllProducts();

echo "Response:\n" . $response;
?>
