<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Vérification d'identité et de permissions
if (!isset($_SESSION['user_id']) || !is_admin($conn, $_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit();
}

$response = ['success' => false, 'message' => ''];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'stats':
            // Statistiques globales
            $stats = [
                'total_users' => get_total_count($conn, 'users'),
                'total_cvs' => get_total_count($conn, 'cvs'),
                'total_invitations' => get_total_count($conn, 'invitations'),
                'last_signups' => get_last_signups($conn, 5),
                'popular_themes' => get_popular_themes($conn)
            ];
            
            $response = ['success' => true, 'stats' => $stats];
            break;
            
        case 'delete_cv':
            $cv_id = intval($_GET['id']);
            $stmt = $conn->prepare("DELETE FROM cvs WHERE id = ?");
            $stmt->bind_param("i", $cv_id);
            
            if ($stmt->execute()) {
                log_action($conn, $_SESSION['user_id'], "CV supprimé (ID: $cv_id)");
                $response = ['success' => true];
            }
            break;
            
        case 'manage_user':
            $user_id = intval($_POST['user_id']);
            $action_type = $_POST['type']; // 'ban', 'unban', 'promote', 'demote'
            
            // Implémentation des actions utilisateur
            break;
            
        case 'get_activity':
            $limit = intval($_GET['limit'] ?? 50);
            $activity = get_recent_activity($conn, $limit);
            $response = ['success' => true, 'activity' => $activity];
            break;
            
        default:
            $response['message'] = 'Action non reconnue';
    }
} catch (Exception $e) {
    $response['message'] = 'Erreur: ' . $e->getMessage();
} finally {
    echo json_encode($response);
    $conn->close();
}

function get_total_count($conn, $table) {
    $sql = "SELECT COUNT(*) as count FROM $table";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['count'];
}

function get_last_signups($conn, $limit = 5) {
    $sql = "SELECT username, email, created_at FROM users ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_popular_themes($conn) {
    $sql = "SELECT t.name, COUNT(c.id) as count 
            FROM cv_themes t 
            LEFT JOIN cvs c ON t.id = c.theme_id 
            GROUP BY t.id 
            ORDER BY count DESC 
            LIMIT 5";
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function get_recent_activity($conn, $limit = 50) {
    $sql = "SELECT a.*, u.username 
            FROM admin_activity a 
            JOIN users u ON a.user_id = u.id 
            ORDER BY a.timestamp DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function log_action($conn, $user_id, $action) {
    $sql = "INSERT INTO admin_activity (user_id, action) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $action);
    $stmt->execute();
}

function is_admin($conn, $user_id) {
    $sql = "SELECT is_admin FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['is_admin'] ?? false;
}