<?php
    try {

        // connexion à MySQL en local
        $pdo = new PDO("mysql:host=localhost", "root", ""); 

        // création de la base (avec encodage permettant les caractères spéciaux)
        $pdo->exec("CREATE DATABASE IF NOT EXISTS boissons CHARACTER SET utf8mb4;");

        // se place sur la base boissons
        $pdo->exec("USE boissons;");

        // création de la table des utilisateurs
        $pdo->exec("
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
        ");

        // création de la table des favoris
        $pdo->exec("
            CREATE TABLE favoris (
                id_user INT NOT NULL,
                id_recette INT NOT NULL,
                PRIMARY KEY (id_user, id_recette),
                FOREIGN KEY (id_user) REFERENCES utilisateur(id)
            );
        ");

        echo "La base a été créée avec succès!";

    } catch (PDOException $e) {

        echo "Erreur lors de la création de la base : " . $e->getMessage();

    }
?>
