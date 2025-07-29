<?php
// forgot_password.php

session_start();

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if ($email) {
        // Simulate sending a reset link (replace with real logic)
        // Example: save token to DB, send email, etc.
        $message = "Si l'adresse e-mail existe, un lien de réinitialisation a été envoyé.";
    } else {
        $message = "Veuillez entrer une adresse e-mail valide.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; }
        .container { max-width: 400px; margin: 60px auto; background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 8px #ccc; }
        h2 { text-align: center; color: #333; }
        label { display: block; margin-bottom: 8px; }
        input[type="email"] { width: 100%; padding: 10px; margin-bottom: 16px; border: 1px solid #bbb; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #007bff; color: #fff; border: none; border-radius: 4px; font-size: 16px; }
        .message { margin-bottom: 16px; color: #007bff; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Mot de passe oublié ?</h2>
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <label for="email">Adresse e-mail</label>
            <input type="email" id="email" name="email" required placeholder="Entrez votre e-mail">
            <button type="submit">Envoyer le lien de réinitialisation</button>
        </form>
        <p style="text-align:center; margin-top:16px;">
            <a href="login.php">Retour à la connexion</a>
        </p>
    </div>
</body>
</html>