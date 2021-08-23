<?php

$mabdd = "pano.db";              		// Le nom de la bdd
$db = new SQLite3($mabdd);

// Decommente les 4 lignes une fois pour remette a zero la bdd attention irreversible
/*
$SqlString = "drop table if exists lespanos"; 
$db->exec($SqlString);
$SqlString = "drop table if exists lespanos_details"; 
$db->exec($SqlString);
*/

$tableCheck =$db->query("SELECT name FROM sqlite_master WHERE name='lespanos'");
if ($tableCheck->fetchArray() === false){
    // La table existe pas creation
    $SqlString = "CREATE TABLE [lespanos] (
	                [fichier] VARCHAR(500)  NULL,
	                [titre] VARCHAR(500)  NULL,
	                [legende] TEXT  NULL,
	                [hashfic] VARCHAR(100)  NULL
	            );";
    $db->exec($SqlString);
    $SqlString = "CREATE INDEX [IDX_lespanos_fichier] ON [lespanos]([fichier]  ASC );";
    $db->exec($SqlString);
}

$tableCheck =$db->query("SELECT name FROM sqlite_master WHERE name='lespanos_details'");
if ($tableCheck->fetchArray() === false){
    $SqlString = "CREATE TABLE [lespanos_details] (
        [fichier] VARCHAR(500)  NULL,
        [hashfic] VARCHAR(100)  NULL,
        [nom_marqueur] VARCHAR(100)  NULL,
        [couleur] VARCHAR(10)  NULL,
        [latitude] VARCHAR(20)  NULL,
        [longitude] VARCHAR(20)  NULL,
        [descri] TEXT  NULL
        );";
    $db->exec($SqlString);
    $SqlString = "CREATE INDEX [IDX_lespanos_DETAILS_hashfic] ON [lespanos_details]([hashfic]  ASC);";
    $db->exec($SqlString);
}

// Partie pour faire evoluer la bdd dans version initiale le champs short_cut existe pas
// On teste si le champs short_code existe sinon on l'ajoute
$res = $db->query('PRAGMA table_info([lespanos])');
$tmp_short_code="";
while ($col = $res->fetchArray(SQLITE3_ASSOC)) {
	if ($col['name'] == "short_code") $tmp_short_code = $col['name'];
}
if (rtrim($tmp_short_code)==""){
	$SqlString ="ALTER TABLE [lespanos] ADD COLUMN [short_code] VARCHAR(25)";
	$db->exec($SqlString);
	$SqlString = "CREATE INDEX [IDX_lespanos_short_code] ON [lespanos]([short_code]  ASC);";
    $db->exec($SqlString);
}

// Test si la chaine passée contient une definition x0000x
function isMiniature($aTester){
	if (strlen($aTester)<=10) return false;
	if (strpos($aTester,"x",-5) && strpos($aTester,"x",-10)){
		return true;
	} else {
		return false;
	}
}

// Cette foncion faisait partie au départ de scan.php
// This function scans the files folder recursively, and builds a large array
function scan($dir){
	global $db;
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
				$statement = $db->prepare('SELECT titre,legende FROM lespanos WHERE fichier = :fichier LIMIT 1;');
				$statement->bindValue(':fichier', $fichier);
				$result = $statement->execute();
				$row=$result->fetchArray(SQLITE3_ASSOC);
				// check for empty result
				if ($row != false) { // Trouvé
					$titre	= $row['titre'];
					$legende= $row['legende'];
				} else {
					// J'ai pas trouvé alors il faut insert
    				$statement = $db->prepare('INSERT INTO lespanos (fichier) VALUES (:fichier);');
					$statement->bindValue(':fichier', $fichier);
					$result = $statement->execute();
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
?>