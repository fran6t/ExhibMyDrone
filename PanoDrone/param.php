<?php

include('inc-config.php');
include('inc-session.php');
include('inc-lib.php');
if (isset($_POST['langue'])){
  if ($_POST['langue']=="fr") $langue = "fr";
  if ($_POST['langue']=="en") $langue = "en";
} else {
  $language="en";
}
if (!isset($langue)) $langue = "en";
$t = new Traductor();
$t->setLanguage($langue);


$lienAdmin = "";
$msgerror = "";

// Come from form
if (isset($_POST["v"])){
    // overwrite file inc-config-perso.php
    $langue = $_POST['langue'];
    $dir = $_POST['dir'];
    $monDomaine = $_POST['monDomaine'];
    $root_complement = $_POST['root_complement'];
    $keyok = $_POST['keyok'];
    $admin = $_POST['admin'];
    $bddtype = $_POST['bddtype'];
    $host = $_POST['host'];
    $db = $_POST['db'];
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $port = $_POST['port'];
    
    $strFic = ";<?php die(); ?>\n";     // If anybody open the file directly
    $strFic .= "[General]\n";    
    $strFic .= "langue=".$langue."\n"; 
    $strFic .= "dir=".$dir."\n";
    $strFic .= "monDomaine=".$monDomaine."\n";
    $strFic .= "root_complement=".$root_complement."\n";
    $strFic .= "[Acces]\n";
    $strFic .= "keyok=".$keyok."\n";
    if (version_compare(phpversion(), '5.5.0', '>=')) {
      if (rtrim($_POST["admin"])==""){                 //Passaword required if blank we use password system of tinymanagerfile
        $strFic .= "admin=".password_hash("admin@123", PASSWORD_DEFAULT)."\n";
      } else {
        $strFic .= "admin=".password_hash($admin, PASSWORD_DEFAULT)."\n";
      }
    } else {
      $strFic .= "admin=".crypt($admin, "le sel de la vie")."\n";
    }  
    $strFic .= "[BDD]\n";
    $strFic .= "bddtype=".$bddtype."\n";
    $strFic .= "host=".$host."\n";
    $strFic .= "db=".$db."\n";
    $strFic .= "user=".$user."\n";
    $strFic .= "pass=".$pass."\n";
    $strFic .= "port=".$port."\n";
    if (@is_dir($_SERVER['DOCUMENT_ROOT']."/".$root_complement."/".$dir)){
      if ($fp = fopen($config_file, 'w')){
        fwrite($fp, $strFic);
        fclose($fp);
      }
      $msg = $t->display("Backup performed !");
    } else {
      $msgerror = $t->display("Directory").": <b>".$root_complement."/".$dir. "</b> ".$t->display("not find ! Please correct (URL and/or Directory sphere)");
    }
}
if (is_readable($config_file)) {
  $ini =  parse_ini_file($config_file);
  if (!isset($ini['langue'])){
    $langue = "en";
  } else {
    $langue = $ini['langue'];
  }
  $dir = $ini['dir'];
  $monDomaine = $ini['monDomaine'];
  $root_complement = $ini['root_complement'];
  $keyok = $ini['keyok'];
  $auth_users['admin'] = $ini['admin'];
  $admin = "";
  $bddtype = $ini['bddtype'];
  $host = $ini['host'];
  $user = $ini['user'];
  $pass = $ini['pass'];
  $port = $ini['port'];
  if (version_compare(phpversion(), '5.5.0', '>=')) { 
    if ($ini['admin'] == password_hash("admin@123", PASSWORD_DEFAULT)) $msgerror = $t->display("You must personalize the administration protection password !");
  } else {
    if ( $keyok == "Azerty001" )  $msgerror = $t->display("You must customize the manual access key!");
    $lienAdmin=$monDomaine."/".$root_complement."/gest.php?k=".$keyok;
  }
} else {
  if ($msgerror==""){
    $msgerror = $t->display("File")." <b>".$config_file."</b> ".$t->display("Missing file, creation after validation of this form !");
  }  
}
if ($keyok == "Azerty001"){
  $msgerror = $t->display("Please change the access key it must not be equal to Azerty001 in production");
}
if ( password_verify("admin@123", $ini['admin'])){
  $msgerror = $t->display("Please change the administration password it must not be equal to admin@123 in production");
}

?>
<!DOCTYPE html>
<html lang="<?php echo $langue; ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $t->display("Install");?></title>
<style>
    html, body {
      background-color: #23232e;
      width: 100%;
      height: 100%;
      /* overflow: hidden; */ 
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
/*
    .demo-label {
      color: white;
      font-size: 20px;
      font-family: Helvetica, sans-serif;
      text-align: center;
      padding: 5px;
      border: 1px solid white;
      background: rgba(0, 0, 0, 0.4);
    }
*/
.holder {
  /* background-color: #f0f0f0; */
  color: white;
  padding: 20px;
  width: 1024px;
  margin: auto;
  margin-left: 0px; 
}

.form-group, .form-group-stacked {
  margin-bottom: 10px;
  overflow: hidden;
}

.form-group:last-child, 
.form-group-stacked:last-child {
  margin-bottom: 0;
}

.form-label, .form-setting-field {
  display: block;
  float: left;
  margin-right: 10px;
}

label {
  cursor: pointer;
}

.form-label {
  font-weight: bold;
  width: 23%;
}

.form-fields, .form-setting {
  overflow: hidden;
}

.form-fields + .form-fields {
  margin-bottom: 10px;
}

.form-tip {
  color: #b9b9b9;
  font-size: 0.8em;
  display: block;
}

.form-group-stacked .form-label {
  float: none;
}

.form-group-stacked .form-fields {
  margin-left: 0;
}

  </style>
  <link href="css/navbar.css" rel="stylesheet"/>
  <script src="../ckeditor/ckeditor.js"></script>
</head>
<body>

<nav class="menu">
	<ul>
		<li><a href="index.php"><?php echo $t->display("Exit management"); ?></a></li>
    <?php
    if (version_compare(phpversion(), '5.5.0', '>=')) { 
    ?>  
      <li><a href="gest.php"><?php echo $t->display("Manage the list"); ?></a></li>
    <?php    
    } else {
    ?>  
      <li><a href="<?php echo $lienAdmin;?>"><?php echo $t->display("Manage the list"); ?></a></li>
    <?php  
    }
		if (version_compare(phpversion(), '5.5.0', '>=')) {     // Must be > 5.5 for use authentification of tinymanagerfile
		?>
		<li><a href="tinyfilemanagergest/tinyfilemanager.php"><?php echo $t->display("Add/Delete files"); ?></a></li>
		<?php 
		} 
		?>
	</ul>
</nav>
<?php 
if(isset($msg)) echo '<p><span  style="background-color:green;">'.$msg.'</span></p>'; 
if(isset($msgerror)) echo '<p><span  style="background-color:red;">'.$msgerror.'</span></p>'; 
?>
<form id="MyForm" action="param.php" method="post">
  <input id="v" name="v" type="hidden" value="ok">
  <div class="holder">
    <div class="form-group">
      <label for="name" class="form-label"><?php echo $t->display("Language"); ?></label>
        <div class="form-fields">
        <select name="langue" id="langue">
                                            <option value="fr"  <?php if ($langue=="fr") echo "SELECTED"; ?>>fr</option>
                                            <option value="en" <?php if ($langue=="en") echo "SELECTED"; ?>>en</option>
        </select>
        <span class="form-tip"><?php echo $t->display("fr by default"); ?></span>
      </div>
    </div>
    <div class="form-group-stacked">
      <label for="name" class="form-label">Url :</label>
      <div class="form-fields">
        <input placeholder="<?php echo $monDomaine; ?>" type="text" name="monDomaine" id="monDomaine" value="<?php echo $monDomaine; ?>">
        <span class="form-tip"><?php echo $t->display("The http of your site"); ?></span>
      </div>
    </div>
    <div class="form-group-stacked">
      <label for="name" class="form-label"><?php echo $t->display("Complément URL"); ?> :</label>
        <div class="form-fields">
        <input placeholder="<?php echo $root_complement; ?>" type="text" name="root_complement" id="root_complement" value="<?php echo $root_complement; ?>">
        <span class="form-tip"><?php echo $t->display("URL complement"); ?></span>
      </div>
    </div>
    <div class="form-group-stacked">
      <label for="name" class="form-label"><?php echo $t->display("Sphere directory"); ?>:</label>
        <div class="form-fields">
        <input placeholder="<?php echo $dir; ?>" type="text" name="dir" id="dir" value="<?php echo $dir; ?>">
        <span class="form-tip"><?php echo $t->display("Sub directory where the spheres are"); ?></span>
      </div>
    </div>
    <div class="form-group-stacked">
      <label for="name" class="form-label"><?php echo $t->display("Manual access key"); ?> :</label>
        <div class="form-fields">
        <input placeholder="<?php echo $keyok; ?>" type="text" name="keyok" id="keyok" value="<?php echo $keyok; ?>">
        <span class="form-tip"><?php echo $t->display("If TinyFileManager disabled you will have to call the administration in this form https://d.wse.fr/ExhibMydrone/PanoDrone/gest.php?k=Azerty001"); ?></span>
      </div>
    </div>
    <div class="form-group-stacked">
      <label for="name" class="form-label"><?php echo $t->display("Administration password"); ?> :</label>
        <div class="form-fields">
        <input placeholder="<?php echo $admin; ?>" type="text" name="admin" id="admin" value="<?php echo $admin; ?>">
        <span class="form-tip"><?php echo $t->display("Mot de passe administration"); ?></span>
      </div>
    </div>
    <h2><?php echo $t->display("Database section"); ?></h2>
    <div class="form-group">
      <label for="name" class="form-label"><?php echo $t->display("Database type"); ?></label>
        <div class="form-fields">
        <select name="bddtype" id="bddtype">
                                            <option value="sqlite"  <?php if ($bddtype=="sqlite") echo "SELECTED"; ?>>Sqlite</option>
                                            <option value="mysql" <?php if ($bddtype=="mysql") echo "SELECTED"; ?>>Mysql</option>
        </select>
        <span class="form-tip"><?php echo $t->display("Sqlite by default"); ?></span>
      </div>
    </div>
    <div class="form-group">
      <label for="name" class="form-label"><?php echo $t->display("Server"); ?></label>
      <div class="form-fields">
        <input placeholder="<?php echo $host; ?>" type="text" name="host" id="host"  value="<?php echo $host; ?>">
        <span class="form-tip"><?php echo $t->display("Not used if Sqlite"); ?></span>
      </div>
    </div>
    <div class="form-group">
      <label for="name" class="form-label"><?php echo $t->display("Database name"); ?></label>
      <div class="form-fields">
        <input placeholder="<?php echo $db; ?>" type="text" name="db" id="db" value="<?php echo $db; ?>">
        <span class="form-tip"><?php echo $t->display("Nom de la base de données"); ?></span>
      </div>
    </div>
    <div class="form-group">
      <label for="name" class="form-label"><?php echo $t->display("User"); ?></label>
      <div class="form-fields">
        <input placeholder="<?php echo $user; ?>" type="text" name="user" id="user"  value="<?php echo $user; ?>">
        <span class="form-tip"><?php echo $t->display("Database user (Not used if Sqlite)"); ?></span>
      </div>
    </div>
    <div class="form-group">
      <label for="name" class="form-label"><?php echo $t->display("Password"); ?></label>
      <div class="form-fields">
        <input placeholder="<?php echo $pass; ?>" type="text" name="pass" id="pass"  value="<?php echo $pass; ?>">
        <span class="form-tip"><?php echo $t->display("Not used if Sqlite"); ?></span>
      </div>
    </div>
    <div class="form-group">
      <label for="name" class="form-label"><?php echo $t->display("Port"); ?></label>
      <div class="form-fields">
        <input placeholder="<?php echo $port; ?>" type="text" name="port" id="port"  value="<?php echo $port; ?>">
        <span class="form-tip"><?php echo $t->display("Not used if Sqlite"); ?></span>
      </div>
    </div>
    <button name="Sauvegarder" type="submit" id="MyForm-submit" data-submit="...Sending"><?php echo $t->display("Save"); ?></button>
  </div>
</form>   
</body>
</html>