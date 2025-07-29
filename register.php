<?php
require_once 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/security.php';
require_once 'includes/validation.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$auth = new Auth($conn);
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Vérifier si l'utilisateur est déjà connecté
        if ($auth->isLoggedIn()) {
            header("Location: my_works.php");
            exit();
        }

        // Vérifier les champs requis
        if (
            empty($_POST['username']) ||
            empty($_POST['email']) ||
            empty($_POST['password']) ||
            empty($_POST['password_confirm'])
        ) {
            throw new Exception("Tous les champs sont requis");
        }

        // Nettoyer et valider les entrées
        $username = htmlspecialchars(trim($_POST['username']));
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];

        // Validation username
        if (strlen($username) < 3 || strlen($username) > 30 || !preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) {
            throw new Exception("Le nom d'utilisateur doit contenir entre 3 et 30 caractères et uniquement lettres, chiffres, tirets ou underscores");
        }

        // Validation email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Adresse email invalide");
        }

        // Validation mot de passe
        if (
            strlen($password) < 8 ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/\d/', $password) ||
            !preg_match('/[\W_]/', $password)
        ) {
            throw new Exception("Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial");
        }

        if ($password !== $password_confirm) {
            throw new Exception("Les mots de passe ne correspondent pas");
        }

        // Vérifier le token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Token CSRF invalide");
        }

        // Enregistrer l'utilisateur
        $user_id = $auth->register($username, $email, $password);
        $success = "Inscription réussie ! Un email de vérification a été envoyé à $email";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Inscription</h1>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php else: ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-control" required>
                <small class="text-muted">8 caractères minimum, avec majuscule, minuscule, chiffre et caractère spécial</small>
            </div>
            
            <div class="form-group">
                <label for="password_confirm">Confirmer le mot de passe</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control" required>
            </div>
            <div class="form-group">        
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            </div>   
            
            <button type="submit" class="btn btn-primary">S'inscrire</button>
        </form>
        
        <div class="auth-links">
            <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
        </div>
        
        <?php endif; ?>
    </div>
</div>

<style>
.text-muted {
    color: #6c757d;
    font-size: 0.8rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
</style>

<?php require_once 'includes/footer.php'; ?>