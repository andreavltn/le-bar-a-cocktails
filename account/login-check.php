<?php

    session_start();
    require_once("../connect_db.php");

    // récupération des infos du formulaire
    $pseudo = $_POST["pseudo"];
    $mdp = $_POST["mdp"];

    // requête pour rechercher l'utilisateur avec le pseudo
    $sql_pseudo = "SELECT * FROM utilisateur WHERE pseudo = ?"; 
    $req_pseudo = mysqli_prepare($dblink, $sql_pseudo); // préparation de la requête
    mysqli_stmt_bind_param($req_pseudo, "s", $pseudo); // liaison du paramètre (chaine)
    mysqli_stmt_execute($req_pseudo); // execution de la requête
    $resultat_pseudo = mysqli_stmt_get_result($req_pseudo); // récupération du résultat

    // si aucun compte n'existe avec ce pseudo
    if($resultat_pseudo->num_rows == 0){ 
        header("Location: login.php?erreur_pseudo=1"); // renvoi sur le formulaire avec l'erreur de pseudo
        exit(); // sortie
    }

    // récupération de l'utilisateur
    $utilisateur = $resultat_pseudo->fetch_assoc();

    // vérification du mot de passe
    if($utilisateur["mdp"] != $mdp){
        header("Location: login.php?erreur_mdp=1"); // renvoi sur le formulaire avec l'erreur de mdp
        exit(); // sortie
    }

    // si tout est bon, paramétrage de la session
    $_SESSION["pseudo"] = $utilisateur["pseudo"];
    $_SESSION["id"] = $utilisateur["id"];

    // transférer les favoris de la session vers la bdd (si il y en a)
    if (isset($_SESSION['favoris']) && is_array($_SESSION['favoris'])) {

        // pour chaque favori
        foreach ($_SESSION['favoris'] as $id_recette) {
            
            // requête qui récupère les favoris de l'utilisateur
            $sql_check = "SELECT id_recette FROM favoris WHERE id_recette = ? AND id_user = ?";
            $req_check = mysqli_prepare($dblink, $sql_check); // préparation de la requête
            mysqli_stmt_bind_param($req_check, "ii", $id_recette, $utilisateur["id"]); // liaison des paramètres (des entiers)
            mysqli_stmt_execute($req_check); // éxécution de la requête
            $result_check = mysqli_stmt_get_result($req_check); // récupération du résultat
            
            // si les favoris n'y sont pas 
            if ($result_check->num_rows == 0) {

                // requête pour les insérer dans la bdd
                $sql_insert = "INSERT INTO favoris (id_recette, id_user) VALUES (?, ?)";
                $req_insert = mysqli_prepare($dblink, $sql_insert); // préparation de la requête
                mysqli_stmt_bind_param($req_insert, "ii", $id_recette, $utilisateur["id"]); // liaison des paramètres (des entiers)
                mysqli_stmt_execute($req_insert); // éxécution de la requête

                mysqli_stmt_close($req_insert); // fermeture de la requête d'insertion
            }
            
            mysqli_stmt_close($req_check); // fermeture de la requête de vérification
        }
        
        // retire les favoris de la session
        unset($_SESSION['favoris']);
    }

    header("Location: ../index.php"); // renvoi sur la page d'accueil

?>
