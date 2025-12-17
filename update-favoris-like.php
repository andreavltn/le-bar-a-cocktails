<?php
    session_start();
    require_once("connect_db.php");

    // si jamais on arrive sur cette page sans passer par recette.php
    // renvoi vers l'accueil
    if (!isset($_GET['id'])) {
        header("Location: accueil.php");
        exit();
    }

    // récupérer l'id de la recette via l'url
    // cast en int = sécurité pour éviter les injections
    $id_recette = (int)$_GET['id'];

    // si l'utilisateur est connecté : gérer avec la bdd
    if (isset($_SESSION["id"])) {
        
        // récupérer l'id de l'utilisateur
        $id_user = (int)$_SESSION["id"];
        
        // requête pour vérifier si la recette est déjà en favori
        $sql_check = "SELECT id_recette FROM favoris WHERE id_recette = ? AND id_user = ?"; 
        $req_check = mysqli_prepare($dblink, $sql_check); // préparation
        mysqli_stmt_bind_param($req_check, "ii", $id_recette, $id_user); // liaison des paramètres (deux entiers)
        mysqli_stmt_execute($req_check); // éxécution de la requête
        $res_check = mysqli_stmt_get_result($req_check); // récupération du résultat
        
        // si il y a un résultat : la recette est déjà en favoris
        if (mysqli_num_rows($res_check > 0)) {
            $est_en_fav = true;
        } else {
            $est_en_fav = false;
        }
    
        mysqli_stmt_close($req_check); // fermeture de la requête
        
        // si la recette est dejà en favoris
        if ($est_en_fav) {

            // requête pour la supprimer de la bdd
            $sql_supprimer = "DELETE FROM favoris WHERE id_recette = ? AND id_user = ?";
            $req_supprimer = mysqli_prepare($dblink, $sql_supprimer); // préparation de la requête
            mysqli_stmt_bind_param($req_supprimer, "ii", $id_recette, $id_user); // liaison des paramètres (deux entiers)
            mysqli_stmt_execute($req_supprimer); // éxécution de la requête
            mysqli_stmt_close($req_supprimer); // fermeture de la requête
        } else {
            // requête pour l'ajouter dans la bdd
            $sql_ajouter = "INSERT INTO favoris (id_recette, id_user) VALUES (?, ?)";
            $req_ajouter = mysqli_prepare($dblink, $sql_ajouter); // préparation de la requête
            mysqli_stmt_bind_param($req_ajouter, "ii", $id_recette, $id_user); // liaison des paramètres (deux entiers)
            mysqli_stmt_execute($req_ajouter); // éxécution de la requête
            mysqli_stmt_close($req_ajouter); // fermeture de la requête
        }
        
    } else {

        // non connecté : gérer avec la session
        
        // initialiser le tableau de favoris si nécessaire
        if (!isset($_SESSION['favoris'])) {
            $_SESSION['favoris'] = [];
        }
        
        // si la recette est déjà en favoris (dans le tableau de la session)
        if (in_array($id_recette, $_SESSION['favoris'])) {

            // retirer des favoris
            $nouveaux_favoris = []; // nouveau tableau
            foreach ($_SESSION['favoris'] as $id) { // pour chaque id de l'ancien tableau
                if ($id != $id_recette) { // si il n'est pas celui qu'on veut supprimmer
                    $nouveaux_favoris[] = $id; // l'ajoute au nouveau tableau
                }
            }

            $_SESSION['favoris'] = $nouveaux_favoris; // remplace le tableau par le nouveau

        } else {

            // sinon elle n'y était pas : on l'ajoute en fin de tableau
            array_push($_SESSION['favoris'], $id_recette);
        }
    }

    exit();
?>
