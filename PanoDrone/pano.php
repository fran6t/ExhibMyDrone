<?php
include('inc-config.php');
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
include('inc-lib.php');
include('inc-bdd-ctrl.php');
if (!isset($langue)) $langue = "en";
$t = new Traductor();
$t->setLanguage($langue);

if (isset($_GET['c'])){
  // Short URL redirect to good place
  $statement = $pdo->prepare('SELECT fichier FROM lespanos WHERE short_code = :short_code LIMIT 1;');
  $statement->bindValue(':short_code', rtrim($_GET['c']), PDO::PARAM_STR );
  $statement->execute();
  $fichier="";
  while ($row = $statement->fetch()) {
    $quelfic = $row['fichier'];
  }
} else {
  if (!isset($_GET["p"])){
    display_Frontend_Error("Missing parameter !!!");
    return;
  }
  $quelfic = urldecode($_GET["p"]);
} 	
if (!isset($quelfic)){
  display_Frontend_Error("Missing file panorama !!!");
	return;
}
// test if file panorama exist 
if (!file_exists($quelfic)){
	display_Frontend_Error("Missing file panorama !!!");
	return;
}
$goMarker = ""; 
$titre=$legende=$titrerouge=$latituderouge=$descmarqueurrouge=$titrebleu=$latitudebleu=$descmarqueurbleu="";
$statement = $pdo->prepare('SELECT hashfic,titre,legende,sphere_origin FROM lespanos WHERE fichier = :fichier LIMIT 1;');
$statement->bindValue(':fichier', $quelfic);
$statement->execute();

$hashfic=$titre=$legende=$sphere_origin="";
while ($row = $statement->fetch()) {
  $titre = $row['titre'];
  $legende = $row['legende'];
  $hashfic = $row['hashfic'];
  $sphere_origin = $row['sphere_origin'];
}

if (rtrim($hashfic) == ""){
  $hashfic = hash_file('md5', $quelfic, false);
  // Store $hashfic, hashfic is link 1 <--> 1 between table lespanos and lespanos_details
  $stmt = $pdo->prepare('UPDATE lespanos SET hashfic = :hashfic WHERE fichier = :fichier');
  $stmt->bindValue(':hashfic', $hashfic, PDO::PARAM_STR);
  $stmt->bindValue(':fichier', $quelfic, PDO::PARAM_STR);
  $stmt->execute();
}

// Retrieve marker from database
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
  // Construct array javascript of marker
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

// If marker have been define "center" open panorama is centered on
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
<html lang="<?php echo $langue;?>">
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
  <meta property="og:image:alt" content="<?php echo $t->display("Thumbnail of the sphere or panorama representing")." : ".$titre; ?>" />
  <meta property="og:url" content="<?php echo $lien; ?>" />
  <meta property="og:description" content="<?php echo $t->display("Panorama 360° : Sphere")." ".$titre; ?>" />

  <meta name="description"  content="<?php echo $t->display("Panorama 360° : Sphere")." ".$titre; ?>" />
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
<script type="text/javascript" src="dist/fromcdn/three.min.js"></script>
<script type="text/javascript" src="dist/fromcdn/polyfill.min.js"></script>
<script type="text/javascript" src="dist/fromcdn/browser.js"></script>
<script type="text/javascript" src="dist/fromcdn/photo-sphere-viewer.js"></script>
<script type="text/javascript" src="dist/fromcdn/equirectangular-tiles.js"></script>
<script src="dist/plugins/gyroscope.js"></script>
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
    caption    : '<?php echo addslashes($titre); ?>',
    <?php
    if (removeSmall($quelfic) <> ""){
      // It's a big sphere run mode tile
      $path_tiles=nameDirD($quelfic)."/tiles/";
      $path_hd=nameDirD($quelfic)."/src/";
    ?>
      adapter: PhotoSphereViewer.EquirectangularTilesAdapter,
      panorama: {
      width: 16000,
      cols: 16,
      rows: 8,
      baseUrl: '<?php echo $quelfic; ?>',
        tileUrl: (col, row) => {
          const num = row * 16 + col;
          console.log(`<?php echo $path_tiles; ?>tile_${('000' + num).slice(-4)}.jpg`);
          return `<?php echo $path_tiles; ?>tile_${('000' + num).slice(-4)}.jpg`;
        },
    },
    <?php
    } else {
      // It's a normal sphere
    ?>
      panorama   : '<?php echo $quelfic; ?>',
    <?php  
    }
    ?>
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
      'caption', 'gyroscope', 'fullscreen',
    ],
    plugins   : [
      PhotoSphereViewer.GyroscopePlugin,
      PhotoSphereViewer.StereoPlugin,
      [PhotoSphereViewer.MarkersPlugin, {
        markers: (function () {
          var a = [];
          <?php 
            echo $jmarqueur;
            // If file UHD exist then add a fictif marker for open jpg
            // For example for Sphere/Aquitaine/dji_soulac-sur-mer.jpg  we search if directory Sphere/Aquitaine/dji_soulac-sur-mer.d exist
            if (!isset($path_hd)){   
              $path_hd = nameDirD($quelfic)."/src";
            }
            if (is_dir($path_hd)){
              for ($iImg=1; $iImg <= 26; $iImg++){      // For 26 picture of the sphere taken by DJI mini air 2
                echo listimg("DJI_".str_pad ( $iImg, 4, '0', STR_PAD_LEFT ).".jpg",$sphere_origin);
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