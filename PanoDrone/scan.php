<?php
include('inc-config.php');
include('inc-lib.php');

// La fonction est maintenant dans inc-lib.php car utilisée aussi dans gestion.php en plus de assets/js/script.js 
// Run the recursive function

$frontend = true;			// On passe $frontend à vrai car nous sommes en frontend	
$response = scan($dir);		//Nous somme côté frontend alors on passe true à la fonction

// Output the directory listing as JSON

header('Content-type: application/json');

echo json_encode(array(
	"name" => $dir,
	"type" => "folder",
	"path" => $dir,
	"items" => $response
));
?>