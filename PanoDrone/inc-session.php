<?php
// Au 26/08/2021 la version tinyfilemanager exige au minimum php 5.5
// Dans le cas d'une version inferieur on utilise le parametre clef que l'on compare a la clef dans inc-config.php  

if (version_compare(phpversion(), '5.5.0', '>=')) {     // Must be > 5.5 for use authentification of tinymanagerfile
	// On va se servir de la connection de tinyfilemanager pour savoir si on peu acceder
	// Attention tous ceux qui sont identifiés correctement dans tinyfilemanger accederons 
	if ( !defined( 'FM_SESSION_ID')) {
	    define('FM_SESSION_ID', 'filemanager');
	}
	session_name(FM_SESSION_ID );	// On pointe la session de tinyfilemanager
	session_start();
	if (!isset($_SESSION[FM_SESSION_ID]['logged'])){
		// On redirige vers tinyfilemanager pour se connecter
		header('Location: tinyfilemanagergest/tinyfilemanager.php');
		exit;
	}
} else {
	// Si le parametre k est egale a k du fichier config on memorise en session et on peut poursuivre sinon on s'arrete la
    $msg = "Désolé version php trop ancienne et/ou parametre k non egal à celui définit dans inc-config.php";
    session_start();
	if (isset($_GET['k'])){
        if ( $_GET['k'] == $keyok ){
            $msg="";
            $_SESSION['k'] = $keyok;
        }
	} else {
        if (isset($_SESSION['k'])){
            if ($_SESSION['k'] == $keyok ) $msg="";
        }
    }
    if ($msg!=""){ 
		echo "Désolé version php trop ancienne et/ou parametre k non egal à celui définit dans inc-config.php ou inc-config-perso.php";
        exit;
	}
}
?>
