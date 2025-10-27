<?php
// Inclut la configuration globale du projet
// `config.php` doit créer la connexion PDO ($pdo), démarrer la session
// et exposer des helpers comme isLoggedIn() et getCurrentUser().
require_once 'config.php';

// Si l'utilisateur est déjà connecté, on le redirige vers l'accueil.
// isLoggedIn() est une fonction utilitaire définie dans config.php
if (isLoggedIn()) {
    header('Location: index.php'); // envoie un header HTTP de redirection
    exit(); // arrête l'exécution du script après la redirection
}

// Variable pour stocker un message d'erreur à afficher au besoin
$error = '';

// Traitement du formulaire envoyé en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* CSRF protection for login
     * Raison : même le formulaire de connexion peut être ciblé par des requêtes
     * forgées. On vérifie ici la présence et la validité du token avant tout traitement.
     */
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "Requête invalide (jeton CSRF manquant ou invalide).";
    } else {
        // Récupération et nettoyage basique des entrées
        // trim() enlève les espaces en début/fin du login
        $login = trim($_POST['login'] ?? '');
        // mot de passe en clair reçu depuis le formulaire (ne pas stocker ainsi)
        $password = $_POST['password'] ?? '';

        // Validation minimale côté serveur
        if (empty($login) || empty($password)) {
            // empty() couvre '', null, '0' etc. Ici on exige les deux champs non vides
            $error = "Veuillez remplir tous les champs.";
        } else {
            try {
                // Prépare une requête SQL sécurisée (requête préparée)
                // Sélectionne l'utilisateur par login (nom d'utilisateur)
                $stmt = $pdo->prepare("SELECT id, login, password FROM utilisateurs WHERE login = ?");
                // Exécute la requête avec le login en paramètre (protection contre l'injection SQL)
                $stmt->execute([$login]);
                // Récupère la ligne (ARRAY associatif grâce au fetch mode défini dans config)
                $user = $stmt->fetch();

                // Vérifie que l'utilisateur existe et que le mot de passe haché en DB
                // correspond au mot de passe fourni (password_verify utilise les hashs créés par password_hash)
                if ($user && password_verify($password, $user['password'])) {
                    // Connexion réussie : on stocke l'id et le login dans la session
                    // $_SESSION est un tableau superglobal géré par PHP pour la session utilisateur
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_login'] = $user['login'];

                    // Optionnel : fermer explicitement la connexion PDO si vous voulez
                    // libérer la ressource immédiatement (PHP la fermera automatiquement à la fin du script)
                    // $pdo = null; // décommentez si nécessaire

                    // Redirection vers la page d'accueil après connexion
                    header('Location: index.php');
                    exit();
                } else {
                    // Erreur générique pour éviter de révéler si le login ou le mot de passe est incorrect
                    $error = "Nom d'utilisateur ou mot de passe incorrect.";
                }
            } catch (PDOException $e) {
                // En développement on peut afficher le message d'erreur
                // En production, préférez logger l'erreur et afficher un message générique
                $error = "Erreur de connexion : " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Livre d'Or</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1> Connexion</h1>
            <p>Connectez-vous à votre compte</p>
        </header>

        <nav class="nav">
            <ul>
                <li><a href="index.php"> Accueil</a></li>
                <li><a href="livre-or.php"> Livre d'Or</a></li>
                <li><a href="connexion.php"> Connexion</a></li>
                <li><a href="inscription.php"> Inscription</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="form-container">
                <h2>Se connecter</h2>
                
                <?php if ($error): ?>
                    <div class="message error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <!-- Champ caché CSRF (getCsrfInput) : protège la soumission du formulaire -->
                    <?= getCsrfInput() ?>
                    <div class="form-group">
                        <label for="login">Nom d'utilisateur :</label>
                        <input type="text" id="login" name="login" required 
                               value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
                               placeholder="Votre nom d'utilisateur">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe :</label>
                        <input type="password" id="password" name="password" required
                               placeholder="Votre mot de passe">
                    </div>
                    
                    <div class="form-group btn-center">
                        <button type="submit" class="btn">Se connecter</button>
                    </div>
                </form>
                
                <div style="text-align: center; margin-top: 20px;">
                    <p>Pas encore de compte ? <a href="inscription.php" style="color: #764ba2; text-decoration: none; font-weight: 500;">S'inscrire gratuitement</a></p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>