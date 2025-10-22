<?php
require_once 'config.php';

// redirige si déjà connecté
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_POST) {
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    if (empty($login) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, login, password FROM utilisateurs WHERE login = ?");
            $stmt ->execute([$login]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // connexion réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_login'] = $user['login'];


                // Optionnel : fermer explicitement la connexion PDO si vous souhaitez libérer la ressource
                // $pdo = null; // décommentez si nécessaire
                header('Location: index.php');
                exit();
            } else {
                $error = "Nom d'utilisateur ou mot de passe incorrect.";
            }
        } catch(PDOException $e) {
            $error = "Erreur de connexion : " . $e->getMessage();
    }  
    
}
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Livre d'Or</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1> Connexion</h1>
            <p>Connectez-vous à votre compte</p>
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
                <h2>Se connecter</h2>
                
                <?php if ($error): ?>
                    <div class="message error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="login">Nom d'utilisateur :</label>
                        <input type="text" id="login" name="login" required 
                               value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
                               placeholder="Votre nom d'utilisateur">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe :</label>
                        <input type="password" id="password" name="password" required
                               placeholder="Votre mot de passe">
                    </div>
                    
                    <div class="form-group btn-center">
                        <button type="submit" class="btn">Se connecter</button>
                    </div>
                </form>
                
                <div style="text-align: center; margin-top: 20px;">
                    <p>Pas encore de compte ? <a href="inscription.php" style="color: #764ba2; text-decoration: none; font-weight: 500;">S'inscrire gratuitement</a></p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>