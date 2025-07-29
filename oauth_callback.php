<?php
//echo $auth_url; exit;
require_once 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/oauth.php';


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$provider = $_GET['provider'] ?? '';
$code = $_GET['code'] ?? '';
$error = $_GET['error'] ?? '';

try {
    if ($error) {
        throw new Exception("Erreur d'authentification: ".htmlspecialchars($error));
    }

    if (!in_array($provider, ['google', 'facebook'])) {
        throw new Exception("Provider non supporté");
    }

    if (empty($code)) {
        throw new Exception("Code d'autorisation manquant");
    }

    $oauth = new OAuthHandler($conn);
    $user = $oauth->handleCallback($provider, $code);

    // Créer la session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['is_premium'] = $user['is_premium'];
    $_SESSION['is_admin'] = $user['is_admin'];
    
    // Vérifier si 2FA est requis
    $twoFA = new TwoFactorAuth($conn);
    if ($twoFA->is2FAEnabled($user['id'])) {
        $_SESSION['requires_2fa'] = true;
        $_SESSION['temp_user'] = $user;
        header('Location: /verify_2fa.php');
        exit();
    }

    // Rediriger vers la page d'accueil
    header('Location: /my_works.php');
    exit();

} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Erreur de connexion</h1>
        
        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        
        <p>Veuillez <a href="login.php">essayer de nouveau</a> ou nous <a href="contact.php">contacter</a> si le problème persiste.</p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>