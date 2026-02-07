<?php
class Request {
    // Get request method
    public static function method() {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    // Get JSON input
    public static function input() {
        return json_decode(file_get_contents("php://input"), true);
    }
    
    // Get query parameters
    public static function query($key = null) {
        if ($key) {
            return $_GET[$key] ?? null;
        }
        return $_GET;
    }
}
?>