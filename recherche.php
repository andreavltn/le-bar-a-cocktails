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
<title>Recherche</title>

</head>
<body>

<h2>Recherche d'aliments</h2>


 <!-- barre de recherche des aliments -->

<input type="text" id="rechercheAliment" class="form-control  rechercheAliment" list="aliments" placeholder="Rechercher un aliment...">  
<datalist id="aliments">
    <?php foreach ($aliments as $a): ?>
        <option value="<?= htmlspecialchars($a['nom']) ?>"></option>
    <?php endforeach ?>
</datalist>
<button id="bouttonRechercheAlim" style="padding:6px 12px;cursor:pointer;">
        Rechercher
    </button>

<div id="GrilleAliments" class="stylegrille"></div>

<div id="affichageMenuAliments" style="display:flex;align-items:center;cursor:pointer;">
    <h3 id="texteFleche">Masquer</h3>
    <span id="fleche" style="margin-left:10px;transition:.3s;">&#9650;</span>
</div>

<hr>

<h2>Affichage boissons</h2>



<div id="GrilleBoissons" class="stylegrilleboissons"></div>



<script>
// convertit les resultats des select pour js //
const aliments = <?= json_encode($aliments) ?>;   // >> id_aliment, nom //
const boissons = <?= json_encode($boissons) ?>;    // >> id_recette, titre //
const hierarchie = <?= json_encode($hierarchie) ?>;   // >> id_aliment, id_sous_categorie //
const recette = <?= json_encode($contenu_recette) ?>;  // >> id_recette, id_ingredient //

const recetteMap = new Map();     //crée la map id_recette >> liste ingredients

recette.forEach(rec => {
    if (!recetteMap.has(rec.id_recette)) {
        recetteMap.set(rec.id_recette, []);   //on set la clée si elle n'existe pas
    }
    recetteMap.get(rec.id_recette).push(rec.id_ingredient); 
});

const hierarchiemap = new Map();     //crée la map id_ingredient >> liste sous ingredients

hierarchie.forEach(ingr => {
    if (!hierarchiemap.has(ingr.id_aliment)) {
        hierarchiemap.set(ingr.id_aliment, []);   //on set la clée si elle n'existe pas
    }
    hierarchiemap.get(ingr.id_aliment).push(ingr.id_sous_categorie); 
});





let alimentsSelect= [];
let  alimentsInterdit= [];
let superalimentsSelect= new Map();
let sousalimentsSelect=[];
let sousalimentsNettoyage=[];

function afficherGrille(data, domId, type = "aliment") {
    const container = document.getElementById(domId);
    container.innerHTML = "";

    
    data.forEach(item => {
        const div = document.createElement("div");
        div.className = type === "aliment" ? "stylegrilleinter" : "stylegrilleinterboissons";
        div.id = item.id_aliment || item.id_recette;
        div.innerHTML = `<strong>${item.nom || item.titre}</strong>`;
        div.style.order = 50;
        if (type === "aliment") {
            div.onclick = () => {
                sousaliments(div.id);
                filtrerBoissons(alimentsSelect, alimentsInterdit);


                if (alimentsSelect.includes(div.id)) {
                    div.classList.add("selected");
                    div.style.order = 10; 
                
                } 
                else if (alimentsInterdit.includes(div.id)) {
                    div.classList.remove("selected");
                    div.classList.add("interdit");
                    div.style.order = 20; 
                } 
                else if (superalimentsSelect.has(div.id)) {
                    div.classList.add("selected");
                    div.style.order = 5; 

                    // Parcours des sous-aliments
                    superalimentsSelect.get(div.id).forEach(val => {
                        let sousDiv = document.getElementById(val);
                        if (sousDiv) {
                            sousDiv.classList.add("superalim");
                            sousDiv.style.order = 30; 
                        }
                    });
                } 
                else {
                    div.classList.remove("interdit");
                    div.classList.remove("selected");
                    while (sousalimentsNettoyage.length > 0) {
                        let sousalim = document.getElementById(sousalimentsNettoyage.shift());
                        if (sousalim) {
                         sousalim.classList.remove("superalim");
                        sousalim.style.order = 50; }}
                   div.style.order = 50; 
                }
            };
        }

        container.appendChild(div);
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
        if (!resultat.some(item => item.id_aliment == div.id)) {
            div.style.display = "none"; // masquer
        } else {
            div.style.display = "flex"; 
        }
    });

    return resultat.map(a => a.id_aliment);
}



//crée les tableaux des aliments selectionés
function ajouterAlimentSelect(id) {
  if (alimentsSelect.includes(id)) {
    alimentsSelect = alimentsSelect.filter(idalim => idalim !== id);
    alimentsInterdit.push(id);
  } 
   else if (alimentsInterdit.includes(id)) {
    alimentsInterdit = alimentsInterdit.filter(idalim => idalim !== id);
  }
  else {
    alimentsSelect.push(id);
    console.log(alimentsSelect);
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
    let sousalim = [...recupSousalim(id).map(String)]
    if (!superalimentsSelect.has(id)) {

       superalimentsSelect.set(id,sousalim);
       sousalimentsSelect.push(...sousalim);

       
    }
    else{
        superalimentsSelect.delete(id);
        sousalim.forEach(val => {
            let index = sousalimentsSelect.indexOf(val);
            if (index !== -1) {
                sousalimentsSelect.splice(index, 1);
            }
            index = sousalimentsSelect.indexOf(val);
            if (index == -1) {
            sousalimentsNettoyage.push(val);}

        });
}
}




    //affiche les boissons selon le filtre aliments
function filtrerBoissons(idIngredientsSelectionnes, alimentsInterdits) {

    // pour les ou

    // pour les et

  let resultat = boissons.filter(b => idIngredientsSelectionnes.every(id => (recetteMap.get(b.id_recette).includes(id))));
    resultat = resultat.filter(b => !recetteMap.get(b.id_recette).some(id => alimentsInterdits.includes(id)));


    resultat = resultat.filter(b => {
    const ingredients = recetteMap.get(b.id_recette);
    return Array.from(superalimentsSelect.values()).every(sousAlimGroup => {      // concatenation des ou
        return sousAlimGroup.some(val => ingredients.includes(val));             // true si la recette contient ou moins 1 alim
    });
});
    // Afficher dans la grille
    afficherGrille(resultat, "GrilleBoissons", "boisson");
}

//prend l'id d'un aliment et retoune tous les sous aliment


// differentie les aliments basiques des supeer aliments
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


// Affichage initial (tout)
afficherGrille(aliments, "GrilleAliments");
afficherGrille(boissons, "GrilleBoissons");

// ---------------------- Gestion Masquer / Afficher -------------------------
function afficherMenu(){
    const btn = document.getElementById("affichageMenuAliments");
    const grille = document.getElementById("GrilleAliments");
    const texte = document.getElementById("texteFleche");
    const fleche = document.getElementById("fleche");
    const recherche = document.getElementById("rechercheAliment");
    const bouton = document.getElementById("bouttonRechercheAlim");
    btn.onclick = () => {
        grille.classList.toggle("cache");
        recherche.classList.toggle("cache");
        bouton.classList.toggle("cache");

        if(grille.classList.contains("cache")){
            texte.textContent = "Afficher";
            fleche.style.transform = "rotate(180deg)";
        } else {
            texte.textContent = "Masquer";
            fleche.style.transform = "rotate(0deg)";
        }
    }
}

afficherMenu();
</script>





<style>

#rechercheAliment {
    
    max-height: 60px;
    transition: all 0.3s ease;
}

#rechercheAliment.cache {
    
    display:none;
}
#bouttonRechercheAlim.cache {
    
    display:none;
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

.stylegrilleinter.selected {
    background-color: #14dd06ff;
}
.stylegrilleinter.interdit {
    background-color: #dd0606ff;
}

.stylegrilleinter.superalim {
    background-color: #c4b213ff;
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





</body>
</html>
