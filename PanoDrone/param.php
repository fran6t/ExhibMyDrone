<?php
include('inc-config.php');
include('inc-lib.php');

// Si nous arrivons du formulaire
if (isset($_POST["v"])){
    // On ecrase le fichier inc-config-perso.php
    $dir = $_POST['dir'];
    $monDomaine = $_POST['monDomaine'];
    $root_complement = $_POST['root_complement'];
    $admin = $_POST['admin'];
    $bddtype = $_POST['bddtype'];
    $host = $_POST['host'];
    $db = $_POST['db'];
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $port = $_POST['port'];
    
    $strFic = ";<?php die(); ?>\n";     // Si quelqu'un essai d'ouvrir le fichier coté web
    $strFic .= "[General]\n";     
    $strFic .= "dir=".$dir."\n";
    $strFic .= "monDomaine=".$monDomaine."\n";
    $strFic .= "root_complement=".$root_complement."\n";
    $strFic .= "[Acces]\n";
    $strFic .= "keyok=".$keyok."\n";
    if (rtrim($_POST["admin"])==""){                 //On ne laisse pas sans mot de passe minimum du coup on force avec celui de tinymanagerfile
        $strFic .= "admin=".password_hash("admin@123", PASSWORD_DEFAULT)."\n";
    } else {
        $strFic .= "admin=".password_hash($admin, PASSWORD_DEFAULT)."\n";
    }
    $strFic .= "[BDD]\n";
    $strFic .= "bddtype=".$bddtype."\n";
    $strFic .= "host=".$host."\n";
    $strFic .= "db=".$db."\n";
    $strFic .= "user=".$user."\n";
    $strFic .= "pass=".$pass."\n";
    $strFic .= "port=".$port."\n";
    if ($fp = fopen($config_file, 'w')){
      fwrite($fp, $strFic);
      fclose($fp);
    }
    $msg = "Sauvegarde effectuée";
}
if (is_readable($config_file)) {
  $ini =  parse_ini_file($config_file);
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
    if ($ini['admin'] == password_hash("admin@123", PASSWORD_DEFAULT)) $msgerror = "Vous devez personaliser le mot de passe protection administration !";
  } else {
    if ( $keyok == "Azerty001" )  $msgerror = "Vous devez personaliser la clef accès manuel !";
  }
} else {
  $msgerror = "Fichier ".$config_file."manquant !";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Installation</title>
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
		<li><a href="index.php">Quitter gestion</a></li>
    <li><a href="gest.php">Gérer la liste</a></li>
		<?php
		if (version_compare(phpversion(), '5.5.0', '>=')) {     // Must be > 5.5 for use authentification of tinymanagerfile
		?>
		<li><a href="tinyfilemanagergest/tinyfilemanager.php">Ajouter/Supprimer des fichiers</a></li>
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
    <div class="form-group-stacked">
      <label for="name" class="form-label">Url :</label>
      <div class="form-fields">
        <input placeholder="<?php echo $monDomaine; ?>" type="text" name="monDomaine" id="monDomaine" value="<?php echo $monDomaine; ?>">
        <span class="form-tip">Le http de votre hebergement</span>
      </div>
    </div>
    <div class="form-group-stacked">
      <label for="name" class="form-label">Répertoire :</label>
        <div class="form-fields">
        <input placeholder="<?php echo $root_complement; ?>" type="text" name="root_complement" id="root_complement" value="<?php echo $root_complement; ?>">
        <span class="form-tip">Répertoire ou se trouve les sphères (complément de l'Url</span>
      </div>
    </div>
    <div class="form-group-stacked">
      <label for="name" class="form-label">Répertoire :</label>
        <div class="form-fields">
        <input placeholder="<?php echo $dir; ?>" type="text" name="dir" id="dir" value="<?php echo $dir; ?>">
        <span class="form-tip">Répertoire ou se trouve les sphères</span>
      </div>
    </div>
    <div class="form-group-stacked">
      <label for="name" class="form-label">Clef accès manuel :</label>
        <div class="form-fields">
        <input placeholder="<?php echo $keyok; ?>" type="text" name="keyok" id="keyok" value="<?php echo $keyok; ?>">
        <span class="form-tip">Si TinyFileManager désactivé vous devrez appeler l'administration sous cette forme https://d.wse.fr/ExhibMydrone/PanoDrone/gest.php?k=Azerty001</span>
      </div>
    </div>
    <div class="form-group-stacked">
      <label for="name" class="form-label">Mot de passe administration :</label>
        <div class="form-fields">
        <input placeholder="<?php echo $admin; ?>" type="text" name="admin" id="admin" value="<?php echo $admin; ?>">
        <span class="form-tip">Mot de passe admin</span>
      </div>
    </div>
    <h2>Partie BDD</h2>
    <div class="form-group">
      <label for="name" class="form-label">Type BDD</label>
        <div class="form-fields">
        <select name="bddtype" id="bddtype">
                                            <option value="sqlite"  <?php if ($bddtype=="sqlite") echo "SELECTED"; ?>>Sqlite</option>
                                            <option value="mysql" <?php if ($bddtype=="mysql") echo "SELECTED"; ?>>Mysql</option>
        </select>
        <span class="form-tip">Sqlite par defaut</span>
      </div>
    </div>
    <div class="form-group">
      <label for="name" class="form-label">Serveur</label>
      <div class="form-fields">
        <input placeholder="<?php echo $host; ?>" type="text" name="host" id="host"  value="<?php echo $host; ?>">
        <span class="form-tip">Non utilisé si Sqlite</span>
      </div>
    </div>
    <div class="form-group">
      <label for="name" class="form-label">Nom DBB</label>
      <div class="form-fields">
        <input placeholder="<?php echo $db; ?>" type="text" name="db" id="db" value="<?php echo $db; ?>">
        <span class="form-tip">Nom de la base de données</span>
      </div>
    </div>
    <div class="form-group">
      <label for="name" class="form-label">Utilisateur</label>
      <div class="form-fields">
        <input placeholder="<?php echo $user; ?>" type="text" name="user" id="user"  value="<?php echo $user; ?>">
        <span class="form-tip">Utilisateur de la bdd (Non utilisé si Sqlite)</span>
      </div>
    </div>
    <div class="form-group">
      <label for="name" class="form-label">Mot de passe</label>
      <div class="form-fields">
        <input placeholder="<?php echo $pass; ?>" type="text" name="pass" id="pass"  value="<?php echo $pass; ?>">
        <span class="form-tip">Non utilisé si Sqlite</span>
      </div>
    </div>
    <div class="form-group">
      <label for="name" class="form-label">Port</label>
      <div class="form-fields">
        <input placeholder="<?php echo $port; ?>" type="text" name="port" id="port"  value="<?php echo $host; ?>">
        <span class="form-tip">Non utilisé si Sqlite</span>
      </div>
    </div>
    <button name="Sauvegarder" type="submit" id="MyForm-submit" data-submit="...Sending">Sauvegarder</button>
  </div>
</form>   
</body>
</html>