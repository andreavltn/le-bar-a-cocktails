<?php

    session_start();
    require_once("../connect_db.php");

    // récupération des infos du formulaire
    $pseudo = $_POST["pseudo"];
    $mdp = $_POST["mdp"];

    // requête pour rechercher l'utilisateur avec le pseudo
    $sql_pseudo = "SELECT * FROM utilisateur WHERE pseudo = ?"; 
    $req_pseudo = $dblink->prepare($sql_pseudo); // préparation de la requête

    if ($req_pseudo === false) { // si la requête génère une erreur : stop
        die("Erreur prepare: " . $dblink->error);
    }

    $req_pseudo->bind_param("s", $pseudo); // pseudo est un string
    $req_pseudo->execute(); // execution de la requête
    $resultat_pseudo = $req_pseudo->get_result(); // récupération du résultat

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

    header("Location: ../accueil.php"); // renvoi sur la page d'accueil

?>
