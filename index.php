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
                    