<?php
require_once 'config.php';

$currentUser = getCurrentUser();

// récupère tous les commentaires avec les informations des utilisateurs
try {
    $stmt = $pdo->prepare("
    SELECT c.id, c.commentaire, c.date, u.login
    FROM commentaires c
    INNER JOIN utilisateurs u ON c.id_utilisateur = u.id
    ORDER BY c.date DESC
    ");
    $stmt->execute();
    $commentaires = $stmt->fetchAll();
} catch(PDOException $e) {
    $commentaires = [];
    $error = "Erreur lors du chargement des commentaires : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livre d'Or - Commentaires</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1> Livre d'Or</h1>
            <p>Découvrez les avis de notre communauté</p>
        </header>

        <nav class="nav">
            <ul>
                <li><a href="index.php"> Accueil</a></li>
                <li><a href="livre-or.php"> Livre d'Or</a></li>
                <?php if ($currentUser): ?>
                    <li><a href="profil.php"> Profil</a></li>
                    <li><a href="commentaire.php"> Nouveau Commentaire</a></li>
                    <li><a href="deconnexion.php"> Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="connexion.php"> Connexion</a></li>
                    <li><a href="inscription.php"> Inscription</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <main class="main-content">
            <?php if ($currentUser): ?>
                <div style="text-align: center; margin-bottom: 30px;">
                    <a href="commentaire.php" class="btn"> Ajouter un commentaire</a>
                </div>
            <?php else: ?>
                <div style="text-align: center; margin-bottom: 30px; padding: 20px; background: rgba(118, 75, 162, 0.1); border-radius: 10px;">
                    <p><strong>Envie de partager votre avis ?</strong></p>
                    <p>Connectez-vous ou créez un compte pour laisser un commentaire !</p>
                    <a href="connexion.php" class="btn" style="margin-right: 10px;">Se connecter</a>
                    <a href="inscription.php" class="btn">S'inscrire</a>
                </div>
            <?php endif; ?>

            <h2>Commentaires de la communauté</h2>
            
            <?php if (isset($error)): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (empty($commentaires)): ?>
                <div style="text-align: center; padding: 40px; background: rgba(118, 75, 162, 0.05); border-radius: 10px;">
                    <p style="font-size: 1.2em; color: #666;"> Soyez le premier à laisser un commentaire ! </p>
                    <?php if ($currentUser): ?>
                        <a href="commentaire.php" class="btn" style="margin-top: 15px;">Écrire le premier commentaire</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div style="margin-bottom: 20px; color: #666;">
                    <p><strong><?= count($commentaires) ?></strong> commentaire(s) au total</p>
                </div>
                
                <?php foreach ($commentaires as $comment): ?>
                    <article class="comment">
                        <div class="comment-meta">
                             Posté le <?= date('d/m/Y à H:i', strtotime($comment['date'])) ?> 
                            par <strong><?= htmlspecialchars($comment['login']) ?></strong>
                        </div>
                        <div class="comment-text">
                            <?= nl2br(htmlspecialchars($comment['commentaire'])) ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>