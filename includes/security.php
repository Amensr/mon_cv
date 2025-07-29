<?php

if (!defined('JWT_SECRET')) {
    define('JWT_SECRET', 'votre_secret_jwt_super_secure_!2023');
}
if (!defined('JWT_ALGORITHM')) {
    define('JWT_ALGORITHM', 'HS256');
}
//Protection contre les injections SQL
require_once 'config.php';
// Protection contre les attaques XSS
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return $data;
}

// Protection CSRF
class CSRF_Protection {
    private static $token_name = 'csrf_token';
    
    public static function generate_token() {
        if (empty($_SESSION[self::$token_name])) {
            $_SESSION[self::$token_name] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::$token_name];
    }
    
    public static function validate_token($token) {
        return isset($_SESSION[self::$token_name]) && 
               hash_equals($_SESSION[self::$token_name], $token);
    }
    
    public static function get_token_field() {
        return '<input type="hidden" name="csrf_token" value="'.self::generate_token().'">';
    }
}

// Protection contre le bruteforce
class Login_Protection {
    private static $max_attempts = 5;
    private static $lockout_time = 300; // 5 minutes
    
    public static function check_attempts($conn, $ip) {
        $sql = "SELECT COUNT(*) as count, MAX(attempt_time) as last_attempt 
                FROM login_attempts 
                WHERE ip_address = ? 
                AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $ip, self::$lockout_time);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['count'] >= self::$max_attempts) {
            $remaining_time = self::$lockout_time - (time() - strtotime($result['last_attempt']));
            if ($remaining_time > 0) {
                die("Trop de tentatives. Veuillez réessayer dans ".gmdate("i:s", $remaining_time));
            }
        }
    }
    
    public static function record_attempt($conn, $ip, $success) {
        $sql = "INSERT INTO login_attempts (ip_address, success) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $ip, $success);
        $stmt->execute();
        
        if (!$success) {
            // Nettoyer les anciennes tentatives
            $clean_sql = "DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL ? SECOND)";
            $stmt = $conn->prepare($clean_sql);
            $stmt->bind_param("i", self::$lockout_time);
            $stmt->execute();
        }
    }
}

// Sécurité des headers HTTP
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");




// Validation des mots de passe 

function validate_password($password) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
}

// Protection des fichiers uploadés
function validate_uploaded_file($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }
    
    if ($file['size'] > $max_size) {
        return false;
    }
    
    // Vérifier que c'est bien une image
    if (strpos($file['type'], 'image') === 0) {
        $image_info = getimagesize($file['tmp_name']);
        if (!$image_info) {
            return false;
        }
    }
    
    return true;
}

// [...] (contenu précédent)


function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !$token || $token !== $_SESSION['csrf_token']) {
        throw new Exception("Token CSRF invalide");
    }
}
class JWT {
    public static function encode($payload, $key, $alg) {
        $header = json_encode(['typ' => 'JWT', 'alg' => $alg]);
        $payload = json_encode($payload);
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $key, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    public static function decode($jwt, $key) {
        $parts = explode('.', $jwt);
        
        if (count($parts) != 3) {
            throw new Exception('Token JWT invalide');
        }
        
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;
        
        $signature = base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlSignature));
        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $key, true);
        
        if (!hash_equals($signature, $expectedSignature)) {
            throw new Exception('Signature JWT invalide');
        }
        
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlPayload)), true);
        
        // Vérifier l'expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token JWT expiré');
        }
        
        return $payload;
    }
}

function validate_jwt($token) {
    try {
        return JWT::decode($token, JWT_SECRET);
    } catch (Exception $e) {
        return false;
    }
}

// Fonction pour vérifier l'authentification de l'utilisateur

// Fonction pour vérifier si l'utilisateur est admin ou premium 

function check_admin() {
    check_auth();
    
    if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        header('Location: /unauthorized.php');
        exit();
    }
}

function check_premium() {
    check_auth();
    
    if (!isset($_SESSION['is_premium']) || !$_SESSION['is_premium']) {
        header('Location: /upgrade.php');
        exit();
    }
}