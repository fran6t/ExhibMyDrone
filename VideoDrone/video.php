<?php
if (!isset($_GET["p"])){
	echo "Parametres manquants !!!!";
	return;
}
$p = urldecode($_GET["p"]);

/*
$chemin			= pathinfo($p, PATHINFO_DIRNAME);
$chanson		= pathinfo($p, PATHINFO_FILENAME);
$chanson_video	= $chemin."/".$chanson.".mp4";
$chanson_srt	= $chemin."/".$chanson.".srt";
$chanson_vtt	= $chemin."/".$chanson.".vtt";
$chanson_ft		= $chemin."/".$chanson.".ft";
$titre = str_replace("_", " ", pathinfo($chanson, PATHINFO_FILENAME));
$titre = str_replace("-", " - ", $titre);
*/
?>

<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title>Video <?php echo $p; ?></title>

<style type="text/css">
#player-overlay, #Dialog {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: #000;
  z-index: 999;
}

video {
  display: block;
  width: 100%;
  height: 100%;
}

#Dialog{
  display: none;
}

#sDialog{
  margin: auto;
}

#sDialog a{
  color: #ffffff;
	font-size: 15px;
	font-weight: 700;
	text-decoration: none;
}
</style>

</head>
<body style="background-color: #000;">
	<div id="player-overlay">
		<video controls autoplay>
			<source src="stream.php?p=<?php echo urlencode($p)."&k=".$cle=md5(uniqid(rand(), true)); ?>" type="video/mp4" />
			<p>Désolé navigateur incompatible</p>
		</video>
	</div>
  <div id="Dialog">
    <div id="sDialog">centré verticalement ? <a href="javascript:history.back()">Retour</a></div>
  </div>
  <script type='text/javascript'>
    var video = document.getElementsByTagName('video')[0];
    video.onended = function(e) {
      console.log("The End");
      const varDialog = document.getElementById('Dialog');
      const varPlayerOverlay = document.getElementById('player-overlay');
      varDialog.style.display = 'flex';
      varPlayerOverlay.style.display = 'none';
    };
  </script>
</body>