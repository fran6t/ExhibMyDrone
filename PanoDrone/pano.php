<?php
include('inc-lib.php');
if (isset($_GET['c'])){
  // Sommes en presence d'une url courte on redirige directement au bon endroit
  $statement = $db->prepare('SELECT fichier FROM lespanos WHERE short_code = :short_code LIMIT 1;');
  $statement->bindValue(':short_code', rtrim($_GET['c']), SQLITE3_TEXT);
  $result = $statement->execute();
  $fichier="";
  while ($row = $result->fetchArray()) {
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

$titre=$legende=$titrerouge=$latituderouge=$descmarqueurrouge=$titrebleu=$latitudebleu=$descmarqueurbleu="";
$statement = $db->prepare('SELECT hashfic,titre,legende FROM lespanos WHERE fichier = :fichier LIMIT 1;');
$statement->bindValue(':fichier', $quelfic);
$result = $statement->execute();
$row=$result->fetchArray(SQLITE3_ASSOC);

$hashfic=$titre=$legende="";
while ($row = $result->fetchArray()) {
  $titre = $row['titre'];
  $legende = $row['legende'];
  $hashfic = $row['hashfic'];
}

if (rtrim($hashfic) == ""){
  $hashfic = hash_file('md5', $quelfic, false);
  // On mémorise le $hashfic c'est lui qui fera la liaison entre la table lespanos et lespanos_details
  $stmt = $db->prepare('UPDATE lespanos SET hashfic = :hashfic WHERE fichier = :fichier');
  $stmt->bindValue(':hashfic', $hashfic, SQLITE3_TEXT);
  $stmt->bindValue(':fichier', $quelfic, SQLITE3_TEXT);
  $result = $stmt->execute();
}

// On recupere les marqueurs
$statement = $db->prepare('SELECT * FROM lespanos_details WHERE hashfic = :hashfic;');
$statement->bindValue(':hashfic', $hashfic, SQLITE3_TEXT);
$result = $statement->execute();
$nb_marqueur = $i = 0;
while ($row = $result->fetchArray()) {
  $i = $i +1;
  $nb_marqueur = $i;
  $nom_marqueur[$nb_marqueur] = $row['nom_marqueur'];
  $couleur[$nb_marqueur] = $row['couleur'];
  $latitude[$nb_marqueur] = $row['latitude'];
  $longitude[$nb_marqueur] = $row['longitude'];
  $descri[$nb_marqueur] = $row['descri'];
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $titre; ?></title>

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
    caption    : '<?php echo $queltit; ?>',
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
          <?php echo $jmarqueur; ?> 
          return a;
        }())
      }]
    ]
  });
  var markers = PSV.getPlugin(PhotoSphereViewer.MarkersPlugin);
</script>
</body>
</html>