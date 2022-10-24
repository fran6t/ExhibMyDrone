<?php
if (isset($_GET['c'])){
  header('Location:pano.php?c='.$_GET['c']);
  return;
}
include('inc-config.php');
include('inc-lib.php');
include('inc-bdd-ctrl.php');

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
	<style>
		.detailsfile a {
			position: static !important;
			color: white;
		}
	</style>

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
        <div class="namefile"><a href="../">[<?php echo $t->display("Back"); ?>]</a> / <a href="gest.php">[<?php echo $t->display("Administration"); ?>]</a> |  <a href="https://github.com/fran6t/ExhibMyDrone"><?php echo $t->display("Shared on Github"); ?></a> Version <?php echo $version ?><br /><?php echo $t->display("Credits"); ?>: <a href="http://tutorialzine.com/2014/09/cute-file-browser-jquery-ajax-php/">Cute File Browser with jQuery, AJAX and PHP</a> & <a href="https://photo-sphere-viewer.js.org/">Photo Sphere Viewer</a> & <a href="https://tinyfilemanager.github.io/">TinyFileManager</a></div>
        <div id="tzine-actions"></div>
        <!-- <span class="close"></span> -->
    </footer>

	<!-- Include our script files -->
	<script>
	<?php 
	// On active une variable four faire connaitre que nous sommes en session donc administrateur afin d'avoir un lien 
	// direct vers le formulaire de gestion de spahere
	if ( !defined( 'FM_SESSION_ID')) {
		define('FM_SESSION_ID', 'filemanager');
	}
	session_name(FM_SESSION_ID );	// On pointe la session de tinyfilemanager
	session_start();
	if (isset($_SESSION[FM_SESSION_ID]['logged'])){
		echo 'let maSession = true;';	
	} else {
		echo 'let maSession = false;';	
	}
	?>
	</script>
	<script src="assets/js/jquery-1.11.0.min.js"></script>
	<script src="assets/js/script.js"></script>

</body>
</html>