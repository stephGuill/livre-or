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
    // libère les variables de session
    session_unset();
    // détruit la session
    session_destroy();

    // optionnel : fermer explicitement la connexion si définie globalement
    global $pdo;
    if (isset($pdo)) {
        $pdo = null;
    }
    header('Location: index.php');
    exit();
}
?>