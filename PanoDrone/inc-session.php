<?php
if (is_readable($config_file)) {
	$ini =  parse_ini_file($config_file);
	$dir = $ini['dir'];
	$monDomaine = $ini['monDomaine'];
	$root_complement = $ini['root_complement'];
	$keyok = $ini['keyok'];
	$auth_users['admin'] = $ini['admin'];
	$bddtype = $ini['bddtype'];
	$host = $ini['host'];
	$user = $ini['user'];
	$pass = $ini['pass'];
	$port = $ini['port'];
} else {
	header('Location: param.php');
	exit;
}

// Au 26/08/2021 la version tinyfilemanager exige au minimum php 5.5
// Dans le cas d'une version inferieur on utilise le parametre clef que l'on compare a la clef dans inc-config.php  
$versParam = false;
if (version_compare(phpversion(), '5.5.0', '>=')) {     // Must be > 5.5 for use authentification of tinymanagerfile
	// On test si le fichier inc-config-perso.ini.php existe et si oui si le mot de passe d'origine a été changé
	// Si ce n'est pas le cas on dirige vers param.php tant que le mot de passe n'a pas été changé 
	if ($ini['admin'] == password_hash("admin@123", PASSWORD_DEFAULT)) $versParam = true;	// Le Mot de passe est d'origine
	if ($versParam) {
		header('Location: param.php');
		exit;
	}
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
    $msg = "Version php trop ancienne et/ou parametre k non egal à celui définit dans inc-config.php ou ".$config_file;
    session_start();
	if (isset($_GET['k'])){
		if (rtrim($_GET['k'])== "Azerty001"){
			header('Location: param.php'); // keyok est d'origine on branche vers param.php
			exit;
		} 
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
		echo "Version php trop ancienne et/ou parametre k non egal à celui définit dans inc-config.php ou ".$config_file;
        exit;
	}
}
?>
