<?php
    include 'Donnees.inc.php'; 

    // connexion à MySQL
    $mysqli = mysqli_connect("localhost", "root", "", "");
    

    if (!$mysqli) {
        die("Erreur de connexion : " . mysqli_connect_error());
    }

    // création de la base
    $mysqli->query("DROP DATABASE IF EXISTS boissons");

    $requete = "CREATE DATABASE IF NOT EXISTS boissons CHARACTER SET utf8mb4;";
    if (!mysqli_query($mysqli, $requete)) {
        die("Erreur création base : " . mysqli_error($mysqli));
    }

    // sélection de la base
    if (!mysqli_select_db($mysqli, "boissons")) {
        die("Erreur sélection base : " . mysqli_error($mysqli));
    }

    // création table utilisateur
    $requete = "
        CREATE TABLE utilisateur (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pseudo VARCHAR(20) UNIQUE NOT NULL,
            mdp VARCHAR(40) NOT NULL,
            nom VARCHAR(100),
            prenom VARCHAR(100),
            sexe ENUM('H', 'F'),
            email VARCHAR(100),
            date_naissance DATE,
            adresse VARCHAR(255),
            code_postal VARCHAR(5),
            ville VARCHAR(100),
            tel VARCHAR(10)
        );
    ";

    if (!mysqli_query($mysqli, $requete)) {
        die("Erreur création table utilisateur : " . mysqli_error($mysqli));
    }

    // création table favoris
    $requete = "
        CREATE TABLE favoris (
            id_user INT NOT NULL,
            id_recette INT NOT NULL,
            PRIMARY KEY (id_user, id_recette),
            FOREIGN KEY (id_user) REFERENCES utilisateur(id)
        );
    ";
    
    if (!mysqli_query($mysqli, $requete)) {
        die("Erreur création table favoris : " . mysqli_error($mysqli));
    }
    
    // création table recette
    $requete = "
        CREATE TABLE recettes (
    	id_recette INT AUTO_INCREMENT PRIMARY KEY,
    	titre VARCHAR(255) NOT NULL,
    	ingredients TEXT NOT NULL,
    	preparation TEXT NOT NULL
	);
    ";
	if (!mysqli_query($mysqli, $requete)) {
	die("Erreur création table recette : " . mysqli_error($mysqli));
    }
    
    
    $i = 0;
    while ($i < count($Recettes)) {   // parcours de l'array recette

        $titre = $mysqli -> escape_string($Recettes[$i]["titre"]);
        $ingredients = $mysqli -> escape_string($Recettes[$i]["ingredients"]);
        $preparation = $mysqli -> escape_string($Recettes[$i]["preparation"]);
        
        $requete = "INSERT INTO recettes (titre,ingredients,preparation)
        VALUES ('$titre','$ingredients','$preparation');";          // on ajoute la ligne i à la table
    
        if (!mysqli_query($mysqli, $requete)) {
            die("Erreur création update table recette " . mysqli_error($mysqli));
        }
        
        
        $i++;
    }
    
    
    // Création table aliments
    $requete = "
        CREATE TABLE aliments (
    	id_aliment INT AUTO_INCREMENT PRIMARY KEY,
    	nom VARCHAR(255) NOT NULL
	);
    ";
	if (!mysqli_query($mysqli, $requete)) {
	die("Erreur création table aliments : " . mysqli_error($mysqli));
    }
    
    
    foreach ($Hierarchie as $nom => $data) {  //parcours de l'array hierarchie

         $nom = $mysqli -> escape_string($nom);
    
         $requete = "INSERT INTO aliments (nom)
                    VALUES ('$nom');";          //on ajoute la ligne i à la table

    if (!mysqli_query($mysqli, $requete)) {
	die("Erreur création update table aliments " . mysqli_error($mysqli));
    }}
    
    

    //Création table hierarchie
    
    $requete = "
        CREATE TABLE hierarchie(
            id_aliment INT NOT NULL,
            id_sous_categorie INT NOT NULL,
            PRIMARY KEY (id_aliment, id_sous_categorie),
            FOREIGN KEY (id_aliment) REFERENCES aliments(id_aliment)
        );
    ";
    	if (!mysqli_query($mysqli, $requete)) {
	die("Erreur création table hierarchie : " . mysqli_error($mysqli));
    }
    
        foreach ($Hierarchie as $nom => $data) {  //parcours de l'array hierarchie

         $nom = $mysqli -> escape_string($nom);
         $requete = "SELECT id_aliment FROM aliments WHERE nom = '$nom';";
         
         $result = mysqli_query($mysqli, $requete); 
         $row = mysqli_fetch_row($result);
         $id = $row[0];
         
         if (isset($data['sous-categorie']) && is_array($data['sous-categorie'])) { //check si l'aliment a une sous categorie
         
        foreach ($data['sous-categorie'] as $sous_nom) {            //regarde les sous categories de l'element ou on est
                $sous_nom = $mysqli -> escape_string($sous_nom);
                $requete = "SELECT id_aliment FROM aliments WHERE nom = '$sous_nom';";
         
         		$result = mysqli_query($mysqli, $requete); 
         		$row = mysqli_fetch_row($result);
         		$idsc = $row[0];
         
    	
         	$requete = "INSERT INTO hierarchie(id_aliment,id_sous_categorie)
                    	VALUES ($id,$idsc);";    //ajoute a la table l'aliment et une de ses sous categories

    if (!mysqli_query($mysqli, $requete)) {
	die("Erreur création update table hierarchie " . mysqli_error($mysqli));
	
	
	}
    }}
    }
    
    
    //Création table ingredient_recette
    
    $requete = "
        CREATE TABLE ingredient_recette(
            id_recette INT NOT NULL,
            id_ingredient INT NOT NULL,
            PRIMARY KEY (id_recette,id_ingredient),
            FOREIGN KEY (id_ingredient) REFERENCES aliments(id_aliment),
            FOREIGN KEY (id_recette) REFERENCES recettes(id_recette)
        );
    ";
    	if (!mysqli_query($mysqli, $requete)) {
	die("Erreur création table ingredient_recette: " . mysqli_error($mysqli));
    }
    
        $i = 0;
    	while ($i < count($Recettes)) {   //parcours de l'array recette 

         $titre = $mysqli -> escape_string($Recettes[$i]["titre"]);
         $requete = "SELECT id_recette FROM recettes WHERE titre = '$titre';";
         
         $result = mysqli_query($mysqli, $requete); 
         $row = mysqli_fetch_row($result);
         $idrecette = $row[0];
         
        
        foreach ($Recettes[$i]['index'] as $ingredient) {            //regarde les sous categories de l'element ou on est
                $ingredient = $mysqli -> escape_string($ingredient);
                $requete = "SELECT distinct id_aliment FROM aliments WHERE nom = '$ingredient';";
         
         		$result = mysqli_query($mysqli, $requete); 
         		$row = mysqli_fetch_row($result);
         		$idingredient = $row[0];
         
    	
         	$requete = "INSERT IGNORE INTO ingredient_recette(id_recette,id_ingredient)
                    	VALUES ($idrecette,$idingredient);";    //ajoute a la table l'aliment et une de ses sous categories

    if (!mysqli_query($mysqli, $requete)) {
	die("Erreur création update table ingredient_recette " . mysqli_error($mysqli));
	
	
	        }
        }
    $i++;    
    }
    
    
    



    echo "La base a été créée avec succès !";

    // Fermeture de la connexion
    mysqli_close($mysqli);
?>