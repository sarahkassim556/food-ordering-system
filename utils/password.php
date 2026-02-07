<?php
class Password {
    // Hash password
    public static function hash($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }
    
    // Verify password
    public static function verify($password, $hash) {
        return password_verify($password, $hash);
    }
    
    // Generate random password
    public static function generate($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }
}
?>