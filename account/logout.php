<?php
    session_start();
    
    // destruction de la session (compte deconnecté)
    session_destroy();
    
    // affichage du popup de confirmation et redirection vers la page d'accueil
    echo "<script>
        alert('Vous êtes maintenant déconnecté');
        window.location.href = '../index.php';
    </script>";

?>
