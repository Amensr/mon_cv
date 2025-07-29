<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/security.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];
$action = $_GET['action'] ?? '';

try {
    // Vérifier l'authentification
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Authentification requise');
    }

    $user_id = $_SESSION['user_id'];
    $conn = get_db_connection();

    switch ($action) {
        case 'start_session':
            $cv_id = intval($_POST['cv_id']);
            validate_cv_ownership($conn, $cv_id, $user_id);
            
            // Créer une nouvelle session
            $stmt = $conn->prepare("
                INSERT INTO collaboration_sessions (cv_id, created_by) 
                VALUES (?, ?)
            ");
            $stmt->bind_param("ii", $cv_id, $user_id);
            $stmt->execute();
            
            $session_id = $conn->insert_id;
            
            // Ajouter le créateur comme participant
            $stmt = $conn->prepare("
                INSERT INTO collaboration_participants (session_id, user_id) 
                VALUES (?, ?)
            ");
            $stmt->bind_param("ii", $session_id, $user_id);
            $stmt->execute();
            
            $response = [
                'success' => true,
                'session_id' => $session_id,
                'invite_code' => generate_invite_code($session_id)
            ];
            break;
            
        case 'join_session':
            $invite_code = $_POST['invite_code'];
            $session_id = decode_invite_code($invite_code);
            
            // Vérifier que la session existe et est active
            $stmt = $conn->prepare("
                SELECT cs.*, c.user_id as cv_owner_id 
                FROM collaboration_sessions cs
                JOIN cvs c ON cs.cv_id = c.id
                WHERE cs.id = ? AND cs.is_active = TRUE
            ");
            $stmt->bind_param("i", $session_id);
            $stmt->execute();
            $session = $stmt->get_result()->fetch_assoc();
            
            if (!$session) {
                throw new Exception('Session invalide ou terminée');
            }
            
            // Vérifier les permissions (le propriétaire ou premium)
            if ($session['cv_owner_id'] != $user_id && !is_premium_user($conn, $user_id)) {
                throw new Exception('Vous devez être premium pour rejoindre cette session');
            }
            
            // Vérifier si l'utilisateur est déjà dans la session
            $stmt = $conn->prepare("
                SELECT 1 FROM collaboration_participants 
                WHERE session_id = ? AND user_id = ?
            ");
            $stmt->bind_param("ii", $session_id, $user_id);
            $stmt->execute();
            
            if (!$stmt->get_result()->num_rows) {
                // Ajouter le participant
                $stmt = $conn->prepare("
                    INSERT INTO collaboration_participants (session_id, user_id) 
                    VALUES (?, ?)
                ");
                $stmt->bind_param("ii", $session_id, $user_id);
                $stmt->execute();
            }
            
            // Mettre à jour last_active
            $stmt = $conn->prepare("
                UPDATE collaboration_participants 
                SET last_active = NOW() 
                WHERE session_id = ? AND user_id = ?
            ");
            $stmt->bind_param("ii", $session_id, $user_id);
            $stmt->execute();
            
            $response = [
                'success' => true,
                'session_id' => $session_id,
                'cv_id' => $session['cv_id']
            ];
            break;
            
        case 'end_session':
            $session_id = intval($_POST['session_id']);
            validate_session_ownership($conn, $session_id, $user_id);
            
            // Terminer la session
            $stmt = $conn->prepare("
                UPDATE collaboration_sessions 
                SET is_active = FALSE, ended_at = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("i", $session_id);
            $stmt->execute();
            
            $response = ['success' => true];
            break;
            
        case 'get_participants':
            $session_id = intval($_GET['session_id']);
            
            $stmt = $conn->prepare("
                SELECT u.id, u.username, u.email, cp.joined_at, cp.last_active
                FROM collaboration_participants cp
                JOIN users u ON cp.user_id = u.id
                WHERE cp.session_id = ?
                ORDER BY cp.last_active DESC
            ");
            $stmt->bind_param("i", $session_id);
            $stmt->execute();
            
            $participants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            $response = [
                'success' => true,
                'participants' => $participants
            ];
            break;
            
        default:
            throw new Exception('Action non reconnue');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    echo json_encode($response);
    if (isset($conn)) $conn->close();
}

function generate_invite_code($session_id) {
    return base64_encode("cvses_{$session_id}_" . bin2hex(random_bytes(4)));
}

function decode_invite_code($code) {
    $decoded = base64_decode($code);
    if (preg_match('/^cvses_(\d+)_/', $decoded, $matches)) {
        return intval($matches[1]);
    }
    throw new Exception('Code d\'invitation invalide');
}

function validate_cv_ownership($conn, $cv_id, $user_id) {
    $stmt = $conn->prepare("SELECT 1 FROM cvs WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cv_id, $user_id);
    $stmt->execute();
    if (!$stmt->get_result()->num_rows) {
        throw new Exception('Vous n\'êtes pas propriétaire de ce CV');
    }
}

function validate_session_ownership($conn, $session_id, $user_id) {
    $stmt = $conn->prepare("
        SELECT 1 FROM collaboration_sessions 
        WHERE id = ? AND created_by = ?
    ");
    $stmt->bind_param("ii", $session_id, $user_id);
    $stmt->execute();
    if (!$stmt->get_result()->num_rows) {
        throw new Exception('Vous n\'êtes pas propriétaire de cette session');
    }
}

function is_premium_user($conn, $user_id) {
    $stmt = $conn->prepare("SELECT is_premium FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['is_premium'] ?? false;
}