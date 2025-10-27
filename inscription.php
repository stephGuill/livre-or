<?php
// Inclusion du fichier de configuration global
// - Initialise la connexion PDO dans $pdo
// - Démarre la session (session_start())
// - Fournit des helpers comme isLoggedIn() et getCurrentUser()
require_once 'config.php';

// Variables pour stocker les messages qui seront affichés à l'utilisateur
$error = '';
$success = '';

// Traitement du formulaire : on vérifie si la requête est de type POST
// $_POST est un tableau superglobal qui contient les valeurs envoyées par le formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* CSRF check for inscription
     * - Le champ `csrf_token` doit être présent et valide.
     * - En cas d'échec, on bloque l'inscription pour éviter les requêtes forgées.
     */
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "Requête invalide (jeton CSRF manquant ou invalide).";
    } else {
        // Récupération des champs envoyés par le formulaire
        // trim() enlève les espaces début/fin du login
        $login = trim($_POST['login'] ?? '');
        // mot de passe en clair entré par l'utilisateur (ne pas stocker tel quel)
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // --- Validation côté serveur ---
        // Vérifie que tous les champs sont fournis
        if (empty($login) || empty($password) || empty($confirm_password)) {
            // empty() vérifie '', 0, null, false ; utile pour valider la présence d'une valeur
            $error = "Tous les champs sont obligatoires.";
        } elseif ($password !== $confirm_password) {
            // Vérifie que le mot de passe et sa confirmation sont identiques
            $error = "Les mots de passe ne correspondent pas.";
        } elseif (strlen($password) < 6) {
            // Vérifie une longueur minimale (note : pour UTF-8 multioctets, mb_strlen() pourrait être préférable)
            $error = "Le mot de passe doit contenir au moins 6 caractères.";
        } else {
            // Si les validations passent, on tente d'insérer l'utilisateur en base
            try {
                // Requête préparée : sélectionne l'utilisateur par login pour vérifier l'unicité
                // Les requêtes préparées (prepare/execute) protègent contre l'injection SQL
                $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = ?");
                $stmt->execute([$login]);
                
                // rowCount() retourne le nombre de lignes retournées (pour SELECT, comportement dépend du driver)
                // Ici on vérifie s'il existe déjà un utilisateur avec le même login
                if ($stmt->rowCount() > 0) {
                    $error = "Ce nom d'utilisateur est déjà pris.";
                } else {
                    // Hash sécurisé du mot de passe : password_hash utilise bcrypt/argon2 selon la configuration
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    // Insertion de l'utilisateur dans la table 'utilisateurs' (login, password)
                    $stmt = $pdo->prepare("INSERT INTO utilisateurs (login, password) VALUES (?, ?)");
                    $stmt->execute([$login, $hashedPassword]);
                    
                    // Message de succès affiché à l'utilisateur
                    $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";

                    // Redirection après 2 secondes vers la page de connexion (refresh côté client)
                    // Note : on peut aussi utiliser header('Location: connexion.php') et exit() pour une redirection immédiate
                    header("Refresh: 2; url=connexion.php");
                }
            } catch (PDOException $e) {
                // En dev on peut afficher le message d'erreur ; en production, logger et afficher un message générique
                $error = "Erreur lors de l'inscription : " . $e->getMessage();
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
    <title>Inscription - Livre d'Or</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1> Inscription</h1>
            <p>Créez votre compte pour rejoindre notre communauté</p>
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
                <h2>Créer un compte</h2>
                
                <?php if ($error): ?>
                    <div class="message error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="message success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <!-- Protection CSRF : champ caché auto-généré -->
                    <?= getCsrfInput() ?>
                    <div class="form-group">
                        <label for="login">Nom d'utilisateur :</label>
                        <input type="text" id="login" name="login" required 
                               value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
                               placeholder="Choisissez un nom d'utilisateur unique">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe :</label>
                        <input type="password" id="password" name="password" required
                               placeholder="Minimum 6 caractères">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe :</label>
                        <input type="password" id="confirm_password" name="confirm_password" required
                               placeholder="Confirmez votre mot de passe">
                    </div>
                    
                    <div class="form-group btn-center">
                        <button type="submit" class="btn">S'inscrire</button>
                    </div>
                </form>
                
                <div style="text-align: center; margin-top: 20px;">
                    <p>Déjà membre ? <a href="connexion.php" style="color: #764ba2; text-decoration: none; font-weight: 500;">Se connecter</a></p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>