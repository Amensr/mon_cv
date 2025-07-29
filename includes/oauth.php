<?php
require_once 'config.php';
require_once 'db.php';

// Classe pour gérer l'authentification OAuth
// Cette classe gère l'authentification OAuth pour Google et Facebook
// Elle permet de générer les URLs d'authentification, de gérer les callbacks et de récupérer les informations utilisateur.
// Pour Google
return 'https://accounts.google.com/o/oauth2/auth?...&redirect_uri=http://localhost/oauth_callback.php';

// Pour Facebook
return 'https://www.facebook.com/v10.0/dialog/oauth?...&redirect_uri=http://localhost/oauth_callback.php';
class OAuthHandler {
    private $conn;
    private $providers = [
        'google' => [
            'client_id' => '554525097091-r369c5v50ccv5k09gb3cnubap2igu408.apps.googleusercontent.com',
            'client_secret' => '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',
            'redirect_uri' => SITE_URL.'/oauth-callback.php?provider=google',
            'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'user_info_url' => 'https://www.googleapis.com/oauth2/v3/userinfo',
            'scopes' => 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email'
        ],
        'facebook' => [
            'client_id' => '123456789012345',
            'client_secret' => 'VOTRE_SECRET_FACEBOOK',
            'redirect_uri' => SITE_URL.'/oauth-callback.php?provider=facebook',
            'auth_url' => 'https://www.facebook.com/v12.0/dialog/oauth',
            'token_url' => 'https://graph.facebook.com/v12.0/oauth/access_token',
            'user_info_url' => 'https://graph.facebook.com/v12.0/me?fields=id,name,email,picture',
            'scopes' => 'email,public_profile'
        ]
    ];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAuthUrl($provider) {
        if (!isset($this->providers[$provider])) {
            throw new Exception("Provider non supporté");
        }

        $params = [
            'client_id' => $this->providers[$provider]['client_id'],
            'redirect_uri' => $this->providers[$provider]['redirect_uri'],
            'response_type' => 'code',
            'scope' => $this->providers[$provider]['scopes'],
            'state' => bin2hex(random_bytes(16))
        ];

        return $this->providers[$provider]['auth_url'].'?'.http_build_query($params);
    }

    public function handleCallback($provider, $code) {
        if (!isset($this->providers[$provider])) {
            throw new Exception("Provider non supporté");
        }

        // Échanger le code contre un token
        $token = $this->getAccessToken($provider, $code);
        
        // Récupérer les infos utilisateur
        $user_info = $this->getUserInfo($provider, $token);

        // Trouver ou créer l'utilisateur
        return $this->findOrCreateUser($provider, $user_info);
    }

    private function getAccessToken($provider, $code) {
        $params = [
            'client_id' => $this->providers[$provider]['client_id'],
            'client_secret' => $this->providers[$provider]['client_secret'],
            'redirect_uri' => $this->providers[$provider]['redirect_uri'],
            'code' => $code,
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init($this->providers[$provider]['token_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['error']) || !isset($data['access_token'])) {
            throw new Exception($data['error_description'] ?? "Erreur lors de l'obtention du token");
        }

        return $data['access_token'];
    }

    private function getUserInfo($provider, $token) {
        $url = $this->providers[$provider]['user_info_url'];
        
        if ($provider === 'facebook') {
            $url .= '&access_token='.$token;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($provider === 'google') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer '.$token
            ]);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['error'])) {
            throw new Exception($data['error']['message'] ?? "Erreur lors de la récupération des infos utilisateur");
        }

        return $this->normalizeUserInfo($provider, $data);
    }

    private function normalizeUserInfo($provider, $data) {
        $user_info = [
            'provider' => $provider,
            'provider_id' => $data['id']
        ];

        if ($provider === 'google') {
            $user_info['email'] = $data['email'];
            $user_info['name'] = $data['name'];
            $user_info['avatar'] = $data['picture'];
        } elseif ($provider === 'facebook') {
            $user_info['email'] = $data['email'] ?? null;
            $user_info['name'] = $data['name'];
            $user_info['avatar'] = $data['picture']['data']['url'] ?? null;
        }

        return $user_info;
    }

    private function findOrCreateUser($provider, $user_info) {
        // Vérifier si l'utilisateur existe déjà
        $stmt = $this->conn->prepare("
            SELECT u.* FROM users u
            JOIN oauth_users ou ON u.id = ou.user_id
            WHERE ou.provider = ? AND ou.provider_id = ?
        ");
        $stmt->bind_param("ss", $provider, $user_info['provider_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        // Si l'utilisateur n'existe pas, le créer
        $this->conn->begin_transaction();

        try {
            // Créer l'utilisateur
            $username = $this->generateUsername($user_info['name']);
            $email = $user_info['email'] ?? $username.'@'.$provider.'.oauth';
            
            $stmt = $this->conn->prepare("
                INSERT INTO users (username, email, is_verified, is_active)
                VALUES (?, ?, TRUE, TRUE)
            ");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $user_id = $this->conn->insert_id;

            // Lier le compte OAuth
            $stmt = $this->conn->prepare("
                INSERT INTO oauth_users (user_id, provider, provider_id, email, name, avatar)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "isssss", 
                $user_id, 
                $provider, 
                $user_info['provider_id'],
                $user_info['email'] ?? null,
                $user_info['name'],
                $user_info['avatar'] ?? null
            );
            $stmt->execute();

            $this->conn->commit();

            // Récupérer l'utilisateur complet
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    private function generateUsername($name) {
        $base = preg_replace('/[^a-z0-9]/i', '', strtolower($name));
        $username = $base;
        $counter = 1;

        while (true) {
            $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows === 0) {
                return $username;
            }

            $username = $base.$counter;
            $counter++;
        }
    }
}