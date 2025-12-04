<?php
//include de la db avec qdb(requete) 
include 'connect_db.php';

//recupere tout les aliments
$liste_aliment = qdb("SELECT id_aliment, nom FROM aliments ORDER BY nom ASC");

//recupere toutes les boissons
$liste_boisson = qdb("SELECT id_recette, titre FROM recettes ORDER BY titre ASC");



// --------------- REQUETE DE LA BARRE DE RECHERCHE DES ALIMENTS ---------------- //

$resultatsingredient = null;
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["recherche"])) {

    $rechercheingredient = mysqli_escape_string($dblink, $_POST["recherche"]);   

    $request = "SELECT * FROM aliments WHERE true ";    //true pour pouvoir mettre and dans tout les if

    if (!empty($rechercheingredient)) {
        $mots = explode(" ", $rechercheingredient);   // separe les mots de la recherche
        foreach ($mots as $i){
            $i = trim($i);                //enleve les espaces
            if ($i !== "") {              // ne prend pas en compte les chaines vides

            $request .= " AND nom LIKE '%$i%'";    //recherche les ingredients avec le mot demander dedans
        }}}


    $request .= " ORDER BY nom ASC";
    $resultatsingredient = qdb($request);
}


// --------------- REQUETE DE LA BARRE DE RECHERCHE DES BOISSONS ---------------- //
$resultatsboisson = null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["rechercheboisson"])) {

    $rechercheboisson = mysqli_escape_string($dblink, $_POST["rechercheboisson"]);   

    $request = "SELECT * FROM recettes WHERE true ";    //true pour pouvoir mettre and dans tout les if

    if (!empty($rechercheboisson)) {
        $mots = explode(" ", $rechercheboisson);   // separe les mots de la recherche
        foreach ($mots as $i){
            $i = trim($i);                //enleve les espaces
            if ($i !== "") {              // ne prend pas en compte les chaines vides

            $request .= " AND titre LIKE '%$i%'";    //recherche les ingredients avec le mot demander dedans
        }}}


    $request .= " ORDER BY titre ASC";
    $resultatsboisson = qdb($request);
}
?>




<!-- -------------------------- -->



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche d'aliments</title>

    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .result { padding: 10px; margin-top: 10px; background: #f3f3f3; border-radius: 4px; }
        .form-control { padding: 6px; margin-right: 5px; }
    </style>
</head>
<body>


<!-- -------------RECHERCHE DES ALIMENTS ------------- -->


<h2>Recherche d'aliments</h2>

<form method="POST"  id="recherche">
    <input type="hidden" name="type" value="aliment">
    <input list="aliments" name="recherche" class="form-control" placeholder="Rechercher un aliment...">

    <datalist id="aliments">                                    
        <?php while ($a = mysqli_fetch_assoc($liste_aliment)):?>    <!-- parcours la liste les aliments-->
                  
            <option value="<?= htmlspecialchars($a['nom']) ?>"> <!-- remplit la datalist avec les nom des ingredients -->
        <?php endwhile; ?>
    </datalist>
    <button type="submit">Rechercher</button>  
</form>
<hr>



<!-- Création de la grille qui affiche les aliments de la recherche -->


<div class="stylegrille" id = GrilleAliments >
    <?php if (!empty($resultatsingredient)): ?>
        <?php while ($row = mysqli_fetch_assoc($resultatsingredient)): ?>   
            <div class="stylegrilleinter"  id="<?= htmlspecialchars($row['id_aliment']) ?>"  onclick="alert(id)">


                <strong><?= htmlspecialchars($row['nom']) ?></strong><br>  <!-- contient les ingredients qui correspondent à la requete  -->
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>



<div style="display: flex; align-items: center; cursor: pointer;" id="affichageMenuAliments">
    <h3 id="texteFleche" style="margin-right: 10px;">Masquer</h3>
    <span id="fleche" style="transition: transform 0.3s;">&#9650;</span>
</div>





<!-- -------------RECHERCHE DES Boissons ------------- -->


<h2>Recherche de Boisson</h2>

<form method="POST"  id="rechercheboisson">    
    <input type="hidden" name="type" value="boisson">  
    <input list="boissons" name="rechercheboisson" class="form-control" placeholder="Rechercher une boisson ">

    <datalist id="boissons">                                    
        <?php while ($a = mysqli_fetch_assoc($liste_boisson)):?>    <!-- parcours la liste des boissons -->
                  
            <option value="<?= htmlspecialchars($a['titre']) ?>"> <!-- remplit la datalist avec les nom des boissons -->
        <?php endwhile; ?>
    </datalist>
    <button type="submit">Rechercher</button>  
</form>
<hr>



<!-- Création de la grille qui affiche les boissons de la recherche -->


<div class="stylegrilleboissons" id = GrilleBoissons >
    <?php if (!empty($resultatsboisson)): ?>
        <?php while ($row = mysqli_fetch_assoc($resultatsboisson)): ?>   
            <div class="stylegrilleinterboissons"  id="<?= htmlspecialchars($row['id_recette']) ?>"  onclick="alert(id)">


                <strong><?= htmlspecialchars($row['titre']) ?></strong><br>  <!-- contient les boissons qui correspondent à la requete  -->
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>



<div style="display: flex; align-items: center; cursor: pointer;" id="affichageMenuBoisson">
    <h3 id="texteFlecheboisson" style="margin-right: 10px;">Masquer</h3>
    <span id="flecheboisson" style="transition: transform 0.3s;">&#9650;</span>
</div>







<!-- -----------GESTION DES STYLES ----------- -->

<style>

#recherche {
    
    max-height: 60px;
    transition: max-height 0.3s ease;
}

#recherche.cache {
    
    max-height: 0;
    overflow: hidden;
}


.stylegrille {
    max-height: 280px; /* hauteur approximative de 4 cases de 50px + gap */
    overflow-y: auto;  /* scroll si plus de 4 cases */
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 16px;
    margin-top: 10px;
    transition: max-height 0.3s ease;  /* temps de la transi entre les 2 max height */
}

.stylegrille.cache {
    max-height: 0;
    overflow: hidden;   /*cache ce qui depasse*/
}

.stylegrilleinter {
                display: flex;     /* flex pour pouvoir gerer la case plus facilement */
                flex-direction: column; 
                justify-content: center;   /* centrage vertical */
                align-items: center;       /* centrage horizontal */
                height: 50px;             /* hauteur d'une boite */
                border: 1px solid #eb2626ff; /* couleur bordure*/
                padding: 10px;   /* espace entre la boite et le texte*/
                border-radius: 5px; /* arrondi coin */
                box-shadow: 0 0 5px rgba(116, 24, 24, 0.1); /* couleur ombre*/
                background-color: #06ddb9ff;   /* couleur boite */
                transition: transform 0.2s, background-color 0.2s; /* temps de l'animation du mouseover */
}

.stylegrilleinter:hover {
    transform: translateY(-5px);
    background-color: #633ed2ff;

}

#rechercheboisson {
    
    max-height: 60px;
    transition: max-height 0.3s ease;
}

#rechercheboisson.cache {
    
    max-height: 0;
    overflow: hidden;
}


.stylegrilleboissons {
    max-height: 500px; /* hauteur approximative de 4 cases de 50px + gap */
    overflow-y: auto;  /* scroll si plus de 4 cases */
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 16px;
    margin-top: 10px;
    transition: max-height 0.3s ease;  /* temps de la transi entre les 2 max height */
}

.stylegrilleboissons.cache {
    max-height: 0;
    overflow: hidden;   /*cache ce qui depasse*/
}

.stylegrilleinterboissons {
                display: flex;     /* flex pour pouvoir gerer la case plus facilement */
                flex-direction: column; 
                justify-content: center;   /* centrage vertical */
                align-items: center;       /* centrage horizontal */
                height: 50px;             /* hauteur d'une boite */
                border: 1px solid #eb2626ff; /* couleur bordure*/
                padding: 10px;   /* espace entre la boite et le texte*/
                border-radius: 5px; /* arrondi coin */
                box-shadow: 0 0 5px rgba(116, 24, 24, 0.1); /* couleur ombre*/
                background-color: #06ddb9ff;   /* couleur boite */
                transition: transform 0.2s, background-color 0.2s; /* temps de l'animation du mouseover */
}

.stylegrilleinterboissons:hover {
    transform: translateY(-5px);
    background-color: #633ed2ff;
}

</style>




<!-- ----------------------- PARTIE JS ------------------ -->




<script>
const affichageMenuAliments = document.getElementById('affichageMenuAliments');
const texteFleche = document.getElementById('texteFleche'); 
const recherche = document.getElementById('recherche');
const menuGrille = document.getElementById('GrilleAliments');
const fleche = document.getElementById('fleche');

affichageMenuAliments.addEventListener('click', () => {       //S'active si on clic sur la fleche 
    menuGrille.classList.toggle('cache');                 // les styles changent et cachent la recherche
    recherche.classList.toggle('cache');


    if (menuGrille.classList.contains('cache')) {
        fleche.style.transform = 'rotate(180deg)';   // tourne la fleche
        texteFleche.textContent = 'Afficher';       //change le texte
    } else {
        fleche.style.transform = 'rotate(0deg)';
        texteFleche.textContent = 'Masquer';
    }
});



const affichageMenuBoisson = document.getElementById('affichageMenuBoisson');
const texteFlecheBoisson = document.getElementById('texteFlecheboisson');
const rechercheBoisson = document.getElementById('rechercheboisson');
const menuGrilleBoisson = document.getElementById('GrilleBoissons');
const flecheBoisson = document.getElementById('flecheboisson');

affichageMenuBoisson.addEventListener('click', () => {
    menuGrilleBoisson.classList.toggle('cache');
    rechercheBoisson.classList.toggle('cache');
    if (menuGrilleBoisson.classList.contains('cache')) {
        flecheBoisson.style.transform = 'rotate(180deg)';
        texteFlecheBoisson.textContent = 'Afficher';
    } else {
        flecheBoisson.style.transform = 'rotate(0deg)';
        texteFlecheBoisson.textContent = 'Masquer';
    }
});

</script>

</body>
</html>
