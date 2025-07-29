<?php
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.use_strict_mode', '1');
ini_set('expose_php', 'Off');
require_once 'config.php';
session_start();
require_once 'config.php';
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
    <?php if(isset($theme_css)): ?>
        <link rel="stylesheet" href="assets/css/themes/<?= $theme_css ?>">
    <?php endif; ?>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <a href="index.php" class="logo">CV<span>Creator</span></a>
            <nav class="main-nav">
                <ul>
                    <li><a href="create_cv.php">Créer un CV</a></li>
                    <li><a href="create_invitation.php">Créer une invitation</a></li>
                    <li><a href="my_works.php">Mes réalisations</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="logout.php">Déconnexion</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Connexion</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">