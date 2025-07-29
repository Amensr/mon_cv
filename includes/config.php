<?php
// Configuration de base
define('SITE_NAME', 'CV Creator Pro');
define('SITE_URL', 'http://localhost/cv.fe');
define('DEFAULT_THEME', 'cybersecurity');

// Activation du reporting d'erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Paramètres de sécurité
define('CSRF_TOKEN_NAME', 'csrf_token');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 300); // 5 minutes en secondes

// Configuration JWT
define('JWT_SECRET', 'votre_secret_jwt_super_secure_!2023');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRATION', 60 * 60 * 24 * 7); // 1 semaine en secondes

// Activation du reporting d'erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration des emails
define('EMAIL_FROM', 'alidjogounamen@gmamil.com');
define('EMAIL_FROM_NAME', SITE_NAME);

// Chemins des dossiers
define('TEMPLATES_DIR', __DIR__ . '/../templates');
define('UPLOADS_DIR', __DIR__ . '/../uploads');
define('ASSETS_DIR', __DIR__ . '/../assets');

// Autoloader pour les classes
spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/../classes/' . str_replace('\\', '/', $class_name) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Configuration des webhooks
define('WEBHOOK_SECRET', 'votre_secret_webhook_super_secure');
define('STRIPE_WEBHOOK_SECRET', 'whsec_votre_secret_stripe');


// ...autres définitions...


// ...autres définitions...

// Configuration OAuth Google
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', '554525097091-r369c5v50ccv5k09gb3cnubap2igu408.apps.googleusercontent.com');
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', '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');
}
if (!defined('GOOGLE_REDIRECT_URI')) {
    define('GOOGLE_REDIRECT_URI', 'http://localhost/cv.fe/oauth_callback.php');
}

// Configuration OAuth Facebook
if (!defined('FACEBOOK_CLIENT_ID')) {
    define('FACEBOOK_CLIENT_ID', 'votre-facebook-app-id');
}
if (!defined('FACEBOOK_CLIENT_SECRET')) {
    define('FACEBOOK_CLIENT_SECRET', 'votre-facebook-app-secret');
}
if (!defined('FACEBOOK_REDIRECT_URI')) {
    define('FACEBOOK_REDIRECT_URI', 'http://localhost/cv.fe/oauth_callback.php');
}

// Configuration Stripe
if (!defined('STRIPE_API_KEY')) {
    define('STRIPE_API_KEY', 'sk_live_votre_cle_stripe');
}
if (!defined('STRIPE_PUBLIC_KEY')) {
    define('STRIPE_PUBLIC_KEY', 'pk_live_votre_cle_publique_stripe');
}

