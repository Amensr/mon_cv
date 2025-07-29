<?php
require_once 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/session.php';

check_auth();

$sessionManager = new SessionManager($conn);
$twoFA = new TwoFactorAuth($conn);
$error = '';
$success = '';

// Gérer les actions de sécurité
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'change_password':
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                if ($new_password !== $confirm_password) {
                    throw new Exception("Les nouveaux mots de passe ne correspondent pas");
                }
                
                if (!validate_password($new_password)) {
                    throw new Exception("Le mot de passe doit contenir 8 caractères minimum, avec majuscule, minuscule, chiffre et caractère spécial");
                }
                
                // Vérifier le mot de passe actuel
                $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    throw new Exception("Utilisateur non trouvé");
                }
                
                $user = $result->fetch_assoc();
                
                if (!password_verify($current_password, $user['password'])) {
                    throw new Exception("Mot de passe actuel incorrect");
                }
                
                // Mettre à jour le mot de passe
                $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $new_hashed_password, $_SESSION['user_id']);
                $stmt->execute();
                
                // Invalider toutes les sessions sauf la courante
                $sessionManager->invalidateAllSessions($_SESSION['user_id'], $_SESSION['session_id']);
                
                $success = "Votre mot de passe a été mis à jour avec succès";
                break;
                
            case 'revoke_session':
                $session_id = $_POST['session_id'] ?? '';
                
                if (empty($session_id)) {
                    throw new Exception("Session invalide");
                }
                
                $sessionManager->invalidateSession($session_id);
                $success = "La session a été révoquée avec succès";
                break;
                
            case 'enable_2fa':
                header('Location: /setup_2fa.php');
                exit();
                
            case 'disable_2fa':
                $current_password = $_POST['current_password'] ?? '';
                
                // Vérifier le mot de passe
                $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                if (!password_verify($current_password, $user['password'])) {
                    throw new Exception("Mot de passe incorrect");
                }
                
                $twoFA->disable2FA($_SESSION['user_id']);
                $success = "L'authentification à deux facteurs a été désactivée";
                break;
                
            default:
                throw new Exception("Action non reconnue");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Récupérer les données
$is_2fa_enabled = $twoFA->is2FAEnabled($_SESSION['user_id']);
$active_sessions = $sessionManager->getActiveSessions($_SESSION['user_id']);
?>

<div class="account-container">
    <h1>Sécurité du compte</h1>
    
    <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <div class="security-sections">
        <section class="security-section">
            <h2>Authentification à deux facteurs</h2>
            <p class="section-description">
                Ajoutez une couche de sécurité supplémentaire à votre compte.
            </p>
            
            <div class="security-status">
                <span class="status-label">Statut :</span>
                <span class="status-value <?= $is_2fa_enabled ? 'enabled' : 'disabled' ?>">
                    <?= $is_2fa_enabled ? 'Activé' : 'Désactivé' ?>
                </span>
            </div>
            
            <?php if ($is_2fa_enabled): ?>
            <form method="POST" class="security-form">
                <input type="hidden" name="action" value="disable_2fa">
                
                <div class="form-group">
                    <label for="current_password">Mot de passe actuel</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-danger">Désactiver 2FA</button>
            </form>
            <?php else: ?>
            <a href="setup_2fa.php" class="btn btn-primary">Activer 2FA</a>
            <?php endif; ?>
        </section>
        
        <section class="security-section">
            <h2>Changer le mot de passe</h2>
            
            <form method="POST" class="security-form">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label for="current_password">Mot de passe actuel</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                    <small class="text-muted">8 caractères minimum, avec majuscule, minuscule, chiffre et caractère spécial</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Mettre à jour le mot de passe</button>
            </form>
        </section>
        
        <section class="security-section">
            <h2>Sessions actives</h2>
            <p class="section-description">
                Voici les appareils actuellement connectés à votre compte.
            </p>
            
            <div class="sessions-list">
                <?php foreach ($active_sessions as $session): ?>
                <div class="session-item <?= $session['session_id'] === $_SESSION['session_id'] ? 'current' : '' ?>">
                    <div class="session-info">
                        <div class="session-device">
                            <?= getDeviceInfo($session['user_agent']) ?>
                        </div>
                        <div class="session-ip">
                            IP: <?= htmlspecialchars($session['ip_address']) ?>
                        </div>
                        <div class="session-date">
                            Connecté le <?= format_date($session['created_at']) ?>
                        </div>
                        <?php if ($session['is_2fa_verified']): ?>
                        <div class="session-verified">
                            ✅ Vérifié par 2FA
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($session['session_id'] !== $_SESSION['session_id']): ?>
                    <form method="POST" class="session-action">
                        <input type="hidden" name="action" value="revoke_session">
                        <input type="hidden" name="session_id" value="<?= htmlspecialchars($session['session_id']) ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Révoquer</button>
                    </form>
                    <?php else: ?>
                    <div class="session-current">
                        Cette session
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>

<style>
.account-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.security-sections {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.security-section {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.section-description {
    color: #666;
    margin-bottom: 1.5rem;
}

.security-status {
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
}

.status-label {
    font-weight: bold;
    margin-right: 0.5rem;
}

.status-value {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: bold;
}

.status-value.enabled {
    background: #d4edda;
    color: #155724;
}

.status-value.disabled {
    background: #f8d7da;
    color: #721c24;
}

.security-form {
    margin-top: 1.5rem;
}

.sessions-list {
    margin-top: 1rem;
}

.session-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border: 1px solid #eee;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.session-item.current {
    background: #f8f9fa;
}

.session-info {
    flex: 1;
}

.session-device {
    font-weight: bold;
    margin-bottom: 0.3rem;
}

.session-ip, .session-date, .session-verified {
    font-size: 0.9rem;
    color: #666;
}

.session-verified {
    color: #28a745;
    margin-top: 0.3rem;
}

.session-current {
    color: #6c757d;
    font-style: italic;
}

@media (min-width: 768px) {
    .security-sections {
        grid-template-columns: 1fr 1fr;
    }
}
</style>

<?php 
function getDeviceInfo($user_agent) {
    // Analyse simple de l'user agent
    if (strpos($user_agent, 'Mobile') !== false) {
        if (strpos($user_agent, 'iPhone') !== false) {
            return 'iPhone';
        } elseif (strpos($user_agent, 'Android') !== false) {
            return 'Téléphone Android';
        }
        return 'Mobile';
    } elseif (strpos($user_agent, 'Macintosh') !== false) {
        return 'Mac';
    } elseif (strpos($user_agent, 'Windows') !== false) {
        return 'PC Windows';
    } elseif (strpos($user_agent, 'Linux') !== false) {
        return 'Linux';
    }
    return 'Appareil inconnu';
}

require_once 'includes/footer.php'; 
?>