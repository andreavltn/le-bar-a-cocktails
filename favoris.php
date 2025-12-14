<!DOCTYPE html>

<?php
    session_start();
    require_once("connect_db.php");
?>

<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Recette - Le bar à cocktails</title>
    <link rel="shortcut icon" href="images/logo-site.png">
    <link rel="stylesheet" href="css/accueil.css">
    <link rel="stylesheet" href="css/favoris.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body>
    <header class="banniere">
        <div class="banniere-bloc-gauche">
            <a href="accueil.php"><img src="images/logo-site.png" alt="logo-site.png"></a>
        </div>

        <div class="banniere-bloc-milieu">
            <li><a href="accueil.php">Accueil du site</a></li>
            <li><a href="recherche.php">Rechercher une recette</a></li>
            <li class ="page-en-cours">Consulter les favoris</a></li>
            <?php
                // si on est connecté, affiche le bouton de déconnexion
                if (isset($_SESSION["pseudo"])) {
                    echo "<li><button class='bouton-deconnexion' onclick='confirmerDeconnexion()'>Se déconnecter</button></li>";
                }
            ?>
        </div>

        <div class="banniere-bloc-droite">
            <img src="images/logo-compte.png" alt="logo-compte.png">
            <?php
                // si on est connecté, affiche le pseudo
                if (isset($_SESSION["pseudo"])) {
                    echo "<span>" . $_SESSION["pseudo"] . "</span>";
                } else {
                    // sinon affiche "Connexion" qui renvoie vers la page de login
                    echo "<a href='account/login.php'>Connexion</a>";
                }
            ?>
        </div>
    </header>

    <section class="contenu">
        

        <div class="bloc-recette">
            <?php
                
                

                // récupérer les recette depuis la BDD
                

                    // requête sql pour rechercher les éléments des recettes 
                    if (isset($_SESSION["pseudo"])) {
                    // récupérer le pseudo depuis la session
                    $pseudo = $_SESSION["pseudo"];

                    // requête SQL pour récupérer les recettes favorites
                    $sql_recettes = "
                        SELECT id_recette, titre
                        FROM recettes
                        INNER JOIN favoris USING (id_recette)
                        INNER JOIN utilisateur ON id_user = id
                        WHERE pseudo = ?
                    ";

                    // préparation
                    $stmt = mysqli_prepare($dblink, $sql_recettes);
                    if (!$stmt) {
                        die("Erreur prepare: " . mysqli_error($dblink));
                    }

                    // liaison du paramètre
                    mysqli_stmt_bind_param($stmt, "s", $pseudo);

                    // exécution
                    mysqli_stmt_execute($stmt);

                    // récupération du résultat
                    $resultat = mysqli_stmt_get_result($stmt);}

                    else {} //////////// FAIRE LE CAS OU ON EST PAS CONNECT2

                    if (!$resultat) {
                        die("Erreur requete recettes : " . mysqli_error($dblink));
                    }   

                    // requête sql pour rechercher les ingrédients clé à partir de l'id de la recette
                    $sql_tags = "SELECT id_recette, a.nom FROM aliments a
                                INNER JOIN ingredient_recette ir ON a.id_aliment = ir.id_ingredient";

                        $req_tags = mysqli_query($dblink, $sql_tags);

                    if (!$req_tags) {
                        die("Erreur requete ingrédients : " . mysqli_error($dblink));
                    }


                    // fait la table des noms d'ingredients par id recette
                    $ingredients_par_recette = [];
                    while ($tag = mysqli_fetch_assoc($req_tags)) {
                        $ingredients_par_recette[$tag['id_recette']][] = $tag['nom'];
                    }

    //boucle des reettes                     
    while ($recette = mysqli_fetch_assoc($resultat)) {

        echo '<div class="recette-container" onclick="window.location.href=\'recette.php?id=' . $recette['id_recette'] . '\'">';


            // ------------------- IMAGE -------------------

        echo '<div class="recette-image">';

        // créer le nom de l'image (pour la retrouver dans le dossier images)
        // en minuscules, remplace les espaces par des _
        $nom_image = strtolower(str_replace(' ', '_', $recette['titre']));

        // extraire seulement le nom avant les parenthèses (si il y en a)
        $nom_image = explode('(', $nom_image)[0];

        // enlever les espaces/caractères vides au début et à la fin
        $nom_image = trim($nom_image);

        // création du chemin du fichier
        $chemin_image = "images/" . $nom_image . ".jpg";

        // vérifier si l'image existe, sinon utilise l'image par défaut
        if (!file_exists($chemin_image)) {
            $chemin_image = "images/image_placeholder.jpg";
        }

        // affiche l'image 
        echo '<img src="' . $chemin_image . '" alt="' . htmlspecialchars($recette['titre']) . '">';
        echo '</div>';


        echo '<div class="recette-contenu">';

        // ------------------- TITRE -------------------
        
        echo '<h2 class="recette-titre">' . htmlspecialchars($recette['titre']) . '</h2>';

        // INGREDIENTS CLÉS
        if (isset($ingredients_par_recette[$recette['id_recette']])) {

            echo '<div class="recette-section">';
            echo '<h3>Ingrédients clés</h3>';
            echo '<div class="tags-ingredients">';

        // pour chaque tag de la recette, l'affiche dans un span
        foreach ($ingredients_par_recette[$recette['id_recette']] as $ingredient) {
            echo '<span class="tag">' . htmlspecialchars($ingredient) . '</span>';
        }

        echo '</div>';
        echo '</div>';
    }

    echo '</div>'; // recette-contenu
    echo '</div>'; // recette-container
}
            ?>
        </div>
    </section>

    <script>
        // fonction qui affiche un pop up pour valider la déconnexion
        function confirmerDeconnexion() {
            if (confirm('Souhaitez vous vraiment vous déconnecter ?')) {
                // redirection vers la page de déconnexion
                window.location.href = 'account/logout.php';
            }
        }
    </script>

</body>
</html>