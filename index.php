<?php
require_once 'config.php';

// Inclusion du fichier de configuration global
// `config.php` contient la configuration de la base de données (PDO), démarre la session
// et définit des helpers comme isLoggedIn() et getCurrentUser().
require_once 'config.php';

// Récupère les informations de l'utilisateur connecté (si présent)
// getCurrentUser() est défini dans config.php et retourne typiquement un tableau
// ['id' => ..., 'login' => ...] ou null si non connecté.
$currentUser = getCurrentUser();
?>

<!DOCTYPE html>
<!-- Balise DOCTYPE indique au navigateur d'utiliser le mode standard -->
<html lang="fr">
    <head>
        <!-- Déclaration d'encodage : important pour l'affichage des caractères accentués -->
        <meta charset="UTF-8">
        <!-- Meta viewport pour rendre la page responsive sur mobiles -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Titre affiché dans l'onglet du navigateur -->
        <title>Livre d'or - Accueil</title>
        <!-- Lien vers la feuille de style principale (CSS) -->
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="container">
            <!-- En-tête de la page : titre et description -->
            <header class="header">
                <h1> Livre d'or Digital </h1>
                <p>Partager vos avis et découvrez ceux de notre communauté</p>
            </header>

            <!-- Barre de navigation : liens principaux du site -->
            <nav class="nav">
                <ul>
                    <!-- Lien vers la page d'accueil -->
                    <li><a href="index.php"> Accueil</a></li>
                    <!-- Lien vers la page du livre d'or (liste des commentaires) -->
                    <li><a href="livre-or.php"> Livre d'Or</a></li>

                    <?php
                    // Bloc conditionnel PHP : affiche des liens selon l'état de connexion
                    // Si $currentUser est truthy (utilisateur connecté), affiche les liens protégés
                    if ($currentUser) : ?>
                        <!-- Lien vers la page de profil -->
                        <li><a href="profil.php"> Profil</a></li>
                        <!-- Lien pour poster un nouveau commentaire -->
                        <li><a href="commentaire.php"> Nouveau commentaire</a></li>
                        <!-- Lien de déconnexion -->
                        <li><a href="deconnexion.php"> Déconnexion</a></li>
                    <?php else: ?>
                        <!-- Si pas connecté : montrer les liens vers connexion/inscription -->
                        <li><a href="connexion.php"> Connexion</a></li>
                        <li><a href="inscription.php"> Inscription</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <!-- Contenu principal de la page -->
            <main class="main-content">
                <?php if ($currentUser): ?>
                    <!-- Affiche une boîte d'information utilisateur lorsque connecté -->
                    <div class="user-info">
                        <!-- htmlspecialchars() empêche les attaques XSS en encodant les caractères spéciaux -->
                        <p>Bienvenue <?= htmlspecialchars($currentUser['login']) ?> ! </p>
                    </div>
                <?php endif; ?>

                <!-- Titre et description de la section d'accueil -->
                <h2>Bienvenue sur notre Livre d'Or Digital</h2>

                <p>Découvrez notre plateforme interactive où vous pouvez :</p>

                <!-- Liste des actions possibles pour l'utilisateur -->
                <ul style="margin: 20px 0; padding-left: 30px; line-height: 1.8">
                    <li>Consulter les avis - Lisez les commentaires de notre communauté</li>
                    <li>Partager votre expérience - Laissez votre propre commentaire</li>
                    <li>Rejoindre la communauté - Créez votre compte personnel</li>
                    <li>Gérer votre profil - Modifiez vos informations à tout moment</li>
                </ul>

                <!-- Zone d'appel à l'action centrée -->
                <div style="text-align: center; margin: 30px 0;">
                    <?php if (!$currentUser): ?>
                        <!-- Appel à l'inscription si l'utilisateur n'est pas connecté -->
                        <p style="margin-bottom: 20px;">Vous n'êtes pas encore membre ? Rejoignez-nous dès maintenant !</p>
                        <a href="inscription.php" class="btn">S'inscrire gratuitement</a>
                        <a href="connexion.php" class="btn" style="margin-left: 10px;">Se connecter</a>
                    <?php else: ?>
                        <!-- Liens rapides lorsque l'utilisateur est connecté -->
                        <p style="margin-bottom: 20px;">Vous êtes connecté ! Découvrez notre livre d'or ou ajoutez votre commentaire.</p>
                        <a href="livre-or.php" class="btn">Voir le Livre d'Or</a>
                        <a href="commentaire.php" class="btn" style="margin-left: 10px;">Ajouter un Commentaire</a>
                    <?php endif; ?>
                </div>

                <!-- Encadré thématique expliquant la mission du site -->
                <div style="background: rgba(118, 75, 162, 0.1); padding: 20px; border-radius: 10px; margin-top: 30px;">
                    <h3 style="color: #764ba2; margin-bottom: 15px;"> Thème : Communauté et Partage</h3>
                    <p>Notre livre d'or digital célèbre l'esprit de communauté et l'importance du partage d'expériences.
                    Chaque commentaire contribue à créer un espace chaleureux et accueillant pour tous nos visiteurs.</p>
                </div>
            </main>
        </div>
    </body>
</html>



