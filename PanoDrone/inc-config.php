<?php
$dir = "Spheres";						// Important dans scan.php sert au debut et a la fin pour le json

// Pour tinyfilemanager il faut completer le chemin (c'est utilisé dans son config.php)
// Exemple les sphères sont sont visibles ou accessibles sous l'URL http://www.mondomaine.xx/PanoDrone/Spheres
// Alors ont renseigne comme ci-dessous la variable $dir completea cette info
$root_complement = "ExhibMyDrone/PanoDrone";



// Login user name and password
// Users: array('Username' => 'Password', 'Username2' => 'Password2', ...)
// Generate secure password hash - https://tinyfilemanager.github.io/docs/pwd.html
$auth_users = array(
    'admin' => '$2y$10$/K.hjNr84lLNDt8fTXjoI.DBp6PpeyoJ.mGwrrLuCZfAwfSAGqhOW', //admin@123
    'user' => '$2y$10$Fg6Dz8oH9fPoZ2jJan5tZuv6Z4Kp7avtQ9bDfrdRntXtPeiMAZyGO' //12345
);

// Maximum file upload size
// Increase the following values in php.ini to work properly
// memory_limit, upload_max_filesize, post_max_size
$max_upload_size_bytes = 5000;

/* Ci-dessous ne doit pas être modifié */

// Prefix of the short URL 
// $shortURL_Prefix = 'https://xyz.com/'; // with URL rewrite    Non implémenté
$shortURL_Prefix = $_SERVER['SERVER_NAME'].'/'.$root_complement."/?c="; // without URL rewrite

$root_complement .= "/".$dir;
// $root_complement = "/cportail/PanoDrone/Spheres";
$root_path = $_SERVER['DOCUMENT_ROOT'].'/'.$root_complement;

// Root url for links in file manager.Relative to $http_host. Variants: '', 'path/to/subfolder'
// Will not working if $root_path will be outside of server document root
$root_url = $root_complement;

?>