<?php
require_once 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

$step = $_GET['step'] ?? 'identify';
$error = '';
$success = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $auth = new Auth($conn);
        
        switch ($step) {
            case 'identify':
                $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
                
                if (!validate_email($email)) {
                    throw new Exception("Adresse email invalide");
                }
                
                // Vérifier si l'email existe
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                
                if ($stmt->get_result()->num_rows === 0) {
                    throw new Exception("Aucun compte associé à cet email");
                }
                
                // Créer un token de récupération
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET reset_token = ?, reset_expires = ? 
                    WHERE email = ?
                ");
                $stmt->bind_param("sss", $token, $expires, $email);
                $stmt->execute();
                
                // Envoyer l'email de récupération
                $reset_link = SITE_URL."/account-recovery.php?step=reset&token=$token";
                sendRecoveryEmail($email, $reset_link);
                
                $success = "Un email de récupération a été envoyé à $email";
                $step = 'email_sent';
                break;
                
            case 'reset':
                $token = $_GET['token'] ?? '';
                $password = $_POST['password'] ?? '';
                $password_confirm = $_POST['password_confirm'] ?? '';
                
                if (empty($token)) {
                    throw new Exception("Token de réinitialisation manquant");
                }
                
                if ($password !== $password_confirm) {
                    throw new Exception("Les mots de passe ne correspondent pas");
                }
                
                if (!validate_password($password)) {
                    throw new Exception("Le mot de passe doit contenir 8 caractères minimum, avec majuscule, minuscule, chiffre et caractère spécial");
                }
                
                // Vérifier le token
                $stmt = $conn->prepare("
                    SELECT id FROM users 
                    WHERE reset_token = ? 
                    AND reset_expires > NOW()
                ");
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    throw new Exception("Token invalide ou expiré");
                }
                
                $user_id = $result->fetch_assoc()['id'];
                
                // Mettre à jour le mot de passe
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET password = ?, 
                        reset_token = NULL, 
                        reset_expires = NULL 
                    WHERE id = ?
                ");
                $stmt->bind_param("si", $hashed_password, $user_id);
                $stmt->execute();
                
                // Invalider toutes les sessions existantes
                $sessionManager = new SessionManager($conn);
                $sessionManager->invalidateAllSessions($user_id);
                
                $success = "Votre mot de passe a été réinitialisé avec succès";
                $step = 'complete';
                break;
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

function sendRecoveryEmail($email, $reset_link) {
    $subject = "Réinitialisation de votre mot de passe - " . SITE_NAME;
    
    $message = "
        <html>
        <head>
            <title>Réinitialisation de mot de passe</title>
        </head>
        <body>
            <h2>Réinitialisation de mot de passe</h2>
            <p>Vous avez demandé à réinitialiser votre mot de passe pour le site " . SITE_NAME . ".</p>
            <p>Cliquez sur le lien suivant pour procéder :</p>
            <p><a href='$reset_link'>$reset_link</a></p>
            <p>Ce lien expirera dans 1 heure.</p>
            <p>Si vous n'êtes pas à l'origine de cette demande, veuillez ignorer cet email.</p>
        </body>
        </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . EMAIL_FROM_NAME . " <" . EMAIL_FROM . ">\r\n";
    
    mail($email, $subject, $message, $headers);
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Récupération de compte</h1>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php switch ($step): 
            case 'identify': ?>
            <form method="POST">
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Continuer</button>
            </form>
            <?php break; ?>
            
            <?php case 'email_sent': ?>
            <p>Veuillez vérifier votre boîte email et suivre les instructions.</p>
            <p>Si vous ne recevez pas d'email, vérifiez votre dossier spam ou <a href="account_recovery.php">essayez à nouveau</a>.</p>
            <?php break; ?>
            
            <?php case 'reset': ?>
            <form method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
                
                <div class="form-group">
                    <label for="password">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <small class="text-muted">8 caractères minimum, avec majuscule, minuscule, chiffre et caractère spécial</small>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Réinitialiser le mot de passe</button>
            </form>
            <?php break; ?>
            
            <?php case 'complete': ?>
            <p>Vous pouvez maintenant vous <a href="login.php">connecter</a> avec votre nouveau mot de passe.</p>
            <?php break; ?>
            
        <?php endswitch; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>