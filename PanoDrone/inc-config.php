<?php
// Les variables ci-dessous sont celles par defaut vous n'avez pas a y toucher
// Vous serez automatiquement redirigé vers un formulaire de mises à jour de ces variables. 
// Les nouvelles valeurs que vous aurez saisies vont l'emporter sur celle de ce fichier
// Elles seront mémorisées dans le fichier inc-config-perso.ini.php
$version = "0.12";

$langue = "fr";                         // en for English

$dir = "Spheres";						// Important dans scan.php sert au debut et a la fin pour le json

$hosttmp = $_SERVER['HTTP_HOST'];
$protocoltmp=$_SERVER['PROTOCOL'] = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http';
$monDomaine = $protocoltmp."://".$hosttmp; // Va servir pour construire un lien court vers la sphère directement exemple https://d.wse.fr

// Pour tinyfilemanager il faut completer le chemin (c'est utilisé dans son config.php)
// Exemple les sphères sont sont visibles ou accessibles sous l'URL http://www.mondomaine.xx/PanoDrone/Spheres
// Alors ont renseigne comme ci-dessous la variable $dir completea cette info
$root_complement = substr(dirname($_SERVER['PHP_SELF']),1);      // Le chemin sans le premier / exemple francis/PanoDrone

$keyok = "Azerty001";       // Si votre php est trop ancien l'usage de tinyfilemanager pour gerer l'accès est impossible
                            // vous pourrez néanmoins gerer les point d'interets en appelant l'interface de gestion manuellement de la façon suivante
                            // http://www.mondomaine.xx/PanoDrone/gest.php?k=Azerty001

// Login user name and password
// Users: array('Username' => 'Password', 'Username2' => 'Password2', ...)
// Generate secure password hash - https://tinyfilemanager.github.io/docs/pwd.html
$auth_users = array(
    'admin' => '$2y$10$/K.hjNr84lLNDt8fTXjoI.DBp6PpeyoJ.mGwrrLuCZfAwfSAGqhOW', //admin@123
    'user' => '$2y$10$Fg6Dz8oH9fPoZ2jJan5tZuv6Z4Kp7avtQ9bDfrdRntXtPeiMAZyGO' //12345
);

$admin = "admin@123";

$browsingProtect = "Y";     // Placera un index.php vide dans chaque sous repertoire du repertoire Spheres permettant d'interdire le parcours des repertoires

$bddtype = 'sqlite';					    // sqlite ou mysql
$host = '127.0.0.1';
$db   = 'pano.db';							// Nom de la bdd
$user = 'user';
$pass = 'pasword';
$port = "3306";

// Maximum file upload size
// Increase the following values in php.ini to work properly
// memory_limit, upload_max_filesize, post_max_size
$max_upload_size_bytes = 5000;

// Attention si vous changez le nom de ce fichier veillez à ce qu'il soit bien en .php sinon il y a risque de visualisation de son contenu
$config_file = 'inc-config-perso.ini.php';

// Don't show with tinyfilemanager Files and folders to excluded from listing
// e.g. array('myfile.html', 'personal-folder', '*.php', ...)
$exclude_items = array('*.php','*.db');
?>