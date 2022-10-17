<?php
include('inc-config.php');

// Si le repertoire export-import existe pas alors create
$path_import_export = $_SERVER['DOCUMENT_ROOT']."/".$root_complement."/export-import";
if (!is_dir($path_import_export)){
    mkdir($path_import_export);
    touch($path_import_export."/index.php");        //Pour eviter les curieux on place un index.php vide
}

if (!is_dir($path_import_export."/import")){    // C dans ce repertoire que le zip est mis puis decompressé avant d'être placé dans Sphere
    mkdir($path_import_export."/import");
    touch($path_import_export."/import/index.php");        //Pour eviter les curieux on place un index.php vide
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



$msg = "";
// Si nous arrivons du formulaire
if (isset($_POST['quelForm'])){
    // Si c'est le premier formulaire c'est donc celui qui sert a telecharger le zip
    // On va chercher si une sphere existe déjà pour confirmer ce que l'on doit faire
    if (isset($_FILES["file"]["type"])){
        //Nous traitons uniquement des fichier .zip
        if ($_FILES["file"]["type"]!="application/zip"){
            $msg.= "Fichier .zip attendu (fichier non conforme) !";
        } else {
            $destination = $_POST['destination'];
            move_uploaded_file($_FILES["file"]["tmp_name"],"export-import/import/" . $_FILES["file"]["name"]);
            $msg.= "Fichier ".$_FILES["file"]["name"]." enregistré !";
            $zip = new ZipArchive;
            if ($zip->open("export-import/import/" . $_FILES["file"]["name"]) === TRUE) {
                $zip->extractTo('export-import/import/');
                $zip->close();
                $msg.= "<br />Dezip ok";
            } else {
                $msg.= "<br />Dezip failed";
            }
            // On recupere maintenant l'information de date des dernières modifiations de saisie sur la sphère
            // Le fichier txt doit se trouver dans le sous repertoire .d portant le meme nom que le zip
            $nom_Sphere = pathinfo($_FILES["file"]["name"], PATHINFO_FILENAME);     // On retire le .zip 
            $nom_Sphere_Without_Ext = pathinfo($nom_Sphere, PATHINFO_FILENAME);     // On retire le .jpg 
            $fictxt = 'export-import/import/'.$nom_Sphere.'.d/'.$nom_Sphere_Without_Ext.'.txt';
            $handle = fopen($fictxt, 'rb');
            $tab_hashfic = explode(";",fread($handle, filesize($fictxt)));
            // Maintenant une recherche si une sphere de ce nom existe déjà
            $statement = $pdo->prepare('SELECT fichier,titre,legende,legende_long,hashfic,short_code,sphere_origin,date_update FROM lespanos WHERE hashfic = :hashfic LIMIT 1;');
            $statement->bindValue(':hashfic', $tab_hashfic[0], PDO::PARAM_STR);
            $statement->execute();
            $hashfic=$titre=$legende=$short_code="";
            while ($row = $statement->fetch()) {
                $fichier = $row['fichier'];
                $titre = $row['titre'];
                $legende = $row['legende'];
                $legende_long = $row['legende_long'];
                $hashfic = $row['hashfic'];
                $short_code = $row['short_code'];
                $sphere_origin = $row['sphere_origin'];
                $date_update = $row['date_update'];
            }
            if ($hashfic!=""){
                $msg.= "<br />".$t->display("Carefully a Sphere already exist !!!!");
                //On memorise le nouvel emplacement pour le formulaire de confirmation
                $new_Name = $_POST['destination']."/".$nom_Sphere.".jpg";
            } else {
                //On neutralise quelform pour ne pas presenter le formulaire de confirmation
                $_POST['quelForm']="";
                //La sphere n'existe pas on peu donc faire les insertions et la copie sans passer par le deuxieme formulaire
                //echo "appel SphereImport avec param :".$tab_hashfic[0].",".$nom_Sphere.",".$_POST['destination'];
                SphereImport($tab_hashfic[0],$nom_Sphere,$_POST['destination']);
                $msg.= "<br />".$t->display("Import successful");
            }   
        }
    // C'est le formulaire de confirmation    
    } else {
        if ($_POST['ecraser'] == "Y"){
            // On commence par effacer ce qui concerne la sphere
            $hasficForm = stripslashes(rtrim($_POST['hashficForm']));
            if($hasficForm==""){
                echo $t->display("Error Sphere not found");
                return;
            }
            SphereEfface($hasficForm);
            SphereImport($hasficForm,$_POST['quelSphereForm'],$_POST['destinationForm']);
            $msg.= "<br />".$t->display("Import successful");            

        } else {
            $msg = "<br />".$t->display("Import canceled");
        }
    }
}

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
    <h1><?php echo $t->display("Import zip sphere"); ?></h1>
    <br />
    <?php echo display_msg($msg); ?>
    <br />
    <?php
    // Si il y a pas eu encore de selection de fichier alors on presente le formulaire selection nouvel emplacement 
    // et choix du fichier a télécharge
    if (!isset($_POST['quelForm'])){
    ?>  
        <form action="import.php" method="post" enctype="multipart/form-data">
            <input id="quelForm" name="quelForm" type="hidden" value="fichier" />
            <br />
            <label for="destination"><?php echo $t->display("Please choose destination Sphere : "); ?></label>
            <br />
            <?php
            // On construit l'arbre pour remplissage de la combo du choix 
            $comboDest = '<select name="destination" id="destination">';
            dirToOptions($dir);
            $comboDest .= '</select>';
            echo $comboDest;
            ?>
            <br />
            <i><?php echo $t->display("If destination not exist in combo, please use TinyFileManager for create directory"); ?> <a href="tinyfilemanagergest/tinyfilemanager.php" title="<?php echo $t->display("Link to TinyFileManager"); ?>" class="lnktlch">TinyFileManager</a></i>
            <br />
            <br />
            <label for="file"><?php echo $t->display("Choose your ZIP file :"); ?></label>
            <input type="file" name="file" id="file" />
            <br />
            <br />
            <input type="submit" name="submit" value="<?php echo $t->display("Submit"); ?>" />
        </form>
    <?php
    } else {
        if ($_POST['quelForm']=="fichier"){
    ?>
        <form action="import.php" method="post">
            <input id="quelForm" name="quelForm" type="hidden" value="<?php echo $t->display("Confirm"); ?>" />
            <input id="hashficForm" name="hashficForm" type="hidden" value="<?php echo $hashfic; ?>" />
            <input id="destinationForm" name="destinationForm" type="hidden" value="<?php echo $destination; ?>" />
            <input id="quelSphereForm" name="quelSphereForm" type="hidden" value="<?php echo $nom_Sphere; ?>" />
            <br />
            <label for="ecraser"><?php echo $t->display("Confirm overwrite :"); ?></label>
            <br />
            <br />
            <select name="ecraser"><option value="Y"><?php echo $t->display("Yes"); ?></option><option value="N" selected><?php echo $t->display("No"); ?></option></select>
            <br />
            <br />
            <input type="submit" name="submit" value="<?php echo $t->display("Confirm"); ?>" />
        </form>
    <?php    
        }
    }    
    ?>
</div>
</body>
</html>