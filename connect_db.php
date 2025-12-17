<?php

    // identifiants et paramètres de connexion
    $dbhost = "localhost"; 
    $dbuser = "root"; 
    $dbpassword = ""; 
    $dbname = "boissons"; 

    // connexion à la base de données boissons
    $dblink = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname); 
    if (!$dblink) {
        die("Erreur de connexion : " . mysqli_connect_error());
    }

    // sélection de la base de données boissons
    $db = mysqli_select_db($dblink, $dbname);

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


