<?php
include 'connect_db.php';

/* recup de la racine */
$racine = qdb("
    SELECT id_aliment, nom
    FROM aliments
    where lower(nom)='aliment'
");


//crée la table id racine // id sous aliment // nom sous aliment //

$tablenomsousalim = qdb("SELECT  h.id_aliment AS base, h.id_sous_categorie AS idsousaliment, a.nom AS nomsousaliment
    FROM hierarchie h JOIN aliments a ON a.id_aliment = h.id_sous_categorie
    ORDER BY a.nom ASC    
");


//crée la table nom >> idsouslim/nomsousalim

$arraynomsousalim = [];

foreach ($tablenomsousalim as $i) {
    $arraynomsousalim[$i['base']][] = [
        'id'  => $i['idsousaliment'],
        'nom' => $i['nomsousaliment']
    ];
}
?>



<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Hierarchie</title>



<script>
const racines = <?= json_encode($racine) ?>;
const enfants = <?= json_encode($arraynomsousalim) ?>;



function afficherSousaliment(id, container) {
    const list = enfants[id];
    container.innerHTML = "";

    if (!list) {
        return;
    }

    const next = document.createElement('div');
    next.style.marginLeft = "20px";

    const btnContainer = document.createElement('div');
    btnContainer.style.margin = "10px 0";

    list.forEach(item => {
        const bouton = document.createElement('button');
        bouton.textContent = item.nom;
        bouton.style.margin = "0 10px 10px 0";
        bouton.dataset.id = item.id;

        bouton.onclick = () => afficherSousaliment(item.id, next);

        btnContainer.appendChild(bouton);
    });

    container.appendChild(btnContainer);
    container.appendChild(next);
}
</script>

</head>
<body>

<h2>Arborescence des aliments</h2>

<!-- Boutons racines -->
<div id="div_racines">
<?php foreach ($racine as $r) { ?>
    <button 
        class="racineBtn" 
        data-id="<?= $r['id_aliment'] ?>"
        style="margin-right:10px; margin-bottom:10px;"
        onclick="afficherSousaliment('<?= $r['id_aliment'] ?>', document.getElementById('div_children'))">
        <?= htmlspecialchars($r['nom']) ?>
    </button>
<?php } ?>
</div>
<!-- Conteneur de l'arborescence -->
<div id="div_children"></div>










<style>
button {
    padding: 7px 12px;
    border: 1px solid #555;
    background: #f2f2f2;
    border-radius: 6px;
    cursor: pointer;
}
button:hover {
    background: #655f5fff;
}
</style>







</body>
</html>

