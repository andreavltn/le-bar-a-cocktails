<?php
    require_once("../connect_db.php");

    // récupération des infos du formulaire
    $pseudo = $_POST["pseudo"];
    $mdp = $_POST["mdp"];
    $mdp2 = $_POST["mdp2"];
    $nom = $_POST["nom"];
    $prenom = $_POST["prenom"];
    $sexe = $_POST["sexe"];
    $email = $_POST["email"];
    $date_naissance = $_POST["date_naissance"];
    $adresse = $_POST["adresse"];
    $code_postal = $_POST["code_postal"];
    $ville = $_POST["ville"];
    $tel = $_POST["tel"];
    

    // si les deux mots de passe entrés ne correspondent pas
    if($mdp != $mdp2){ 
        header("Location: signup.php?erreur_mdp=1"); // renvoi sur le formulaire avec l'erreur de mdp
        exit(); // sortie
    }


    // requête pour recherche les lignes avec le pseudo
    $sql_pseudo = "SELECT * FROM utilisateur WHERE pseudo=?"; 
    $req_pseudo = $dblink->prepare($sql_pseudo); // préparation de la requête

    if ($req_pseudo === false) { // si la requête génère une erreur : stop
        die("Erreur prepare: " . $dblink->error);
    }

    $req_pseudo->bind_param("s", $pseudo); // pseudo est un string
    $req_pseudo->execute(); // execution de la requête
    $resultat_pseudo = $req_pseudo->get_result(); // récupération du résultat

    // si un compte existe déjà avec le pseudo
    if($resultat_pseudo->num_rows==1){ 
        header("Location: signup.php?erreur_pseudo=1"); // renvoi sur le formulaire avec l'erreur de pseudo
        exit(); // sortie
    }


    // sinon tout est bon, on insère dans la bdd

    // préparation des variables non-obligatoires (null si elles ne sont pas renseignées, sinon elles gardent leur valeur)
    $nom_val = !empty($nom) ? $nom : null;
    $prenom_val = !empty($prenom) ? $prenom : null;
    $sexe_val = !empty($sexe) ? $sexe : null;
    $date_naissance_val = !empty($date_naissance) ? $date_naissance : null;
    $adresse_val = !empty($adresse) ? $adresse : null;
    $code_postal_val = !empty($code_postal) ? $code_postal : null;
    $ville_val = !empty($ville) ? $ville : null;
    $tel_val = !empty($tel) ? $tel : null;
    
    $sql = "INSERT INTO utilisateur(pseudo, mdp, nom, prenom, sexe, email, date_naissance, adresse, code_postal, ville, tel) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // requête d'insertion dans la base
    $req_finale = $dblink->prepare($sql); // préparation de la requête
    $req_finale->bind_param("sssssssssss", // toutes les variables sont des string
        $pseudo, 
        $mdp, 
        $nom_val, 
        $prenom_val, 
        $sexe_val, 
        $email, 
        $date_naissance_val, 
        $adresse_val, 
        $code_postal_val, 
        $ville_val, 
        $tel_val); 
        
    $req_finale->execute(); // exécution de la requête

    // affichage du popup et redirection
    echo "<script>
        alert('Le compte a été créé avec succès');
        window.location.href = 'login.php';
    </script>";

?>