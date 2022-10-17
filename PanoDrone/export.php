<?php
include('inc-config.php');


$quelfic = stripSlashes($_GET["p"]);
$quelficPath = dirname($quelfic);
$zipname = basename($quelfic);
$nameSphereWithExt = basename($quelfic);
$nameSphereWithoutExt = pathinfo($nameSphereWithExt, PATHINFO_FILENAME);
$lien_tlch= "export-import/".$nameSphereWithoutExt.".zip";

// Si le repertoire export-import existe pas alors create
$path_import_export = $_SERVER['DOCUMENT_ROOT']."/".$root_complement."/export-import";
if (!is_dir($path_import_export)){
    mkdir($path_import_export);
    touch($path_import_export."/index.php");        //Pour eviter les curieux on place un index.php vide
}

if (is_readable($config_file)) {
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
} else {
  echo $t->display("Parameter file missing");
  return;
}
include('inc-session.php');
include('inc-lib.php');
include('inc-bdd-ctrl.php');

if (!isset($langue)) $langue = "en";
$t = new Traductor();
$t->setLanguage($langue);

// Les fichiers compléments .sql et .txt sont créé dans un repertoire du meme nom que la sphere mais finissant par .d
// Si la sphère est a base de tuiles, ou si elle a fait l'objet de creation de miniature ce reeroire existe déjà
// Nous aurons ainsi dans le zip le fichier image de la spheres et dans le sous-repertoire avec l'extension .d tout les éléements necessaire à la sphere

$nameDir = nameDirD($quelfic);
if (!is_dir($nameDir)){
    mkdir($nameDir);
}

// On commence par creer un fichier .sql qui contiendra les requetes insert
// Read details markers
$statement = $pdo->prepare('SELECT titre,legende,legende_long,hashfic,short_code,sphere_origin,date_update FROM lespanos WHERE fichier = :fichier LIMIT 1;');
$statement->bindValue(':fichier', $quelfic, PDO::PARAM_STR);
$statement->execute();
$hashfic=$titre=$legende=$short_code="";
while ($row = $statement->fetch()) {
  $titre = $row['titre'];
  $legende = $row['legende'];
  $legende_long = $row['legende_long'];
  $hashfic = $row['hashfic'];
  $short_code = $row['short_code'];
  $sphere_origin = $row['sphere_origin'];
  $date_update = $row['date_update'];
}

$sqlstring_lespanos="INSERT INTO lespanos_import (fichier, titre, legende, legende_long, hashfic, short_code, sphere_origin, date_update) 
            VALUES 
            (\"$quelfic\", \"".addslashes($titre)."\", \"".addslashes($legende)."\", \"".addslashes($legende_long)."\", \"$hashfic\", \"$short_code\", \"$sphere_origin\",\"$date_update\");\n";

//echo $sqlstring_lespanos;

// Store all markers form form and display on sphere
$statement = $pdo->prepare('SELECT fichier,hashfic,nom_marqueur,couleur,latitude,longitude,descri,marker_center FROM lespanos_details WHERE hashfic = :hashfic;');
$statement->bindValue(':hashfic', $hashfic, PDO::PARAM_STR);
$statement->execute();

$sqlstring_lespanos_details="";
while ($row = $statement->fetch()) {
    $sqlstring_lespanos_details.="INSERT INTO lespanos_details_import (fichier,hashfic,nom_marqueur, couleur, latitude, longitude, descri, marker_center) 
    VALUES 
    (\"".$row['fichier']."\",\"".$row['hashfic']."\", \"".addslashes($row['nom_marqueur'])."\", \"".$row['couleur']."\", \"".$row['latitude']."\", \"".$row['longitude']."\", \"".addslashes($row['descri'])."\", \"".$row['marker_center']."\");\n";
}

//echo $sqlstring_lespanos_details;

// Maintenant on enregistre dans un fichier du nom du fichier de la sphere avec l'extension .sql
file_put_contents($nameDir.'/'.$nameSphereWithoutExt.'.sql', $sqlstring_lespanos.$sqlstring_lespanos_details);

// On enregistree aussi un fichier .txt avec le nom du fichier de la sphère la date de l'export
// Ce sont ces infos qui servirons à ne pas écraser une autre sphère lors d'une imortation future
file_put_contents($nameDir.'/'.$nameSphereWithoutExt.'.txt', $hashfic.";".$date_update);

$archive_name = "$path_import_export/".$nameSphereWithoutExt.".zip"; // name of zip file
$archive_folder = $quelficPath; // the folder which you archivate

$zip = new ZipArchive();
$zip->open($archive_name, ZipArchive::CREATE);

$dirName = $archive_folder;

if (!is_dir($dirName)) {
    throw new Exception('Directory ' . $dirName . ' does not exist');
}

// On compresse le repertoire du nom dela sphere .d
$dirName = realpath($nameDir);

if (substr($dirName, -1) != '/') {
    $dirName.= '/';
}

//echo "dirName=".$dirName;
/*
* NOTE BY danbrown AT php DOT net: A good method of making
* portable code in this case would be usage of the PHP constant
* DIRECTORY_SEPARATOR in place of the '/' (forward slash) above.
*/

$dirStack = array($dirName);
//Find the index where the last dir starts
$cutFrom = strrpos(substr($dirName, 0, -1), '/')+1;

while (!empty($dirStack)) {
    $currentDir = array_pop($dirStack);
    $filesToAdd = array();

    $dir = dir($currentDir);
    while (false !== ($node = $dir->read())) {
        if (($node == '..') || ($node == '.')) {
            continue;
        }
        if (is_dir($currentDir . $node)) {
            array_push($dirStack, $currentDir . $node . '/');
        }
        if (is_file($currentDir . $node)) {
            $filesToAdd[] = $node;
        }
    }

    $localDir = substr($currentDir, $cutFrom);
    $zip->addEmptyDir($localDir);
   
    foreach ($filesToAdd as $file) {
        $zip->addFile($currentDir . $file, $localDir . $file);
    }
}

//On ajoute la sphere
$zip->addFile($quelfic, $zipname);

$zip->close(); 
    

?>
<!DOCTYPE html>
<html>
<head lang="<?php echo $langue; ?>">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<title><?php echo $t->display("Sphères"); ?></title>


	<!-- Include our stylesheet -->
	<link href="assets/css/styles.css" rel="stylesheet"/>
	<link href="css/navbar.css" rel="stylesheet"/>
	<style type="text/css">
        .lnktlch{
            font-weight: 700;
			color: yellow; 
        }
        .letext{
            margin-left: 1em;
            color: white;
        }
        fieldset{
            color: white;
            padding: 15px;
        }
        legend{
            color: white;
            font-weight:bold;
        }
	</style>

</head>
<body>

<nav class="menu">
	<ul>
		<li><a href="gest.php" title="<?php echo $t->display("Back to list"); ?>"><?php echo $t->display("Back to list"); ?></a></li>
	</ul>
</nav>
<div class="letext">
    <br /><br />
    <h1><?php echo $t->display("Zip file for export created"); ?></h1>
    <br />
    <?php echo display_msg($msg); ?>
    <br />
    <p>
        <br />
        <br />
    	<a href="<?php echo $lien_tlch; ?>" class="lnktlch" title="<?php echo $t->display("Download Zip File"); ?>"><?php echo $t->display("Download Zip File"); ?></a><br />
    </p>
</div>
</body>
</html>