<?php

$mabdd = "video.db";              		// Le nom de la bdd
$db = new SQLite3($mabdd);

// Decommente les 4 lignes une fois pour remette a zero la bdd attention irreversible
/*
$SqlString = "drop table if exists lesvideos"; 
$db->exec($SqlString);
$SqlString = "drop table if exists lesvideos_details"; 
$db->exec($SqlString);
*/

$tableCheck =$db->query("SELECT name FROM sqlite_master WHERE name='lesvideos'");
if ($tableCheck->fetchArray() === false){
    // La table existe pas creation
    $SqlString = "CREATE TABLE [lesvideos] (
	                [fichier] VARCHAR(500)  NULL,
	                [titre] VARCHAR(500)  NULL,
	                [legende] TEXT  NULL,
	                [hashfic] VARCHAR(100)  NULL
	            );";
    $db->exec($SqlString);
    $SqlString = "CREATE INDEX [IDX_LESvideos_fichier] ON [lesvideos]([fichier]  ASC );";
    $db->exec($SqlString);
}

$tableCheck =$db->query("SELECT name FROM sqlite_master WHERE name='lesvideos_details'");
if ($tableCheck->fetchArray() === false){
    $SqlString = "CREATE TABLE [lesvideos_details] (
        [fichier] VARCHAR(500)  NULL,
        [hashfic] VARCHAR(100)  NULL,
        [nom_marqueur] VARCHAR(100)  NULL,
        [couleur] VARCHAR(10)  NULL,
        [latitude] VARCHAR(20)  NULL,
        [longitude] VARCHAR(20)  NULL,
        [descri] TEXT  NULL
        );";
    $db->exec($SqlString);
    $SqlString = "CREATE INDEX [IDX_LESvideos_DETAILS_hashfic] ON [lesvideos_details]([hashfic]  ASC);";
    $db->exec($SqlString);
}


// Cette foncion faisait partie au départ de scan.php
// This function scans the files folder recursively, and builds a large array
function scan($dir){
	global $db;
	$files = array();

	// Is there actually such a folder/file?

	if(file_exists($dir)){
	
		foreach(scandir($dir) as $f) {
		
			if(!$f || $f[0] == '.' || pathinfo($f, PATHINFO_EXTENSION )=="xml") {
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
				$statement = $db->prepare('SELECT titre,legende FROM lesvideos WHERE fichier = :fichier LIMIT 1;');
				$statement->bindValue(':fichier', $fichier);
				$result = $statement->execute();
				$row=$result->fetchArray(SQLITE3_ASSOC);
				// check for empty result
				if ($row != false) { // Trouvé
					$titre	= $row['titre'];
					$legende= $row['legende'];
				} else {
					// J'ai pas trouvé alors il faut insert
    				$statement = $db->prepare('INSERT INTO lesvideos (fichier) VALUES (:fichier);');
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
?>