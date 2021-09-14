<?php
include('inc-config.php');
if (is_readable($config_file)) {
	$ini =  parse_ini_file($config_file);
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

if (isset($_GET['c'])){
  // Sommes en presence d'une url courte on redirige directement au bon endroit
  $statement = $pdo->prepare('SELECT fichier FROM lespanos WHERE short_code = :short_code LIMIT 1;');
  $statement->bindValue(':short_code', rtrim($_GET['c']), PDO::PARAM_STR );
  $statement->execute();
  $fichier="";
  while ($row = $statement->fetch()) {
    $quelfic = $row['fichier'];
  }
} else {
  if (!isset($_GET["p"])){
    echo "Parametres manquants !!!!";
    return;
  }
  $quelfic = urldecode($_GET["p"]);
} 	

// On test si le fichier existe 
if (!file_exists($quelfic)){
  echo $quelfic;
	echo "Pano manquants !!!!";
	return;
}
$goMarker = ""; 
$titre=$legende=$titrerouge=$latituderouge=$descmarqueurrouge=$titrebleu=$latitudebleu=$descmarqueurbleu="";
$statement = $pdo->prepare('SELECT hashfic,titre,legende FROM lespanos WHERE fichier = :fichier LIMIT 1;');
$statement->bindValue(':fichier', $quelfic);
$statement->execute();

$hashfic=$titre=$legende="";
while ($row = $statement->fetch()) {
  $titre = $row['titre'];
  $legende = $row['legende'];
  $hashfic = $row['hashfic'];
}

if (rtrim($hashfic) == ""){
  $hashfic = hash_file('md5', $quelfic, false);
  // On mémorise le $hashfic c'est lui qui fera la liaison entre la table lespanos et lespanos_details
  $stmt = $pdo->prepare('UPDATE lespanos SET hashfic = :hashfic WHERE fichier = :fichier');
  $stmt->bindValue(':hashfic', $hashfic, PDO::PARAM_STR);
  $stmt->bindValue(':fichier', $quelfic, PDO::PARAM_STR);
  $stmt->execute();
}

// On recupere les marqueurs
$statement = $pdo->prepare('SELECT * FROM lespanos_details WHERE hashfic = :hashfic;');
$statement->bindValue(':hashfic', $hashfic, PDO::PARAM_STR);
$statement->execute();
$nb_marqueur = $i = 0;
$jmarqueur = "";
while ($row = $statement->fetch()) {
  $i = $i +1;
  $nb_marqueur = $i;
  $nom_marqueur[$nb_marqueur] = $row['nom_marqueur'];
  $couleur[$nb_marqueur] = $row['couleur'];
  $latitude[$nb_marqueur] = $row['latitude'];
  $longitude[$nb_marqueur] = $row['longitude'];
  $descri[$nb_marqueur] = "<div class=\"lainner\">".$row['descri']."</div>";
  // On construit le tableau des marqueurs javascript
  $jmarqueur.="a.push({\n";
  $jmarqueur.="\t id       : 'Marker".$nb_marqueur."',\n";
  $jmarqueur.="\t tooltip  : {\n";
  $jmarqueur.="\t\t content : '".addslashes($row['nom_marqueur'])."',\n";
  $jmarqueur.="\t\t position: 'bottom right',\n";
  $jmarqueur.="\t },\n";
  $jmarqueur.="\t content  : document.getElementById('pin-".$nb_marqueur."').innerHTML,\n";
  $jmarqueur.="\t latitude : ".$row['latitude'].",\n";
  $jmarqueur.="\t longitude: ".$row['longitude'].",\n";
  $jmarqueur.="\t image    : 'example/assets/pin-".$row['couleur'].".png',\n";
  $jmarqueur.="\t width    : 32,\n";
  $jmarqueur.="\t height   : 32,\n";
  $jmarqueur.="\t anchor   : 'bottom center',\n";
  $jmarqueur.="});\n";
  $m = "Marker".$nb_marqueur;
  if ($row['marker_center']=="O"){
    $goMarker  = "PSV.once('ready', () => {\n";
    $goMarker .= "  setTimeout(() => {\n";
    $goMarker .= "  markers.toggleAllTooltips();\n";  
    $goMarker .= "  markers.gotoMarker('".$m."',500);\n";
    $goMarker .= "  });\n";
    $goMarker .= "});\n";  
  }
}

// Si un marqueur est defini dans l'URL c'est lui qui sera priortaire, la sphère s'ouvre alors centrée sur lui
if (isset($_GET['m'])){
  $m = urldecode($_GET['m']);
  $goMarker  = "PSV.once('ready', () => {\n";
  $goMarker .= "  setTimeout(() => {\n";
  $goMarker .= "  markers.gotoMarker('".$m."',500);\n";
  $goMarker .= "  markers.toggleAllTooltips();\n";
  $goMarker .= "  });\n";
  $goMarker .= "});\n";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $titre; ?></title>
  <meta property="og:type" content="website" />
  <meta property="og:title" content="<?php echo $titre; ?>" />
  <?php
  $nbrePixels = "-MinX0600.jpg";
  $lien = $monDomaine.'/'.$root_complement.'/pano.php?p='.$quelfic;
  $lienImg = $monDomaine.'/'.$root_complement.'/'.$quelfic.$nbrePixels;
  ?>
  <meta property="og:image" content="<?php echo $lienImg; ?>"/>
  <meta property="og:image:alt" content="Miniature à plat de la sphère ou panorama représentant une vue de <?php echo $titre; ?>" />
  <meta property="og:url" content="<?php echo $lien; ?>" />
  <meta property="og:description" content="Panorama 360° : Sphère <?php echo $titre; ?>" />

  <meta name="description"  content="Panorama 360° : Sphère <?php echo $titre; ?>" />
  <link rel="canonical" href="<?php echo $lien; ?>" />

  <link rel="stylesheet" href="dist/photo-sphere-viewer.css">
  <link rel="stylesheet" href="dist/plugins/markers.css">

  <style>
    html, body {
      width: 100%;
      height: 100%;
      overflow: hidden;
      margin: 0;
      padding: 0;
    }

    #photosphere {
      width: 100%;
      height: 100%;
    }

    .psv-button.custom-button {
      font-size: 22px;
      line-height: 20px;
    }

    .demo-label {
      color: white;
      font-size: 20px;
      font-family: Helvetica, sans-serif;
      text-align: center;
      padding: 5px;
      border: 1px solid white;
      background: rgba(0, 0, 0, 0.4);
    }
    .lainner a {
      color: white;
      font-weight: bold;
    }
  </style>
</head>
<body>

<div id="photosphere"></div>

<script src="node_modules/three/build/three.js"></script>
<script src="node_modules/promise-polyfill/dist/polyfill.js"></script>
<script src="node_modules/uevent/browser.js"></script>
<script src="node_modules/nosleep.js/dist/NoSleep.js"></script>
<script src="dist/photo-sphere-viewer.js"></script>
<script src="dist/plugins/gyroscope.js"></script>
<script src="dist/plugins/stereo.js"></script>
<script src="dist/plugins/markers.js"></script>
<script src="creativa-popup.js"></script>

<!-- text used for the marker description -->
<?php
for($inner = 1; $inner <= $nb_marqueur; $inner++) {
  echo "<script type=\"text/template\" id=\"pin-".$inner."\">\n";
  echo $descri[$inner]."\n";
  echo "</script>\n";
}
?>

<script>
  const PSV = new PhotoSphereViewer.Viewer({
    container : 'photosphere',
    panorama   : '<?php echo $quelfic; ?>',
    caption    : '<?php echo addslashes($titre); ?>',
    loadingImg: 'example/assets/photosphere-logo.gif',
    navbar    : [
      'autorotate', 'zoom', 'download', 'markers', 'markersList',
      {
        content  : '💬',
        title    : 'Show all tooltips',
        className: 'custom-button',
        onClick  : function () {
          markers.toggleAllTooltips();
        }
      },
      'caption', 'gyroscope', 'stereo', 'fullscreen',
    ],
    plugins   : [
      PhotoSphereViewer.GyroscopePlugin,
      PhotoSphereViewer.StereoPlugin,
      [PhotoSphereViewer.MarkersPlugin, {
        markers: (function () {
          var a = [];
          <?php 
            echo $jmarqueur;
            // Si presence des fichiers haute definition de la sphère on provoque l'affichage de marqueur permettant d'ouvrir le jpg correspondant
            // Exemple la sphere est Sphere/Aquitaine/dji_soulac-sur-mer.jpg  on charche si le repertoire Sphere/Aquitaine/dji_soulac-sur-mer.d existe
            $path_parts = pathinfo($quelfic);
            $repHD = $path_parts['dirname']."/".$path_parts['filename'].".d";
            if (is_dir($repHD)){
              for ($iImg=1; $iImg <= 26; $iImg++){      // Pour les 26 images qui constitue la sphère du DJI mini air 2
                echo listimg("DJI_".str_pad ( $iImg, 4, '0', STR_PAD_LEFT ).".jpg");
              }
            }
          ?>
          return a;
        }())
      }]
    ]
  });
  var markers = PSV.getPlugin(PhotoSphereViewer.MarkersPlugin);
  
  markers.on('select-marker', function (e, marker, data) {
    console.log('select', marker.id);
    console.log('latitude:',marker.latitude,'longitude:',marker.longitude);
    const lesMarqueurs = ["DJI_0001.jpg", "DJI_0002.jpg", "DJI_0003.jpg", "DJI_0004.jpg", "DJI_0005.jpg", "DJI_0006.jpg", "DJI_0007.jpg", "DJI_0008.jpg", "DJI_0009.jpg","DJI_0010.jpg", "DJI_0011.jpg", "DJI_0012.jpg", "DJI_0013.jpg", "DJI_0014.jpg", "DJI_0015.jpg", "DJI_0016.jpg", "DJI_0017.jpg", "DJI_0018.jpg", "DJI_0019.jpg", "DJI_0020.jpg", "DJI_0021.jpg", "DJI_0022.jpg", "DJI_0023.jpg", "DJI_0024.jpg", "DJI_0025.jpg", "DJI_0026.jpg"];
    if (lesMarqueurs.includes(marker.id)){         // returns true
      //if (marker.id == 'DJI_0001.jpg'){
      window.open('view.php?p=<?php echo urlencode($quelfic);?>&img='+marker.id,marker.id);
    }  
  });

  PSV.on('dblclick', function (e, data) {
    history.back();
  });
  <?php echo $goMarker; ?>
</script>
</body>
</html>