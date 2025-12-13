<?php
include 'connect_db.php';

// recuper les données des tables et le met dans des tableaux


$liste_aliment = qdb("SELECT id_aliment, nom FROM aliments ORDER BY nom ASC");
$aliments = [];
while ($aliment = mysqli_fetch_assoc($liste_aliment)) $aliments[] = $aliment;



$hierarchie_aliments = qdb("SELECT id_aliment, id_sous_categorie FROM hierarchie");
$hierarchie = [];
while ($hie = mysqli_fetch_assoc($hierarchie_aliments)) $hierarchie[] = $hie;


$liste_boisson = qdb("SELECT id_recette, titre FROM recettes ORDER BY titre ASC");
$boissons = [];
while ($boisson = mysqli_fetch_assoc($liste_boisson)) $boissons[] = $boisson;


$recette = qdb("SELECT id_recette, id_ingredient FROM ingredient_recette");
$contenu_recette = [];
while ($alim = mysqli_fetch_assoc($recette)) $contenu_recette [] = $alim;

?>



<!DOCTYPE html>
<html lang="fr">


<head>
    <meta charset="UTF-8">
    <title>Recette - Le bar à cocktails</title>
    <link rel="shortcut icon" href="images/logo-site.png">
    <link rel="stylesheet" href="css/accueil.css">
    <link rel="stylesheet" href="css/recherche.css">
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
            <li><a href="#">Consulter les favoris</a></li>
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




<div id="conteneurPrincipal">



    <!-- Menu aliments -->
    <div id="conteneurAliments">
        <h2 style="text-align: center;">Personnalisez votre recette !</h2>

        <input type="text" id="rechercheAliment" class="form-control rechercheAliment" list="aliments" placeholder="Rechercher un aliment...">  
        <datalist id="aliments">
            <?php foreach ($aliments as $a): ?>
                <option value="<?= htmlspecialchars($a['nom']) ?>"></option>
            <?php endforeach ?>
        </datalist>

        <div style="display: flex; gap: 2px; margin: 2px 2px;">
            <button id="bouttonRechercheAlim">Rechercher</button>
            <button id="bouttonReset">Reset</button>
        </div>

        <div id="GrilleAliments" class="stylegrille"></div>
    </div>


    <div id="conteneurBoissons">
        <h2 style="text-align: center;">Trouvez celle qui vous convient</h2>
        <div id="GrilleBoissons" class="stylegrilleboissons"></div>

                

    </div>


</div>

<script>
// convertit les resultats des select pour js //
const aliments = <?= json_encode($aliments) ?>;   // >> id_aliment, nom //
const boissons = <?= json_encode($boissons) ?>;    // >> id_recette, titre //
const hierarchie = <?= json_encode($hierarchie) ?>;   // >> id_aliment, id_sous_categorie //
const recette = <?= json_encode($contenu_recette) ?>;  // >> id_recette, id_ingredient //

const recetteMap = new Map();     //crée la map id_recette >> liste ingredients
const hierarchiemap = new Map();     //crée la map id_ingredient >> liste sous ingredients



recette.forEach(rec => {
    if (!recetteMap.has(rec.id_recette)) {
        recetteMap.set(rec.id_recette, []);   //on set la clée si elle n'existe pas
    }
    recetteMap.get(rec.id_recette).push(rec.id_ingredient); 
});



hierarchie.forEach(ingr => {
    if (!hierarchiemap.has(ingr.id_aliment)) {
        hierarchiemap.set(ingr.id_aliment, []);   //on set la clée si elle n'existe pas
    }
    hierarchiemap.get(ingr.id_aliment).push(ingr.id_sous_categorie); 
});




let superalimentsSelect= new Map();       // Map des Super alim Selectionnés super id alim -> sous id alim
let sousalimentsSelect= [];              //tableau des sous aliments selectionés via les super aliments

let superalimentsInterdits= new Map();     // Map des Super alim Interdits super id alim -> sous id alim
let sousalimentsInterdits= [];           //tableau des sous aliments Interdits via les super aliments interdts

let alimentsSelect= new Set();        //liste des aliments directement selectionés
let alimentsInterdit= new Set();      //liste des aliments directement interdits


let sousalimentsNettoyage= new Set();           //liste des sous aliments a mettre a jours
let sousalimentsInterditsNettoyage= new Set();  //liste des sous aliments a mettre a jours 






function afficherGrilleAliments(ali, idgrille) {             // Affiche la grille contenant les aliments et ajoute tous les aliments dedans
    const container = document.getElementById(idgrille);
    container.innerHTML = "";
    ali.forEach(aliment => {
        const div = document.createElement("div");
        div.className =  "stylegrilleinter";
        div.id = "alim-" + aliment.id_aliment;
        div.dataset.id_aliment = aliment.id_aliment;
        div.innerHTML = `<strong>${aliment.nom }</strong>`;
        div.onclick = () => {
            sousaliments(div.dataset.id_aliment);                                     // lance la fonction qui met l'aliment dans la liste voulue
            calculerScoresTableau();                                  // Calcule le score pour toutes les recettes
            rafraichirAffichageAliments(div.id);                      // modifie la couleur et l'ordre des cases
            };
        container.appendChild(div);
        });  
}


//Fonction qui change la couleur d"une case aliment si on clic dessus , change egalement les cases dependantes

function rafraichirAffichageAliments(Grilleid) {
    const grillea = document.getElementById(Grilleid);             //recupere la case
    if (!grillea) return;

    const idAlim = grillea.dataset.id_aliment;             //recupere l'id de l'aliment


    //parcours des aliments à recolorer
    sousalimentsNettoyage.forEach(id => {

        const sousalim = document.getElementById("alim-"+id); 

        //Test si une case jaune doit etre recolorés
        if (sousalim && sousalim.classList.contains("jaune")) { 
            sousalim.classList.remove("jaune");
            sousalim.classList.add("base"); }
        sousalimentsNettoyage.delete(id); });
                 
    //parcours des aliments à recolorer
    sousalimentsInterditsNettoyage.forEach(id => { 
        const sousalim = document.getElementById("alim-"+id);
        //Test si une case rouge doit etre recolorés
        if (sousalim && sousalim.classList.contains("rougesa") ) { 
            sousalim.classList.remove("rougesa");
            sousalim.classList.add("base"); }
        sousalimentsInterditsNettoyage.delete(id); });


    
    //Reset de la couleur de la case cliqué        
    grillea.classList.remove("vert", "jaune", "base", "rouge","rougesa");


    //check si la case est un super aliment et change ses sous aliment en plus
    if (superalimentsSelect.has(idAlim)) {
        grillea.classList.add("vert"); 

        superalimentsSelect.get(idAlim).forEach(val => {
            let sousal = document.getElementById("alim-"+val);
                if (sousal && !sousal.classList.contains("vert")) {
                    sousal.classList.remove("vert", "jaune", "base", "rouge","rougesa");
                    sousal.classList.add("jaune"); 
                }
            });
        return;
    } 

    //check si la case est un super aliment et change ses sous aliment en plus
    if (superalimentsInterdits.has(idAlim)) {
        grillea.classList.add("rouge"); 

        superalimentsInterdits.get(idAlim).forEach(val => {
            let sousal = document.getElementById("alim-"+val);
            if (sousal && !sousal.classList.contains("vert")&& !sousal.classList.contains("jaune")&& !sousal.classList.contains("rouge")) {
                sousal.classList.remove("vert", "jaune", "base", "rouge");
                sousal.classList.add("rougesa"); 
                }
            });
        return;
    }

    //check si la case est un aliment interdit
    if (alimentsInterdit.has(idAlim)) {
        grillea.classList.add("rouge"); 
    return;
    }
    //check si la case est un aliment selectioné
    if (alimentsSelect.has(idAlim)) {
        grillea.classList.add("vert"); 
        return;
    } 
    if (sousalimentsSelect.includes(idAlim)) {
        grillea.classList.add("jaune"); 
        return;
    }    
    if (sousalimentsInterdits.includes(idAlim)) {
        grillea.classList.add("rougesa"); 
        return;
    }            
    grillea.classList.add("base");
        

}




function afficherGrilleBoissons(rct, grilleboi) {
    const grille = document.getElementById(grilleboi);
    grille.innerHTML = "";
    rct.forEach(item => {
        const div = document.createElement("div");
        div.className = "stylegrilleinterboissons";
        div.id = item.id_recette;
        div.innerHTML = `<strong>${item.titre}</strong>`;
        div.style.order = 50;
        div.onclick = () => {
            window.location.href = "recette.php?id=" + item.id_recette;
        };

        grille.appendChild(div); 
    });
}


// Filtre les aliments afficher en fonction de la barre de recherche
function filtrerAliments() {
    const recherche = rechercheAliment.value.trim().toLowerCase();
    const mots = recherche.split(" ").filter(m => m);

    // Filtrer les aliments dont le nom contient tous les mots recherchés
    const resultat = aliments.filter(aliment =>
        mots.every(m => aliment.nom.toLowerCase().includes(m))
    );

    const container = document.getElementById("GrilleAliments");
    const divs = Array.from(container.children);
    divs.forEach(div => {
        if (!resultat.some(item => item.id_aliment == div.dataset.id_aliment)) {
            div.style.display = "none"; // masquer
        } else {
            div.style.display = "flex"; 
        }
    });

    return resultat.map(a => a.id_aliment);
}



//crée les tableaux des aliments selectionés
function ajouterAlimentSelect(id) {
  if (alimentsSelect.has(id)) {
    alimentsSelect.delete(id);
    alimentsInterdit.add(id);
  } 
   else if (alimentsInterdit.has(id)) {
    alimentsInterdit.delete(id);
  }
  else {
    alimentsSelect.add(id);
  }
}


function recupSousalim(id){
    const result = [];
    function boucle(key) {
        const children =  hierarchiemap.get(key);
        if (!children) return;
        for (const child of children) {
            result.push(child);
            boucle(child); // descendre dans la hiérarchie
        }
    }

    boucle(id);
return result;
}


function ajouterSuperAlimentSelect(id) {
    let sousalim = [...recupSousalim(id)]
    if (!superalimentsSelect.has(id) && !superalimentsInterdits.has(id)) {

       superalimentsSelect.set(id,sousalim);
       sousalimentsSelect.push(...sousalim);
    }
    else if (superalimentsSelect.has(id)) {

        superalimentsInterdits.set(id,sousalim);
        sousalimentsInterdits.push(...sousalim);
        superalimentsSelect.delete(id);

        sousalim.forEach(val => {
            let index = sousalimentsSelect.indexOf(val);
            if (index !== -1) {
                sousalimentsSelect.splice(index, 1);
            }
            if (!sousalimentsSelect.includes(val)) {
            sousalimentsNettoyage.add(val);}

        });
    }
    else{
        superalimentsInterdits.delete(id);
        sousalim.forEach(val => {
            let index = sousalimentsInterdits.indexOf(val);
            if (index !== -1) {
                sousalimentsInterdits.splice(index, 1);
            }
            if (!sousalimentsInterdits.includes(val)) {
                sousalimentsInterditsNettoyage.add(val);}

        });
}

}





// Calcule le score d'une boisson selon les ingredients
function calculerScoresTableau() {
    const scores = [];
    let score;
    let total;
    boissons.forEach(b => {

        const ingredients =  new Set(recetteMap.get(b.id_recette));  // liste des ingredients pour la recette b
        // pour calculer le pourcentage
        score = 0;
        total = 0;


        // augmente le score si un aliment selection est dans la recette
        alimentsSelect.forEach(id => {
            if (ingredients.has(id)) 
                {score ++;}
            total ++;});

         // augmente le score si un aliment interdit  n'est pas dans la recette
        alimentsInterdit.forEach(id => {
            if (!ingredients.has(id)) 
                {score ++;}
            total ++;});


        //augmente le score si un des sous aliment est  dans la recette
        Array.from(superalimentsSelect.values()).forEach(sousAlim => {
            if (sousAlim.some(ingr => ingredients.has(ingr))) {
                score ++;}
            total ++;});

        //diminue le score si un sous aliment interdits est dans la recette
        Array.from(superalimentsInterdits.values()).forEach(sousAlim => {
            if (sousAlim.some(ingr => ingredients.has(ingr))) {
                total +=1;}
            });



        if (total == 0){
            score = 100;
        }
        else {
            score = (100*score)/total; 
        }

        scores.push({ id_recette: b.id_recette,  titre:b.titre , score : score });
    });

    // Trier par score décroissant
    scores.sort((a, b) => b.score - a.score);
    
    
    afficherGrilleBoissons(scores, "GrilleBoissons");
    const container = document.getElementById("GrilleBoissons");
    const divs = Array.from(container.children);

divs.forEach(div => {
    
    const item = scores.find(i => i.id_recette == div.id);
    if (!item) return; 
    const span = document.createElement("span");
    span.classList.add("texte-pourcent");
    span.textContent = Math.round(item.score) + "%";
    div.appendChild(span);

    div.classList.remove("vert", "jaune", "orange", "rouge");

    if (item.score >= 99) div.classList.add("vert");
    else if (item.score >= 60) div.classList.add("jaune");
    else if (item.score >= 1) div.classList.add("orange");
    else div.classList.add("rouge");



});

}



// differentie les aliments basiques des super aliments
function sousaliments(id) {
    if (hierarchiemap.has(id)) {
        ajouterSuperAlimentSelect(id);
    } else {
        ajouterAlimentSelect(id); // si pas de sous-aliments, ajouter juste l'id
    }
}



//ecouteur sur le bonton rechercher
document.getElementById("bouttonRechercheAlim").onclick = () => {
    filtrerAliments(); 
};

document.getElementById("bouttonReset").onclick = () => {
    superalimentsSelect.clear();       
    superalimentsInterdits.clear();    
    sousalimentsSelect = [];             
    sousalimentsInterdits = [];
    alimentsSelect.clear();        
    alimentsInterdit.clear();  
    afficherGrilleAliments(aliments, "GrilleAliments");
    afficherGrilleBoissons(boissons, "GrilleBoissons");
};

// Affichage initial (tout)
afficherGrilleAliments(aliments, "GrilleAliments");
afficherGrilleBoissons(boissons, "GrilleBoissons");


</script>

</body>
</html>
