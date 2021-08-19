<?php
if (isset($_GET['c'])){
  header('Location:pano.php?c='.$_GET['c']);
}
?>
<!DOCTYPE html>
<html>
<head lang="en">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<title>PanoDrone</title>


	<!-- Include our stylesheet -->
	<link href="assets/css/styles.css" rel="stylesheet"/>

</head>
<body>

	<div class="filemanager">

		<div class="search">
			<input type="search" placeholder="Find a file.." />
		</div>

		<div class="breadcrumbs"></div>

		<ul class="data"></ul>

		<div class="nothingfound">
			<div class="nofiles"></div>
			<span>No files here.</span>
		</div>

	</div>

	<footer>
        <div class="namefile">Crédits: <a href="http://tutorialzine.com/2014/09/cute-file-browser-jquery-ajax-php/">Cute File Browser with jQuery, AJAX and PHP</a> & <a href="https://photo-sphere-viewer.js.org/">Photo Sphere Viewer</a> & <a href="https://tinyfilemanager.github.io/">TinyFileManager</a><br />Administration: <a href="gest.php">ici</a> <a href="../">Retour</a></div>
        <div id="tzine-actions"></div>
        <!-- <span class="close"></span> -->
    </footer>

	<!-- Include our script files -->
	<script src="assets/js/jquery-1.11.0.min.js"></script>
	<script src="assets/js/script.js"></script>

</body>
</html>