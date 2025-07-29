<?php
// includes/validation.php

/**
 * Validate if a string is not empty.
 *
 * @param string $value
 * @return bool
 */
function isNotEmpty($value) {
    return isset($value) && trim($value) !== '';
}
function validate_username($username) {
    if (empty($username) || strlen($username) < 3 || strlen($username) > 30) {
        throw new Exception("Le nom d'utilisateur doit contenir entre 3 et 30 caractères");
    }
    if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) {
        throw new Exception("Le nom d'utilisateur ne doit contenir que des lettres, chiffres, tirets ou underscores");
    }
    return true;
}
function validate_email($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Adresse email invalide");
    }
    return true;
}
/**
 * Validate if an email address is valid.
 *
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate if a string is a valid phone number (simple version).
 *
 * @param string $phone
 * @return bool
 */
function isValidPhone($phone) {
    return preg_match('/^\+?[0-9\s\-]{7,20}$/', $phone);
}
?>