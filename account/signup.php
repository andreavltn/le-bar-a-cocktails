<!DOCTYPE html>

<?php
    session_start();
    require_once("../connect_db.php");
?>

<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Inscription | Le bar à cocktails</title>
    <link rel="shortcut icon" href="../images/logo-site.png">
    <link rel="stylesheet" href="../css/signup.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body>
    <header class="banniere">
        <div class="banniere-bloc-milieu">
            <img src="../images/logo-site.png" alt="logo-site.png">
        </div>
    </header>

    <section class="contenu">
        <form class="formulaire" method="post" action="signup-check.php">
            <div class="titre-formulaire">Créer un compte</div>

            <div class="formulaire-grid">
                <div class="formulaire-col">
                    <div class="section-formulaire">
                        <label for="nom">Nom</label>
                        <input id="nom" name="nom" type="text">
                    </div>

                    <div class="section-formulaire">
                        <label for="prenom">Prénom</label>
                        <input id="prenom" name="prenom" type="text">
                    </div>

                    <div class="section-formulaire">
                        <label for="sexe">Sexe</label>
                        <select id="sexe" name="sexe">
                            <option value="">- Choisir -</option>
                            <option value="H">Homme</option>
                            <option value="F">Femme</option>
                        </select>
                    </div>

                    <div class="section-formulaire">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email">
                    </div>

                    <div class="section-formulaire">
                        <label for="date_naissance">Date de naissance</label>
                        <input id="date_naissance" name="date_naissance" type="date">
                    </div>

                    <div class="section-formulaire">
                        <label for="adresse">Adresse</label>
                        <input id="adresse" name="adresse" type="text">
                    </div>
                </div>

                <div class="formulaire-col">
                    <div class="section-formulaire">
                        <label for="code_postal">Code postal</label>
                        <input id="code_postal" name="code_postal" type="text">
                    </div>

                    <div class="section-formulaire">
                        <label for="ville">Ville</label>
                        <input id="ville" name="ville" type="text">
                    </div>

                    <div class="section-formulaire">
                        <label for="tel">Téléphone</label>
                        <input id="tel" name="tel" type="tel">
                    </div>

                    <div class="section-formulaire">
                        <label for="pseudo">Pseudo
                        <?php
                            if (isset($_GET['erreur_pseudo'])){ // si un code erreur est renvoyé dans l'url
                                // afficher message d'erreur
                                echo "<span style='color: red; font-size: 14px; padding-left: 5px'> Un compte utilise déjà ce pseudo</span>"; 
                            }
                        ?>
                        </label>
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
    
    <script>

        // vérifier si les deux mots de passes correspondent
        const form = document.querySelector('.formulaire');
        const mdpInput = document.getElementById('mdp');
        const mdp2Input = document.getElementById('mdp2');
        const submitBtn = document.querySelector('.bouton-sinscrire');

        // sauvegarde du texte original du bouton
        const texteBoutonOriginal = submitBtn.textContent;
        
        // vérifier quand on change le premier mdp
        mdpInput.addEventListener('input', function() {
            if (mdpInput.value !== mdp2Input.value) { // si les valeurs sont différentes
                submitBtn.disabled = true; // le bouton de validation est désactivé
                submitBtn.style.opacity = '0.5';
                submitBtn.style.cursor = 'not-allowed';
                submitBtn.textContent = 'Les deux mots de passe ne correspondent pas';
            } else if (mdp2Input.value !== '') {
                submitBtn.disabled = false; // sinon il est activé
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
                submitBtn.textContent = texteBoutonOriginal; // et on remet le texte original
            }
        });

        // vérifier aussi pour le deuxième (exactement le même schéma)
        mdp2Input.addEventListener('input', function() {
            if (mdpInput.value !== mdp2Input.value) { 
                submitBtn.disabled = true; 
                submitBtn.style.opacity = '0.5';
                submitBtn.style.cursor = 'not-allowed';
                submitBtn.textContent = 'Les deux mots de passe ne correspondent pas'; 
            } else if (mdp2Input.value !== '') {
                submitBtn.disabled = false; 
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
                submitBtn.textContent = texteBoutonOriginal;
            }
        });

    </script>                        

</body>
</html>
