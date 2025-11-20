<?php
    try {

        // connexion à localhost
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
                mdp VARCHAR(255) NOT NULL,
                nom VARCHAR(100),
                prenom VARCHAR(100),
                sexe ENUM('H', 'F'),
                email VARCHAR(150),
                date_naissance DATE,
                adresse VARCHAR(255),
                code_postal VARCHAR(10),
                ville VARCHAR(100),
                tel VARCHAR(20)
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
