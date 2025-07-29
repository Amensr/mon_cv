<?php
require_once 'config.php';
require_once 'db.php';

class TwoFactorAuth {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function setup2FA($user_id) {
        require_once 'vendor/autoload.php'; // Inclure Google Authenticator
        
        $ga = new PHPGangsta_GoogleAuthenticator();
        $secret = $ga->createSecret();
        
        // Sauvegarder le secret
        $stmt = $this->conn->prepare("
            INSERT INTO user_2fa (user_id, secret, created_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE secret = ?
        ");
        $stmt->bind_param("iss", $user_id, $secret, $secret);
        $stmt->execute();
        
        return [
            'secret' => $secret,
            'qr_code_url' => $ga->getQRCodeGoogleUrl(SITE_NAME, $secret)
        ];
    }
    
    public function verify2FASetup($user_id, $code) {
        $secret = $this->getUserSecret($user_id);
        
        if (!$secret) {
            throw new Exception("Aucune configuration 2FA trouvée");
        }
        
        $ga = new PHPGangsta_GoogleAuthenticator();
        $isValid = $ga->verifyCode($secret, $code, 2); // 2 = marge de 60 secondes
        
        if ($isValid) {
            $stmt = $this->conn->prepare("UPDATE user_2fa SET is_active = TRUE WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }
        
        return $isValid;
    }
    
    public function verifyLoginCode($user_id, $code) {
        $secret = $this->getUserSecret($user_id);
        
        if (!$secret) {
            return true; // Si 2FA n'est pas activé, laisser passer
        }
        
        $ga = new PHPGangsta_GoogleAuthenticator();
        return $ga->verifyCode($secret, $code, 2);
    }
    
    public function is2FAEnabled($user_id) {
        $stmt = $this->conn->prepare("
            SELECT is_active FROM user_2fa 
            WHERE user_id = ? AND is_active = TRUE
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    public function disable2FA($user_id) {
        $stmt = $this->conn->prepare("DELETE FROM user_2fa WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }
    
    private function getUserSecret($user_id) {
        $stmt = $this->conn->prepare("SELECT secret FROM user_2fa WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0 ? $result->fetch_assoc()['secret'] : null;
    }
    
    public function generateBackupCodes($user_id, $count = 5) {
        $codes = [];
        
        for ($i = 0; $i < $count; $i++) {
            $codes[] = bin2hex(random_bytes(4)); // 8 caractères hexadécimaux
        }
        
        // Sauvegarder les codes (hachés)
        $hashed_codes = array_map('password_hash', $codes, array_fill(0, $count, PASSWORD_BCRYPT));
        $codes_json = json_encode($hashed_codes);
        
        $stmt = $this->conn->prepare("
            UPDATE user_2fa 
            SET backup_codes = ?
            WHERE user_id = ?
        ");
        $stmt->bind_param("si", $codes_json, $user_id);
        $stmt->execute();
        
        return $codes;
    }
    
    public function verifyBackupCode($user_id, $code) {
        $stmt = $this->conn->prepare("SELECT backup_codes FROM user_2fa WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return false;
        }
        
        $backup_codes = json_decode($result->fetch_assoc()['backup_codes'], true);
        
        foreach ($backup_codes as $index => $hashed_code) {
            if (password_verify($code, $hashed_code)) {
                // Supprimer le code utilisé
                unset($backup_codes[$index]);
                $new_codes = json_encode(array_values($backup_codes));
                
                $stmt = $this->conn->prepare("UPDATE user_2fa SET backup_codes = ? WHERE user_id = ?");
                $stmt->bind_param("si", $new_codes, $user_id);
                $stmt->execute();
                
                return true;
            }
        }
        
        return false;
    }
}