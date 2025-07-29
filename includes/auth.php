<?php
require_once 'db.php';
require_once 'security.php';
require_once __DIR__ . '/validation.php';

class Auth {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
     public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    public function getUserEmail() {
        return $_SESSION['email'] ?? null;
    }
    public function getUserName() {
        return $_SESSION['username'] ?? null;
    }
    public function isUserPremium() {
        return isset($_SESSION['is_premium']) && $_SESSION['is_premium'];
    }
    public function isUserAdmin() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    }
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
    }
    /**
     * Enregistre un nouvel utilisateur.
     *
     * @param string $username Nom d'utilisateur
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe de l'utilisateur
     * @return int ID de l'utilisateur créé
     * @throws Exception Si l'email ou le nom d'utilisateur est déjà utilisé, ou si les validations échouent
     */
    public function validateCsrfToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            throw new Exception("Token CSRF invalide");
        }
    }
    public function register($username, $email, $password) {
        // Validation des entrées
        if (!validate_email($email)) {
            throw new Exception("Email invalide");
        }
        
        if (!validate_password($password)) {
            throw new Exception("Le mot de passe doit contenir 8 caractères minimum, avec majuscule, minuscule, chiffre et caractère spécial");
        }
        
        // Vérifier si l'email ou le username existe déjà
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Email ou nom d'utilisateur déjà utilisé");
        }
        
        // Hachage du mot de passe
        $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Génération du token de vérification
        $verification_token = bin2hex(random_bytes(32));
        $verification_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Insertion de l'utilisateur
        $stmt = $this->conn->prepare("
            INSERT INTO users (username, email, password, verification_token, verification_expires) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssss", $username, $email, $hashed_password, $verification_token, $verification_expires);
        
        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de la création du compte");
        }
        
        // Envoyer l'email de vérification
        $this->send_verification_email($email, $username, $verification_token);
        
        return $this->conn->insert_id;
    }
    
    public function login($email, $password) {
        // Récupérer l'utilisateur
        $stmt = $this->conn->prepare("
            SELECT id, username, email, password, is_verified, is_active, is_premium, is_admin 
            FROM users 
            WHERE email = ?
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Identifiants incorrects");
        }
        
        $user = $result->fetch_assoc();
        
        // Vérifier le mot de passe
        if (!password_verify($password, $user['password'])) {
            throw new Exception("Identifiants incorrects");
        }
        
        // Vérifier si le compte est actif
        if (!$user['is_active']) {
            throw new Exception("Ce compte a été désactivé");
        }
        
        // Vérifier si l'email est confirmé
        if (!$user['is_verified']) {
            throw new Exception("Veuillez vérifier votre email avant de vous connecter");
        }
        
        // Générer un token JWT
        $token = $this->generate_jwt($user['id'], $user['email'], $user['is_admin']);
        
        // Mettre à jour la dernière connexion
        $this->update_last_login($user['id']);
        
        return [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'is_premium' => $user['is_premium'],
            'is_admin' => $user['is_admin'],
            'token' => $token
        ];
    }
    
    public function verify_email($token) {
        $stmt = $this->conn->prepare("
            SELECT id FROM users 
            WHERE verification_token = ? 
            AND verification_expires > NOW() 
            AND is_verified = FALSE
        ");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Token invalide ou expiré");
        }
        
        $user_id = $result->fetch_assoc()['id'];
        
        // Activer le compte
        $stmt = $this->conn->prepare("
            UPDATE users 
            SET is_verified = TRUE, 
                verification_token = NULL, 
                verification_expires = NULL 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $user_id);
        
        return $stmt->execute();
    }
    
    private function generate_jwt($user_id, $email, $is_admin) {
        $payload = [
            'iss' => SITE_URL,
            'aud' => SITE_URL,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24 * 7), // 1 semaine
            'userId' => $user_id,
            'email' => $email,
            'isAdmin' => $is_admin
        ];
        
        return JWT::encode($payload, JWT_SECRET, 'HS256');
    }
    
    private function update_last_login($user_id) {
        $stmt = $this->conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
    
    private function send_verification_email($email, $username, $token) {
        $verification_url = SITE_URL . "/verify_email.php?token=" . $token;
        $subject = "Vérification de votre email - " . SITE_NAME;
        
        $message = "
            <html>
            <head>
                <title>Vérification d'email</title>
            </head>
            <body>
                <h2>Bonjour $username,</h2>
                <p>Merci de vous être inscrit sur " . SITE_NAME . "!</p>
                <p>Veuillez cliquer sur le lien suivant pour vérifier votre adresse email :</p>
                <p><a href='$verification_url'>$verification_url</a></p>
                <p>Ce lien expirera dans 24 heures.</p>
                <p>Cordialement,<br>L'équipe " . SITE_NAME . "</p>
            </body>
            </html>
        ";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: no-reply@" . parse_url(SITE_URL, PHP_URL_HOST) . "\r\n";
        
        mail($email, $subject, $message, $headers);
    }
}

