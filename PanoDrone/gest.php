<?php
include('inc-config.php');
include('inc-lib.php');
include('inc-bdd-ctrl.php');
include('inc-session.php');

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

if (!isset($langue)) $langue = "en";
$t = new Traductor();
$t->setLanguage($langue);

if (isset($_POST['v'])){
	for ($a = 1; $a <= $_POST['cpt']; $a++){
		if (isset($_POST['C_'.$a])){							// Only checked checkbox fill this variable
			if ($_POST['C_'.$a]=="Ok"){
				$quelfic = $_POST['FIC_'.$a];
				$statement = $pdo->prepare('DELETE FROM lespanos WHERE fichier = :fichier;');
	    		$statement->bindValue(':fichier', $quelfic, PDO::PARAM_STR);
				$result = $statement->execute();
				$statement = $pdo->prepare('DELETE FROM lespanos_details WHERE fichier = :fichier;');
	    		$statement->bindValue(':fichier', $quelfic, PDO::PARAM_STR);
				$result = $statement->execute();
				$msg = $t->display("Delete ok !");
			}
		}
	}
}

// we begin by scan for refresh database
$frontend = false;
$reality = scan($dir);

// Then all real file are present in database
// we spide database to fill array and top all file non-existent

$statement = $pdo->prepare('SELECT * FROM lespanos;');
$statement->execute();
$hashfic=$titre=$legende="";

$montab = "<table>\n";
$montab .= "<tr>";
$montab .= "        <th>".$t->display("Sel.")."</th>";
$montab .= "        <th>".$t->display("Private")."</th>";
$montab .= "        <th>".$t->display("Export")."</th>";
$montab .= "        <th>".$t->display("File")."</th>";
$montab .= "        <th>".$t->display("Title")."</th>";
$montab .= "        <th>".$t->display("Legend")."</th>";
$montab .= "        <th>".$t->display("Hash")."</th>";
$montab .= "</tr>\n";
$i=0;
while ($row = $statement->fetch()) {
	$i = $i+1;
	if (!file_exists($row['fichier'])){ 	
		$backgroungColor = ' style="background-color:red;"';
		$checkBox = "checked";
	} else {
		$backgroungColor = $checkBox = "";
		if (rtrim($row['titre'])==""){		// File exist but not personalized gray color is affected
			$backgroungColor = ' style="background-color:gray;"';
		}
	}
	$titreTmp = "";
	if (rtrim($row['titre'])==""){
		$titreTmp = "...";
	} else {
		$titreTmp = $row['titre'];
	}
	if (strpos($row['fichier'],"-p-") === false ){	// Not a private file
		$priv = "";
	} else {							// is private
		$priv='<span style="color:orange;font-weight: 500;">Privée</span> ';
	}
	if ($checkBox <> "checked"){
		$titreTmp = '<a href="gest-form.php?p='.urlencode($row['fichier']).'">'.$titreTmp.'</a>';
	}
	$titreExport = '<a href="export.php?p='.urlencode($row['fichier']).'" title="'.$t->display("Sphere Export").'">Z</a>';
	
	$montab .= "<tr".$backgroungColor.">";
	$montab .= '		<td><input type="checkbox" name="C_'.$i.'" value="Ok"'.$checkBox.'>';
	$montab .= '			<input type="hidden" name="FIC_'.$i.'" value="'.$row['fichier'].'">';
	$montab .= "		</td>";
	$montab .= "        <td>".$priv."</td>";
	$montab .= "        <td>".$titreExport."</td>";
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
<head lang="<?php echo $langue; ?>">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<title><?php echo $t->display("Sphères"); ?></title>


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
		<li><a href="index.php"><?php echo $t->display("Exit management"); ?></a></li>
		<?php
		if (version_compare(phpversion(), '5.5.0', '>=')) {     // Must be > 5.5 for use authentification of tinymanagerfile
		?>
		<li><a href="tinyfilemanagergest/tinyfilemanager.php"><?php echo $t->display("Add/Delete files"); ?></a></li>
		<li><a href="param.php"><?php echo $t->display("Parameters"); ?></a></li>
		<li><a href="import.php" title="<?php echo $t->display("Import Form"); ?>"><?php echo $t->display("Import"); ?></a></li>
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
		<span style="background-color:red"><?php echo $t->display("Red background files that no longer exist, they are checked by default for deletion from the database"); ?></span><br />
		<span style="background-color:gray"><?php echo $t->display("Gray background unpersonalized files"); ?></span><br />
	</p>
	<br />
	<?php echo $montab; ?> 
	<br />
	<input type="submit" value="&nbsp;<?php echo $t->display("Clear files with checked lines"); ?>&nbsp;" /><br/><br />
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