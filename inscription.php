<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_POST) {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($login) || empty($password) || empty($confirm_password)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        try {
            // Vérifier si le login existe déjà
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = ?");
            $stmt->execute([$login]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Ce nom d'utilisateur est déjà pris.";
            } else {
                // Insérer le nouvel utilisateur
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO utilisateurs (login, password) VALUES (?, ?)");
                $stmt->execute([$login, $hashedPassword]);
                
                $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                header("refresh:2;url=connexion.php");
            }
            } catch(PDOException $e) {
            $error = "Erreur lors de l'inscription : " . $e->getMessage();
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