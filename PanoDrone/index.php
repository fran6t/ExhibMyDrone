<?php
if (isset($_GET['c'])){
  header('Location:pano.php?c='.$_GET['c']);
  return;
}
include('inc-config.php');
include('inc-session.php');
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
  echo "Fichier parametres manquant";
  return;
}
include('inc-lib.php');
if (!isset($langue)) $langue = "en";
$t = new Traductor();
$t->setLanguage($langue);
?>
<!DOCTYPE html>
<html>
<head lang="<?php echo $langue; ?>">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<title><?php echo $t->display("Panorama 360°"); ?></title>
	<meta name="description"  content="<?php echo $t->display("Listing of 360 ° Panoramas or Spheres present on this website."); ?>" />

	<!-- Include our stylesheet -->
	<link href="assets/css/styles.css" rel="stylesheet"/>

</head>
<body>

	<div class="filemanager">

		<div class="search">
			<input type="search" placeholder="<?php echo $t->display("Find a file..."); ?>" />
		</div>

		<div class="breadcrumbs"></div>

		<ul class="data"></ul>

		<div class="nothingfound">
			<div class="nofiles"></div>
			<span>No files here.</span>
		</div>

	</div>

	<footer>
        <div class="namefile"><a href="../">[<?php echo $t->display("Back"); ?>]</a> / <a href="gest.php">[<?php echo $t->display("Administration"); ?>]</a> |  <a href="https://github.com/fran6t/ExhibMyDrone"><?php echo $t->display("Shared on Github"); ?></a><br /><?php echo $t->display("Credits"); ?>: <a href="http://tutorialzine.com/2014/09/cute-file-browser-jquery-ajax-php/">Cute File Browser with jQuery, AJAX and PHP</a> & <a href="https://photo-sphere-viewer.js.org/">Photo Sphere Viewer</a> & <a href="https://tinyfilemanager.github.io/">TinyFileManager</a></div>
        <div id="tzine-actions"></div>
        <!-- <span class="close"></span> -->
    </footer>

	<!-- Include our script files -->
	<script src="assets/js/jquery-1.11.0.min.js"></script>
	<script src="assets/js/script.js"></script>

</body>
</html>