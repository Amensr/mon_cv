<?php
require_once 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/oauth.php';
require_once 'oauth_callback.php';

$provider = $_GET['provider'] ?? '';

try {
    if (!in_array($provider, ['google', 'facebook'])) {
        throw new Exception("Provider non supporté");
    }

    $oauth = new OAuthHandler($conn);
    $auth_url = $oauth->getAuthUrl($provider);
    
    header("Location: $auth_url");
    exit();

} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <title>Connexion OAuth</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/main.css">
    <?php if(isset($theme_css)): ?>
        <link rel="stylesheet" href="assets/css/themes/<?= $theme_css ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/css/line-awesome.min.css">  
</head>
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