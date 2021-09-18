<?php
$frontend = true;				// By default we considere all file, if $frontend = true then private file are not show 
								// If script php need accepte private file  set $frontend = false, for example in  gest.php

if ($bddtype=='mysql'){
	$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=$port";
	try {
		$pdo = new PDO($dsn, $user, $pass);
   	} catch (Exception $e) {
		echo "Access database fail : ".$e->getMessage();
		die();
   	} 
} 
if ($bddtype=='sqlite'){
	$dsn = "sqlite:".dirname(__FILE__).'/'.$db;
	try{
		$pdo = new PDO($dsn);
		$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // ERRMODE_WARNING | ERRMODE_EXCEPTION | ERRMODE_SILENT
	} catch(Exception $e) {
		echo "Access SQLite fail : ".$e->getMessage();
		die();
	}
	
}

// Uncoment the next 4 lines = reset table of database
/*
$SqlString = "drop table if exists lespanos"; 
$pdo->exec($SqlString);

$SqlString = "drop table if exists lespanos_details"; 
$pdo->exec($SqlString);
*/

if (!DB_table_exists('lespanos')){
	$SqlString = "CREATE TABLE 'lespanos' (
		'fichier' VARCHAR(500)  NULL,
		'titre' VARCHAR(500)  NULL,
		'legende' TEXT  NULL,
		'hashfic' VARCHAR(100)  NULL
	);";
	$pdo->exec($SqlString);
	$SqlString = "CREATE INDEX 'IDX_lespanos_fichier' ON 'lespanos' ('fichier')";
	$pdo->exec($SqlString);
}

if (!DB_table_exists('lespanos_details')){
    $SqlString = "CREATE TABLE [lespanos_details] (
        [fichier] VARCHAR(500)  NULL,
        [hashfic] VARCHAR(100)  NULL,
        [nom_marqueur] VARCHAR(100)  NULL,
        [couleur] VARCHAR(10)  NULL,
        [latitude] VARCHAR(20)  NULL,
        [longitude] VARCHAR(20)  NULL,
        [descri] TEXT  NULL
        );";
    $pdo->exec($SqlString);
    $SqlString = "CREATE INDEX [IDX_lespanos_DETAILS_hashfic] ON [lespanos_details] ([hashfic])";
    $pdo->exec($SqlString);
}

// If Col named short_code not exist then add to the table
if (!DB_column_exists('lespanos','short_code')){
	$SqlString ="ALTER TABLE [lespanos] ADD COLUMN [short_code] VARCHAR(25)";
	$pdo->exec($SqlString);
	$SqlString = "CREATE INDEX [IDX_lespanos_short_code] ON [lespanos]([short_code]  ASC);";
    $pdo->exec($SqlString);
}

// If Col named marker_center not exist then add to the table
if (!DB_column_exists('lespanos_details','marker_center')){
	$SqlString ="ALTER TABLE [lespanos_details] ADD COLUMN [marker_center] VARCHAR(1)";
	$pdo->exec($SqlString);
}

// Si la column existe pas on l'ajoute à la table
// Ce champs vaudra Standard,URL ou IMG
//if (!DB_column_exists('lespanos_details','marker_type')){
//	$SqlString ="ALTER TABLE [lespanos_details] ADD COLUMN [marker_type] VARCHAR(8)";
//	$pdo->exec($SqlString);
//}


// End traitment

// List function php used in all script include inc-lib.php order by aphabetic name

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


function DB_table_exists($table){
    GLOBAL $pdo;
    try{
        $pdo->query("SELECT 1 FROM $table");
    } catch (PDOException $e){
        return false;
    }
    return true;
}

function display_Frontend_Error($quelError){
	GLOBAL $t;
	echo "*** ".$t->display("Error")." ***";
	echo "<br />".$t->display($quelError);
	echo "<br /><a href=\".\">".$t->display("Go home sphere")."</a>";
}

function imageResize($quelfic,$after_width){
	// Example thumbnail name for "my-picture.jpg" create  "my-picture-MinX0200.jpg"
	$compl_img = "-MinX0".$after_width.".jpg";
	if( class_exists("Imagick") ){
		// We create 2 thumbnail for blog or seo
		// First size 200px thumb, second 600px medium
		// Thumbnail named with x200x or x600x are ignored by function scan()
		$image = new Imagick($quelfic);
		$image->thumbnailImage($after_width, 0, false);
		$image->writeImage($quelfic.$compl_img);
		return;
	}
	if (version_compare(phpversion(), '5.5.0', '>=')) {     // Must be > 5.5 because use imagescale

    	$img = imagecreatefromjpeg($quelfic);

    	//Let's do the resize thing
		//imagescale([returned image], [width of the resized image], [height of the resized image], [quality of the resized image]);
		$imgResized = imagescale($img, $after_width, -1);
 
		//now save the resized image with a suffix called "-resized" and with its extension. 
		imagejpeg($imgResized, $quelfic.$compl_img);
  
		//Finally frees any memory associated with image
		//**NOTE THAT THIS WONT DELETE THE IMAGE
  		imagedestroy($img);
		imagedestroy($imgResized);
		return;
	}
	//Resize with native function of gd lib
	createThumb($quelfic, $quelfic.$compl_img, $after_width);	
}



// Test if directory name ending by .d
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

// Test if $aTester contain string "MinX"
function isMiniature($aTester){
	// Quand la fonction retourne true c'est que c'est une miniature	
	// If string "MinX" then we retuen true it's a thumbnail and is ignored by function scan()
	$pos = strpos($aTester,"MinX");
	if ($pos === false){
		return false;
	} else {
		return true;
	}
}

$frontend = false;  // Only script php name scan.php need $fronted = true to ignore file with string "-p-" because it's a private file on link direct can show private file

function isPrivate($f){
	// When true is returned it's a private file
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

// Function create marker to access high resolution file used for stitch panorama
// 26 icon + represent link to .jpg showing on the sphere
// Used in pano.php
function listimg($nom_img){
	$array_latitude['DJI_0001.jpg']='-0.09252984397812103';
	$array_longitude['DJI_0001.jpg']='5.555662773914915';
	$array_latitude['DJI_0002.jpg']='0.232558003708351';
	$array_longitude['DJI_0002.jpg']='5.554250459465138';
	$array_latitude['DJI_0003.jpg']='-0.4626724435377434';
	$array_longitude['DJI_0003.jpg']='5.567678392009997';
	$array_latitude['DJI_0004.jpg']='-0.962383806250426';
	$array_longitude['DJI_0004.jpg']='5.637974232515125';
	$array_latitude['DJI_0005.jpg']='-1.5536787971303037';
	$array_longitude['DJI_0005.jpg']='2.692606580364135';
	$array_latitude['DJI_0006.jpg']='-0.9781904794075316';
	$array_longitude['DJI_0006.jpg']='4.783367293051897';
	$array_latitude['DJI_0007.jpg']='-0.38867924822037425';
	$array_longitude['DJI_0007.jpg']='4.838971582862978';
	$array_latitude['DJI_0008.jpg']='0.14407080030219688';
	$array_longitude['DJI_0008.jpg']='4.747493272911039';
	$array_latitude['DJI_0009.jpg']='0.21079314150783213';
	$array_longitude['DJI_0009.jpg']='3.931816379538116';
	$array_latitude['DJI_0010.jpg']='-0.34722532850718';
	$array_longitude['DJI_0010.jpg']='4.018244572905599';
	$array_latitude['DJI_0011.jpg']='-1.015882144445956';
	$array_longitude['DJI_0011.jpg']='4.027805712412321';
	$array_latitude['DJI_0012.jpg']='-0.9373559905048263';
	$array_longitude['DJI_0012.jpg']='3.1564785404179676';
	$array_latitude['DJI_0013.jpg']='-0.33625680993064444';
	$array_longitude['DJI_0013.jpg']='3.2676951112933614';
	$array_latitude['DJI_0014.jpg']='0.13549596190930968';
	$array_longitude['DJI_0014.jpg']='3.2700915614427246';
	$array_latitude['DJI_0015.jpg']='0.11561547636859704';
	$array_longitude['DJI_0015.jpg']='2.4295950354945464';
	$array_latitude['DJI_0016.jpg']='-0.31342876318014423';
	$array_longitude['DJI_0016.jpg']='2.36760514237171';
	$array_latitude['DJI_0017.jpg']='-0.9530711639848573';
	$array_longitude['DJI_0017.jpg']='2.4236265274907773';
	$array_latitude['DJI_0018.jpg']='-0.8834796416291382';
	$array_longitude['DJI_0018.jpg']='1.617121461494011';
	$array_latitude['DJI_0019.jpg']='-0.3329297927757393';
	$array_longitude['DJI_0019.jpg']='1.6213608783442042';
	$array_latitude['DJI_0020.jpg']='0.13141430090940664';
	$array_longitude['DJI_0020.jpg']='1.1587510462647024';
	$array_latitude['DJI_0021.jpg']='0.15167681027720192';
	$array_longitude['DJI_0021.jpg']='0.8037879174658019';
	$array_latitude['DJI_0022.jpg']='-0.33269261920485826';
	$array_longitude['DJI_0022.jpg']='0.7736362471043422';
	$array_latitude['DJI_0023.jpg']='-0.9143608483005834';
	$array_longitude['DJI_0023.jpg']='0.9110400685003149';
	$array_latitude['DJI_0024.jpg']='-0.7975382334172547';
	$array_longitude['DJI_0024.jpg']='0.08457568661042872';
	$array_latitude['DJI_0025.jpg']='-0.3828550804822781';
	$array_longitude['DJI_0025.jpg']='0.06140379425587219';
	$array_latitude['DJI_0026.jpg']='0.17567919428837753';
	$array_longitude['DJI_0026.jpg']='0.17567919428837753';
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
	return $jmarqueur;
}


// At start this function is in script scan.php but we need in scan.php and gest.php
// This function scans the files folder recursively, and builds a large array
function scan($dir){
	global $pdo;
	$files = array();

	// Is there actually such a folder/file?

	if(file_exists($dir)){
	
		foreach(scandir($dir) as $f) {
		
			if(!$f || $f[0] == '.' || pathinfo($f, PATHINFO_EXTENSION )=="xml" || isMiniature($f) || isPrivate($f) || isDirectoryHD($f)) {
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