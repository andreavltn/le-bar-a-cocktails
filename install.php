<?php
    // Connexion à MySQL
    $mysqli = mysqli_connect("localhost", "root", "", "");

    if (!$mysqli) {
        die("Erreur de connexion : " . mysqli_connect_error());
    }

    // Création de la base
    $requete = "CREATE DATABASE IF NOT EXISTS boissons CHARACTER SET utf8mb4;";
    if (!mysqli_query($mysqli, $requete)) {
        die("Erreur création base : " . mysqli_error($mysqli));
    }

    // Sélection de la base
    if (!mysqli_select_db($mysqli, "boissons")) {
        die("Erreur sélection base : " . mysqli_error($mysqli));
    }

    // Création table utilisateur
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

    // Création table favoris
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

    echo "La base a été créée avec succès !";

    // Fermeture de la connexion
    mysqli_close($mysqli);
?>
