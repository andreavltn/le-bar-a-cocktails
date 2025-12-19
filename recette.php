<!DOCTYPE html>

<?php
    session_start();
    require_once("connect_db.php");

    // récupérer l'id de la recette dans l'url, sinon null
    // cast en int pour éviter les injections
    if (isset($_GET['id'])) {
        $id_recette = (int)$_GET['id'];
    } else {
        $id_recette = null;
    }

    // récupérer la recette depuis la BDD si l'id est dans l'url
    if ($id_recette != null) {

        // requête sql pour récupérer les éléments de la recette à partir d'un id
        $sql = "SELECT id_recette, titre, ingredients, preparation FROM recettes WHERE id_recette = ?";
        $req_recettes = mysqli_prepare($dblink, $sql); // préparation de la requête
        mysqli_stmt_bind_param($req_recettes, "i", $id_recette); // liaison du paramètre (entier)
        mysqli_stmt_execute($req_recettes); // exécuter la requête
        $resultat = mysqli_stmt_get_result($req_recettes); // récupérer le résultat

        // récupération des résultats (null si il n'y a rien)
        if (!$recette = mysqli_fetch_assoc($resultat)) {
            $recette = null;
        }

        // fermeture de la requête
        mysqli_stmt_close($req_recettes);

    } else {

        // sinon par sécurité on met à null (ca ne devrait pas arriver)
        $recette = null; 
    }
?>

<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>
        <?php

            // si la recette existe
            if ($recette != null) {

                // récupérer uniquement le nom (sans les textes supplémentaires)
                $titre = $recette['titre'];
                $titre = explode(':', $titre)[0];
                $titre = explode('(', $titre)[0];
                $titre = trim($titre);

                echo htmlspecialchars($titre); // affiche le titre de la recette comme nom d'onglet

            } else {
                
                echo "Recette introuvable"; // sinon "recette introuvable"
            }
        ?>
    </title>
    <link rel="shortcut icon" href="images/logo-site.png">
    <link rel="stylesheet" href="css/structure-page.css">
    <link rel="stylesheet" href="css/recette.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body>
    <header class="banniere">
        <div class="banniere-bloc-gauche">
            <a href="index.php"><img src="images/logo-site.png" alt="logo-site.png"></a>
        </div>

        <div class="banniere-bloc-milieu">
            <li><a href="index.php">Accueil du site</a></li>
            <li><a href="decouvrir-recettes.php">Liste des recettes</a></li>
            <li><a href="recherche.php">Rechercher une recette</a></li>
            <li><a href="hierarchie.php">Rechercher par hiérarchie</a></li>
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

                // si une recette est trouvée
                if ($recette !== null) {

                   echo '<div class="recette-container">';

                    // ------------------- IMAGE -------------------

                    echo '<div class="recette-image">';

                    // créer le nom de l'image (pour la retrouver dans le dossier images)
                    // en minuscules, et remplace les espaces par des _
                    $nom_image = strtolower(str_replace(' ', '_', $recette['titre']));

                    // extraire seulement le nom avant les parenthèses (si il y en a)
                    $nom_image = explode('(', $nom_image)[0];

                    // enlever les espaces/caractères vides au début et à la fin
                    $nom_image = trim($nom_image);

                    // création du chemin du fichier
                    $chemin_image = "images/" . $nom_image . ".jpg";

                    // vérifier si l'image existe, sinon utilise l'image placeholder 
                    if (!file_exists($chemin_image)) {
                        $chemin_image = "images/image_placeholder.jpg";
                    }

                    // affiche l'image 
                    echo '<img src="' . $chemin_image . '" alt="' . htmlspecialchars($recette['titre']) . '">';
                    echo '</div>';

                    echo '<div class="recette-contenu">';

                    // ------------------- TITRE ET BOUTON LIKE -------------------

                    // affichage au chargement de la page (initialisation)

                    // variable a true si la recette est déjà dans les favoris
                    $estFavori = false;

                    // si on est connecté
                    if (isset($_SESSION["id"])) {

                        // requête pour vérifier si la recette est dans les favoris
                        $sql_check = "SELECT id_recette FROM favoris WHERE id_recette = ? AND id_user = ?";
                        $req_check = mysqli_prepare($dblink, $sql_check); // préparation de la requête
                        mysqli_stmt_bind_param($req_check, "ii", $id_recette, $_SESSION["id"]); // liaison des paramètres (deux entiers)
                        mysqli_stmt_execute($req_check); // éxécution de la requête

                        // si il elle y est
                        if(mysqli_num_rows(mysqli_stmt_get_result($req_check)) > 0) {
                            $estFavori = true; // met à jour la variable
                        }

                        mysqli_stmt_close($req_check); // fermeture de la requête

                    } else {

                        // sinon on n'est pas connecté

                        // si l'id de la recette est dans les favoris de la session
                        if(isset($_SESSION['favoris']) && in_array($id_recette, $_SESSION['favoris'])) {
                            $estFavori = true; // met à jour la variable
                        }
                    }

                    // si la recette est en favoris 
                    if($estFavori) {

                        $classe_like = 'image-like actif'; // ajout de la classe "actif"
                        $texte_like = 'Supprimer des favoris'; // change le texte au survol
                        $image_like = 'images/logo-etoile-jaune.png'; // affiche l'étoile en jaune
                    } else { 
                        
                        // sinon elle n'est pas en favori
                        $classe_like = 'image-like'; // sinon il reste bouton-like
                        $texte_like = 'Ajouter aux favoris'; // change le texte au survol
                        $image_like = 'images/logo-etoile-blanc.png'; // change l'étoile en blanc
                    }

                    // affiche le nom de la recette et l'étoile de favoris
                    echo "<div class='titre-like'>
                            <h1 class='recette-titre'>" . htmlspecialchars($recette['titre']) . "</h1>
                            <img src='" . $image_like . "'
                                class='" . $classe_like . "'
                                onclick='liker(event)'
                                title='" . $texte_like . "'>
                        </div>";

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

                    $req_tags = mysqli_prepare($dblink, $sql_tags); // préparation de la requête
                    mysqli_stmt_bind_param($req_tags, "i", $id_recette); // liaison du paramètre (entier)
                    mysqli_stmt_execute($req_tags); // exécution la requête
                    $resultat_tags = mysqli_stmt_get_result($req_tags); // récupérer les résultats

                    // si il y a des tags
                    if (mysqli_num_rows($resultat_tags) > 0) {

                        echo '<div class="recette-section">';
                        echo '<h3>Ingrédients clés</h3>';
                        echo '<div class="tags-ingredients">';

                        // pour chaque tag de la recette, l'affiche dans un span
                        while ($tag = mysqli_fetch_assoc($resultat_tags)) {
                            echo '<span class="tag">' . htmlspecialchars($tag['nom']) . '</span>';
                        }

                        echo '</div>'; // tag-ingredients
                        echo '</div>'; // recette-section
                    }

                    // fermeture de la requête tags
                    mysqli_stmt_close($req_tags);

                    echo '</div>'; // recette-contenu
                    echo '</div>'; // recette-container

                } else {

                    // cas ou il n'y a pas de recette associée à l'id : message avertissement
                    echo '<div class="erreur-recette">';
                    echo '<p>Aucune recette trouvée.</p>';
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

        // fonction appelée au clic sur l'étoile (mettre en favori/retirer des favoris une recette) :
        function liker(event) {

            // empêche la propagation (pour éviter la redirection vers la recette)
            // qui vient de l'élément parent (la carte)
            event.stopPropagation(); 

            const img = event.currentTarget; // récupère l'image cliquée
            const devientActif = !img.classList.contains('actif'); // true si le clic ajoute aux favoris

            // si le clic va ajouter la recette aux favoris
            if(devientActif){

                img.classList.add('actif'); // ajoute la classe "actif" à l'image
                img.title = 'Supprimer des favoris'; // change le texte au survol
                img.src = 'images/logo-etoile-jaune.png'; // l'étoile devient jaune

            } else { // sinon le clic va supprimer la recette des favoris

                img.classList.remove('actif'); // retire la classe actif à l'image
                img.title = 'Ajouter aux favoris'; // change le texte au survol
                img.src = 'images/logo-etoile-blanc.png'; // l'étoile devient blanche
            }

            // requête à update-favoris.php en arrière-plan pour mettre à jour la bdd/la session
            fetch('update-favoris.php?id=<?php echo $id_recette; ?>');

            // popup de confirmation
            if(devientActif){
                alert('La recette a été ajoutée aux favoris!');
            } else {
                alert('La recette a été supprimée des favoris!');
            }
        }
    </script>

</body>
</html>
