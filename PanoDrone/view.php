<?php
$quelfic = urldecode($_GET["p"]);
$quelimg = urldecode($_GET["img"]);
$msgError ="";
if (!file_exists($quelfic)){
	$msgError .= "<br />Pano manquants !!!!";
}
$path_parts = pathinfo($quelfic);
$repHD = $path_parts['dirname']."/".$path_parts['filename'].".d";
if (!is_dir($repHD)){
	$msgError .= "<br />Repertoire HD manquants !!!!";
}
$imgHD = $repHD."/".$quelimg; 
if (!file_exists($imgHD)){
	$msgError .= "<br />Image HD manquante !!!!";
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="css/navbar.css" rel="stylesheet"/>
</head>
<body>
<nav class="menu">
	<ul>
		<li><a href="javascript:window.close();">Fermer</a></li>
	</ul>
</nav>
<div style="overflow:auto;">
	<?php
	if ($msgError==""){
		echo '<img src="'.$imgHD.'" width="4000" height="3000"/>';
	} else {
		echo $msgError;
	}
	?>
</div>  
</body>
</html>
