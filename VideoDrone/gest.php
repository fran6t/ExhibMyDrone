<?php
include('inc-config.php');
include('inc-lib.php');

// On va se servir de la connection de tinyfilemanager pour savoir si on peu acceder
// Attention tous ceux qui sont identifiés correctement dans tinyfilemanger accederons 
if ( !defined( 'FM_SESSION_ID')) {
    define('FM_SESSION_ID', 'filemanager');
}
session_name(FM_SESSION_ID );	// On pointe la session de tinyfilemanager
session_start();
if (!isset($_SESSION[FM_SESSION_ID]['logged'])){
	// On redirige vers tinyfilemanager pour se connecter
	header('Location: tinyfilemanagergest/tinyfilemanager.php');
	exit;
}

if (isset($_POST['v'])){
	for ($a = 1; $a <= $_POST['cpt']; $a++){
		if ($_POST['C_'.$a]=="Ok"){
			$quelfic = $_POST['FIC_'.$a];
			$statement = $db->prepare('DELETE FROM lesvideos WHERE fichier = :fichier;');
	    	$statement->bindValue(':fichier', $quelfic, SQLITE3_TEXT);
			$result = $statement->execute();
			$statement = $db->prepare('DELETE FROM lesvideos_details WHERE fichier = :fichier;');
	    	$statement->bindValue(':fichier', $quelfic, SQLITE3_TEXT);
			$result = $statement->execute();
			$msg = "Suppression Ok !";			
		}
	}
}

// On commence apr faire un scan tout frais des la réalité des fichiers en place
$reality = scan("../Public/Videos");

// A l'issue de ce scan tous les fichiers réels présent sur le disque sont donc dans la bdd
// On parcours donc la bdd maintenant pour remplir un tableau et marqer tous les fichiers qui n'existent plus

$statement = $db->prepare('SELECT * FROM lesvideos;');
$result = $statement->execute();
$hashfic=$titre=$legende="";

$montab = "<table>\n";
$montab .= "<tr>";
$montab .= "        <th>Sel.</th>";
$montab .= "        <th>Fichier</th>";
$montab .= "        <th>Titre</th>";
$montab .= "        <th>Legende</th>";
$montab .= "        <th>Hash</th>";
$montab .= "</tr>\n";
$i=0;
while ($row = $result->fetchArray()) {
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
	$titreTmp = '<a href="gest-form.php?p='.urlencode($row['fichier']).'">'.$titreTmp.'</a>';
	$montab .= "<tr".$backgroungColor.">";
	$montab .= '		<td><input type="checkbox" name="C_'.$i.'" value="Ok"'.$checkBox.'>';
	$montab .= '			<input type="hidden" name="FIC_'.$i.'" value="'.$row['fichier'].'">';
	$montab .= "		</td>";
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

	<title>Vidéos</title>


	<!-- Include our stylesheet -->
	<link href="assets/css/styles.css" rel="stylesheet"/>

	<style type="text/css">
		body{color: white;}
		table { color: white; }
		td, th { border: 1px solid #111; padding: 6px; }
		th { font-weight: 700; }
		table a{ 
			font-weight: 700;
			color: yellow; 
		}

        /* Pour la nav bar qui chapeaute la liste */
		nav {
            background-color: #333;
            border: 1px solid #333;
            color: #fff;
            display: block;
            margin: 0;
            overflow: hidden;
        }

	    nav ul{
    	    margin: 0;
        	padding: 0;
         	list-style: none;
    	}
    	nav ul li {
           	margin: 0;
          	display: inline-block;
           	list-style-type: none;
           	transition: all 0.2s;
    	}

    	nav > ul > li > a {
          	color: #aaa;
          	display: block;
          	line-height: 2em;
          	padding: 0.5em 2em;
           	text-decoration: none;

    	}

    	nav li > ul{
          	display : none;
          	margin-top:1px;
          	background-color: #bbb;

    	}

    	nav li > ul li{
          	display: block;
	    }

	    nav  li > ul li a {
          	color: #111;
          	display: block;
        	line-height: 2em;
        	padding: 0.5em 2em;
    	    text-decoration: none;
    	}

	    nav li:hover {
        	background-color: #666;
	    }
	    nav li:hover > ul{
        	position:absolute;
        	display : block;
	    }
	    nav li > ul > li ul  {
         	display: none;
        	background-color: #888;	
	    }
	    nav li > ul > li:hover > ul  {
        	position:absolute;
        	display : block;
        	margin-left:100%;
         	margin-top:-3em;
	    }

	    nav ul > li.sub{
        	background: url(ic_keyboard_arrow_down_white_18dp.png) right center no-repeat;
	    }

    	nav ul > li.sub li.sub{
         	background: url(ic_keyboard_arrow_right_white_18dp.png) right center no-repeat;
	    }

	</style>


</head>
<body>

<nav class="menu">
	<ul>
		<li><a href="index.php">Quitter</a></li>
		<li><a href="tinyfilemanagergest/tinyfilemanager.php">Ajouter/Supprimer des vidéos</a></li>
	</ul>
</nav>
<?php if(isset($msg)) echo '<p><span  style="background-color:green;">'.$msg.'</span></p>'; ?>

<form id="MyForm" action="gest.php" method="post" class="form-example">
	<input id="v" name="v" type="hidden" value="ok">
	<input id="cpt" name="cpt" type="hidden" value="<?php echo $i; ?>">
	<br />
	<input type="submit" value="Valider" />
	<br /><br />
	<p>
		<span style="background-color:red">Fond rouge les fichiers qui n'existent plus, ils sont cochés par defaut pour effacement de la base de données</span><br />
		<span style="background-color:gray">Fond gris les fichiers non personalisés</span>
	</p>
	<br />
	<?php echo $montab; ?> 
	<br />
	<input type="submit" value="Valider" /><br/><br />
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