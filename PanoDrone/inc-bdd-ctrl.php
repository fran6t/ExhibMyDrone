<?php

function createTable($table_name){
	if ($table_name=="lespanos" || $table_name=="lespanos_new" || $table_name=="lespanos_import"){
		$SqlString = "CREATE TABLE '".$table_name."' (
			'fichier' VARCHAR(500)  NULL,
			'titre' VARCHAR(500)  NULL,
			'legende' TEXT  NULL,
			'legende_long' BLOB NULL,
			'hashfic' VARCHAR(100)  NULL,
			'short_code' VARCHAR(25),
			'sphere_origin' VARCHAR(1),
			'date_update' DATETIME DEFAULT CURRENT_TIMESTAMP	
		);";
	}

	if ($table_name=="lespanos_details" || $table_name=="lespanos_details_new" || $table_name=="lespanos_details_import"){
		$SqlString = "CREATE TABLE '".$table_name."' (
			'fichier' VARCHAR(500)  NULL,
			'hashfic' VARCHAR(100)  NULL,
			'nom_marqueur' VARCHAR(100)  NULL,
			'couleur' VARCHAR(10)  NULL,
			'latitude' VARCHAR(20)  NULL,
			'longitude' VARCHAR(20)  NULL,
			'descri' TEXT  NULL,
			'marker_center' VARCHAR(1)
			);";
	}

	if (!isset($SqlString)) $SqlString = "Major error table_name not found";

	return $SqlString;

}



$frontend = true;				// By default we considere all file, if $frontend = true then private file are not show 
								// If script php need accepte private file  set $frontend = false, for example in  gest.php

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