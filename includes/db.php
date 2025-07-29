<?php
require_once 'config.php';

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'cv_creator';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fonction pour sécuriser les entrées
function sanitize_input($value, $conn) {
    return mysqli_real_escape_string($conn, trim($value ?? ''));
}
?>