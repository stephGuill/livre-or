<?php
// Inclusion du fichier de configuration : connexion PDO ($pdo), démarrage de session et fonctions utilitaires
require_once 'config.php';

// Vérifie si l'utilisateur est connecté via la fonction isLoggedIn() définie dans config.php
// Si non connecté, redirige vers la page de connexion
if (!isLoggedIn()) {
    header('Location: connexion.php');
    exit();
}

// Récupère les informations de l'utilisateur connecté (id, login)
$currentUser = getCurrentUser();

// Variables pour stocker les messages d'erreur et de succès
$error = '';
$success = ''; // corrigé depuis $succes --> $success

// Traitement du formulaire : si des données POST sont envoyées
if ($_POST) {
    // Récupération des champs envoyés
    $new_login = trim($_POST['login']); // on enlève les espaces autour
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation côté serveur
    if (empty($new_login)) {
        // Le login est obligatoire
        $error = "Le nom d'utilisateur est obligatoire.";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        // Si un nouveau mot de passe est renseigné, vérifier la confirmation
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (!empty($new_password) && strlen($new_password) < 6) {
        // Vérifier la longueur minimale du mot de passe
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        try {
            // Préparer une requête pour vérifier l'unicité du login (exclut l'utilisateur courant)
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = ? AND id != ?");
            $stmt->execute([$new_login, $currentUser['id']]);

            // rowCount() peut fonctionner, mais fetch() est plus portable ; ici on vérifie s'il y a un résultat
            if ($stmt->rowCount() > 0) {
                $error = "Ce nom d'utilisateur est déjà pris.";
            } else {
                // Si un nouveau mot de passe est fourni, hasher et mettre à jour login+password
                if (!empty($new_password)) {
                    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT); // hash sécurisé
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET login = ?, password = ? WHERE id = ?");
                    $stmt->execute([$new_login, $hashedPassword, $currentUser['id']]);
                } else {
                    // Sinon, mettre à jour uniquement le login
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET login = ? WHERE id = ?");
                    $stmt->execute([$new_login, $currentUser['id']]);
                }

                // Mettre à jour la session et préparer un message de succès
                $_SESSION['user_login'] = $new_login;
                $success = "Profil mis à jour avec succès !";
                $currentUser['login'] = $new_login; // mise à jour locale pour affichage immédiat
            }
        } catch(PDOException $e) {
            // En développement on affiche le message d'erreur ; en production mieux vaut logger l'erreur
            $error = "Erreur lors de la mise à jour : " . $e->getMessage();
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
                    <!-- Affiche en toute sécurité le login de l'utilisateur connecté -->
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

                <!-- Formulaire de modification du profil -->
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="login">Nom d'utilisateur :</label>
                        <!-- Valeur conservée après soumission en cas d'erreur, sinon login courant -->
                        <input type="text" id="login" name="login" required 
                               value="<?= htmlspecialchars($_POST['login'] ?? $currentUser['login']) ?>"
                               placeholder="Votre nom d'utilisateur">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Nouveau mot de passe (optionnel) :</label>
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
                        // Affiche le résultat (cast int si souhaité)
                        echo "<p>Vous avez posté <strong>" . (int)$stats['nb_commentaires'] . "</strong> commentaire(s)</p>";
                    } catch(PDOException $e) {
                        // Message générique en cas d'erreur
                        echo "<p>Impossible de charger les statistiques</p>";
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>