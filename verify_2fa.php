<?php
require_once 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Vérifier si l'utilisateur est en cours d'authentification
if (!isset($_SESSION['requires_2fa']) || !isset($_SESSION['temp_user'])) {
    header('Location: /login.php');
    exit();
}

$twoFA = new TwoFactorAuth($conn);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $code = $_POST['code'] ?? '';
        
        if (empty($code)) {
            throw new Exception("Veuillez entrer le code de vérification");
        }
        
        // Vérifier si c'est un code de secours
        if ($twoFA->verifyBackupCode($_SESSION['temp_user']['id'], $code)) {
            $valid = true;
        } else {
            // Vérifier le code normal
            $valid = $twoFA->verifyLoginCode($_SESSION['temp_user']['id'], $code);
        }
        
        if (!$valid) {
            throw new Exception("Code de vérification invalide");
        }
        
        // Authentification réussie
        $_SESSION['user_id'] = $_SESSION['temp_user']['id'];
        $_SESSION['username'] = $_SESSION['temp_user']['username'];
        $_SESSION['email'] = $_SESSION['temp_user']['email'];
        $_SESSION['is_premium'] = $_SESSION['temp_user']['is_premium'];
        $_SESSION['is_admin'] = $_SESSION['temp_user']['is_admin'];
        
        unset($_SESSION['requires_2fa']);
        unset($_SESSION['temp_user']);
        
        header('Location: /my_works.php');
        exit();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Vérification en deux étapes</h1>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <p>Entrez le code à 6 chiffres depuis votre application d'authentification :</p>
        
        <form method="POST">
            <div class="form-group">
                <label for="code">Code de vérification</label>
                <input type="text" id="code" name="code" class="form-control" 
                       placeholder="123456" pattern="\d{6}" required autofocus>
            </div>
            
            <button type="submit" class="btn btn-primary">Vérifier</button>
        </form>
        
        <div class="auth-links">
            <p>Vous n'avez pas accès à votre application ? <a href="#" id="use-backup-code">Utiliser un code de secours</a></p>
        </div>
        
        <div id="backup-code-section" style="display: none;">
            <p>Entrez l'un de vos codes de secours :</p>
            <input type="text" id="backup-code" name="backup_code" class="form-control" 
                   placeholder="Code de secours">
        </div>
    </div>
</div>

<script>
document.getElementById('use-backup-code').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('backup-code-section').style.display = 'block';
    document.getElementById('code').disabled = true;
    document.getElementById('backup-code').focus();
});
</script>

<style>
#code {
    letter-spacing: 0.5rem;
    font-size: 1.5rem;
    text-align: center;
    padding: 0.5rem 1rem;
}

#backup-code {
    margin-top: 1rem;
}
</style>

<?php require_once 'includes/footer.php'; ?>