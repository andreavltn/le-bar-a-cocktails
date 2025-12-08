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
            <img src="images/logo-site.png" alt="logo-site.png">
        </div>

        <div class="banniere-bloc-milieu">
            <li class ="page-en-cours"><div>Accueil du site</div></li>
            <li><a href="recherche.php">Rechercher une recette</a></li>
            <li><a href="#">Consulter les favoris</a></li>
        </div>

        <div class="banniere-bloc-droite">
            <img src="images/logo-compte.png" alt="logo-compte.png">
            <a href="account/login.php">Connexion</a>
        </div>
    </header>

    <section class="contenu">
        <div class="bloc-intro">
            <div class="bloc-intro-texte">
                <div class="intro-titre">Bienvenue sur le site!</div>
                <p>
                    Découvrez plus de <strong>100 recettes de cocktails</strong> en tout genre, en filtrant et choisissant vos <strong>ingrédients préférés</strong>,
                    pour créer la boisson parfaite qui saura plaire à tous vos invités.
                </p>
                <a href="#" class="bouton-intro-decouvrir">Découvrir les recettes</a>
            </div>
            <div class="image-intro">
                <img src="images/image-accueil.png" alt="image-accueil.png">
            </div>
        </div>
    </section>

</body>
</html>
