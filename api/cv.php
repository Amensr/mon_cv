<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'save':
            // Sauvegarder les données du CV
            $data = json_decode(file_get_contents('php://input'), true);
            $result = save_cv($conn, $_SESSION['user_id'], $data['theme_id'], $data['title'], $data);
            
            if ($result) {
                $response = [
                    'success' => true,
                    'message' => 'CV sauvegardé avec succès',
                    'cv_id' => $conn->insert_id
                ];
            } else {
                $response['message'] = 'Erreur lors de la sauvegarde';
            }
            break;
            
        case 'delete':
            $cv_id = intval($_POST['cv_id']);
            $user_id = $_SESSION['user_id'];
            
            $stmt = $conn->prepare("DELETE FROM cvs WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $cv_id, $user_id);
            
            if ($stmt->execute()) {
                $response = [
                    'success' => true,
                    'message' => 'CV supprimé avec succès'
                ];
            }
            break;
            
        case 'get':
            $cv_id = intval($_GET['id']);
            $cv = get_cv_by_id($conn, $cv_id);
            
            if ($cv && $cv['user_id'] == $_SESSION['user_id']) {
                $response = [
                    'success' => true,
                    'data' => [
                        'personal_info' => json_decode($cv['personal_info'], true),
                        'experiences' => json_decode($cv['experiences'], true),
                        'education' => json_decode($cv['education'], true),
                        'skills' => json_decode($cv['skills'], true),
                        'theme_id' => $cv['theme_id']
                    ]
                ];
            }
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