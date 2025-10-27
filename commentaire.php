<?php
// Inclusion du fichier de configuration global
// Contient :
// - la connexion PDO $pdo
// - session_start() et les fonctions utilitaires isLoggedIn(), getCurrentUser(), logout()
require_once 'config.php';

// Vérification que l'utilisateur est authentifié
// isLoggedIn() retourne true si $_SESSION['user_id'] est défini
if (!isLoggedIn()) {
    // Redirection vers la page de connexion si l'utilisateur n'est pas connecté
    header('Location: connexion.php');
    exit(); // Arrête l'exécution du script après la redirection
}

// Récupération des informations de l'utilisateur courant (id, login)
$currentUser = getCurrentUser();

// Préparation des variables pour messages d'erreur et de succès
$error = '';
$success = '';

// Traitement du formulaire si la méthode POST est utilisée
if ($_POST) {
    // Récupère et normalise le champ 'commentaire' envoyé par le formulaire
    $commentaire = trim($_POST['commentaire']);

    // Validation côté serveur : évite d'accepter des commentaires vides ou trop courts/longs
    if (empty($commentaire)) {
        // empty() vérifie '', null, '0' etc. Ici on veut empêcher un commentaire vide
        $error = "Le commentaire ne peut pas être vide.";
    } elseif (strlen($commentaire) < 10) {
        // strlen() retourne la longueur en octets ; pour UTF-8 multioctets mb_strlen() serait préférable
        $error = "Le commentaire doit contenir au moins 10 caractères.";
    } elseif (strlen($commentaire) > 1000) {
        $error = "Le commentaire ne peut pas dépasser 1000 caractères.";
    } else {
        // Si les validations passent, on essaie d'insérer le commentaire en base
        try {
            // Requête préparée pour insérer en toute sécurité (prévention injection SQL)
            $stmt = $pdo->prepare("INSERT INTO commentaires (commentaire, id_utilisateur, date) VALUES (?, ?, NOW())");
            // Exécution avec paramètres positionnels : commentaire texte et id de l'utilisateur
            $stmt->execute([$commentaire, $currentUser['id']]);

            // Prépare un message de succès pour affichage
            $success = "Votre commentaire a été ajouté avec succès !";

            // Redirection rafraîchissante : attend 2 secondes puis redirige vers la page du livre d'or
            header("Refresh: 2; url=livre-or.php");
            // NOTE : on ne met pas exit() immédiatement si l'on veut laisser le message s'afficher
        } catch (PDOException $e) {
            // En environnement de développement on peut afficher le message de l'exception
            // En production, préférez logger $e->getMessage() et afficher un message générique
            $error = "Erreur lors de l'ajout du commentaire : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Commentaire - Livre d'Or</title>
    <!-- Inclusion du fichier CSS principal -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- En-tête de la page -->
        <header class="header">
            <h1> Nouveau Commentaire</h1>
            <p>Partagez votre expérience avec la communauté</p>
        </header>

        <!-- Barre de navigation (liens principaux) -->
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
                    <!-- Affiche le login de l'utilisateur connecté, encodé pour prévenir XSS -->
                    <p>Vous publiez en tant que <strong><?= htmlspecialchars($currentUser['login']) ?></strong></p>
                </div>
                
                <h2>Ajouter un commentaire</h2>
                
                <!-- Affichage des messages d'erreur ou de succès -->
                <?php if ($error): ?>
                    <div class="message error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="message success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <!-- Formulaire d'envoi d'un commentaire -->
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="commentaire">Votre commentaire :</label>
                        <!-- textarea : conserve la valeur soumise en cas d'erreur pour éviter perte de saisie -->
                        <textarea id="commentaire" name="commentaire" required 
                                  placeholder="Partagez votre expérience, vos impressions, vos suggestions...\n\nSoyez respectueux et constructif dans vos commentaires."><?= htmlspecialchars($_POST['commentaire'] ?? '') ?></textarea>
                        <small style="color: #666; font-size: 0.9em;">
                            Entre 10 et 1000 caractères. 
                            <span id="charCount">0</span>/1000
                        </small>
                    </div>
                    
                    <div class="form-group btn-center">
                        <button type="submit" class="btn">Publier le commentaire</button>
                        <!-- Lien d'annulation redirige vers la page du livre d'or -->
                        <a href="livre-or.php" class="btn" style="background: #6c757d; margin-left: 10px;">Annuler</a>
                    </div>
                </form>
                
                <!-- Section d'aide / bonnes pratiques pour rédiger un commentaire utile -->
                <div style="background: rgba(118, 75, 162, 0.1); padding: 20px; border-radius: 10px; margin-top: 30px;">
                    <h3 style="color: #764ba2; margin-bottom: 15px;"> Conseils pour un bon commentaire</h3>
                    <ul style="padding-left: 20px; line-height: 1.6;">
                        <li>Soyez précis et détaillé</li>
                        <li>Restez respectueux envers les autres utilisateurs</li>
                        <li>Partagez votre expérience personnelle</li>
                        <li>Faites preuve de constructivité</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
