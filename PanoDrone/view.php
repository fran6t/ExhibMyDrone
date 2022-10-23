<?php
/* Permit view and zoom on original .jpg
*
*
* 
* Use Fullscreen Image Zoom and Pan with Jquery 
* Original version by Samil Hazir (https://github.com/saplumbaga)
* Version 2.x and .3.x by JM Alarcon (https://github.com/jmalarcon/)
* Version used is https://github.com/jmalarcon/jquery.pan
*/

include('inc-lib.php');

$quelfic = urldecode($_GET["p"]);
$quelimg = urldecode($_GET["img"]);
$msgError ="";
if (!file_exists($quelfic)){
	$msgError .= "<br />Pano manquants !!!!";
}
$path_parts = pathinfo($quelfic);
$repHD = $path_parts['dirname']."/".$path_parts['filename'].".d/src";
$repTHMB = $path_parts['dirname']."/".$path_parts['filename'].".d/thmb";
if (!is_dir($repHD)){
	$msgError .= "<br />Repertoire HD manquants !!!!";
}
$imgHD = $repHD."/".$quelimg;
$imgTHMB =  $repTHMB."/".$quelimg;
if (!file_exists($repHD."/".$quelimg)){
	//On Sd card file .jpg is upper .JPG
	$imgHD = $repHD."/".strtoupper($quelimg);
	if (!file_exists($imgHD)){
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
		<li><a href="javascript:window.close();"><img src="jquery.pan/dist/css/img/close.png" style="width: 32px;height: 32px;"/></a></li>
	</ul>
</nav>
<?php
	if ($msgError==""){
		// If directory thumbnail exist
		if (is_dir($repTHMB)){
			for ($iImg=1; $iImg <= 26; $iImg++){      // For 26 picture of the sphere taken by DJI mini air 2
				$name_IMG = "DJI_".str_pad ( $iImg, 4, '0', STR_PAD_LEFT ).".jpg";
				$widthStyle="";
				if ($name_IMG==$quelimg) $widthStyle=' style="width:300px; border-width:5px; border-color:red; border-style:solid;" ';
				echo '<a class="pan" data-big="'.$repHD.'/'.$name_IMG.'" href="#"><img src="'.$repTHMB.'/'.$name_IMG.'" alt="'.$name_IMG.'"'.$widthStyle.'></a> ';
			}
		} else {
		?>
			<a class="pan" data-big="<?php echo $imgHD; ?>" href="#"><img src="<?php echo $imgHD; ?>" alt="" style="width:300px;"/></a>
		<?php
		}
	} else {
		echo $msgError;
	}
?>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="jquery.pan/dist/jquery.pan.min.js"></script>
<script type="text/javascript">
    $(window).on('load', function() {
		$(".pan").pan();
    });
</script>
</body>