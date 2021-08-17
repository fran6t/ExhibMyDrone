<?php
// Renomez ce fichier in-config-perso-sample.php en in-config-perso.php  si ce fichier est trouvé par l'appli alors il aura la primeur sur les variables communes avec inc-config.php
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
?>