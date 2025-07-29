<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_types':
            $types = get_invitation_types($conn);
            $response = ['success' => true, 'types' => $types];
            break;
            
        case 'save_invitation':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $required = ['user_id', 'type_id', 'title', 'content', 'design_settings'];
            
            foreach ($required as $field) {
                if (!isset($data[$field])) {
                    throw new Exception("Champ manquant: $field");
                }
            }
            
            if (save_invitation($conn, $data['user_id'], $data['type_id'], $data['title'], $data['content'], $data['design_settings'])) {
                $response = [
                    'success' => true,
                    'message' => 'Invitation sauvegardée',
                    'invitation_id' => $conn->insert_id
                ];
            } else {
                throw new Exception('Erreur lors de la sauvegarde');
            }
            break;
            
        case 'get_invitation':
            $invitation_id = intval($_GET['id']);
            $invitation = get_invitation_by_id($conn, $invitation_id);
            
            if ($invitation) {
                $response = ['success' => true, 'invitation' => $invitation];
            } else {
                throw new Exception('Invitation non trouvée');
            }
            break;
            
        default:
            throw new Exception('Action non reconnue');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    echo json_encode($response);
    $conn->close();
}

function get_invitation_types($conn) {
    $types = [];
    $sql = "SELECT * FROM invitation_types WHERE is_active = TRUE";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $types[] = $row;
        }
    }
    
    return $types;
}

function save_invitation($conn, $user_id, $type_id, $title, $content, $design_settings) {
    $content_json = json_encode($content);
    $design_json = json_encode($design_settings);
    
    $sql = "INSERT INTO invitations (user_id, type_id, title, content, design_settings) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $user_id, $type_id, $title, $content_json, $design_json);
    
    return $stmt->execute();
}

function get_invitation_by_id($conn, $id) {
    $sql = "SELECT * FROM invitations WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    $invitation = $result->fetch_assoc();
    $invitation['content'] = json_decode($invitation['content'], true);
    $invitation['design_settings'] = json_decode($invitation['design_settings'], true);
    
    return $invitation;
}