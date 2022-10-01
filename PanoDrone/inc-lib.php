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
		$sphere_origin = "Origin";
	} else {
		$sphere_origin = "Hugin";
	}
	if ($tile){
		$tile = "withTiles";
	} else {
		$tile = "withoutTiles";
	}

	$choice = "MiniAir2_".$sphere_origin."_".$tile;

	switch($choice){
		case "MiniAir2_Origin_withoutTiles":
			// For panorama obtain by share from dji gallery application
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
		
		case "MiniAir2_Origin_withTiles":
			// For panorama obtain by share from dji gallery application display mode tile (format 1/2)
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
		
		case "MiniAir2_Hugin_withoutTiles":
			// For panorama assembly by hugin and script DJI_assistant for find gimbal angle
			$array_latitude['DJI_0001.jpg']='-0.023129741203452348';
			$array_longitude['DJI_0001.jpg']='1.294224560923339';
			$array_latitude['DJI_0002.jpg']='0.2679818382924857';
			$array_longitude['DJI_0002.jpg']=$array_longitude['DJI_0001.jpg'];
			$array_latitude['DJI_0003.jpg']='-0.36455648486698133';
			$array_longitude['DJI_0003.jpg']='1.3081719154037335';
			$array_latitude['DJI_0004.jpg']='-0.9861030460664333';
			$array_longitude['DJI_0004.jpg']='1.3442387965488791';
			$array_latitude['DJI_0005.jpg']='-1.5698908146146549';
			$array_longitude['DJI_0005.jpg']='2.0008531012105912';
			$array_latitude['DJI_0006.jpg']='-0.979075097264245';
			$array_longitude['DJI_0006.jpg']='0.5268581720717227';
			$array_latitude['DJI_0007.jpg']='-0.36708593620045593';
			$array_longitude['DJI_0007.jpg']='0.5167905639184484';
			$array_latitude['DJI_0008.jpg']='0.28125475789140086';
			$array_longitude['DJI_0008.jpg']='0.5104873425627767';
			$array_latitude['DJI_0009.jpg']='0.2696912859988263';
			$array_longitude['DJI_0009.jpg']='6.063012918826523';
			$array_latitude['DJI_0010.jpg']='-0.3692607210596708';
			$array_longitude['DJI_0010.jpg']='6.039504913200643';
			$array_latitude['DJI_0011.jpg']='-0.9778826286578055';
			$array_longitude['DJI_0011.jpg']='6.038034656810654';
			$array_latitude['DJI_0012.jpg']='-1.060514737516296';
			$array_longitude['DJI_0012.jpg']='5.265785363227322';
			$array_latitude['DJI_0013.jpg']='-0.37051905114975536';
			$array_longitude['DJI_0013.jpg']='5.247316173047778';
			$array_latitude['DJI_0014.jpg']='0.19625116619238003';
			$array_longitude['DJI_0014.jpg']='5.265834181556693';
			$array_latitude['DJI_0015.jpg']='0.2649858680990891';
			$array_longitude['DJI_0015.jpg']='4.447265861687228';
			$array_latitude['DJI_0016.jpg']='-0.3714859116137259';
			$array_longitude['DJI_0016.jpg']='4.467730762155975';
			$array_latitude['DJI_0017.jpg']='-0.9694774955136607';
			$array_longitude['DJI_0017.jpg']='4.437365104146282';
			$array_latitude['DJI_0018.jpg']='-0.9886890462181959';
			$array_longitude['DJI_0018.jpg']='3.672301841259361';
			$array_latitude['DJI_0019.jpg']='-0.37344103691521635';
			$array_longitude['DJI_0019.jpg']='3.636414689935509';
			$array_latitude['DJI_0020.jpg']='0.28063744205892593';
			$array_longitude['DJI_0020.jpg']='3.6769850808489237';
			$array_latitude['DJI_0021.jpg']='0.2649042427542665';
			$array_longitude['DJI_0021.jpg']='2.8740664793193194';
			$array_latitude['DJI_0022.jpg']='-0.370796262619026';
			$array_longitude['DJI_0022.jpg']='2.884102995244064';
			$array_latitude['DJI_0023.jpg']='-1.0477200487861076';
			$array_longitude['DJI_0023.jpg']='2.7581406197077287';
			$array_latitude['DJI_0024.jpg']='-1.0237430074406175';
			$array_longitude['DJI_0024.jpg']='1.9711228062910162';
			$array_latitude['DJI_0025.jpg']='-0.3764765172308504';
			$array_longitude['DJI_0025.jpg']='2.0736756054154943';
			$array_latitude['DJI_0026.jpg']='0.24008054993753003';
			$array_longitude['DJI_0026.jpg']='2.0789165517628305';
			break;
		case "MiniAir2_Hugin_withTiles":
			// For panorama obtain by Hugin display mode tile (format 1/2)
			$array_latitude['DJI_0001.jpg']='-0.03642408475783232'; //DJI_0001.jpg
			$array_longitude['DJI_0001.jpg']='1.3029502583882626';
			$array_latitude['DJI_0002.jpg']='0.41802797449718065'; //DJI_0002.jpg
			$array_longitude['DJI_0002.jpg']='1.3012644921883374';
			$array_latitude['DJI_0003.jpg']='-0.3479333488682208'; //DJI_0003.jpg
			$array_longitude['DJI_0003.jpg']='1.316089800003878';
			$array_latitude['DJI_0004.jpg']='-1.0122914907365717'; //DJI_0004.jpg
			$array_longitude['DJI_0004.jpg']='1.3254225416811236';
			$array_latitude['DJI_0005.jpg']='-1.5528409952543627'; //DJI_0005.jpg
			$array_longitude['DJI_0005.jpg']='5.174792988803333';
			$array_latitude['DJI_0006.jpg']='-0.9158820300714949'; //DJI_0006.jpg
			$array_longitude['DJI_0006.jpg']='0.5085866871256626';
			$array_latitude['DJI_0007.jpg']='-0.39227012251587157'; //DJI_0007.jpg
			$array_longitude['DJI_0007.jpg']='0.5379117579336068';
			$array_latitude['DJI_0008.jpg']='0.11205605191817902'; //DJI_0008.jpg
			$array_longitude['DJI_0008.jpg']='0.547223225651063';
			$array_latitude['DJI_0009.jpg']='0.29130023999785726'; //DJI_0009.jpg
			$array_longitude['DJI_0009.jpg']='5.997441616566143';
			$array_latitude['DJI_0010.jpg']='-0.35412563914228246'; //DJI_0010.jpg
			$array_longitude['DJI_0010.jpg']='6.027006123573356';
			$array_latitude['DJI_0011.jpg']='-0.9563436477905904'; //DJI_0011.jpg
			$array_longitude['DJI_0011.jpg']='5.925481213338923';
			$array_latitude['DJI_0012.jpg']='-0.9586970951108129'; //DJI_0012.jpg
			$array_longitude['DJI_0012.jpg']='5.200881825955657';
			$array_latitude['DJI_0013.jpg']='-0.37269936014379734'; //DJI_0013.jpg
			$array_longitude['DJI_0013.jpg']='5.320123473989385';
			$array_latitude['DJI_0014.jpg']='0.023756441302952513'; //DJI_0014jpg
			$array_longitude['DJI_0014.jpg']='5.336914483985275';
			$array_latitude['DJI_0015.jpg']='0.01861830511308571'; //DJI_0015.jpg
			$array_longitude['DJI_0015.jpg']='4.4905119049615365';
			$array_latitude['DJI_0016.jpg']='-0.36277240945031597'; //DJI_0016.jpg
			$array_longitude['DJI_0016.jpg']='4.4494829010751875';
			$array_latitude['DJI_0017.jpg']='-0.9442873259308486'; //DJI_0017.jpg
			$array_longitude['DJI_0017.jpg']='4.410195896898944';
			$array_latitude['DJI_0018.jpg']='-0.9114097410185895'; //DJI_0018.jpg
			$array_longitude['DJI_0018.jpg']='3.581898944796255';
			$array_latitude['DJI_0019.jpg']='-0.35291866162066454'; //DJI_0019.jpg
			$array_longitude['DJI_0019.jpg']='3.5953925801180135';
			$array_latitude['DJI_0020.jpg']='0.007178088823265316'; //DJI_0020.jpg
			$array_longitude['DJI_0020.jpg']='3.773572418796416';
			$array_latitude['DJI_0021.jpg']='0.013470866484330823'; //DJI_0021.jpg
			$array_longitude['DJI_0021.jpg']='2.7940510973030603';
			$array_latitude['DJI_0022.jpg']='-0.29945408508978755'; //DJI_0022.jpg
			$array_longitude['DJI_0022.jpg']='2.834928862798277';
			$array_latitude['DJI_0023.jpg']='-0.8423018315248152'; //DJI_0023.jpg
			$array_longitude['DJI_0023.jpg']='2.6731214678313515';
			$array_latitude['DJI_0024.jpg']='-0.8635942765409199'; //DJI_0024.jpg
			$array_longitude['DJI_0024.jpg']='2.098871297596804';
			$array_latitude['DJI_0025.jpg']='-0.4000962018455878'; //DJI_0025.jpg
			$array_longitude['DJI_0025.jpg']='2.032824545338504';
			$array_latitude['DJI_0026.jpg']='0.016733696692202038'; //DJI_0026.jpg
			$array_longitude['DJI_0026.jpg']='2.0629417060069537';
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
	$jmarqueur.="console.log('".$choice."')\n";
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
		
			if(!$f || $f[0] == '.' || pathinfo($f, PATHINFO_EXTENSION )=="xml" || isPrivate($f) || isDirectoryHD($f) || pathinfo($f, PATHINFO_EXTENSION )=="php" || pathinfo($f, PATHINFO_EXTENSION )=="html" || pathinfo($f, PATHINFO_EXTENSION )=="sql") {
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