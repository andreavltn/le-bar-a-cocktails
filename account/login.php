<!DOCTYPE html>

<?php
    session_start();
    require_once("../connect_db.php");
?>

<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Se connecter</title>
    <link rel="shortcut icon" href="../images/logo-site.png">
    <link rel="stylesheet" href="../css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body>
    <header class="banniere">
        <div class="banniere-bloc-milieu">
            <img src="../images/logo-site.png" alt="logo-site.png">
        </div>
    </header>

    <section class="contenu">
        <form class="formulaire" method="post" action="login-check.php">
            <div class="titre-formulaire">Connexion</div>
            <div class="section-formulaire">
                <label for="pseudo">Pseudo</label>
                <input id="pseudo" name="pseudo" type="text" required>
            </div>
            <div class="section-formulaire">
                <label for="mdp">Mot de passe</label>
                <input id="mdp" name="mdp" type="password" required>
            </div>
            
            <button type="submit" class="bouton-seconnecter">Se connecter</button>
                
            <div class="section-sinscrire">
                <div class="texte-sinscrire">Pas encore de compte ?</div>
                <a class="lien-sinscrire" href="sign-in.php">S'inscrire</a>
            </div>
        </form>

    </section>

</body>
</html>
