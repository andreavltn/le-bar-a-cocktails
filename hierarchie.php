<!DOCTYPE html>

<?php
session_start();
require_once("connect_db.php");
?>
<head>
<html lang="fr">
<meta charset="UTF-8">
<title>Hiérarchie | Le bar à cocktails</title>
<link rel="shortcut icon" href="images/logo-site.png">
<link rel="stylesheet" href="css/structure-page.css">
<link rel="stylesheet" href="css/hierarchie.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>



<script>
    function confirmerDeconnexion() {
        if (confirm('Souhaitez vous vraiment vous déconnecter ?')) {
            window.location.href = 'account/logout.php';
        }
    }
</script>

<?php

    // ---------------------------
    // Gestion affichage recettes via sous-aliment
    // ---------------------------
    if (isset($_GET['action']) && $_GET['action'] === 'recettesavecalim' && isset($_GET['id_aliment'])) { // lance la requete si on a fait le fetch
        $id_aliment = (int)$_GET['id_aliment'];  //recup de l'id aliment

        //recup des recettes qui contiennent l'aliment
        $sql = "SELECT id_recette, titre, ingredients
                FROM recettes 
                INNER JOIN ingredient_recette USING (id_recette)
                WHERE id_ingredient = ?";
        $requete = mysqli_prepare($dblink, $sql);
        mysqli_stmt_bind_param($requete, "i", $id_aliment);
        mysqli_stmt_execute($requete);
        $result = mysqli_stmt_get_result($requete);

        //si on trouve des recettes on les affiche
        if (mysqli_num_rows($result) > 0) {
            while ($recette = mysqli_fetch_assoc($result)) {
                echo '<div class="recette-container" onclick="window.location.href=\'recette.php?id=' . $recette['id_recette'] . '\'">';
                
                $nom_image = strtolower(str_replace(' ', '_', $recette['titre']));
                $nom_image = trim(explode('(', $nom_image)[0]);
                $chemin_image = "images/" . $nom_image . ".jpg";
                if (!file_exists($chemin_image)) $chemin_image = "images/image_placeholder.jpg";

                echo '<div class="recette-image"><img src="' . $chemin_image . '" alt="' . htmlspecialchars($recette['titre']) . '"></div>';
                echo '<div class="recette-contenu">';
                echo '<h1 class="recette-titre">' . htmlspecialchars($recette['titre']) . '</h1>';
                echo '<div class="recette-section"><h2>Ingrédients</h2><ul>';
                $ingredients = explode('|', $recette['ingredients']);
                foreach ($ingredients as $ingredient) {
                    echo '<li>' . htmlspecialchars(trim($ingredient)) . '</li>';
                }
                echo '</ul></div>';
                echo '</div></div>';
            }
        } else {
            echo "<p>Aucune recette trouvée pour ce sous-aliment.</p>";
        }
        exit;
    }


    // Récupération des aliments racines et sous-aliments

    $racine = qdb("SELECT id_aliment, nom FROM aliments WHERE id_aliment NOT IN (SELECT id_sous_categorie FROM hierarchie)");

    $tablenomsousalim = qdb("SELECT h.id_aliment AS base, h.id_sous_categorie AS idsousaliment, a.nom AS nomsousaliment
                            FROM hierarchie h JOIN aliments a ON a.id_aliment = h.id_sous_categorie
                            ORDER BY a.nom ASC");

    $arraynomsousalim = [];
    foreach ($tablenomsousalim as $i) {
        $arraynomsousalim[$i['base']][] = [
            'id' => $i['idsousaliment'],
            'nom' => $i['nomsousaliment']
        ];
    }
?>

<body>

<header class="banniere">
    <div class="banniere-bloc-gauche">
        <a href="index.php"><img src="images/logo-site.png" alt="logo-site.png"></a>
    </div>

    <div class="banniere-bloc-milieu">
        <li><a href="index.php">Accueil du site</a></li>
        <li><a href="decouvrir-recettes.php">Liste des recettes</a></li>
        <li><a href="recherche.php">Rechercher une recette</a></li>
        <li class="page-en-cours">Rechercher par hiérarchie</a></li>
        <li><a href="favoris.php">Consulter les favoris</a></li>
        <?php if (isset($_SESSION["pseudo"])) {
            echo "<li><button class='bouton-deconnexion' onclick='confirmerDeconnexion()'>Se déconnecter</button></li>";
        } ?>
    </div>

    <div class="banniere-bloc-droite">
        <img src="images/logo-compte.png" alt="logo-compte.png">
        <?php
        if (isset($_SESSION["pseudo"])) {
            echo "<span>" . $_SESSION["pseudo"] . "</span>";
        } else {
            echo "<a href='account/login.php'>Connexion</a>";
        }
        ?>
    </div>
</header>




<script>
    const racine = <?= json_encode($racine) ?>;
    const hierarchie = <?= json_encode($arraynomsousalim) ?>;  // transfert des 2 requetes en js


    // Fonction pour afficher les sous-aliments

    function afficherSousaliment(id, boite) {
        const listSA = hierarchie[id];  // recup les sous aliments
        boite.innerHTML = "";  //clear le niveau

        if (!listSA) return;    // si aucun sous alim on return

        const suivant = document.createElement('div');  // crée le conteneur d'en dessous
        const boutonalim = document.createElement('div'); // recrée le niveau actuel
        boutonalim.style.margin = "10px 0";

        listSA.forEach(sousal => {
            const bouton = document.createElement('button');
            bouton.textContent = sousal.nom;
            bouton.style.margin = "0 10px 10px 0";
            bouton.dataset.id = sousal.id;

            bouton.onclick = () => {
                afficherSousaliment(sousal.id, suivant);

                // Recolorie tous les boutons du même niveau
                Array.from(boutonalim.children).forEach(b => b.style.backgroundColor = "#f2f2f2");

                // Colorie le bouton cliqué
                bouton.style.backgroundColor = hierarchie[sousal.id] ? "#FFD700" : "#15ac98ff";

                // Affiche les recettes liées à ce sous-aliment
                afficherRecettesSousAliment(sousal.id);
            }

            boutonalim.appendChild(bouton);
        });

        boite.appendChild(boutonalim);
        boite.appendChild(suivant);
    }

    // ---------------------------
    // Fonction pour afficher les recettes
    // ---------------------------
    function afficherRecettesSousAliment(id_sousaliment) {
        const conteneur = document.getElementById('recettes');
        conteneur.innerHTML = "";  // enleve les anciennes recettes

        fetch('?action=recettesavecalim&id_aliment=' + id_sousaliment)
            .then(response => response.text())
            .then(html => conteneur.innerHTML = html)  //met l'affichage au bon endroit
            .catch(err => conteneur.innerHTML = "<p>Erreur lors du chargement des recettes</p>");
    }
</script>



<section class="contenu">
    <h2>Arborescence des aliments</h2>

    <!-- AFFICHAGE DES BOUTONS A LA BASE -->
    <div id="div_racines">
    <?php foreach ($racine as $rac) { ?>   
        <button 
            class="boutonRacine" 
            data-id="<?php echo $rac['id_aliment']; ?>"
            onclick="
                this.style.backgroundColor='#FFD700';  //passe dorés si on clic dessus , na change jamais vu qu'il y en a que 1 
                afficherSousaliment('<?php echo $rac['id_aliment']; ?>', document.getElementById('arborecence'));  //affiche les sous elem de la racine
                afficherRecettesSousAliment('<?php echo $rac['id_aliment']; ?>');  // affiche les recettes qui contiennent la racine (aucune normalement)
            ">
            <?php echo htmlspecialchars($rac['nom']) ?>
        </button>
    <?php } ?>
    </div>

 <!-- CONTENEUR ARBO -->
    <div id="arborecence"></div>
 <!-- CONTENEUR RECETTES -->
    <h2>Recettes contenant le sous-aliment sélectionné</h2>
    <div id="recettes" class="bloc-recette"></div>
</section>

</body>
</html>
