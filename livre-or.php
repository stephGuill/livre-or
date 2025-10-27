<?php
// Inclusion du fichier de configuration global
// 'config.php' contient la création de la connexion PDO ($pdo),
// le démarrage de session et des fonctions utilitaires (ex: getCurrentUser()).
require_once 'config.php';

// Appel d'une fonction utilitaire définie dans config.php qui retourne
// les informations de l'utilisateur courant (ou null si non connecté).
$currentUser = getCurrentUser();

// Récupération de tous les commentaires avec le login de l'utilisateur qui les a postés.
// Utilisation de try/catch pour capturer les erreurs PDO et éviter une erreur fatale affichée à l'utilisateur.
try {
    // Prépare une requête SQL avec jointure pour récupérer :
    // - c.id           => identifiant du commentaire
    // - c.commentaire   => texte du commentaire
    // - c.date          => date d'enregistrement
    // - u.login         => login de l'utilisateur (provenant de la table utilisateurs)
    $stmt = $pdo->prepare(
        "SELECT c.id, c.commentaire, c.date, u.login
         FROM commentaires c
         INNER JOIN utilisateurs u ON c.id_utilisateur = u.id
         ORDER BY c.date DESC"
    );

    // Exécution de la requête préparée (sécurise la requête si on avait des paramètres).
    $stmt->execute();

    // Récupère tous les résultats dans un tableau associatif.
    // $commentaires sera un tableau de tableaux, chaque élément contenant les colonnes sélectionnées.
    $commentaires = $stmt->fetchAll();

} catch (PDOException $e) {
    // En cas d'erreur, on initialise $commentaires à un tableau vide pour ne pas casser l'affichage,
    // et on conserve un message d'erreur restrictif dans $error.
    $commentaires = [];
    $error = "Erreur lors du chargement des commentaires : " . $e->getMessage();
    // Note : en production, préférez logger $e->getMessage() plutôt que l'afficher directement.
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Déclaration de l'encodage des caractères (UTF-8 recommandé) -->
    <meta charset="UTF-8">
    <!-- Meta responsive pour que le rendu soit correct sur mobiles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livre d'Or - Commentaires</title>
    <!-- Feuille de style principale -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <!-- Titre principal de la page -->
            <h1> Livre d'Or</h1>
            <!-- Petite description / sous-titre -->
            <p>Découvrez les avis de notre communauté</p>
        </header>

        <nav class="nav">
            <ul>
                <!-- Lien vers la page d'accueil -->
                <li><a href="index.php"> Accueil</a></li>
                <!-- Lien actif vers cette page (livre d'or) -->
                <li><a href="livre-or.php"> Livre d'Or</a></li>

                <?php if ($currentUser): ?>
                    <!-- Si l'utilisateur est connecté, affichage des liens privés -->
                    <li><a href="profil.php"> Profil</a></li>
                    <li><a href="commentaire.php"> Nouveau Commentaire</a></li>
                    <li><a href="deconnexion.php"> Déconnexion</a></li>
                <?php else: ?>
                    <!-- Sinon, proposer de se connecter ou de s'inscrire -->
                    <li><a href="connexion.php"> Connexion</a></li>
                    <li><a href="inscription.php"> Inscription</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <main class="main-content">
            <?php if ($currentUser): ?>
                <!-- Si connecté, proposer un bouton pour ajouter un commentaire -->
                <div style="text-align: center; margin-bottom: 30px;">
                    <a href="commentaire.php" class="btn"> Ajouter un commentaire</a>
                </div>
            <?php else: ?>
                <!-- Incitation à se connecter / s'inscrire pour laisser un commentaire -->
                <div style="text-align: center; margin-bottom: 30px; padding: 20px; background: rgba(118, 75, 162, 0.1); border-radius: 10px;">
                    <p><strong>Envie de partager votre avis ?</strong></p>
                    <p>Connectez-vous ou créez un compte pour laisser un commentaire !</p>
                    <a href="connexion.php" class="btn" style="margin-right: 10px;">Se connecter</a>
                    <a href="inscription.php" class="btn">S'inscrire</a>
                </div>
            <?php endif; ?>

            <!-- Titre de la section des commentaires -->
            <h2>Commentaires de la communauté</h2>
            
            <?php if (isset($error)): ?>
                <!-- Affiche un message d'erreur si la récupération des commentaires a échoué -->
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (empty($commentaires)): ?>
                <!-- Aucun commentaire : affichage invitant à écrire le premier -->
                <div style="text-align: center; padding: 40px; background: rgba(118, 75, 162, 0.05); border-radius: 10px;">
                    <p style="font-size: 1.2em; color: #666;"> Soyez le premier à laisser un commentaire ! </p>
                    <?php if ($currentUser): ?>
                        <!-- Lien visible seulement aux utilisateurs connectés -->
                        <a href="commentaire.php" class="btn" style="margin-top: 15px;">Écrire le premier commentaire</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Affiche le nombre total de commentaires récupérés -->
                <div style="margin-bottom: 20px; color: #666;">
                    <p><strong><?= count($commentaires) ?></strong> commentaire(s) au total</p>
                </div>
                
                <?php foreach ($commentaires as $comment): ?>
                    <!-- Boucle sur chaque commentaire : $comment est un tableau associatif -->
                    <article class="comment">
                        <div class="comment-meta">
                            <!-- Affiche la date formatée et le login de l'auteur -->
                             Posté le <?= date('d/m/Y à H:i', strtotime($comment['date'])) ?> 
                            par <strong><?= htmlspecialchars($comment['login']) ?></strong>
                        </div>
                        <div class="comment-text">
                            <!-- Affiche le texte du commentaire en échappant les entités HTML
                                 puis convertit les sauts de ligne en <br> pour garder la mise en forme -->
                            <?= nl2br(htmlspecialchars($comment['commentaire'])) ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>