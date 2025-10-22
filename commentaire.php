<?php
require_once 'config.php';

// vérifie si l'utilisateur est connecté
if (!isLoggedIn()) {
    header('Location: connexion.php');
    exit();
}

$currentUser = getCurrentUser();
$error = '';
$success = '';

if ($_POST) {
    $commentaire = trim($_POST['commentaire']);

    if (empty($commentaire)) {
        $error = "Le commmentaire ne peut pas être.";
    } elseif (strlen($commentaire) < 10) {
        $error = "Le commentaire doit contenir au moins 10 caractères";
    } elseif (strlen($commentaire) > 1000) {
        $error = "Le commentaire ne peut pas dépasser 1000 caratères.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO commentaires (commentaire, id_utilisateur, date) VALUES (?, ?, NOW())");
            $stmt ->execute([$commentair, $currentUser['id']]);

            $sussess = "Votre commentaire a été ajouté avec succès !";
            header("refresh:2;url=livre-or.php");
        } catch(PDOException $e) {
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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1> Nouveau Commentaire</h1>
            <p>Partagez votre expérience avec la communauté</p>
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
                    <p>Vous publiez en tant que <strong><?= htmlspecialchars($currentUser['login']) ?></strong></p>
                </div>
                
                <h2>Ajouter un commentaire</h2>
                
                <?php if ($error): ?>
                    <div class="message error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="message success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="commentaire">Votre commentaire :</label>
                        <textarea id="commentaire" name="commentaire" required 
                                  placeholder="Partagez votre expérience, vos impressions, vos suggestions...&#10;&#10;Soyez respectueux et constructif dans vos commentaires."><?= htmlspecialchars($_POST['commentaire'] ?? '') ?></textarea>
                        <small style="color: #666; font-size: 0.9em;">
                            Entre 10 et 1000 caractères. 
                            <span id="charCount">0</span>/1000
                        </small>
                    </div>
                    
                    <div class="form-group btn-center">
                        <button type="submit" class="btn">Publier le commentaire</button>
                        <a href="livre-or.php" class="btn" style="background: #6c757d; margin-left: 10px;">Annuler</a>
                    </div>
                </form>
                
                <div style="background: rgba(118, 75, 162, 0.1); padding: 20px; border-radius: 10px; margin-top: 30px;">
                    <h3 style="color: #764ba2; margin-bottom: 15px;"> Conseils pour un bon commentaire</h3>
                    <ul style="padding-left: 20px; line-height: 1.6;">
                        <li>Soyez précis et détaillé</li>
                        <li>Restez respectueux envers les autres utilisateurs</li>
                        <li>Partagez votre expérience personnelle</li>
                        <li>Constructif et utile pour la communauté</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
