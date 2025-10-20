<?php
require_once 'config.php';

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta chartset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Livre d'or - Accueil</title>
        <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1> Livre d'or Digital </h1>
            <p>Partager vos avis et découvrez ceux de notre communauté</p>
        </header>

        <nav class="nav">
            <ul>
                <li><a href="index.php"> Accueil</a></li>
                <li><a href="livre-or.php"> Livre d'Or</a></li>
                <?php if ($currentUser) : ?>
                    <li><a href="profil.php"> Profil</a></li>
                    <li><a href="commentaire.php"> Nouveau commentaire</a></li>
                    <li><a href="deconnexion.php"> Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="connexion.php"> Connexion</a></li>
                    <li><a href="inscription.php"> Inscription</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <main class="main-content">
            <?php if ($currentUser): ?>
            <div class="user-info">
                <p>Bienvennue <?= htmlspecialchars($currentUser['login']) ?> ! </p>
            </div>
        <?php endif; ?>

            <h2>Bienvennue sur notre Livre d'Or Digital</h2> 

            <p>Décovrez notre plateforme interractive où vous pouvez :</p>

            <ul style="margin: 20px 0; padding-left: 30px; line-height: 1.8">
                <li> Consulter les avis - Lisez les commentaires de notre communauté</li>
                <li> Partager votre expérience - Laissez votre propre commentaire</li>
                <li> Rejoindre la communauté - Créez votre compte personnel</li>
                <li> Gérer votre profil - Modifiez vos informations à tout moment</li>
            </ul>

            <div style="text-align: center; margin: 30px 0;">
                <?php if (!$currentUser): ?>
                    <p style="margin-bottom: 20px;">Vous n'êtes pas encore membre ? Rejoignez-nous dès maintenant !</p>
                    <a href="inscription.php" class="btn">S'inscrire gratuitement</a>
                    <a href="connexion.php" class="btn" style="margin-left: 10px;">Se connecter</a>
                <?php else: ?>
                    <p style="margin-bottom: 20px;">Vous êtes connecté ! Découvrez notre livre d'or ou ajoutez votre commentaire.</p>
                    <a href="livre-or.php" class="btn">Voir le Livre d'Or</a>
                    <a href="commentaire.php" class="btn" style="margin-left: 10px;">Ajouter un Commentaire</a>
                <?php endif; ?>
            </div>

            <div style="background: rgba(118, 75, 162, 0.1); padding: 20px; border-radius: 10px; margin-top: 30px;">
                <h3 style="color: #764ba2; margin-bottom: 15px;"> Thème : Communauté et Partage</h3>
                <p>Notre livre d'or digital célèbre l'esprit de communauté et l'importance du partage d'expériences. 
                Chaque commentaire contribue à créer un espace chaleureux et accueillant pour tous nos visiteurs.</p>
            </div>
        </main>
    </div>
</body>
</html>


                    
