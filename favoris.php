<!DOCTYPE html>

<?php
    session_start();
    require_once("connect_db.php");
?>

<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Favoris - Le bar à cocktails</title>
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
        <?php
            // si on est connecté
            if (isset($_SESSION["pseudo"])) {
                // récupérer le pseudo depuis la session
                $pseudo = $_SESSION["pseudo"];

                // requête SQL pour récupérer les recettes favorites
                $sql_recettes_fav = "
                    SELECT id_recette, titre
                    FROM recettes
                    INNER JOIN favoris USING (id_recette)
                    INNER JOIN utilisateur ON id_user = id
                    WHERE pseudo = ?
                ";

                // préparation
                $req_recettes_fav = mysqli_prepare($dblink, $sql_recettes_fav);

                // liaison du paramètre (chaine)
                mysqli_stmt_bind_param($req_recettes_fav, "s", $pseudo);

                // exécution
                mysqli_stmt_execute($req_recettes_fav);

                // récupération du résultat
                $resultat_recettes_fav = mysqli_stmt_get_result($req_recettes_fav);
                // unifie la variable résultat pour l'affichage
                $resultat = $resultat_recettes_fav;

            }
            else {

                // sinon on n'est pas connecté :

                // si on a des favoris (stocké dans la session)
                if(isset($_SESSION['favoris'])) {
                    $favoris_session = $_SESSION['favoris']; // les récupère
                } else {
                    $favoris_session = []; // sinon tableau vide
                }
                
                // nombre de favoris
                $nb_fav_session = count($favoris_session);

                // si le tableau est vide (pas de favoris) : message d'avertissement
                if ($nb_fav_session === 0) {
                    echo '<div class="aucun-favori-message">Aucun favori pour le moment.<br><a href="recherche.php">Découvrir des recettes</a></div>';
                    // on sort de la section pour ne pas afficher la grille
                    return;
                }
                
                // placeholders pour la requête préparée
                // pour chaque élément de la liste, on met un ? dans la requête
                $placeholders = implode(',', array_fill(0, $nb_fav_session, '?'));

                // requête pour récupérer les recettes dans la bdd
                $sql_recettes = "
                    SELECT id_recette, titre
                    FROM recettes
                    WHERE id_recette IN ($placeholders)
                ";
                
                $req_recettes = mysqli_prepare($dblink, $sql_recettes); // préparation de la requête
                
                // les types sont des entiers (on crée une liste de n "i", n étant le nombre de favoris)
                $types = str_repeat('i', count($favoris_session));

                mysqli_stmt_bind_param($req_recettes, $types, ...$favoris_session); // liaison des paramètres
                mysqli_stmt_execute($req_recettes); // exécution de la requête
                $resultat_recettes = mysqli_stmt_get_result($req_recettes); // récupération du résultat
                
                // unifie la variable résultat pour l'affichage
                $resultat = $resultat_recettes;

                if (!$resultat_recettes) {
                    die("Erreur requete recettes : " . mysqli_error($dblink));
                }   
            }


            // requête sql pour rechercher les ingrédients clé à partir de l'id de la recette
            $sql_tags = "SELECT id_recette, a.nom FROM aliments a
                        INNER JOIN ingredient_recette ir ON a.id_aliment = ir.id_ingredient";

            $req_tags = mysqli_query($dblink, $sql_tags); // préparation de la requête

            if (!$req_tags) {
                die("Erreur requete ingrédients : " . mysqli_error($dblink));
            }

            // fait la table des noms d'ingredients par id recette
            $ingredients_par_recette = [];
            while ($tag = mysqli_fetch_assoc($req_tags)) {
                $ingredients_par_recette[$tag['id_recette']][] = $tag['nom'];
            }

            // vérifier s'il y a des recettes
            if (mysqli_num_rows($resultat) == 0) {
                echo '<div class="aucun-favori-message">Aucun favori pour le moment.<br><a href="recherche.php">Découvrir des recettes</a></div>';
            } else {
                //boucle des recettes                     
                echo '<div class="bloc-recette">';
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
                echo '</div>';
            }
        ?>
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