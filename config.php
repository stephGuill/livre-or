<?php
//  configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'livreor');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// démarage de la session
session_start();

// --------------------
// Protection CSRF - utilitaires
// - generateCsrfToken() : crée un token aléatoire stocké en session
// - getCsrfInput() : renvoie le HTML d'un input caché contenant le token
// - validateCsrfToken($token) : vérifie la validité du token soumis
// Utiliser un token par session est un bon compromis simplicité/sécurité.
function generateCsrfToken() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        // random_bytes pour cryptographie forte, bin2hex pour stockage en ASCII
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function getCsrfInput() {
    $token = generateCsrfToken();
    // htmlspecialchars au cas où on affiche dans un attribut
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function validateCsrfToken($tokenFromRequest) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($tokenFromRequest) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    // Utiliser hash_equals pour prévenir les attaques timing
    return hash_equals($_SESSION['csrf_token'], $tokenFromRequest);
}

//  fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

//  fonction pour obtenir l'utilisateur connecté
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'login' => $_SESSION['user_login']
        ];
    }
    return null;
}

// fonction pour déconnecter l'utilisateur
function logout() {
    // Vide les variables de session
    session_unset();
    // Détruit la session elle-même
    session_destroy();

    // Ferme la connexion à la base si elle existe globalement
    global $pdo;
    if (isset($pdo)) {
        $pdo = null;
    }

    // Redirection propre vers la page d'accueil
    header('Location: index.php');
    exit();
}

// Fonction utilitaire pour fermer explicitement la connexion PDO si souhaité
function closeDb() {
    global $pdo;
    if (isset($pdo)) {
        $pdo = null;
    }

    // optionnel : fermer explicitement la connexion si définie globalement
    global $pdo;
    if (isset($pdo)) {
        $pdo = null;
    }
    header('Location: index.php');
    exit();
}
?>