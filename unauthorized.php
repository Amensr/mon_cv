<?php
// unauthorized.php

http_response_code(401);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accès non autorisé</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: #f8f9fa;
            color: #333;
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: #fff;
            padding: 2rem 3rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            text-align: center;
        }
        h1 {
            color: #c0392b;
            margin-bottom: 1rem;
        }
        a {
            color: #2980b9;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>401 - Accès non autorisé</h1>
    <p>Vous n'avez pas la permission d'accéder à cette page.</p>
    <p><a href="/">Retour à l'accueil</a></p>
</div>
</body>
</html>