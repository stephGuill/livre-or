<?php
require_once 'config.php';

//  vérifie si l'utilisateur es connecté
if (!isLoggedIn()) {
    header('Location: connexion.php');
    exit();
}

$currentUser = getCurrentUser();
$error = '';
$succes = '';

if ($_POST) {
    $new_login = trim($_POST['login']);
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    //  validation
     if (empty($new_login)) {
        $error = "Le nom d'utilisateur est obligatoire.";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (!empty($new_password) && strlen($new_password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        try {
            // Vérifier si le nouveau login existe déjà (sauf pour l'utilisateur actuel)
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = ? AND id != ?");
            $stmt->execute([$new_login, $currentUser['id']]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Ce nom d'utilisateur est déjà pris.";
            } else {
                // Mettre à jour le profil
                if (!empty($new_password)) {
                    // Mettre à jour login et mot de passe
                    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET login = ?, password = ? WHERE id = ?");
                    $stmt->execute([$new_login, $hashedPassword, $currentUser['id']]);
                } else {
                    // Mettre à jour seulement le login
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET login = ? WHERE id = ?");
                    $stmt->execute([$new_login, $currentUser['id']]);
                }
                
                // Mettre à jour la session
                $_SESSION['user_login'] = $new_login;
                $success = "Profil mis à jour avec succès !";
                $currentUser['login'] = $new_login;
            }
        } catch(PDOException $e) {
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
                    <p>Connecté en tant que <strong><?= htmlspecialchars($currentUser['login']) ?></strong></p>
                </div>
                
                <h2>Modifier mon profil</h2>
                
                <?php if ($error): ?>
                    <div class="message error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="message success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="login">Nom d'utilisateur :</label>
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
                    try {
                        $stmt = $pdo->prepare("SELECT COUNT(*) as nb_commentaires FROM commentaires WHERE id_utilisateur = ?");
                        $stmt->execute([$currentUser['id']]);
                        $stats = $stmt->fetch();
                        echo "<p>Vous avez posté <strong>" . $stats['nb_commentaires'] . "</strong> commentaire(s)</p>";
                    } catch(PDOException $e) {
                        echo "<p>Impossible de charger les statistiques</p>";
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>