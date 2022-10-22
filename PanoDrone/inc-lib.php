<?php
// Function php used in all script order by aphabetic name

/**
 * Test if column of table exist 
 * 
 *
 * @param string $spath
 * 
 * 		file source
 * 
 * @param string $dpath
 * 
 * 		file destination
 *
 * @param integer $maxd
 * 
 * 		size in pixels
 *
 * @return boolean
 *      Return true if thumbnail create 
**/
function createThumb($spath, $dpath, $maxd) {
	$src=@imagecreatefromjpeg($spath);
	if (!$src) return false;
	
	$srcw=imagesx($src);
	$srch=imagesy($src);
	if ($srcw<$srch) {
		$height=$maxd;
		$width=floor($srcw*$height/$srch);
	} else {
		$width=$maxd;
		$height=floor($srch*$width/$srcw);
	}
	if ($width>$srcw && $height>$srch) {
		$width=$srcw;
		$height=$srch;
	}  //if image is actually smaller than you want, leave small (remove this line to resize anyway)
	$thumb=imagecreatetruecolor($width, $height);
  	if ($height<100) {
		imagecopyresized($thumb, $src, 0, 0, 0, 0, $width, $height, imagesx($src), imagesy($src));
	} else {
		imagecopyresampled($thumb, $src, 0, 0, 0, 0, $width, $height, imagesx($src), imagesy($src));
  		imagejpeg($thumb, $dpath);
	  	return true;
	}
}


/**
 * Test if column of table exist 
 * 
 * Affiche dans une combo les repertoire et sous-repertoire où se trouve des spheres
 *
 * @param string Repertoire racine des spheres
 * 
 * @return string for html combo
 *      Return true if column exist 
 * 
 * Initiale fonction from Pevara https://stackoverflow.com/questions/25067241/how-to-list-all-files-in-folders-and-sub-folders-using-scandir-and-display-them
**/

function dirToOptions($path = __DIR__, $level = 0) {
	global $comboDest;
    $items = scandir($path);
    foreach($items as $item) {
        // ignore items strating with a dot (= hidden or nav)
        if (strpos($item, '.') === 0 ||  pathinfo($item, PATHINFO_EXTENSION) == "d" || pathinfo($item, PATHINFO_EXTENSION) == "jpg" || pathinfo($item, PATHINFO_EXTENSION) == "html") {
        //if (strpos($item, '.') === 0) {
            continue;
        }

        $fullPath = $path . DIRECTORY_SEPARATOR . $item;
        // add some whitespace to better mimic the file structure
        $item = str_repeat('&nbsp;', $level * 3) . $item;
        // file
        if (is_file($fullPath)) {
            // echo "<option>$item</option>";
        }
        // dir
        else if (is_dir($fullPath)) {
            // immediatly close the optgroup to prevent (invalid) nested optgroups
            //echo "<optgroup label='$item'></optgroup>";
            //echo "<option>$item</option>";  // On affiche pas directement sinon ça oblige a utiliser la fonction au milieu du html
			$comboDest .= "<option value=\"".$fullPath."\">$item</option>\n";
            // recursive call to self to add the subitems
            dirToOptions($fullPath, $level + 1);
        }
    }

}

/**
 * Display wwarning or information message
 * 
 *
 * @param string $msg
 * 
 *
 * @return html string
 * 
**/
function display_msg($msg){
	GLOBAL $t;
	
	$msgDisplay = "";
	if($msg!=""){
		$msgDisplay = "<fieldset>";
		$msgDisplay.= '<legend> '.$t->display("General information").' </legend>';
		$msgDisplay.= '<p>'.$msg.'</p>'; 
		$msgDisplay.= "</fieldset>";
	}
	return $msgDisplay;
}


/**
 * Test if column of table exist 
 * 
 *
 * @param string $table
 * 
 * 		Name of table
 * 
 * @param string $column
 * 
 * 		Name of column to test
 *
 * @return boolean
 *      Return true if column exist 
**/
function DB_column_exists($table,$column){
	GLOBAL $pdo;
	// If table is empty we need one row for success test
	$colExist = false;
	$fichier="To delete";
	$stmt = $pdo->prepare('INSERT INTO '.$table.' (fichier) VALUES (:fichier);');
	$stmt->bindValue(':fichier', $fichier);
	$result = $stmt->execute();
	$result = $pdo->query('SELECT * FROM '.$table.' LIMIT 1');
	foreach ($result as $row){
	    foreach($row as $col_name => $val){
			if ($col_name == $column) $colExist = true;   
     	}
 	}
	$stmt = $pdo->prepare('DELETE FROM lespanos WHERE fichier=:fichier');
	$stmt->bindValue(':fichier', $fichier);
	$result = $stmt->execute();
	return $colExist;
}

/**
 * Test if table exist 
 * 
 *
 * @param string $table
 * 
 * 		Name of table to test
 *
 * @return boolean
 *      Return true if table exist 
**/
function DB_table_exists($table){
    GLOBAL $pdo;
    try{
        $pdo->query("SELECT 1 FROM $table");
    } catch (PDOException $e){
        return false;
    }
    return true;
}

/**
 * Translate and display message error with home link 
 * 
 *
 * @param string $quelError
 * 
 * 		Message error must be translate
 *
 * @return 
 *      Print error message with link to home 
**/
function display_Frontend_Error($quelError){
	GLOBAL $t;
	echo "*** ".$t->display("Error")." ***";
	echo "<br />".$t->display($quelError);
	echo "<br /><a href=\".\">".$t->display("Go home sphere")."</a>";
}

/**
 * Resize picture 
 * 
 *
 * @param string $quelfic
 * 
 * 		file picture must be resize

 * @param integer $after_width
 * 
 * 		size in px
 *
 * @return 
 *      create file picture with name concatenation of $quelfic + -MinX0 + $after_width + jpg 
**/


/**
 * Recursive copy move or delete 
 * 
 *
 * @param string $src $dest
 * 
 * 		

 *
 * @return
 * 
 * Source https://stackoverflow.com/questions/9835492/move-all-files-and-folders-in-a-folder-to-another by chybeat
 *       
**/

defined('DS') ? NULL : define('DS',DIRECTORY_SEPARATOR);

function full_move($src, $dst){
    full_copy($src, $dst);
    full_remove($src);
}

function full_copy($src, $dst) {
    if (is_dir($src)) {
        @mkdir( $dst, 0777 ,TRUE);
        $files = scandir($src);
        foreach($files as $file){
            if ($file != "." && $file != ".."){
                full_copy("$src".DS."$file", "$dst".DS."$file");
            }
        }
    } else if (file_exists($src)){
        copy($src, $dst);
    }
}

function full_remove($dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file){
            if ($file != "." && $file != ".."){
                full_remove("$dir".DS."$file");
            }
        }
        rmdir($dir);
    }else if (file_exists($dir)){
        unlink($dir);
    }
}


function imageResize($quelfic,$after_width){
	$name_thumbnail = nameThumbnail($quelfic,$after_width);
	// Example thumbnail name for "my-picture.jpg" create  "my-picture-MinX0200.jpg"
	if( class_exists("Imagick") ){
		// We create 2 thumbnail for blog or seo
		// First size 200px thumb, second 600px medium
		// Thumbnail named with x200x or x600x are ignored by function scan()
		$image = new Imagick($quelfic);
		$image->thumbnailImage($after_width, 0, false);
		$image->writeImage($name_thumbnail);
		return;
	}
	if (version_compare(phpversion(), '5.5.0', '>=')) {     // Must be > 5.5 because use imagescale

    	$img = imagecreatefromjpeg($quelfic);

    	//Let's do the resize thing
		//imagescale([returned image], [width of the resized image], [height of the resized image], [quality of the resized image]);
		$imgResized = imagescale($img, $after_width, -1);
 
		//now save the resized image with a suffix called "-resized" and with its extension. 
		imagejpeg($imgResized, $name_thumbnail);
  
		//Finally frees any memory associated with image
		//**NOTE THAT THIS WONT DELETE THE IMAGE
  		imagedestroy($img);
		imagedestroy($imgResized);
		return;
	}
	//Resize with native function of gd lib
	createThumb($quelfic, $name_thumbnail, $after_width);	
}




/**
 * Test if directory name ending by .d 
 * 
 * Used by function scan() to ignore this directory
 *
 * @param string $aTester

 *
 * @return boolean
 *      true if directory ending by .d
**/
function isDirectoryHD($aTester){
	// Quand c'est un repertoire fichier qui fini par .d retourne true en theorie c'est un repertoire
	// When it's a directory endind by .d we return true this directory is ignored by function scan()
	$path_parts = pathinfo($aTester);
	if (!isset($path_parts['extension'])) return false;
	if ($path_parts['extension']=="d"){	
		return true;
	} else {
		return false;
	}
}

/**
 * Find the name of directory .d contain src, tiles and thumbnail 
 * 
 * 
 *
 * @param string $aTester

 *
 * @return string
 *      string 
**/
function nameDirD($aTester){
	if (removeSmall($aTester)!="") $aTester = removeSmall($aTester);
	$path_parts = pathinfo($aTester);
	return  $path_parts['dirname']."/".$path_parts['filename'].".d";
}

/**
 * Find the prefix of thumbnail and create directory .d if not exist
 * 
 * Example nameThumbnail("Spheres/sphere-name.jpg","600")
 * 		return Spheres/sphere-name.d/sphere-name-600.jpg 
 *
 * @param string $aTester
 * 
 * @param string $size
 *
 * @return string
 *      string 
**/
function nameThumbnail($aTester,$size){
	// First if sub-directory with name of jpg sphere complet with .d is inexistant the create
	$name_Dir_D = nameDirD($aTester);
	if(!is_dir($name_Dir_D)){
		mkdir($name_Dir_D);
	}
	$path_parts = pathinfo($name_Dir_D);
	$name_fic = $path_parts['filename']; 
	return $name_Dir_D."/".$name_fic."-".$size.".jpg";
}


/**
 * if string ".small.jpg"  is found, return string without ".small.jpg" else return blank string
 * 
 * Used by pano to display tile mode or not
 *
 * @param string $aTester

 *
 * @return string
 *      string without true if ".small." is found
**/
function removeSmall($aTester){
	// If string ".small." then return true it's small version of sphere and tile must be displayed
	$pos = strpos($aTester,".small.jpg");
	if ($pos === false){
		return "";
	} else {
		return str_replace(".small.jpg","",$aTester);
	}
}



$frontend = false;  // Only script php name scan.php need $fronted = true to ignore file with string "-p-" because it's a private file on link direct can show private file


/**
 * Protect or not directory by placement of an empty file named index.html
 *
 * @param string $browsingProtect
 * 		Value Y or N 
 * 			if Y we protect elese nothing do
 *
 * @param string $quelfic
 *      Name with is relative path 
 *
 * @return boolean
 *      true is returned it's a private file
**/
function isPrivate($f){
	global $frontend;
	if ($frontend){				// We are in frontend mode we must test if file is private
		if (strpos($f,"-p-") === false ){	// Not private
			return false;
		} else {							// Is private
			return true;
		}
	} else {
		return false;
	}
}


/**
 * Protect directory by placement of an empty file named index.html
 *
 * @param string $browsingProtect
 * 		Value Y or N 
 * 			if Y we protect elese nothing do
 *
 * @param string $quelfic
 *      Name with is relative path 
 *
 * @return
 *      Nothing just create or not empty file index.html
**/

function fBrowsingProtect($browsingProtect,$quelfic){
	GLOBAL $t;
	if ($browsingProtect=="Y"){
		$directory_protect = pathinfo($quelfic);
		if (isset($directory_protect['dirname'])){
			if (!touch($directory_protect['dirname']."/index.html")) {
				echo $t->display("Becarefull creating file failed : ").$directory_protect['dirname']."/index.html";
			}
		}
	}
}


/**
 * Generate a random string
 * 
 *
 * @param integer $length
 * 
 * 		length of the string returned
 *
 * @return string
 *      Return randomed string 
**/
function generateRandomString($length){
	$chars = "abcdfghjkmnpqrstvwxyz|ABCDFGHJKLMNPQRSTVWXYZ|0123456789";
	$sets = explode('|', $chars);
	$all = '';
	$randString = '';
	foreach($sets as $set){
		$randString .= $set[array_rand(str_split($set))];
		$all .= $set;
	}
	$all = str_split($all);
	for($i = 0; $i < $length - count($sets); $i++){
		$randString .= $all[array_rand($all)];
	}
	$randString = str_shuffle($randString);
	return $randString;
}


/**
 * create marker to access high resolution file used for stitch panorama
 * 26 icon + represent link to .jpg showing on the sphere
 * Used in pano.php
 *
 * @param string $nom_img
 * 
 * 		name of the picture hd
 * 
 * 
 * @param string $sphere_origin
 * 
 * 		0 for panorama obtained by share app Dji official app
 * 		1 for assembly by hugin
 * 
 * 		
 * @param string $tile
 * 
 * 		true if display mode is tile
 * 
 * 		
 *
 *
 * @return string
 *      Return string represent javascript code interpreted in navigator 
**/
function listimg($nom_img,$sphere_origin,$tile){
	if ($sphere_origin==0){
		$choice = "Origin";
	}
	if ($sphere_origin==1){
		$choice = "x17000";
	}
	if ($sphere_origin==2){
		$choice = "x8000";
	}

	//echo "<br />".$choice."<br />";
	switch($choice){
		case "Origin":
			// For panorama obtain by share from dji gallery application no tiles
			$array_latitude['DJI_0001.jpg']='-0.004289027394785538';
			$array_longitude['DJI_0001.jpg']='6.270889710111523';
			$array_latitude['DJI_0002.jpg']='0.3013313870042622';
			$array_longitude['DJI_0002.jpg']=$array_longitude['DJI_0001.jpg'];
			$array_latitude['DJI_0003.jpg']='-0.37412795455540393';
			$array_longitude['DJI_0003.jpg']=$array_longitude['DJI_0001.jpg'];
			$array_latitude['DJI_0004.jpg']='-0.9984770290397127';
			$array_longitude['DJI_0004.jpg']=$array_longitude['DJI_0001.jpg'];
			$array_latitude['DJI_0005.jpg']='-1.5515819483700128';
			$array_longitude['DJI_0005.jpg']='4.805821724249035';
			$array_latitude['DJI_0006.jpg']='-0.9441718419815546';
			$array_longitude['DJI_0006.jpg']='5.508066608297477';
			$array_latitude['DJI_0007.jpg']='-0.379117102340472';
			$array_longitude['DJI_0007.jpg']=$array_longitude['DJI_0006.jpg'];
			$array_latitude['DJI_0008.jpg']='0.15706505774670565';
			$array_longitude['DJI_0008.jpg']=$array_longitude['DJI_0006.jpg'];
			$array_latitude['DJI_0009.jpg']='0.3218122427214245';
			$array_longitude['DJI_0009.jpg']='4.702715033629989';
			$array_latitude['DJI_0010.jpg']='-0.34339358135409603';
			$array_longitude['DJI_0010.jpg']='4.7159519648892605';
			$array_latitude['DJI_0011.jpg']='-0.9749973860648611';
			$array_longitude['DJI_0011.jpg']='4.701757751170412';
			$array_latitude['DJI_0012.jpg']='-0.9339941096918665';
			$array_longitude['DJI_0012.jpg']='3.879978004038964';
			$array_latitude['DJI_0013.jpg']='-0.3791113578841814';
			$array_longitude['DJI_0013.jpg']='4.003540131510434';
			$array_latitude['DJI_0014.jpg']='0.0516956220605842';
			$array_longitude['DJI_0014.jpg']='3.921213994602778';
			$array_latitude['DJI_0015.jpg']='0.017818705162738757';
			$array_longitude['DJI_0015.jpg']='3.145644035406239';
			$array_latitude['DJI_0016.jpg']='-0.3576854666765099';
			$array_longitude['DJI_0016.jpg']='3.144716598158034';
			$array_latitude['DJI_0017.jpg']='-0.9492733044471433';
			$array_longitude['DJI_0017.jpg']='3.123834924588665';
			$array_latitude['DJI_0018.jpg']='-0.918862968275767';
			$array_longitude['DJI_0018.jpg']='2.2789628296836844';
			$array_latitude['DJI_0019.jpg']='-0.35609755768594265';
			$array_longitude['DJI_0019.jpg']='2.285192730090629';
			$array_latitude['DJI_0020.jpg']='0.003939075409890247';
			$array_longitude['DJI_0020.jpg']='2.460332835203131';
			$array_latitude['DJI_0021.jpg']='0.031604646893518495';
			$array_longitude['DJI_0021.jpg']='1.5262695520367295';
			$array_latitude['DJI_0022.jpg']='-0.29361429884169077';
			$array_longitude['DJI_0022.jpg']='1.5332463426195047';
			$array_latitude['DJI_0023.jpg']='-0.845293098201418';
			$array_longitude['DJI_0023.jpg']='1.4026881634682018';
			$array_latitude['DJI_0024.jpg']='-0.9246040107043139';
			$array_longitude['DJI_0024.jpg']='0.739180226759827';
			$array_latitude['DJI_0025.jpg']='-0.4023236804092969';
			$array_longitude['DJI_0025.jpg']='0.7238443462199831';
			$array_latitude['DJI_0026.jpg']='0.10871059434518116';
			$array_longitude['DJI_0026.jpg']='0.7756130501397881';
			break;
		
		case "x8000":
			// For panorama obtain by hugin 8000x4000 pixels display mode tile (format 1/2)
			$array_latitude['DJI_0001.jpg']='-0.023760355615198847';
			$array_longitude['DJI_0001.jpg']='1.0287609507774766';
			$array_latitude['DJI_0002.jpg']='0.29818518695013907';
			$array_longitude['DJI_0002.jpg']='1.0198323728996794';
			$array_latitude['DJI_0003.jpg']='-0.39087380436366903';
			$array_longitude['DJI_0003.jpg']='1.1284213853534941';
			$array_latitude['DJI_0004.jpg']='-1.0086060458587873';
			$array_longitude['DJI_0004.jpg']='1.295021633387777';
			$array_latitude['DJI_0005.jpg']='-1.5404403164411162';
			$array_longitude['DJI_0005.jpg']='5.238634969031125';
			$array_latitude['DJI_0006.jpg']='-0.9543740634264375';
			$array_longitude['DJI_0006.jpg']='0.21662540890500814';
			$array_latitude['DJI_0007.jpg']='-0.3832837824745148';
			$array_longitude['DJI_0007.jpg']='0.21851746244170883';
			$array_latitude['DJI_0008.jpg']='0.2545794042943368';
			$array_longitude['DJI_0008.jpg']='0.3265112590579908';
			$array_latitude['DJI_0009.jpg']='0.2557770589795265';
			$array_longitude['DJI_0009.jpg']='6.03282215691589';
			$array_latitude['DJI_0010.jpg']='-0.4215225242418752';
			$array_longitude['DJI_0010.jpg']='5.868150735328409';
			$array_latitude['DJI_0011.jpg']='-0.9283846160164333';
			$array_longitude['DJI_0011.jpg']='5.892352760194571';
			$array_latitude['DJI_0012.jpg']='-1.092160861446268';
			$array_longitude['DJI_0012.jpg']='4.771364735621435';
			$array_latitude['DJI_0013.jpg']='-0.4881905518960741';
			$array_longitude['DJI_0013.jpg']='5.041099583323765';
			$array_latitude['DJI_0014.jpg']='0.030604164298322134';
			$array_longitude['DJI_0014.jpg']='4.934266388999964';
			$array_latitude['DJI_0015.jpg']='0.11102038858674734';
			$array_longitude['DJI_0015.jpg']='4.21528829433074';
			$array_latitude['DJI_0016.jpg']='-0.41578959977846086';
			$array_longitude['DJI_0016.jpg']='4.157292075483124';
			$array_latitude['DJI_0017.jpg']='-1.0024303913777803';
			$array_longitude['DJI_0017.jpg']='3.985621353205736';
			$array_latitude['DJI_0018.jpg']='-0.9421580658371513';
			$array_longitude['DJI_0018.jpg']='3.5974111501583836';
			$array_latitude['DJI_0019.jpg']='-0.3543993446946603';
			$array_longitude['DJI_0019.jpg']='3.5531202647408526';
			$array_latitude['DJI_0020.jpg']='0.12240673650502165';
			$array_longitude['DJI_0020.jpg']='3.420412891914962';
			$array_latitude['DJI_0021.jpg']='0.14815229291558651';
			$array_longitude['DJI_0021.jpg']='2.6928424374862914';
			$array_latitude['DJI_0022.jpg']='-0.36955061520703447';
			$array_longitude['DJI_0022.jpg']='2.631730798405637';
			$array_latitude['DJI_0023.jpg']='-0.9439272529773404';
			$array_longitude['DJI_0023.jpg']='2.6839975967967815';
			$array_latitude['DJI_0024.jpg']='-1.0621494069683424';
			$array_longitude['DJI_0024.jpg']='1.8038985288839868';
			$array_latitude['DJI_0025.jpg']='-0.4041158963385807';
			$array_longitude['DJI_0025.jpg']='1.849555517396198';
			$array_latitude['DJI_0026.jpg']='0.26759115845061876';
			$array_longitude['DJI_0026.jpg']='1.8236936538064574';
			break;
		
		case "x17000":
			// For panorama obtain by Hugin HD 17000x8000 pixels display mode tile (format 1/2)
			$array_latitude['DJI_0001.jpg']='0.008967974697416947'; //DJI_0001.jpg
			$array_longitude['DJI_0001.jpg']='5.0889947190292775';
			$array_latitude['DJI_0002.jpg']='0.22773936535909023'; //DJI_0002.jpg
			$array_longitude['DJI_0002.jpg']='5.110513070426429';
			$array_latitude['DJI_0003.jpg']='-0.3396948468831038'; //DJI_0003.jpg
			$array_longitude['DJI_0003.jpg']='5.1814259177884665';
			$array_latitude['DJI_0004.jpg']='-0.8747986662918383'; //DJI_0004.jpg
			$array_longitude['DJI_0004.jpg']='5.280936731429585';
			$array_latitude['DJI_0005.jpg']='-1.4904039818859336'; //DJI_0005.jpg
			$array_longitude['DJI_0005.jpg']='4.8896591829808775';
			$array_latitude['DJI_0006.jpg']='-0.7952452849305613'; //DJI_0006.jpg
			$array_longitude['DJI_0006.jpg']='4.424596775494944';
			$array_latitude['DJI_0007.jpg']='-0.3154269953500064'; //DJI_0007.jpg
			$array_longitude['DJI_0007.jpg']='4.377986274811221';
			$array_latitude['DJI_0008.jpg']='0.2700166808094029'; //DJI_0008.jpg
			$array_longitude['DJI_0008.jpg']='4.31699451200415';
			$array_latitude['DJI_0009.jpg']='0.2472831946405465'; //DJI_0009.jpg
			$array_longitude['DJI_0009.jpg']='3.5203185278134423';
			$array_latitude['DJI_0010.jpg']='-0.3906012198884059'; //DJI_0010.jpg
			$array_longitude['DJI_0010.jpg']='3.507393354281541';
			$array_latitude['DJI_0011.jpg']='-0.9682058798984614'; //DJI_0011.jpg
			$array_longitude['DJI_0011.jpg']='3.5154456931930462';
			$array_latitude['DJI_0012.jpg']='-0.96570523045557'; //DJI_0012.jpg
			$array_longitude['DJI_0012.jpg']='2.702343710657582';
			$array_latitude['DJI_0013.jpg']='-0.37303458876364504'; //DJI_0013.jpg
			$array_longitude['DJI_0013.jpg']='2.8118763405632485';
			$array_latitude['DJI_0014.jpg']='0.294783815546253'; //DJI_0014jpg
			$array_longitude['DJI_0014.jpg']='2.867018919322091';
			$array_latitude['DJI_0015.jpg']='0.2465790480280634'; //DJI_0015.jpg
			$array_longitude['DJI_0015.jpg']='1.8481583356328843';
			$array_latitude['DJI_0016.jpg']='-0.3404028001768151'; //DJI_0016.jpg
			$array_longitude['DJI_0016.jpg']='1.9636478251139409';
			$array_latitude['DJI_0017.jpg']='-0.8649957664408308'; //DJI_0017.jpg
			$array_longitude['DJI_0017.jpg']='1.915519337803427';
			$array_latitude['DJI_0018.jpg']='-0.9281497613200411'; //DJI_0018.jpg
			$array_longitude['DJI_0018.jpg']='1.219063385561155';
			$array_latitude['DJI_0019.jpg']='-0.3693138161827596'; //DJI_0019.jpg
			$array_longitude['DJI_0019.jpg']='1.1959015239129402';
			$array_latitude['DJI_0020.jpg']='0.15137531284078642'; //DJI_0020.jpg
			$array_longitude['DJI_0020.jpg']='0.6256303847861546';
			$array_latitude['DJI_0021.jpg']='0.18960090573857524'; //DJI_0021.jpg
			$array_longitude['DJI_0021.jpg']='0.4289265763070863';
			$array_latitude['DJI_0022.jpg']='-0.352591764461363'; //DJI_0022.jpg
			$array_longitude['DJI_0022.jpg']='0.4103722686309854';
			$array_latitude['DJI_0023.jpg']='-0.9109537493278612'; //DJI_0023.jpg
			$array_longitude['DJI_0023.jpg']='0.385317686213223';
			$array_latitude['DJI_0024.jpg']='-0.8669910700024062'; //DJI_0024.jpg
			$array_longitude['DJI_0024.jpg']='5.928902872080257';
			$array_latitude['DJI_0025.jpg']='-0.3759656966856393'; //DJI_0025.jpg
			$array_longitude['DJI_0025.jpg']='5.9366970531955126';
			$array_latitude['DJI_0026.jpg']='0.2402713997938748'; //DJI_0026.jpg
			$array_longitude['DJI_0026.jpg']='5.9317015256733745';
			break;	
	}
	
	$jmarqueur="a.push({\n";
	$jmarqueur.="\t id       : '".$nom_img."',\n";
	$jmarqueur.="\t tooltip  : {\n";
	$jmarqueur.="\t\t content : '".$nom_img."',\n";
	$jmarqueur.="\t\t position: 'bottom right',\n";
	$jmarqueur.="\t },\n";
	//$jmarqueur.="\t content  : document.getElementById('dji-".$nb_marqueur."').innerHTML,\n";
	$jmarqueur.="\t latitude : ".$array_latitude[$nom_img].",\n";
	$jmarqueur.="\t longitude: ".$array_longitude[$nom_img].",\n";
	$jmarqueur.="\t image    : 'example/assets/plus.gif',\n";
	$jmarqueur.="\t width    : 32,\n";
	$jmarqueur.="\t height   : 32,\n";
	$jmarqueur.="\t anchor   : 'bottom center',\n";
	$jmarqueur.="\t hideList : 'true',\n";					// Only icon + on sphere is showing, links in right panel are hidden
	$jmarqueur.="});\n";
	$jmarqueur.="console.log('Choice=".$choice."')\n";
	return $jmarqueur;
}

/**
 * Scan the files folder recursively, and builds a large array for json and database
 * 
 * At start this function is in script scan.php but we need in scan.php and gest.php
 *
 * @param string $dir
 * 
 * 		name of the directory browsing
 *
 * @return string
 *      Return json 
**/
function scan($dir){
	global $pdo;
	$files = array();

	// Is there actually such a folder/file?

	if(file_exists($dir)){
	
		foreach(scandir($dir) as $f) {
		
			if(!$f || $f[0] == '.' || pathinfo($f, PATHINFO_EXTENSION )=="xml" || isPrivate($f) || isDirectoryHD($f) || pathinfo($f, PATHINFO_EXTENSION )=="php" || pathinfo($f, PATHINFO_EXTENSION )=="html" || pathinfo($f, PATHINFO_EXTENSION )=="sql" || pathinfo($f, PATHINFO_EXTENSION )=="txt") {
				continue; // Ignore hidden files
			}

			if(is_dir($dir . '/' . $f)) {

				// The path is a folder

				$files[] = array(
					"name" => $f,
					"titre"=> "",
					"type" => "folder",
					"path" => $dir . '/' . $f,
					"items" => scan($dir . '/' . $f) // Recursively get the contents of the folder
				);
			}
			
			else {
				// We store title and legend  in database
				$titre="";
				$legende="";
				$fichier = $dir . '/' . $f;
				$statement = $pdo->prepare('SELECT titre,legende FROM lespanos WHERE fichier = :fichier LIMIT 1;');
				$statement->bindValue(':fichier', $fichier);
				$statement->execute();
				$row=$statement->fetch();
				// check for empty result
				if ($row != false) { // Trouvé
					$titre	= $row['titre'];
					$legende= $row['legende'];
				} else {
					// J'ai pas trouvé alors il faut insert
    				$statement = $pdo->prepare('INSERT INTO lespanos (fichier) VALUES (:fichier);');
					$statement->bindValue(':fichier', $fichier);
					$statement->execute();
				}

				if (rtrim($titre)=="") $titre = $f;
				if (rtrim($legende)=="") $legende = "";
				$files[] = array(
					"name" => $f,
					"titre"=> $titre,
					"legende"=> $legende,
					"type" => "file",
					"path" => $dir . '/' . $f,
					"size" => filesize($dir . '/' . $f) // Gets the size of this file
				);
			}
		}
	
	}

	return $files;
}

/**
 * Sphere Delete 
 *
 *	Fisrt step With the hasfic, find and delete all record in table lespanos and lespanos_details 
 *  Second step delete all file and directory for this sphere
 *
 * @param string $hashfic
 * 
 * 		delete all file for sphere corresponding hasfic and enr in database

 *
 * @return 	false if error
 * 			true if ok
 *       
**/

function SphereEfface($hashfic){
	global $pdo;

	// On commence par retrouver la sphere dans la table panos
	$statement = $pdo->prepare('SELECT fichier FROM lespanos WHERE hashfic = :hashfic LIMIT 1;');
    $statement->bindValue(':hashfic', $hashfic, PDO::PARAM_STR);
    $statement->execute();
	$fichier = "";
    while ($row = $statement->fetch()) {
        $fichier = $row['fichier'];
    }
    if ($fichier!=""){
		// On efface des deux tables tous les enregistrements qui match avec fichier
		
		
		$statement = $pdo->prepare('DELETE FROM lespanos WHERE hashfic = :hashfic;');
    	$statement->bindValue(':hashfic', $hashfic, PDO::PARAM_STR);
    	$statement->execute();

		$statement = $pdo->prepare('DELETE FROM lespanos_details WHERE hashfic = :hashfic LIMIT 1;');
    	$statement->bindValue(':hashfic', $hashfic, PDO::PARAM_STR);
    	$statement->execute();
		

		// Maintenant les fichiers
		unlink($fichier);
		$repertoire = dirname($fichier)."/".pathinfo($fichier, PATHINFO_FILENAME).".d";
		full_remove($repertoire);
		return true;
	} else {
		return false;
	}    

}


/**
 * Sphere Import 
 * 
 *
 * @param string $hashfic
 * 
 * 		

 *
 * @return 	false if error
 * 			true if ok
 *       
**/

function SphereImport($hashfic,$nom_Sphere,$destination){

	global $pdo;

	if (DB_table_exists('lespanos_import')){
		$SqlString = "drop table 'lespanos_import';";
		$pdo->exec($SqlString);
	}
	if (DB_table_exists('lespanos_details_import')){
		$SqlString = "drop table 'lespanos_details_import';";
		$pdo->exec($SqlString);
	}

	// On importe le .sql dans les table d'import
    
    $SqlString = createTable('lespanos_import');
	$pdo->exec($SqlString);
    $SqlString = createTable('lespanos_details_import');
	$pdo->exec($SqlString);

    //Ok maintenant nous avons nos table temporaire d'import nous pouvons importer le .sql
    $ficsql = 'export-import/import/'.$nom_Sphere.'.d/'.$nom_Sphere.'.sql';
    $SqlString =file_get_contents($ficsql);
    $pdo->exec($SqlString);

	//Maintenant on fusionne les tables import dans les tables reelles
	$SqlString = "INSERT INTO 'lespanos' ('fichier','titre','legende','legende_long','hashfic','short_code','sphere_origin') 
						SELECT fichier,titre,legende,legende_long,hashfic,short_code,sphere_origin FROM 'lespanos_import';";
	$pdo->exec($SqlString);

	$SqlString = "INSERT INTO 'lespanos_details' ('fichier','hashfic','nom_marqueur','couleur','latitude','longitude','descri','marker_center') 
						SELECT fichier,hashfic,nom_marqueur,couleur,latitude,longitude,descri,marker_center FROM 'lespanos_details_import';";
	$pdo->exec($SqlString);


    //Nous n'avons plus besoins des table d'import
    $SqlString = "drop table 'lespanos_import';";
	$pdo->exec($SqlString);
    $SqlString = "drop table 'lespanos_details_import';";
	$pdo->exec($SqlString);

	// On met à jour le champs fichier
	$fichier=$destination."/".$nom_Sphere.".jpg";
	$statement = $pdo->prepare("UPDATE 'lespanos' SET fichier=:fichier WHERE hashfic=:hashfic");
	$statement->bindValue(':fichier', $fichier, PDO::PARAM_STR);
	$statement->bindValue(':hashfic', $hashfic, PDO::PARAM_STR);
	$statement->execute();

	$statement = $pdo->prepare("UPDATE 'lespanos_details' SET fichier=:fichier WHERE hashfic=:hashfic");
	$statement->bindValue(':fichier', $fichier, PDO::PARAM_STR);
	$statement->bindValue(':hashfic', $hashfic, PDO::PARAM_STR);
	$statement->execute();

	// Maintenant on deplace ce qui a été dezippé dans la nouvelle destination
	full_move('export-import/import/'.$nom_Sphere.'.d', $destination."/".$nom_Sphere.'.d');

	// On deplace le .jpg
	rename('export-import/import/'.$nom_Sphere.'.jpg' , $destination."/".$nom_Sphere.'.jpg');
	unlink('export-import/import/'.$nom_Sphere.'.zip');
	unlink($destination."/".$nom_Sphere.'.d/'.$nom_Sphere.'.txt');
	unlink($destination."/".$nom_Sphere.'.d/'.$nom_Sphere.'.sql');

	
}

/**
 * TRADUCTOR 1.0
* This class is in charge of translation in a php web application.
 * hosted in github 
 * By adonis97 <simoadonis@gmail.com>
 * Under LGPL licence. 
* HOW IT WORKS !!!!
 * 1- instanciate the class Traductor with a language name or not
 * 2- if there is no language name set in constructor first call setLanguage() methode to initialise a language pack the language pack is set in
 *      languages/{{language_file.php}}
 *      A language file as a array names $lang[] and the global structure is
 *      $lang['MACRO_NAME'] = 'VALUE_OF_MACRO'
 * 3- after that you can display a data just by using display() method like : display(MACRO_NAME);
* 
 * For better using you can store the translation details inside a session by doing
 * $_SESSION['VAR_NAME'] = $traduction->getLanguageData();
 * echo $_SESSION['VAR_NAME'][MACRO_NAME];
*/

class Traductor {

    /**
     * Will hold the a language data
     * @var array $lang
     */
    protected $lang = array();

    /**
     * Will hold the name of the current language 
     *
     * @var type string
     */
    protected $language;
    
    /**
     * Hold the path for finding languages files 
     * @var string The path of the directorie of languages
     */
    protected $LANG_PATH = 'languages/';
    
    /**
     * Contain the file extension 
     *
     * @var text 
     */
    protected $FILE_EXT = '.php';



    /**
     * Initialise the class
     * 
     * @param array $data 
     */
    private function hydrate($data = array()){
        if(isset($data['language'])){
            
            $this->setLanguage($data['language']);
        }
    }
    
    /**
     * This will be use to initialise a data of language
     * 
     * @param string $language_name
     */
    public function setLanguage($language_name){
        $this->language = $language_name;
        $complete_path = $this->LANG_PATH . $language_name.$this->FILE_EXT;
        if(!file_exists($complete_path)){
            echo 'Error : unable to find the specified language {'. $language_name.'} Located at ['. $complete_path .']';
            die();
        }
        //starting loading the language file. It will be a .php file
        @require_once $this->LANG_PATH . $language_name.$this->FILE_EXT;
        //this $lang is comming from the included file !!! and it data 
        // is saved to the lang attribute
        $this->lang = $lang;
    }
    
    /**
     * Is use for displaying and text from the language file
     * 
     * @param string $macro
     */
    public function display($macro){
        /**
         * Verifie first if any language has been set
         * If the macro exist inside the array we display it;
         */
        if($this->is_language_set() === FALSE){
            echo 'Error no language has been set; call setLanguage($lang_name) method before displaying macro';
            die();
        }
        if(array_key_exists($macro, $this->lang)){
            //echo $this->lang[$macro];
            //return $this;
            return $this->lang[$macro];
        }else{
            //echo 'Error unable to find the macro {'. $macro . '} in ['. $this->language .'] translation pack';
            //die();
            // On retroune la clef
            return $macro;
        }
        
    }
    
    /**
     * Is use for testing if a langage has been set by the user
     * 
     * @param string $lang_name
     */
    public function is_language_set(){
        if(empty($this->language)){
            return FALSE;
        }  else {
            return TRUE;
        }
    }
    
    
    /**
     * Is use for retrive the table of language pack
     * 
     * @return array table data of the traduction
     */
    public function getLanguageData(){
        if($this->is_language_set() === FALSE){
            echo 'Error no language has been set; call setLanguage($lang_name) method before displaying macro';
            die();
        }
        return $this->lang;
    }
    
    public function __construct($language = NULL) {
        $this->hydrate(array('language' => $language));
    }

}

?>