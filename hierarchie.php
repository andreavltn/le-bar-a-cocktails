<?php
include 'connect_db.php';

/*
    1) RACINES
    Un aliment est une racine si son ID n'apparait jamais dans id_sous_categorie
*/
$racine = qdb("
    SELECT id_aliment, nom
    FROM aliments
    WHERE id_aliment NOT IN (
        SELECT id_sous_categorie FROM hierarchie
        WHERE id_sous_categorie IS NOT NULL
    )
    ORDER BY nom ASC
");

/*
    2) ENFANTS (parent → enfants)
*/
$liens = qdb("
    SELECT 
        h.id_aliment AS parent,
        h.id_sous_categorie AS enfant,
        a.nom AS enfant_nom
    FROM hierarchie h
    JOIN aliments a ON a.id_aliment = h.id_sous_categorie
    ORDER BY a.nom ASC
");

// Construire map parent -> [ {id, nom}, ... ]
$structure = [];
foreach ($liens as $l) {
    $structure[$l['parent']][] = [
        'id'  => $l['enfant'],
        'nom' => $l['enfant_nom']
    ];
}

// Injecter les données en JSON dans <script>
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Arborescence Aliments</title>

<script>
// Importation des données PHP → JS
const racines = <?= json_encode($racine) ?>;
const enfants = <?= json_encode($structure) ?>;

function choixRacine() {
    const rid = document.getElementById('racine').value;
    const div = document.getElementById('div_children');
    div.innerHTML = "";

    if (!rid) return;

    afficherEnfants(rid, div);
}

function afficherEnfants(parentId, container) {
    const list = enfants[parentId];

    if (!list) {
        container.innerHTML = "<i>Aucun sous-aliment</i>";
        return;
    }

    // Création d'un select comme dans ton exemple départements
    const sel = document.createElement('select');

    const def = document.createElement('option');
    def.value = "";
    def.textContent = "-- Choisir --";
    sel.appendChild(def);

    list.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.nom;
        sel.appendChild(opt);
    });

    container.appendChild(sel);

    // Prévoir un conteneur pour les niveaux suivants
    const next = document.createElement('div');
    next.style.marginLeft = "20px";
    container.appendChild(next);

    sel.addEventListener('change', function () {
        next.innerHTML = "";
        if (sel.value) afficherEnfants(sel.value, next);
    });
}
</script>

</head>
<body>

<h2>Arborescence des aliments</h2>

<form>
    <label>Racines :</label>
    <select id="racine" onchange="choixRacine()">
        <option value="">-- Choisir une racine --</option>

        <?php foreach ($racine as $r) { ?>
            <option value="<?= $r['id_aliment'] ?>">
                <?= htmlspecialchars($r['nom']) ?>
            </option>
        <?php } ?>

    </select>

    <div id="div_children"></div>
</form>

</body>
</html>
