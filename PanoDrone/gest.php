<?php
include('inc-config.php');
include('inc-session.php');
include('inc-lib.php');

if (isset($_POST['v'])){
	for ($a = 1; $a <= $_POST['cpt']; $a++){
		if (isset($_POST['C_'.$a])){							// Seul les checkbox cochées remplissent cette variable
			if ($_POST['C_'.$a]=="Ok"){
				$quelfic = $_POST['FIC_'.$a];
				$statement = $pdo->prepare('DELETE FROM lespanos WHERE fichier = :fichier;');
	    		$statement->bindValue(':fichier', $quelfic, PDO::PARAM_STR);
				$result = $statement->execute();
				$statement = $pdo->prepare('DELETE FROM lespanos_details WHERE fichier = :fichier;');
	    		$statement->bindValue(':fichier', $quelfic, PDO::PARAM_STR);
				$result = $statement->execute();
				$msg = "Suppression Ok !";			
			}
		}
	}
}

// On commence par faire un scan tout frais pour mettre à jour la bdd
$frontend = false;
if ($frontend){
echo "frontend=".$frontend."fin";
}
$reality = scan($dir);

// A l'issue de ce scan tous les fichiers réels présent sur le disque sont donc dans la bdd
// On parcours donc la bdd maintenant pour remplir un tableau et marquer tous les fichiers qui n'existent plus

$statement = $pdo->prepare('SELECT * FROM lespanos;');
$statement->execute();
$hashfic=$titre=$legende="";

$montab = "<table>\n";
$montab .= "<tr>";
$montab .= "        <th>Sel.</th>";
$montab .= "        <th>Privé</th>";
$montab .= "        <th>Fichier</th>";
$montab .= "        <th>Titre</th>";
$montab .= "        <th>Legende</th>";
$montab .= "        <th>Hash</th>";
$montab .= "</tr>\n";
$i=0;
while ($row = $statement->fetch()) {
	$i = $i+1;
	if (!file_exists($row['fichier'])){ 	
		$backgroungColor = ' style="background-color:red;"';
		$checkBox = "checked";
	} else {
		$backgroungColor = $checkBox = "";
		if (rtrim($row['titre'])==""){		// Le fichier existe mais n'a pas été personalisé on lui met une couleur 
			$backgroungColor = ' style="background-color:gray;"';
		}
	}
	$titreTmp = "";
	if (rtrim($row['titre'])==""){
		$titreTmp = "...";
	} else {
		$titreTmp = $row['titre'];
	}
	if (strpos($row['fichier'],"-p-") === false ){	// Ce n'est pas un fichier privee
		$priv = "";
	} else {							// Il est privée
		$priv='<span style="color:orange;font-weight: 500;">Privée</span> ';
	}
	$titreTmp = '<a href="gest-form.php?p='.urlencode($row['fichier']).'">'.$titreTmp.'</a>';
	$montab .= "<tr".$backgroungColor.">";
	$montab .= '		<td><input type="checkbox" name="C_'.$i.'" value="Ok"'.$checkBox.'>';
	$montab .= '			<input type="hidden" name="FIC_'.$i.'" value="'.$row['fichier'].'">';
	$montab .= "		</td>";
	$montab .= "        <td>".$priv."</td>";
	$montab .= "        <td>".$row['fichier']."</td>";
	$montab .= "        <td>".$titreTmp."</td>";
	$montab .= "        <td>".$row['legende']."</td>";
	$montab .= "        <td>".$row['hashfic']."</td>";
	$montab .= "</tr>\n";
}
$montab .= "</table>\n";

?>
<!DOCTYPE html>
<html>
<head lang="fr">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<title>Sphères</title>


	<!-- Include our stylesheet -->
	<link href="assets/css/styles.css" rel="stylesheet"/>
	<link href="css/navbar.css" rel="stylesheet"/>
	<style type="text/css">
		body{color: white;}
		table { color: white; }
		td, th { border: 1px solid #111; padding: 6px; }
		th { font-weight: 700; }
		table a{ 
			font-weight: 700;
			color: yellow; 
		}
	</style>

</head>
<body>

<nav class="menu">
	<ul>
		<li><a href="index.php">Quitter gestion</a></li>
		<?php
		if (version_compare(phpversion(), '5.5.0', '>=')) {     // Must be > 5.5 for use authentification of tinymanagerfile
		?>
		<li><a href="tinyfilemanagergest/tinyfilemanager.php">Ajouter/Supprimer des sphères</a></li>
		<li><a href="param.php">Paramètres</a></li>
		<?php 
		} 
		?>
	</ul>
</nav>
<?php if(isset($msg)) echo '<p><span  style="background-color:green;">'.$msg.'</span></p>'; ?>

<form id="MyForm" action="gest.php" method="post" class="form-example">
	<input id="v" name="v" type="hidden" value="ok">
	<input id="cpt" name="cpt" type="hidden" value="<?php echo $i; ?>">
	<br /><br />
	<p>
		<span style="background-color:red">Fond rouge les fichiers qui n'existent plus, ils sont cochés par defaut pour effacement de la base de données</span><br />
		<span style="background-color:gray">Fond gris les fichiers non personalisés</span><br />
	</p>
	<br />
	<?php echo $montab; ?> 
	<br />
	<input type="submit" value="&nbsp;Effacer les fichiers lignes cochées&nbsp;" /><br/><br />
</form>
<script src="assets/js/jquery-1.11.0.min.js"></script>
<script type='text/javascript'>
	$('th').click(function(){
    	var table = $(this).parents('table').eq(0)
    	var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()))
    	this.asc = !this.asc
    	if (!this.asc){rows = rows.reverse()}
    	for (var i = 0; i < rows.length; i++){table.append(rows[i])}
	})
	function comparer(index) {
    	return function(a, b) {
        	var valA = getCellValue(a, index), valB = getCellValue(b, index)
        	return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB)
    	}
	}
	function getCellValue(row, index){ return $(row).children('td').eq(index).text() }
</script>
</body>
</html>