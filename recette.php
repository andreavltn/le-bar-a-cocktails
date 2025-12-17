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
    <link rel="stylesheet" href="css/recette.css">
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
            <li><a href="favoris.php">Consulter les favoris</a></li>
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
        <div class="bloc-retour">
            <a href="javascript:history.back()">← Retour</a>
        </div>

        <div class="bloc-recette">
            <?php
            
                // récupérer l'id de la recette dans l'url, sinon null
                // cast en int pour éviter les injections
                if (isset($_GET['id'])) {
                    $id_recette = (int)$_GET['id'];
                } else {
                    $id_recette = null;
                }

                // récupérer la recette depuis la BDD
                if ($id_recette !== null) {

                    // requête sql pour rechercher les éléments de la recette à partir d'un id
                    $sql = "SELECT id_recette, titre, ingredients, preparation FROM recettes WHERE id_recette = ?";

                    // préparation de la requête
                    $req_recettes = mysqli_prepare($dblink, $sql);

                    // si erreur : stop
                    if (!$req_recettes) {
                        die("Erreur prepare: " . mysqli_error($dblink));
                    }

                    // l'id est un entier
                    mysqli_stmt_bind_param($req_recettes, "i", $id_recette);

                    // exécuter la requête
                    mysqli_stmt_execute($req_recettes);

                    // récupérer le résultat
                    $resultat = mysqli_stmt_get_result($req_recettes);

                    // si des recettes sont trouvées
                    if ($recette = mysqli_fetch_assoc($resultat)) {

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

                        // ------------------- TITRE ET BOUTON LIKE -------------------
                        
                        echo '<div class="titre-like-container">';
                        echo '<h1 class="recette-titre">' . htmlspecialchars($recette['titre']) . '</h1>';
                        
                        // variable a true si la recet
                        $estFavori = false;

                        // si on est connecté
                        if (isset($_SESSION["id"])) {

                            // requête pour vérifier si la recette est dans les favoris
                            $sql_check = "SELECT id_recette FROM favoris WHERE id_recette = ? AND id_user = ?";
                            $req_check = mysqli_prepare($dblink, $sql_check); // préparation de la requête
                            mysqli_stmt_bind_param($req_check, "ii", $id_recette, $_SESSION["id"]); // liaison des paramètres (deux entiers)
                            mysqli_stmt_execute($req_check); // éxécution de la requête

                            // si il y a au moins un résultat, la recette fait partie des favoris de l'utilisateur
                            if(mysqli_num_rows(mysqli_stmt_get_result($req_check)) > 0) {
                                $estFavori = true;
                            }

                            mysqli_stmt_close($req_check); // fermeture de la requête
                        } else {

                            // sinon on n'est pas connecté

                            // si l'id de la recette est dans les favoris de la session
                            if(isset($_SESSION['favoris']) && in_array($id_recette, $_SESSION['favoris'])) {
                                $estFavori = true;
                            }
                        }
                        
                        // si la recette est en favoris
                        if($estFavori) {
                            $classe_like = 'bouton-like actif'; // passe en actif (ajout d'une classe)
                            $texte_like = 'Supprimer des favoris';
                        } else {
                            $classe_like = 'bouton-like'; // sinon il reste bouton-like
                            $texte_like = 'Ajouter aux favoris';
                        }

                        // ajout du bouton et maj en fonction de l'état favori ou non
                        echo '<button class="' . $classe_like . '" onclick="liker(event)" title="'. $texte_like .'">';
                        echo '<span class="icone-like">♥</span>';
                        echo '</button>';
                        echo '</div>';

                        // ------------------- INGREDIENTS -------------------
                        
                        echo '<div class="recette-section">';
                        echo '<h2>Ingrédients</h2>';
                        echo '<ul class="ingredients-liste">';
                        
                        // sépare les ingrédients par le séparateur |
                        $ingredients = explode('|', $recette['ingredients']);

                        // pour chaque ingrédient, l'ajoute à la liste
                        foreach ($ingredients as $ingredient) {
                            echo '<li>' . htmlspecialchars(trim($ingredient)) . '</li>';
                        }

                        echo '</ul>';
                        echo '</div>';

                        // ------------------- PREPARATION -------------------

                        echo '<div class="recette-section">';
                        echo '<h2>Préparation</h2>';

                        // affiche le texte de préparation
                        echo '<p class="preparation-texte">' . htmlspecialchars($recette['preparation']) . '</p>';
                        echo '</div>';

                        // ------------------- INGREDIENTS CLE (TAGS) -------------------

                        // requête sql pour rechercher les ingrédients clé à partir de l'id de la recette
                        $sql_tags = "SELECT a.nom FROM aliments a
                                     INNER JOIN ingredient_recette ir ON a.id_aliment = ir.id_ingredient
                                     WHERE ir.id_recette = ?";

                        // préparation de la requête
                        $req_tags = mysqli_prepare($dblink, $sql_tags);

                        // si erreur : stop
                        if (!$req_tags) {
                            die("Erreur prepare: " . mysqli_error($dblink));
                        }

                        // l'id est un entier
                        mysqli_stmt_bind_param($req_tags, "i", $id_recette);

                        // exécuter la requête
                        mysqli_stmt_execute($req_tags);

                        // récupérer les résultats
                        $resultat_tags = mysqli_stmt_get_result($req_tags);

                        // si il y a des tags
                        if (mysqli_num_rows($resultat_tags) > 0) {

                            echo '<div class="recette-section">';
                            echo '<h3>Ingrédients clés</h3>';
                            echo '<div class="tags-ingredients">';

                            // pour chaque tag de la recette, l'affiche dans un span
                            while ($tag = mysqli_fetch_assoc($resultat_tags)) {
                                echo '<span class="tag">' . htmlspecialchars($tag['nom']) . '</span>';
                            }

                            echo '</div>';
                            echo '</div>';
                        }

                        // fermeture de la requête tags
                        mysqli_stmt_close($req_tags);

                        echo '</div>'; // fin recette-contenu
                        echo '</div>'; // fin recette-container

                    } else {

                        // (cas ou il n'y a pas de recette associée à l'id)
                        echo '<div class="erreur-recette">';
                        echo '<p>Aucune recette trouvée.</p>';
                        echo '<a href="recherche.php" class="bouton-retour">Retour</a>';
                        echo '</div>';
                    }

                    // fermeture de la requête recette
                    mysqli_stmt_close($req_recettes);

                } else {

                    // (cas ou il n'y a pas d'id dans l'url)
                    echo '<div class="erreur-recette">';
                    echo '<p>Aucun identifiant de recette dans la recherche.</p>';
                    echo '<a href="recherche.php" class="bouton-retour">Retour</a>';
                    echo '</div>';
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

        // fonction pour gérer l'ajout/suppression d'un favori sur la page
        function liker(event) {

            // empêche le clic de remonter sur le conteneur (empêche le rechargement de la page)
            event.stopPropagation();

            // récupère l'élément bouton (celui cliqué)
            const bouton = event.currentTarget;
            
            // devientActif = true si le bouton n'est pas actif
            const devientActif = !bouton.classList.contains('actif');

            // met à jour la classe + le texte au survol
            if(devientActif){
                bouton.classList.add('actif');
                bouton.title = 'Supprimer des favoris';
            } else {
                bouton.classList.remove('actif');
                bouton.title = 'Ajouter aux favoris';
            }
            
            // envoie une demande à toggle_favorite_simple.php pour mettre à jour la bdd
            // avec en paramètre l'id de la recette à ajouter/supprimer des favoris
            fetch('update-favoris-like.php?id=<?php echo $id_recette; ?>');
        }
    </script>

</body>
</html>