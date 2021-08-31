<?php
include('inc-config.php');
include('inc-session.php');
include('inc-lib.php');

$p_cnt = 0;     //Nombre de marqueurs
$jmarqueur="";  //A peupler pour javascript
$contenu="";

// Si nous arrivons du formulaire
if (isset($_POST["v"])){
  $quelfic = stripSlashes($_POST["p"]);
  // ON update avec la clef fichier ce qui permet d'avoir un titre et une legende differente en fonction
  // de l'endroit ou se trouve le fichier par contre les marqueurs eux seront communs
  $stmt = $pdo->prepare('UPDATE lespanos SET titre = :titre , legende = :legende, hashfic = :hashfic WHERE fichier = :fichier');
  $stmt->bindValue(':titre', rtrim($_POST['titre']), PDO::PARAM_STR);
  $stmt->bindValue(':legende', rtrim($_POST['legende']), PDO::PARAM_STR);
  $stmt->bindValue(':hashfic', rtrim($_POST['hashfic']), PDO::PARAM_STR);
  $stmt->bindValue(':fichier', $quelfic, PDO::PARAM_STR);
  $result = $stmt->execute();


  // On commence par effacer tous les marqueurs de cette sphère
  $stmt = $pdo->prepare('DELETE FROM lespanos_details WHERE hashfic = :hashfic');
  $stmt->bindValue(':hashfic', $_POST["hashfic"], PDO::PARAM_STR);
  $result = $stmt->execute();


  // On insere maintenant les marqueurs du formulaire
  // On calcul combien de marqueur sont dans le formulaire
  //echo "<br />Nombre de formulaire = ".count($_POST['formu']);
  //echo "<br />";
  // var_dump($_POST['formu']);
  // echo "<br />Nombre marqueur dans formulaire".count($_POST['formu']);

  for ($a = 1; $a <= count($_POST['formu']); $a++){
    if (rtrim($_POST['formu'][$a]['nom_marqueur'])!=""){   //On insert que si un titre de marqueur est renseigné 
      if (rtrim($_POST['formu'][$a]['couleur']) == "") $_POST['formu'][$a]['couleur'] = 0;
      if (rtrim($_POST['formu'][$a]['longitude']) == "") $_POST['formu'][$a]['longitude'] = 0; 
      $statement = $pdo->prepare('INSERT INTO lespanos_details (fichier, hashfic, nom_marqueur, couleur, latitude, longitude, descri) VALUES (:fichier, :hashfic, :nom_marqueur, :couleur, :latitude, :longitude, :descri);');
	    $statement->bindValue(':fichier', $quelfic);
      $statement->bindValue(':hashfic', $_POST['hashfic']);
      $statement->bindValue(':nom_marqueur', $_POST['formu'][$a]['nom_marqueur']);
      $statement->bindValue(':couleur', $_POST['formu'][$a]['couleur']);
      $statement->bindValue(':latitude', $_POST['formu'][$a]['latitude']);
      $statement->bindValue(':longitude', $_POST['formu'][$a]['longitude']);
      $statement->bindValue(':descri', $_POST['formu'][$a]['descri']);
	    $result = $statement->execute();
      //echo "<br /><br />a=".$a." Marqueur=".$_POST['formu'][$a]['nom_marqueur']."<br /><br />";
      //echo "<br /><br />a=".$a." Couleur=".$_POST['formu'][$a]['couleur']."<br /><br />";
      //echo "<br /><br />a=".$a." Longitude=".$_POST['formu'][$a]['longitude']."<br /><br />";
      //echo "<br /><br />a=".$a." Latitude=".$_POST['formu'][$a]['latitude']."<br /><br />";
      //echo "<br /><br />a=".$a." Description=".$_POST['formu'][$a]['descri']."<br /><br />";
    }
  }

} else {
  if (!isset($quelfic)) $quelfic = stripSlashes($_GET["p"]);
}		

// On recupere les elements eventuel pour les marqueur
//echo "<br />SELECT titre,legende,short_code FROM lespanos WHERE fichier = ".$quelfic." LIMIT 1";
$statement = $pdo->prepare('SELECT titre,legende,hashfic,short_code FROM lespanos WHERE fichier = :fichier LIMIT 1;');
$statement->bindValue(':fichier', $quelfic, PDO::PARAM_STR);
$statement->execute();
$hashfic=$titre=$legende=$short_code="";
while ($row = $statement->fetch()) {
  $titre = $row['titre'];
  $legende = $row['legende'];
  $hashfic = $row['hashfic'];
  $short_code = $row['short_code'];
}

// On calcul le Hash du fichier pour avoir les mêmes infos marqueur pour un fichier
// La legende peut être differentes mais pas les infos marqueurs
if (rtrim($hashfic) == ""){
  $hashfic = hash_file('md5', $quelfic, false);
  // On mémorise le $hashfic c'est lui qui fera la liaison entre la table lespanos et lespanos_details
  $stmt = $pdo->prepare('UPDATE lespanos SET hashfic = :hashfic WHERE fichier = :fichier');
  $stmt->bindValue(':hashfic', $hashfic, PDO::PARAM_STR);
  $stmt->bindValue(':fichier', $quelfic, PDO::PARAM_STR);
  $result = $stmt->execute();
}

if (rtrim($short_code)==""){ // Idem si $short_code est vide on en calcul un
  $short_code = generateRandomString(6); 
  // On mémorise le $hashfic c'est lui qui fera la liaison entre la table lespanos et lespanos_details
  $stmt = $pdo->prepare('UPDATE lespanos SET short_code = :short_code WHERE fichier = :fichier');
  $stmt->bindValue(':short_code', $short_code, PDO::PARAM_STR);
  $stmt->bindValue(':fichier', $quelfic, PDO::PARAM_STR);
  $result = $stmt->execute();
}

// On memorise les marqueurs pour le formulaire et aussi pour l'affichage
$statement = $pdo->prepare('SELECT nom_marqueur,couleur,latitude,longitude,descri FROM lespanos_details WHERE hashfic = :hashfic;');
$statement->bindValue(':hashfic', $hashfic, PDO::PARAM_STR);
$statement->execute();
$nb_marqueur = $i = 0;
while ($row = $statement->fetch()) {
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

// On genere les images reduites pour les partages
imageResize($quelfic,200);
imageResize($quelfic,600);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $titre; ?></title>

  <link rel="stylesheet" href="dist/photo-sphere-viewer.css">
  <link rel="stylesheet" href="dist/plugins/markers.css">
  <link rel="stylesheet" href="assets/css/form.css">

  <style>
    html, body {
      width: 100%;
      height: 100%;
      overflow: hidden;
      margin: 0;
      padding: 0;
    }

    #photosphere {
      width: 70%;
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
  <script src="ckeditor/ckeditor.js"></script>
</head>
<body>
<div id="photosphere"></div>
<div id="DivMyForm">
  <ul>
    <li><a href="gest.php">Retour liste</a></li>
    <li><a href="index.php">Quitter</a></li>
  </ul>
  <form id="MyForm" action="gest-form.php" method="post" class="form-example">
    <input id="p" name="p" type="hidden" value="<?php echo $quelfic; ?>">
    <input id="hashfic" name="hashfic" type="hidden" value="<?php echo $hashfic; ?>">
    <input id="v" name="v" type="hidden" value="ok">
    <fieldset>
      <input placeholder="Titre (liste)" type="text" name="titre" id="titre" value="<?php echo $titre; ?>">
    </fieldset>
    <fieldset>
      <textarea placeholder="Info complèmentaire (liste)...." name="legende"><?php echo $legende; ?></textarea>
    </fieldset>
    <?php
    for ($i = 1; $i <= $nb_marqueur; $i++) {
      echo "<h4>Marqueur n°".$i."</h4>";
    ?>
    <fieldset>
      <input placeholder="Titre du marqueur (Etiquette bulle)" type="text" name="formu[<?php echo $i; ?>][nom_marqueur]" id="nom_marqueur<?php echo $i; ?>"  value="<?php echo $nom_marqueur[$i]; ?>">
    </fieldset>
    <div class="gps">
      <div class="gauche">
        <fieldset>
          <input placeholder="Latitude" type="text" class="gpsCoord" name="formu[<?php echo $i; ?>][latitude]" id="latitude_<?php echo $i; ?>" value="<?php echo $latitude[$i]; ?>">
        </fieldset>
      </div>
      <div class="droite">
        <fieldset>
          <input placeholder="Longitude" type="text"  class="gpsCoord" name="formu[<?php echo $i; ?>][longitude]"    id="longitude_<?php echo $i; ?>" value="<?php echo $longitude[$i]; ?>">
        </fieldset>
      </div>
    </div>
    <fieldset>
      <select name="formu[<?php echo $i; ?>][couleur]"" id="couleur_<?php echo $i; ?>">
                                          <option value="red"  <?php if ($couleur[$i]=="red") echo "SELECTED"; ?>>Rouge</option>
                                          <option value="blue" <?php if ($couleur[$i]=="blue") echo "SELECTED"; ?>>Bleu</option>
      </select>
    </fieldset>
    <fieldset>
      <textarea placeholder="Toutes les infos complèmentaires du marqueur...." name="formu[<?php echo $i; ?>][descri]" id="descri_<?php echo $i; ?>" class="ckeditor"><?php echo $descri[$i]; ?></textarea>
    </fieldset>
    <?php
    }
    ?>
    <h4>Nouveau Marqueur</h4>
    <fieldset>
      <input placeholder="Titre du marqueur (Etiquette bulle)" type="text" name="formu[<?php echo $i; ?>][nom_marqueur]" id="nom_marqueur_<?php echo $i; ?>"  value="">
    </fieldset>
    <?php
    $name_latitude = "formu[".$i."][latitude]";
    $name_longitude = "formu[".$i."][longitude]";
    ?>
    <div class="gps">
      <div class="gauche">
        <fieldset>
          <input placeholder="Latitude" type="text" class="gpsCoord" name="<?php echo $name_latitude; ?>" id="<?php echo $name_latitude; ?>" value="">
        </fieldset>
      </div>
      <div class="droite">
        <fieldset>
          <input placeholder="Longitude" type="text"  class="gpsCoord" name="<?php echo $name_longitude; ?>"    id="<?php echo $name_longitude; ?>" value="">
        </fieldset>
      </div>
    </div>
    <fieldset>
      <select name="formu[<?php echo $i; ?>][couleur]" id="couleur_<?php echo $i; ?>">
                                          <option value="red">Rouge</option>
                                          <option value="blue">Bleu</option>
      </select>
    </fieldset>
    <fieldset>
      <textarea placeholder="Toutes les infos complèmentaires du marqueur...." name="formu[<?php echo $i; ?>][descri]" id="descri_<?php echo $i; ?>"></textarea>
    </fieldset>
    <fieldset>
      <button name="Sauvegarder" type="submit" id="MyForm-submit" data-submit="...Sending">Sauvegarder</button>
    </fieldset>
    <h4>Liens pour partage :</h4>
    <h4>Vers la sphère directement</h43>
    <fielset>
      <input id="copyURL" type="text" value="<?php echo $monDomaine.'/'.$root_complement."/?c=".$short_code; ?>"/><br>
      <button type="button" id="copyButton">Copier le lien</button>
    </fieldset>
    <h4>Vers la sphère avec une miniature de 200px</h4>
    <?php
    $nbrePixels = "-MinX0200.jpg";
    $lien = $monDomaine.'/'.$root_complement.'/?c='.$short_code;
    $lienImg = $monDomaine.'/'.$root_complement.'/'.$quelfic;
    $lienComplet = "<a href='".$lien."' title='".$titre."'><img src='".$lienImg.$nbrePixels."' alt='Minature ".$titre."' /></a>";
    ?>
    <fielset>
      <input id="copyURL200" type="text" value="<?php echo $lienComplet; ?>"/><br>
      <button type="button" id="copyButton200">Copier le lien</button>
      <?php echo $lienComplet; ?>
    </fieldset>
    <h4>Vers la sphère avec une miniature de 600px</h4>
    <?php
    $nbrePixels = "-MinX0600.jpg";
    $lienComplet = "<a href='".$lien."' title='".$titre."'><img src='".$lienImg.$nbrePixels."' alt='Minature ".$titre."' /></a>";
    ?>
    <fielset>
      <input id="copyURL600" type="text" value="<?php echo $lienComplet; ?>"/><br>
      <button type="button" id="copyButton600">Copier le lien</button>
    </fieldset>
  </form>

</div> 
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
  
  <?php // Mettre le lien court dans le press papier lors du clique sur le bouton copier le lien ?>
  function copyPressPapier(quelBouton) {
    switch(quelBouton){
      case "copyButton":
         var copyText = document.querySelector("#copyURL");
         break;
      case "copyButton200":
         var copyText = document.querySelector("#copyURL200");
         break;
      case "copyButton600":
         var copyText = document.querySelector("#copyURL600");
         break;
      default:
        console.log("Désolé, nous n'avons plus de " + quelBouton + ".");
    }  
    copyText.select();
    document.execCommand("copy");
    alert('Copier dans le presse papier fait');
  }

  document.querySelector("#copyButton").addEventListener("click", function() {
    copyPressPapier('copyButton');
  });
  document.querySelector("#copyButton200").addEventListener("click", function() {
    copyPressPapier('copyButton200');
  });
  document.querySelector("#copyButton600").addEventListener("click", function() {
    copyPressPapier('copyButton600');
  }); 

  const PSV = new PhotoSphereViewer.Viewer({
    container : 'photosphere',
    //panorama  : '../Panos/Lorient-pano.jpg',
    //caption   : 'Parc national du Mercantour <b>&copy; Damien Sorel</b>',
    panorama   : '<?php echo $quelfic; ?>',
    caption    : '<?php echo $titre; ?>',
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

  PSV.on('click', function (e, data) {
    if (!data.rightclick) {
      markers.addMarker({
        id       : '#' + Math.random(),
        tooltip  : 'Generated marker',
        longitude: data.longitude,
        latitude : data.latitude,
        image    : 'example/assets/pin-red.png',
        width    : 32,
        height   : 32,
        anchor   : 'bottom center',
        data     : {
          deletable: true,
        },
      });
      console.log('latitude:',data.latitude,'longitude:',data.longitude);
      document.getElementById('<?php echo $name_latitude; ?>').value = data.latitude;
      document.getElementById('<?php echo $name_longitude; ?>').value = data.longitude;
      //document.getElementById('myform').focus();
      document.getElementById('photosphere').disabled = true;
    }
  });

  markers.on('select-marker', function (e, marker, data) {
    console.log('select', marker.id);
    console.log('latitude:',marker.latitude,'longitude:',marker.longitude);
    if (marker.data && marker.data.deletable) {
      if (data.dblclick) {
        markers.removeMarker(marker);
      }
      else if (data.rightclick) {
        markers.updateMarker({
          id   : marker.id,
          image: 'example/assets/pin-blue.png',
        });
      }
    }
  });

  markers.on('unselect-marker', function (e, marker) {
    console.log('unselect', marker.id);
  });

  markers.on('over-marker', function (e, marker) {
    console.log('over', marker.id);
  });

  markers.on('leave-marker', function (e, marker) {
    console.log('leave', marker.id);
  });

  markers.on('select-marker-list', function (e, marker) {
    console.log('select-list', marker.id);
  });

  markers.on('goto-marker-done', function (e, marker) {
    console.log('goto-done', marker.id);
  });
</script>
</body>
</html>