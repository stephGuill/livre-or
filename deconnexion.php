<?php
require_once 'config.php';

//  // libère les variables de session
    session_unset();
// détruit la session
    session_destroy();

// redirige vers l'accueil
header('Location: index.php');
exit();
?>
 

