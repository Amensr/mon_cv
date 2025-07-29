<?php
require_once 'config.php';
require_once 'db.php';

class SessionManager {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function createSession($user_id, $session_data) {
        $session_id = bin2hex(random_bytes(32));
        $ip_address = $session_data['ip_address'] ?? $_SERVER['REMOTE_ADDR'];
        $user_agent = $session_data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'];
        
        $stmt = $this->conn->prepare("
            INSERT INTO user_sessions 
            (user_id, session_id, ip_address, user_agent, expires_at) 
            VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))
        ");
        $stmt->bind_param("isss", $user_id, $session_id, $ip_address, $user_agent);
        $stmt->execute();
        
        return $session_id;
    }
    
    public function validateSession($user_id, $session_id) {
        $stmt = $this->conn->prepare("
            SELECT 1 FROM user_sessions 
            WHERE user_id = ? AND session_id = ? 
            AND expires_at > NOW()
        ");
        $stmt->bind_param("is", $user_id, $session_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    public function invalidateSession($session_id) {
        $stmt = $this->conn->prepare("
            DELETE FROM user_sessions WHERE session_id = ?
        ");
        $stmt->bind_param("s", $session_id);
        return $stmt->execute();
    }
    
    public function invalidateAllSessions($user_id, $exclude_session = null) {
        $sql = "DELETE FROM user_sessions WHERE user_id = ?";
        $params = [$user_id];
        $types = "i";
        
        if ($exclude_session) {
            $sql .= " AND session_id != ?";
            $params[] = $exclude_session;
            $types .= "s";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }
    
    public function getActiveSessions($user_id) {
        $stmt = $this->conn->prepare("
            SELECT * FROM user_sessions 
            WHERE user_id = ? AND expires_at > NOW()
            ORDER BY created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function cleanupExpiredSessions() {
        $this->conn->query("DELETE FROM user_sessions WHERE expires_at <= NOW()");
    }
    
    public function set2FAVerified($session_id) {
        $stmt = $this->conn->prepare("
            UPDATE user_sessions 
            SET is_2fa_verified = TRUE 
            WHERE session_id = ?
        ");
        $stmt->bind_param("s", $session_id);
        return $stmt->execute();
    }
    
    public function is2FAVerified($session_id) {
        $stmt = $this->conn->prepare("
            SELECT is_2fa_verified FROM user_sessions 
            WHERE session_id = ? AND expires_at > NOW()
        ");
        $stmt->bind_param("s", $session_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 && $result->fetch_assoc()['is_2fa_verified'];
    }
}