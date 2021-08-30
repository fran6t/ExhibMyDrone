<?php
/*
But: Tenir une session et filtrer les accès de la partie gestion 

Principe : 
	Si le fichier configuration existe pas on branche vers les parametres
	Sinon
		Si la version de php permet d'utiliser TinyFileManager alors on va se servir de l'authentification de celui-ci
			Sinon
				On va se servir du parametre k et le verifier s'il match avec keyok 
*/
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
	// Si un dex deux parametres est identique à ceux d'origine on oblige à aller dans param.php
	if (password_verify("admin@123", $ini['admin']) || $keyok == "Azerty001"){
		if (pathinfo($_SERVER['PHP_SELF'], PATHINFO_BASENAME) <> "param.php"){ 			//Si nous sommes pas dans param.php
			header('Location: param.php'); 
			exit;
		}
	} else {
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
	    	$msg = "Version php trop ancienne et/ou parametre k non egal à celui définit dans inc-config.php ou ".$config_file;
	    	session_start();
			if (isset($_GET['k'])){
				if ( $_GET['k'] == $keyok ){
            		$_SESSION['k'] = $keyok;
					$msg="";
        		}
			} else {
	        	if (isset($_SESSION['k'])){
            		if ($_SESSION['k'] == $keyok ) $msg="";
        		}
			}
    	}
    	if ($msg!=""){ 
			echo "Version php trop ancienne et/ou parametre k non egal à celui définit dans inc-config.php ou ".$config_file;
        	exit;
		}
	}
} else {
	if (pathinfo($_SERVER['PHP_SELF'], PATHINFO_BASENAME) <> "param.php"){ 			//Si nous sommes pas dans param.php
		header('Location: param.php'); 
		exit;
	}
}
?>
