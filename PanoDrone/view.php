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
if (!file_exists($repHD."/".$quelimg)){
	//On Sd card file .jpg is upper .JPG
	$imgHD = $repHD."/".strtoupper($quelimg);
	if (!file_exists(strtoupper($imgHD))){
		$msgError .= "<br />Image HD manquante !!!!";
	}
}
?>
<!DOCTYPE HTML>
<html>
<head>
<title>Fullscreen Image Zoom and Pan with Jquery</title>
<meta http-equiv="X-UA-Compatible" content="IE=8">
<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0'/>
<link href="jquery.pan/dist/css/jquery.pan.css" rel="stylesheet" type="text/css"/>
<link href="css/navbar.css" rel="stylesheet"/>
</head>
<body>
<nav class="menu">
	<ul>
		<li><a href="javascript:window.close();">Fermer</a></li>
	</ul>
</nav>
<?php
	if ($msgError==""){
		?>
		<a class="pan" data-big="<?php echo $imgHD; ?>" href="#"><img src="<?php echo $imgHD; ?>" alt="" /></a>
		<?php
	} else {
		echo $msgError;
	}
?>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="jquery.pan/src/js/jquery.pan.js"></script>
<script type="text/javascript">
    $(window).on('load', function() {
		$(".pan").pan();
    });
</script>
</body>