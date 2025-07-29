<?php
// autoload.php
// Autoloader simple pour charger automatiquement les classes PHP

require_once 'includes/config.php';
require_once 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// À placer tout en haut, avant session_start()

$auth = new Auth($conn);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        
        $user = $auth->login($email, $password);
        
        // Définir la session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['is_premium'] = $user['is_premium'];
        $_SESSION['is_admin'] = $user['is_admin'];
        $_SESSION['token'] = $user['token'];
        
        // Redirection
        $redirect = $_GET['redirect'] ?? 'my_works.php';
        header("Location: $redirect");
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<div class="auth-container">
    <div class="auth-card">
        <h1>Connexion</h1>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-control" required>
                <small><a href="forgot_password.php">Mot de passe oublié ?</a></small>
            </div>
            
            <button type="submit" class="btn btn-primary">Se connecter</button>
        </form>
        
        <div class="oauth-providers">
            <p class="divider">Ou connectez-vous avec</p>
            <div class="provider-buttons">
                <a href="oauth_login.php?provider=google" class="btn btn-google">
                    <i class="fab fa-google"></i> Google
                </a>
                <a href="/oauth_login.php?provider=facebook" class="btn btn-facebook">
                    <i class="fab fa-facebook-f"></i> Facebook
                </a>
            </div>
        </div>
        
        <div class="auth-links">
            <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
        </div>
    </div>
</div>

<style>
.auth-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 80vh;
    padding: 2rem;
}

.auth-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    padding: 2.5rem;
    width: 100%;
    max-width: 450px;
}

.auth-card h1 {
    text-align: center;
    margin-bottom: 1.5rem;
    color: var(--primary-color);
}

.alert {
    padding: 0.8rem;
    margin-bottom: 1.5rem;
    border-radius: 4px;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.auth-links {
    margin-top: 1.5rem;
    text-align: center;
    font-size: 0.9rem;
}

.auth-links a {
    color: var(--primary-color);
    text-decoration: none;
}

.auth-links a:hover {
    text-decoration: underline;
}
.oauth-providers {
    margin-top: 1.5rem;
    text-align: center;
}
.oauth-providers {
    margin: 1.5rem 0;
}

.divider {
    display: flex;
    align-items: center;
    text-align: center;
    color: #666;
    margin: 1rem 0;
}

.divider::before, .divider::after {
    content: "";
    flex: 1;
    border-bottom: 1px solid #ddd;
}

.divider::before {
    margin-right: 1rem;
}

.divider::after {
    margin-left: 1rem;
}

.provider-buttons {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.btn-google {
    background: #db4437;
    color: white;
}

.btn-facebook {
    background: #3b5998;
    color: white;
}

.icon-google, .icon-facebook {
    margin-right: 0.5rem;
}
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.8rem 1.5rem;
    border-radius: 4px;
    color: white;
    text-decoration: none;
}
</style>

<?php require_once 'includes/footer.php'; ?>