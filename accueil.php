<!DOCTYPE html>

<?php
    session_start();
    require_once("connect_db.php")
?>

<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Le bar à cocktails</title>
    <link rel="shortcut icon" href="images/logo-site.png">
    <link rel="stylesheet" href="css/accueil.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body>
    <header class="banniere">
        <div class="banniere-bloc-gauche">
            <img src="images/logo-site.png" alt="logo-site.png" class="logo-site">
        </div>

        <div class="banniere-bloc-milieu">
            <li class ="page-en-cours"><a href="#">Accueil du site</a></li>
            <li><a href="#">Rechercher une recette</a></li>
            <li><a href="#">Consulter les favoris</a></li>
        </div>

        <div class="banniere-bloc-droite">
            <img src="images/logo-compte.png" alt="logo-compte.png" class="logo-compte">
            <a href="#">Connexion</a>
        </div>
    </header>

    <section class="contenu">
        <h1>Bienvenue sur le site</h1>
    </section>

</body>
</html>
