<?php

if ($bddtype=='mysql'){
	$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=$port";
	try {
		$pdo = new PDO($dsn, $user, $pass);
   	} catch (Exception $e) {
		echo "Impossible d'accéder à la base de données MySql : ".$e->getMessage();
		die();
   	} 
} 
if ($bddtype=='sqlite'){
	$dsn = "sqlite:".dirname(__FILE__).'/'.$db;
	try{
		$pdo = new PDO($dsn);
		$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // ERRMODE_WARNING | ERRMODE_EXCEPTION | ERRMODE_SILENT
	} catch(Exception $e) {
		echo "Impossible d'accéder à la base de données SQLite : ".$e->getMessage();
		die();
	}
	
}

//$pdo = new SQLite3($mabdd);

// Decommente les 4 lignes une fois pour remette a zero la bdd attention irreversible
/*
$SqlString = "drop table if exists lespanos"; 
$pdo->exec($SqlString);

$SqlString = "drop table if exists lespanos_details"; 
$pdo->exec($SqlString);
*/

if (!DB_table_exists('lespanos')){
	$SqlString = "CREATE TABLE 'lespanos' (
		'fichier' VARCHAR(500)  NULL,
		'titre' VARCHAR(500)  NULL,
		'legende' TEXT  NULL,
		'hashfic' VARCHAR(100)  NULL
	);";
	$pdo->exec($SqlString);
	$SqlString = "CREATE INDEX 'IDX_lespanos_fichier' ON 'lespanos' ('fichier')";
	$pdo->exec($SqlString);
}

if (!DB_table_exists('lespanos_details')){
    $SqlString = "CREATE TABLE [lespanos_details] (
        [fichier] VARCHAR(500)  NULL,
        [hashfic] VARCHAR(100)  NULL,
        [nom_marqueur] VARCHAR(100)  NULL,
        [couleur] VARCHAR(10)  NULL,
        [latitude] VARCHAR(20)  NULL,
        [longitude] VARCHAR(20)  NULL,
        [descri] TEXT  NULL
        );";
    $pdo->exec($SqlString);
    $SqlString = "CREATE INDEX [IDX_lespanos_DETAILS_hashfic] ON [lespanos_details] ([hashfic])";
    $pdo->exec($SqlString);
}

// Si la column existe pas on l'ajoute à la table
if (!DB_column_exists('lespanos','short_code')){
	echo "la colonne existe oas je k ajoute";
	$SqlString ="ALTER TABLE [lespanos] ADD COLUMN [short_code] VARCHAR(25)";
	$pdo->exec($SqlString);
	$SqlString = "CREATE INDEX [IDX_lespanos_short_code] ON [lespanos]([short_code]  ASC);";
    $pdo->exec($SqlString);
}

function DB_table_exists($table){
    GLOBAL $pdo;
    try{
        $pdo->query("SELECT 1 FROM $table");
    } catch (PDOException $e){
        return false;
    }
    return true;
}

function DB_column_exists($table,$column){
	GLOBAL $pdo;
	// Si la table est vide il faut au moins un enregistement pour que cela fonctionne
	$colExist = false;
	$fichier="To delete";
	$stmt = $pdo->prepare('INSERT INTO lespanos (fichier) VALUES (:fichier);');
	$stmt->bindValue(':fichier', $fichier);
	$result = $stmt->execute();
	$result = $pdo->query('SELECT * FROM '.$table.' LIMIT 1');
	foreach ($result as $row){
	    foreach($row as $col_name => $val){
     		//echo "<br /> $col_name == $val";
			if ($col_name == $column) $colExist = true;   
     	}
 	}
	$stmt = $pdo->prepare('DELETE FROM lespanos WHERE fichier=:fichier');
	$stmt->bindValue(':fichier', $fichier);
	$result = $stmt->execute();
	return $colExist;
}

// Test si la chaine passée contient la chaine MinX
function isMiniature($aTester){
	$pos = strpos($aTester,"MinX");
	if ($pos === false){
		return false;
	} else {
		return true;
	}
}

// Cette foncion faisait partie au départ de scan.php
// This function scans the files folder recursively, and builds a large array
function scan($dir){
	global $pdo;
	$files = array();

	// Is there actually such a folder/file?

	if(file_exists($dir)){
	
		foreach(scandir($dir) as $f) {
		
			if(!$f || $f[0] == '.' || pathinfo($f, PATHINFO_EXTENSION )=="xml" || isMiniature($f)) {
				continue; // Ignore hidden files
			}

			if(is_dir($dir . '/' . $f)) {

				// The path is a folder

				$files[] = array(
					"name" => $f,
					"titre"=> "",
					"type" => "folder",
					"path" => $dir . '/' . $f,
					"items" => scan($dir . '/' . $f) // Recursively get the contents of the folder
				);
			}
			
			else {
				// On recupere le titre et la legende sinon on insert
				$titre="";
				$legende="";
				$fichier = $dir . '/' . $f;
				$statement = $pdo->prepare('SELECT titre,legende FROM lespanos WHERE fichier = :fichier LIMIT 1;');
				$statement->bindValue(':fichier', $fichier);
				$statement->execute();
				$row=$statement->fetch();
				// check for empty result
				if ($row != false) { // Trouvé
					$titre	= $row['titre'];
					$legende= $row['legende'];
				} else {
					// J'ai pas trouvé alors il faut insert
    				$statement = $pdo->prepare('INSERT INTO lespanos (fichier) VALUES (:fichier);');
					$statement->bindValue(':fichier', $fichier);
					$statement->execute();
				}

				if (rtrim($titre)=="") $titre = $f;
				$files[] = array(
					"name" => $f,
					"titre"=> $titre,
					"legende"=> $legende,
					"type" => "file",
					"path" => $dir . '/' . $f,
					"size" => filesize($dir . '/' . $f) // Gets the size of this file
				);
			}
		}
	
	}

	return $files;
}


function generateRandomString($length){
	$chars = "abcdfghjkmnpqrstvwxyz|ABCDFGHJKLMNPQRSTVWXYZ|0123456789";
	$sets = explode('|', $chars);
	$all = '';
	$randString = '';
	foreach($sets as $set){
		$randString .= $set[array_rand(str_split($set))];
		$all .= $set;
	}
	$all = str_split($all);
	for($i = 0; $i < $length - count($sets); $i++){
		$randString .= $all[array_rand($all)];
	}
	$randString = str_shuffle($randString);
	return $randString;
}

function imageResize($quelfic,$after_width){
	// Exemple nom miniature généré "my-picture.jpg" donnera "my-picture-MinX0200.jpg" pour une 200px par exemple
	$compl_img = "-MinX0".$after_width.".jpg";
	if( class_exists("Imagick") ){
		// On genere 2 versions une pour des vignettes et une pour affichage sur blog
		// La premiere est en 200px thumb, la deuxieme 600px medium
		// Les images avec x200x ou x600x dans leurs noms sont ignorées lors du scan
		$image = new Imagick($quelfic);
		$image->thumbnailImage($after_width, 0, false);
		$image->writeImage($quelfic.$compl_img);
		return;
	}
	if (version_compare(phpversion(), '5.5.0', '>=')) {     // Must be > 5.5 because use imagescale
		//define the quality from 1 to 100
  		$quality = 75;
  		//detect the width and the height of original image
  		list($width, $height) = getimagesize($quelfic);
  
  		//resize only when the original image is larger than expected with.
  		//this helps you to avoid from unwanted resizing.
  		if ($width > $after_width) {
  
   			//get the reduced width
   			$reduced_width = ($width - $after_width);
   			//now convert the reduced width to a percentage and round it to 2 decimal places
  	    	$reduced_radio = round(($reduced_width / $width) * 100, 2);
  
   	   		//ALL GOOD! let's reduce the same percentage from the height and round it to 2 decimal places
  	    	$reduced_height = round(($height / 100) * $reduced_radio, 2);
  	    	//reduce the calculated height from the original height
 	     	$after_height = $height - $reduced_height;
  
  	    	$img = imagecreatefromjpeg($quelfic);
 	    	//Let's do the resize thing
  			//imagescale([returned image], [width of the resized image], [height of the resized image], [quality of the resized image]);
   			$imgResized = imagescale($img, $after_width, $after_height, $quality);
  
   			//now save the resized image with a suffix called "-resized" and with its extension. 
   			imagejpeg($imgResized, $quelfic.$compl_img);
  
   			//Finally frees any memory associated with image
   			//**NOTE THAT THIS WONT DELETE THE IMAGE
    		imagedestroy($img);
   			imagedestroy($imgResized);
			return;
		}
	}
	//On resize avec function de base de gd
	createThumb($quelfic, $quelfic.$compl_img, $after_width);	
}

function createThumb($spath, $dpath, $maxd) {
	$src=@imagecreatefromjpeg($spath);
	if (!$src) return false;
	
	$srcw=imagesx($src);
	$srch=imagesy($src);
	if ($srcw<$srch) {
		$height=$maxd;
		$width=floor($srcw*$height/$srch);
	} else {
		$width=$maxd;
		$height=floor($srch*$width/$srcw);
	}
	if ($width>$srcw && $height>$srch) {
		$width=$srcw;
		$height=$srch;
	}  //if image is actually smaller than you want, leave small (remove this line to resize anyway)
	$thumb=imagecreatetruecolor($width, $height);
  	if ($height<100) {
		imagecopyresized($thumb, $src, 0, 0, 0, 0, $width, $height, imagesx($src), imagesy($src));
	} else {
		imagecopyresampled($thumb, $src, 0, 0, 0, 0, $width, $height, imagesx($src), imagesy($src));
  		imagejpeg($thumb, $dpath);
	  	return true;
	}
}

?>