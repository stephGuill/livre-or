<?php
// Inclusion du fichier de configuration :
// - Initialise la connexion PDO dans la variable $pdo
// - Démarre la session (si pas déjà démarrée)
// - Définit des fonctions utilitaires comme isLoggedIn() et getCurrentUser()
require_once 'config.php';

// Vérifie si l'utilisateur est connecté. isLoggedIn() retourne true/false.
// Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion.
if (!isLoggedIn()) {
    header('Location: connexion.php'); // envoie un header HTTP Location
    exit(); // stoppe l'exécution pour éviter que le reste de la page soit envoyé
}

// Récupère un tableau associatif contenant les informations de l'utilisateur courant
// (typiquement : ['id' => int, 'login' => string, ...])
$currentUser = getCurrentUser();

// Initialisation des variables de message qui seront affichées dans la vue
$error = '';   // message d'erreur (chaîne vide si pas d'erreur)
$success = ''; // message de succès (corrigé depuis $succes)

// Traitement du formulaire : vérifier si la requête contient des données POST
// On utilise la vérification simple "if ($_POST)" ; on peut aussi faire if ($_SERVER['REQUEST_METHOD'] === 'POST')
if ($_POST) {
    // Vérification CSRF : s'assurer que le formulaire contient un token valide
    // Si le token est absent ou invalide, on refuse la requête et on définit une erreur.
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "Requête invalide (jeton CSRF manquant ou invalide).";
    } else {
        // Récupération et nettoyage des données envoyées par l'utilisateur
        $new_login = trim($_POST['login']); // trim() enlève les espaces en début/fin
        $new_password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validation côté serveur : checks de base avant toute opération DB
        if (empty($new_login)) {
            // Le login est requis
            $error = "Le nom d'utilisateur est obligatoire.";

        } elseif (!empty($new_password) && $new_password !== $confirm_password) {
            // Si un mot de passe est fourni, il doit correspondre au champ de confirmation
            $error = "Les mots de passe ne correspondent pas.";

        } elseif (!empty($new_password) && strlen($new_password) < 6) {
            // Vérifier une longueur minimale (ici 6 caractères)
            $error = "Le mot de passe doit contenir au moins 6 caractères.";

        } else {
            // Si les validations de base passent, on tente les opérations sur la base
            try {
                // Préparer une requête pour vérifier si le login demandé existe déjà
                // ON EXCLUT l'utilisateur courant via "AND id != ?" pour permettre de garder
                // le même login si l'utilisateur n'a rien changé.
                $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = ? AND id != ?");
                $stmt->execute([$new_login, $currentUser['id']]);

                // rowCount() retourne le nombre de lignes affectées / trouvées.
                // Note : certains drivers peuvent ne pas supporter rowCount() de façon fiable sur SELECT ;
                // une alternative est d'utiliser fetch() et tester le résultat.
                if ($stmt->rowCount() > 0) {
                    // Si on trouve une ligne, le login est déjà pris
                    $error = "Ce nom d'utilisateur est déjà pris.";
                } else {
                    // Login disponible : procéder à la mise à jour
                    if (!empty($new_password)) {
                        // Si l'utilisateur veut changer son mot de passe, le hacher avant stockage
                        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT); // fonction PHP sécurisée

                        // Met à jour login et mot de passe (paramètres via tableau pour éviter l'injection)
                        $stmt = $pdo->prepare("UPDATE utilisateurs SET login = ?, password = ? WHERE id = ?");
                        $stmt->execute([$new_login, $hashedPassword, $currentUser['id']]);
                    } else {
                        // Sinon, mettre à jour seulement le login
                        $stmt = $pdo->prepare("UPDATE utilisateurs SET login = ? WHERE id = ?");
                        $stmt->execute([$new_login, $currentUser['id']]);
                    }

                    // Mise à jour des informations en session pour refléter le nouveau login
                    $_SESSION['user_login'] = $new_login;
                    $success = "Profil mis à jour avec succès !"; // message pour l'utilisateur

                    // Mettre à jour la variable locale $currentUser également pour que l'affichage
                    // courant (dans la même requête) reflète immédiatement le changement.
                    $currentUser['login'] = $new_login;
                }

            } catch (PDOException $e) {
                // En environnement de développement on peut afficher le message ;
                // en production, logger l'erreur et afficher un message générique.
                $error = "Erreur lors de la mise à jour : " . $e->getMessage();
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
    <title>Mon Profil - Livre d'Or</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <!-- Titre de la page : emoji inclus pour un affichage convivial -->
            <h1>👤 Mon Profil</h1>
            <p>Gérez vos informations personnelles</p>
        </header>

        <nav class="nav">
            <ul>
                <li><a href="index.php"> Accueil</a></li>
                <li><a href="livre-or.php"> Livre d'Or</a></li>
                <li><a href="profil.php"> Profil</a></li>
                <li><a href="commentaire.php"> Nouveau Commentaire</a></li>
                <li><a href="deconnexion.php"> Déconnexion</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="form-container">
                <div class="user-info">
                    <!-- Affiche en toute sécurité (htmlspecialchars) le login de l'utilisateur connecté -->
                    <p>Connecté en tant que <strong><?= htmlspecialchars($currentUser['login']) ?></strong></p>
                </div>
                
                <h2>Modifier mon profil</h2>
                
                <!-- Affiche les messages d'erreur et de succès si présents -->
                <?php if ($error): ?>
                    <div class="message error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="message success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <!-- Formulaire de modification du profil. Méthode POST pour ne pas exposer les données dans l'URL. -->
                <form method="POST" action="">
                    <?= getCsrfInput() ?>
                    <div class="form-group">
                        <label for="login">Nom d'utilisateur :</label>
                        <!-- Valeur conservée après soumission en cas d'erreur, sinon login courant -->
                        <input type="text" id="login" name="login" required 
                               value="<?= htmlspecialchars($_POST['login'] ?? $currentUser['login']) ?>"
                               placeholder="Votre nom d'utilisateur">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Nouveau mot de passe (optionnel) :</label>
                        <!-- Champ password : laissé vide si l'utilisateur ne souhaite pas changer -->
                        <input type="password" id="password" name="password"
                               placeholder="Laissez vide pour ne pas changer">
                        <small style="color: #666; font-size: 0.9em;">Minimum 6 caractères</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               placeholder="Confirmez le nouveau mot de passe">
                    </div>
                    
                    <div class="form-group btn-center">
                        <button type="submit" class="btn">Mettre à jour</button>
                    </div>
                </form>
                
                <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e1e1e1;">
                    <h3>Mes statistiques</h3>
                    <?php
                    // Requête pour compter le nombre de commentaires postés par l'utilisateur
                    try {
                        $stmt = $pdo->prepare("SELECT COUNT(*) as nb_commentaires FROM commentaires WHERE id_utilisateur = ?");
                        $stmt->execute([$currentUser['id']]);
                        $stats = $stmt->fetch();

                        // Affiche le résultat (on cast en int pour s'assurer du type)
                        echo "<p>Vous avez posté <strong>" . (int)$stats['nb_commentaires'] . "</strong> commentaire(s)</p>";
                    } catch (PDOException $e) {
                        // En cas d'erreur, on affiche un message générique ; idéalement logger l'erreur
                        echo "<p>Impossible de charger les statistiques</p>";
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>