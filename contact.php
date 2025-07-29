<?php
// contact.php

require_once 'includes/header.php';
// Simple contact form handler and display

// Initialize variables
$name = $email = $message = "";
$success = $error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic validation
    $name = htmlspecialchars(trim($_POST["name"] ?? ""));
    $email = htmlspecialchars(trim($_POST["email"] ?? ""));
    $message = htmlspecialchars(trim($_POST["message"] ?? ""));

    if ($name && $email && $message && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Here you could send an email or save to a database
        $success = "Merci pour votre message, $name !";
        $name = $email = $message = "";
    } else {
        $error = "Veuillez remplir tous les champs correctement.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contactez-moi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2em; }
        form { max-width: 400px; margin: auto; }
        label { display: block; margin-top: 1em; }
        input, textarea { width: 100%; padding: 0.5em; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Contactez-moi</h1>
    <?php if ($success): ?>
        <p class="success"><?= $success ?></p>
    <?php elseif ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <label for="name">Nom :</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>

        <label for="email">Email :</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

        <label for="message">Message :</label>
        <textarea id="message" name="message" rows="5" required><?= htmlspecialchars($message) ?></textarea>

        <button type="submit">Envoyer</button>
    </form>
</body>
</html>
<?php require_once 'includes/footer.php'; ?>