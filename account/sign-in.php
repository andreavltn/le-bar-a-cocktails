<!DOCTYPE html>

<?php
    session_start();
    require_once("../connect_db.php");
?>

<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>S'inscrire</title>
    <link rel="shortcut icon" href="../images/logo-site.png">
    <link rel="stylesheet" href="../css/sign-in.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body>
    <header class="banniere">
        <div class="banniere-bloc-milieu">
            <img src="../images/logo-site.png" alt="logo-site.png">
        </div>
    </header>

    <section class="contenu">
        <form class="formulaire" method="post" action="sign-in-check.php">
            <div class="titre-formulaire">Créer un compte</div>

            <div class="formulaire-grid">
                <div class="formulaire-col">
                    <div class="section-formulaire">
                        <label for="nom">Nom</label>
                        <input id="nom" name="nom" type="text" required>
                    </div>

                    <div class="section-formulaire">
                        <label for="prenom">Prénom</label>
                        <input id="prenom" name="prenom" type="text" required>
                    </div>

                    <div class="section-formulaire">
                        <label for="sexe">Sexe</label>
                        <select id="sexe" name="sexe" required>
                            <option value="">- Choisir -</option>
                            <option value="H">Homme</option>
                            <option value="F">Femme</option>
                            <option value="A">Autre</option>
                        </select>
                    </div>

                    <div class="section-formulaire">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" required>
                    </div>

                    <div class="section-formulaire">
                        <label for="date_naissance">Date de naissance</label>
                        <input id="date_naissance" name="date_naissance" type="date" required>
                    </div>

                    <div class="section-formulaire">
                        <label for="adresse">Adresse</label>
                        <input id="adresse" name="adresse" type="text" required>
                    </div>
                </div>

                <div class="formulaire-col">
                    <div class="section-formulaire">
                        <label for="code_postal">Code postal</label>
                        <input id="code_postal" name="code_postal" type="text" required>
                    </div>

                    <div class="section-formulaire">
                        <label for="ville">Ville</label>
                        <input id="ville" name="ville" type="text" required>
                    </div>

                    <div class="section-formulaire">
                        <label for="tel">Téléphone</label>
                        <input id="tel" name="tel" type="tel">
                    </div>

                    <div class="section-formulaire">
                        <label for="pseudo">Pseudo</label>
                        <input id="pseudo" name="pseudo" type="text" required>
                    </div>

                    <div class="section-formulaire">
                        <label for="mdp">Mot de passe</label>
                        <input id="mdp" name="mdp" type="password" required>
                    </div>

                    <div class="section-formulaire">
                        <label for="mdp2">Confirmer le mot de passe</label>
                        <input id="mdp2" name="mdp2" type="password" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="bouton-sinscrire">S'inscrire</button>

            <div class="bloc-seconnecter">
                <div class="texte-seconnecter">Déjà un compte ?</div>
                <a class="lien-seconnecter" href="login.php">Se connecter</a>
            </div>
        </form>

    </section>

</body>
</html>
