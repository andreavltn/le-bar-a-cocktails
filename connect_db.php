<?php

    // identifiants et paramètres de connexion
    $dbhost = "localhost"; 
    $dbuser = "root"; 
    $dbpassword = ""; 
    $dbname = "boissons"; 

    // test pour vérifier si la base boissons existe déjà

    // requête de connexion
    $dblink = mysqli_connect($dbhost, $dbuser, $dbpassword);

    // tente de se connecter à la base
    try {
        mysqli_select_db($dblink, $dbname);
        mysqli_set_charset($dblink, 'utf8mb4'); // encodage des caractères
    } catch (mysqli_sql_exception $e) {
        
        // redirige vers l'install si la connexion ne fonctionne pas (pour créer la base)
        header('Location: install.php');
        exit;
    }

    // fonction pour réaliser une requête sql sur la table
    function qdb($sql) {
        
        global $dblink;
        
        // requête
        $resultat = mysqli_query($dblink, $sql); 

        // gestion des erreurs
        if (mysqli_errno($dblink)) { 
            echo "MySQL error " . mysqli_errno($dblink) . " : " . mysqli_error($dblink) . "\n<br>"; 
            echo "Requête MySQL : " . $sql . "\n<br>"; 
            die; 
        }
        
        // affichage de la requête réalisée
        return $resultat; 
    }
    
?>


