<?php
// Inclusion du fichier de configuration global
// `config.php` démarre la session (session_start()) et initialise la connexion PDO ($pdo)
require_once 'config.php';

// --- Nettoyage de la session ---
// session_unset() : libère toutes les variables de session actuellement enregistrées
// Cela vide le tableau $_SESSION mais ne détruit pas la session elle-même (les cookies de session restent)
session_unset();

// session_destroy() : détruit les données de session côté serveur
// Après appel, la session n'est plus disponible côté serveur. Note : le cookie de session peut rester côté client
session_destroy();

// Optionnel : supprimer le cookie de session côté client pour une déconnexion complète
// if (ini_get("session.use_cookies")) {
//     $params = session_get_cookie_params();
//     setcookie(session_name(), '', time() - 42000,
//         $params['path'], $params['domain'], $params['secure'], $params['httponly']
//     );
// }

// Optionnel : fermer explicitement la connexion PDO si elle existe globalement
// global $pdo; if (isset($pdo)) { $pdo = null; }

// Redirection vers la page d'accueil
// header() envoie un en-tête HTTP ; l'appel suivant exit() arrête l'exécution du script
header('Location: index.php');
exit();

?>


