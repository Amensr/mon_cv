<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Configurer le header pour retourner du JSON
header('Content-Type: application/json');

// Vérifier la signature du webhook
function verifyWebhookSignature($payload, $signature, $secret) {
    $computed_signature = hash_hmac('sha256', $payload, $secret);
    return hash_equals($signature, $computed_signature);
}

// Récupérer le payload brut
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';

// Valider la signature
if (!verifyWebhookSignature($payload, $signature, WEBHOOK_SECRET)) {
    http_response_code(401);
    echo json_encode(['error' => 'Signature invalide']);
    exit();
}

// Décoder le payload JSON
$data = json_decode($payload, true);
$event_type = $_SERVER['HTTP_X_EVENT_TYPE'] ?? '';

try {
    $conn = get_db_connection();
    
    switch ($event_type) {
        case 'oauth.revoked':
            // Gérer la révocation d'un token OAuth
            handleOAuthRevocation($conn, $data);
            break;
            
        case 'payment.succeeded':
            // Gérer un paiement réussi
            handlePaymentSucceeded($conn, $data);
            break;
            
        case 'user.deleted':
            // Gérer la suppression d'un compte utilisateur
            handleUserDeleted($conn, $data);
            break;
            
        default:
            throw new Exception("Type d'événement non supporté");
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
    
} finally {
    if (isset($conn)) $conn->close();
}

function handleOAuthRevocation($conn, $data) {
    $provider = $data['provider'] ?? '';
    $provider_id = $data['provider_id'] ?? '';
    
    if (empty($provider) || empty($provider_id)) {
        throw new Exception("Données de révocation invalides");
    }
    
    // Supprimer l'association OAuth
    $stmt = $conn->prepare("
        DELETE FROM oauth_users 
        WHERE provider = ? AND provider_id = ?
    ");
    $stmt->bind_param("ss", $provider, $provider_id);
    $stmt->execute();
    
    // Journaliser l'action
    logWebhookEvent($conn, 'oauth.revoked', $provider_id, $data);
}

function handlePaymentSucceeded($conn, $data) {
    $user_id = $data['metadata']['user_id'] ?? null;
    $plan_id = $data['metadata']['plan_id'] ?? null;
    
    if (!$user_id || !$plan_id) {
        throw new Exception("Données de paiement incomplètes");
    }
    
    // Activer l'abonnement premium
    $stmt = $conn->prepare("
        INSERT INTO premium_subscriptions 
        (user_id, plan_id, starts_at, ends_at, is_active, payment_method) 
        VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), TRUE, ?)
        ON DUPLICATE KEY UPDATE 
        plan_id = VALUES(plan_id),
        ends_at = DATE_ADD(ends_at, INTERVAL 1 YEAR),
        is_active = TRUE
    ");
    $payment_method = $data['payment_method'] ?? 'unknown';
    $stmt->bind_param("iis", $user_id, $plan_id, $payment_method);
    $stmt->execute();
    
    // Mettre à jour le statut de l'utilisateur
    $conn->query("UPDATE users SET is_premium = TRUE WHERE id = $user_id");
    
    // Envoyer un email de confirmation
    sendPaymentConfirmationEmail($user_id, $plan_id);
    
    // Journaliser l'action
    logWebhookEvent($conn, 'payment.succeeded', $user_id, $data);
}

function handleUserDeleted($conn, $data) {
    $user_id = $data['user_id'] ?? null;
    
    if (!$user_id) {
        throw new Exception("ID utilisateur manquant");
    }
    
    // Désactiver le compte (suppression douce)
    $stmt = $conn->prepare("
        UPDATE users 
        SET is_active = FALSE, 
            email = CONCAT('deleted_', UNIX_TIMESTAMP(), '_', email),
            username = CONCAT('deleted_', UNIX_TIMESTAMP(), '_', username)
        WHERE id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    // Invalider toutes les sessions
    $sessionManager = new SessionManager($conn);
    $sessionManager->invalidateAllSessions($user_id);
    
    // Journaliser l'action
    logWebhookEvent($conn, 'user.deleted', $user_id, $data);
}

function logWebhookEvent($conn, $event_type, $entity_id, $payload) {
    $stmt = $conn->prepare("
        INSERT INTO webhook_logs 
        (event_type, entity_id, payload) 
        VALUES (?, ?, ?)
    ");
    $payload_json = json_encode($payload);
    $stmt->bind_param("sss", $event_type, $entity_id, $payload_json);
    $stmt->execute();
}

function sendPaymentConfirmationEmail($user_id, $plan_id) {
    // Implémentation réelle nécessite un système d'email
    // Cette fonction est un placeholder
    error_log("Payment confirmation email sent to user $user_id for plan $plan_id");
}