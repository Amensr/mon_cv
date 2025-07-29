<?php
require_once 'db.php';

// Fonction pour obtenir les thèmes CV disponibles
function get_cv_themes($conn) {
    $themes = [];
    $sql = "SELECT * FROM cv_themes WHERE is_active = TRUE";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $themes[] = $row;
        }
    }
    
    return $themes;
}

// Fonction pour sauvegarder un CV
function save_cv($conn, $user_id, $theme_id, $title, $data) {
    $personal_info = json_encode($data['personal_info']);
    $experiences = json_encode($data['experiences']);
    $education = json_encode($data['education']);
    $skills = json_encode($data['skills']);
    $languages = json_encode($data['languages']);
    $hobbies = json_encode($data['hobbies']);
    
    $sql = "INSERT INTO cvs (user_id, theme_id, title, personal_info, experiences, education, skills, languages, hobbies)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssssss", $user_id, $theme_id, $title, $personal_info, $experiences, $education, $skills, $languages, $hobbies);
    
    return $stmt->execute();
}

// Fonction pour obtenir les CV d'un utilisateur
function get_user_cvs($conn, $user_id) {
    $cvs = [];
    $sql = "SELECT c.*, t.name as theme_name 
            FROM cvs c 
            JOIN cv_themes t ON c.theme_id = t.id 
            WHERE c.user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $cvs[] = $row;
        }
    }
    
    return $cvs;
}

// Obtenir un CV par son ID
function get_cv_by_id($conn, $cv_id) {
    $sql = "SELECT * FROM cvs WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cv_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Obtenir les invitations d'un utilisateur
function get_user_invitations($conn, $user_id) {
    $invitations = [];
    $sql = "SELECT i.*, t.name as type_name 
            FROM invitations i 
            JOIN invitation_types t ON i.type_id = t.id 
            WHERE i.user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $invitations[] = $row;
        }
    }
    
    return $invitations;
}

// Obtenir un thème par son ID
function get_theme_by_id($conn, $theme_id) {
    $sql = "SELECT * FROM cv_themes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $theme_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Obtenir tous les CV (pour l'admin)
function get_all_cvs($conn) {
    $cvs = [];
    $sql = "SELECT c.*, t.name as theme_name, u.username 
            FROM cvs c 
            JOIN cv_themes t ON c.theme_id = t.id 
            JOIN users u ON c.user_id = u.id 
            ORDER BY c.created_at DESC";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $cvs[] = $row;
        }
    }
    
    return $cvs;
}

// Obtenir les types d'invitation
function get_invitation_types($conn) {
    $types = [];
    $sql = "SELECT * FROM invitation_types WHERE is_active = TRUE";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $types[] = $row;
        }
    }
    
    return $types;
}

// Sauvegarder une invitation
function save_invitation($conn, $user_id, $type_id, $title, $content, $design_settings) {
    $content_json = json_encode($content);
    $design_json = json_encode($design_settings);
    
    $sql = "INSERT INTO invitations (user_id, type_id, title, content, design_settings)
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $user_id, $type_id, $title, $content_json, $design_json);
    
    return $stmt->execute();
}

// Obtenir un nom d'utilisateur par ID
function get_username_by_id($conn, $user_id) {
    $sql = "SELECT username FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['username'] ?? 'Inconnu';
}

function get_all_invitations($conn) {
    $invitations = [];
    $sql = "SELECT i.*, it.name as type_name, u.username 
            FROM invitations i
            JOIN invitation_types it ON i.type_id = it.id
            JOIN users u ON i.user_id = u.id
            ORDER BY i.created_at DESC";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['content'] = json_decode($row['content'], true);
            $row['design_settings'] = json_decode($row['design_settings'], true);
            $invitations[] = $row;
        }
    }
    
    return $invitations;
}

function get_new_this_week($conn) {
    $sql = "SELECT COUNT(*) as count FROM cvs 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            UNION ALL
            SELECT COUNT(*) FROM invitations 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    
    $result = $conn->query($sql);
    $count = 0;
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $count += $row['count'];
        }
    }
    
    return $count;
}

function get_recent_activity($conn, $limit = 10) {
    $sql = "SELECT u.username, a.action, a.created_at 
            FROM admin_activity a
            JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function format_date($date_string) {
    $date = new DateTime($date_string);
    return $date->format('d/m/Y H:i');
}

function is_admin($conn, $user_id) {
    $sql = "SELECT is_admin FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['is_admin'] ?? false;
}

function get_premium_plans($conn) {
    $plans = [];
    $sql = "SELECT * FROM premium_plans WHERE is_active = TRUE ORDER BY price ASC";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['features'] = json_decode($row['features'], true);
            $row['recommended'] = $row['id'] == 2; // Marquer le 2ème plan comme recommandé
            $row['duration_months'] = round($row['duration_days'] / 30);
            $plans[] = $row;
        }
    }
    
    return $plans;
}

function get_user_subscription($conn, $user_id) {
    $sql = "SELECT s.*, p.name, p.features 
            FROM premium_subscriptions s
            JOIN premium_plans p ON s.plan_id = p.id
            WHERE s.user_id = ? AND s.is_active = TRUE AND s.ends_at > NOW()";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $subscription = $result->fetch_assoc();
        $subscription['features'] = json_decode($subscription['features'], true);
        return $subscription;
    }
    
    return null;
}

function check_auth() {
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    
    // Optionnel : vérifier si l'utilisateur est actif
    global $conn;
    $stmt = $conn->prepare("SELECT is_active FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0 || !$result->fetch_assoc()['is_active']) {
        session_destroy();
        header('Location: login.php?error=inactive');
        exit();
    }
}
// Fonction pour vérifier si l'utilisateur est premium
function is_user_premium($conn, $user_id) {
    $sql = "SELECT is_premium FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['is_premium'] == 1;
    }
    
    return false;
}
// Fonction pour vérifier si l'utilisateur est admin
function is_user_admin($conn, $user_id) {
    $sql = "SELECT is_admin FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['is_admin'] == 1;
    }
    
    return false;
}

// Fonction pour envoyer un email de récupération
function sendRecoveryEmail($email, $reset_link) {
    $subject = "Récupération de compte";
    $message = "Cliquez sur le lien suivant pour réinitialiser votre mot de passe : $reset_link";
    $headers = "From: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
    return mail($email, $subject, $message, $headers);
}
// Fonction pour générer un token de vérification
function generate_verification_token() {
    return bin2hex(random_bytes(16)); // Génère un token aléatoire de 32 caractères hexadécimaux
}   
?>