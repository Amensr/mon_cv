<?php
require_once 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

$auth = new Auth($conn);
$success = false;
$error = '';

if (isset($_GET['token'])) {
    try {
        $token = $_GET['token'];
        $success = $auth->verify_email($token);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
} else {
    $error = "Token de vérification manquant";
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Vérification d'email</h1>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <p>Veuillez vérifier le lien reçu par email ou <a href="resend-verification.php">demander un nouveau lien</a>.</p>
        <?php elseif ($success): ?>
        <div class="alert alert-success">
            Votre email a été vérifié avec succès ! Vous pouvez maintenant <a href="login.php">vous connecter</a>.
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>