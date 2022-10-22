<?php
$frontend = true;				// By default we considere all file, if $frontend = true then private file are not show 
								// If script php need accepte private file  set $frontend = false, for example in  gest.php

								
// Since v0.1 db sqlite and inc-config-perso.ini.php must be in directory sphere
// The reason : All personal data or work is now in directory sphere
// Save directory sphere ($dir) = save all your work the file out directory and subdirectoeyr is program and could be erase by update
If (isset($version)){
	//Before 0.1 ther is no version number .db and inc-config-perso.ini.php is a them level .php
	//Since 0.1 file .db and inc-config-perso.ini.php must be placed in directory pointed by $dir (spheres)
	if (file_exists($db)){
		// on deplace le fichier db
		rename($db, $dir."/".$db);
	}
	if (file_exists($config_file)){
		// on deplace le fichier inc-config-perso.ini.php
		rename($config_file, $dir."/".'inc-config-perso.ini.php');
		
	}
	$db = $dir."/".$db;
	$config_file = $dir."/".'inc-config-perso.ini.php';
}

if (is_readable($config_file) && is_dir($dir)) {	// Si le fichier de config est ok et que le repertoire d'accueils des spheres est ok
	$ini =  parse_ini_file($config_file);
	$langue = $ini['langue'];
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
} else {											// Sommes dans le cas d'un installation il faut remplir les parametres
	header('Location: param.php');
}

if ($bddtype=='mysql'){
	$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=$port";
	try {
		$pdo = new PDO($dsn, $user, $pass);
   	} catch (Exception $e) {
		echo "Access database fail : ".$e->getMessage();
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
		echo "Access SQLite fail : ".$e->getMessage();
		die();
	}
	
}

// Uncoment the next 4 lines = reset table of database
/*
$SqlString = "drop table if exists lespanos"; 
$pdo->exec($SqlString);

$SqlString = "drop table if exists lespanos_details"; 
$pdo->exec($SqlString);
*/

if (!DB_table_exists('lespanos')){
	$SqlString = createTable('lespanos');
	$pdo->exec($SqlString);
	$SqlString = "CREATE INDEX 'IDX_lespanos_fichier' ON 'lespanos' ('fichier')";
	$pdo->exec($SqlString);
}

if (!DB_table_exists('lespanos_details')){
	$SqlString = createTable('lespanos_details');
    $pdo->exec($SqlString);
    $SqlString = "CREATE INDEX [IDX_lespanos_DETAILS_hashfic] ON [lespanos_details] ([hashfic])";
    $pdo->exec($SqlString);
}

// If Col named short_code not exist then add to the table
if (!DB_column_exists('lespanos','short_code')){
	$SqlString ="ALTER TABLE [lespanos] ADD COLUMN [short_code] VARCHAR(25)";
	$pdo->exec($SqlString);
	$SqlString = "CREATE INDEX [IDX_lespanos_short_code] ON [lespanos]([short_code]  ASC);";
    $pdo->exec($SqlString);
}

// If Col named legende_long not exist then add to the table
if (!DB_column_exists('lespanos','legende_long')){
	$SqlString ="ALTER TABLE [lespanos] ADD COLUMN [legende_long] BLOB";
	$pdo->exec($SqlString);
}

// If Col named marker_center not exist then add to the table
if (!DB_column_exists('lespanos_details','marker_center')){
	$SqlString ="ALTER TABLE [lespanos_details] ADD COLUMN [marker_center] VARCHAR(1)";
	$pdo->exec($SqlString);
}

// If Col named sphere_origin not exist then add to the table it's use to find coordinate to show original .jpg
// 0 Original sphere obtain by direct share from app Dji Album
// 1 Sphere create with Hugin and dji_assistant
if (!DB_column_exists('lespanos','sphere_origin')){
	$SqlString ="ALTER TABLE [lespanos] ADD COLUMN [sphere_origin] VARCHAR(1)";
	$pdo->exec($SqlString);
}

// If Col named date_update not exist then add to the table it's use to import export sphere
if (!DB_column_exists('lespanos','date_update')){
	// $SqlString ="ALTER TABLE [lespanos] ADD COLUMN [date_update] DATETIME DEFAULT CURRENT_TIME";
	// Patch $SqlString generate Error Cannot add a column with non-constant default
	// We must pass by a temprary table
	$SqlString = createTable('lespanos_new');
	$pdo->exec($SqlString);


	$SqlString = "INSERT INTO 'lespanos_new' ('fichier','titre','legende','legende_long','hashfic','short_code','sphere_origin') SELECT fichier,titre,legende,legende_long,hashfic,short_code,sphere_origin FROM 'lespanos';";
	$pdo->exec($SqlString);
	
	$SqlString = "drop table 'lespanos';";
	$pdo->exec($SqlString);

	$SqlString = "alter table 'lespanos_new' rename to 'lespanos';";
	$pdo->exec($SqlString);
}

?>