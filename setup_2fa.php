<?php
require_once 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

check_auth();

$twoFA = new TwoFactorAuth($conn);
$error = '';
$success = '';
$qr_code_url = '';
$secret = '';

// Vérifier si 2FA est déjà configuré
if ($twoFA->is2FAEnabled($_SESSION['user_id'])) {
    header('Location: /account.php');
    exit();
}

// Démarrer la configuration
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $setup_data = $twoFA->setup2FA($_SESSION['user_id']);
        $qr_code_url = $setup_data['qr_code_url'];
        $secret = $setup_data['secret'];
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Valider la configuration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $code = $_POST['code'] ?? '';
        
        if (empty($code)) {
            throw new Exception("Veuillez entrer le code de vérification");
        }
        
        if ($twoFA->verify2FASetup($_SESSION['user_id'], $code)) {
            $backup_codes = $twoFA->generateBackupCodes($_SESSION['user_id']);
            $success = "Authentification à deux facteurs activée avec succès !";
        } else {
            throw new Exception("Code de vérification invalide");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        
        // Re-générer les données 2FA en cas d'erreur
        $setup_data = $twoFA->setup2FA($_SESSION['user_id']);
        $qr_code_url = $setup_data['qr_code_url'];
        $secret = $setup_data['secret'];
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Configurer l'authentification à deux facteurs</h1>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
            
            <h3 class="mt-4">Codes de secours</h3>
            <p>Conservez ces codes en lieu sûr. Chaque code ne peut être utilisé qu'une fois :</p>
            
            <div class="backup-codes">
                <?php foreach ($backup_codes as $code): ?>
                <div class="backup-code"><?= htmlspecialchars($code) ?></div>
                <?php endforeach; ?>
            </div>
            
            <p class="text-muted">Vous pouvez régénérer ces codes dans les paramètres de votre compte.</p>
            
            <a href="account.php" class="btn btn-primary">Retour au compte</a>
        </div>
        <?php else: ?>
        
        <div class="setup-instructions">
            <ol>
                <li>Téléchargez une application d'authentification comme Google Authenticator, Authy ou Microsoft Authenticator</li>
                <li>Scannez le QR code ci-dessous ou entrez manuellement la clé secrète</li>
                <li>Entrez le code à 6 chiffres généré par l'application pour vérifier la configuration</li>
            </ol>
        </div>
        
        <div class="text-center my-4">
            <?php if ($qr_code_url): ?>
            <img src="<?= htmlspecialchars($qr_code_url) ?>" alt="QR Code 2FA" class="qr-code">
            <?php endif; ?>
            
            <div class="manual-secret">
                <p>Clé secrète : <code><?= htmlspecialchars($secret) ?></code></p>
            </div>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="code">Code de vérification</label>
                <input type="text" id="code" name="code" class="form-control" 
                       placeholder="123456" pattern="\d{6}" required autofocus>
            </div>
            
            <button type="submit" class="btn btn-primary">Activer 2FA</button>
            <a href="account.php" class="btn btn-secondary">Annuler</a>
        </form>
        
        <?php endif; ?>
    </div>
</div>

<style>
.qr-code {
    width: 200px;
    height: 200px;
    margin: 0 auto;
    display: block;
}

.manual-secret {
    margin: 1rem 0;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 4px;
}

.manual-secret code {
    font-size: 1.1rem;
    word-break: break-all;
}

.setup-instructions {
    margin-bottom: 2rem;
}

.setup-instructions ol {
    padding-left: 1.5rem;
}

.setup-instructions li {
    margin-bottom: 0.5rem;
}

.backup-codes {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
    margin: 1rem 0;
}

.backup-code {
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 4px;
    text-align: center;
    font-family: monospace;
}

.mt-4 {
    margin-top: 1.5rem;
}

.text-muted {
    color: #6c757d;
}
</style>

<?php require_once 'includes/footer.php'; ?>