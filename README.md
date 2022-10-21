# ExhibMyDrone

__Goal__: Make it possible to visualize and enrich the spherical photos taken with his drone, to broadcast the drone's video rushes.

To do this, the following software is used :

- [Photo Sphères Viewer](https://photo-sphere-viewer.js.org/) by Damien Sorel for displaying and marking points of interest;
- [Cute File Browser](https://tutorialzine.com/2014/09/cute-file-browser-jquery-ajax-php) by Nick Anastasov to browse photo and video directories;
- [TinyFileManager](https://tinyfilemanager.github.io) by CCP Programmers o manage the files to be presented;
- [CkEditor](https://ckeditor.com) by CKSource for entering the description text of points of interest (markers).

Useful software to make 4K spheres from DJI mini air 2 :
- [Rawtherapee](https://www.rawtherapee.com/) for the development of raw files
- [Hugin](http://hugin.sourceforge.net/) panorama stitching software


## Features :
- Spheres visualization including :
    - Display source images of the sphere if they are presents;
    - Addition deletion of spheres (integrated file manager);
    - Addition of points of interest with additional text (via simple form);
    - Centering when opening on a point of interest;
    - Private sphere;
    - Link for direct sharing;
    - Import export spheres.
- Viewing droonies and rushes from a drone :
    - Added deletion of videos (integrated file manager).

## Principle of operation : 

Cute File Browser allows you to move in the tree of spheres photos, then when you click on the info tile of the photosphere, you switch the hand to Photo Spheres Viewer

Photo Spheres Viewer then allows you to visually navigate in the sphere and display the markers (POI).

Cute File Browser is slightly modified, it scans .jpg files, then inserts the name of the file in a sqlite database which will then be enriched to give a title and marker info.

TinyFileManager is used to add delete files to present.

## License

No idea of ​​a license, so I chose one that seems to me to respect everyone's work, personally everything I have done myself is totally free to use except the shots
All Parts in this repository are licensed under CC-BY 3.0 http://creativecommons.org/licenses/by/3.0/
Each Part is copyrighted by and should be attributed to its respective author(s).
See commit details to find the authors of each Part.

If you are uploading parts to this repository, please make sure you are the author of the model,
or otherwise that you have right to share it here under the CC-BY 3.0 license, and make sure the author
is mentioned in the commit message.

## Prerequisites :
A web hosting supporting php  

Sqlite3 database to store persistent info.

The function of scanning spheres and videos files is in php the rest in javascript.

## File format :


- Spheres & panorama : 
    - Currently, only the native spheres obtained by exporting from the DJI gallery, and those assembled with the Hugin software have been tested;
    -Raw images developed with [rawtherapee](https://www.rawtherapee.com/) and assembled with [Hugin](http://hugin.sourceforge.net/). 

- Vidéos : 
    - The raw DJI video rushes are not readable by browsers, it is necessary for the moment to pass
by a transcoding it is possible that it is just a story of mp4 header the idea would be to add it
on the fly at the beginning of the file but I haven't succeeded yet.


## Installation on its server :


In theory, any web server using the PHP language and its sqlite3 library allows the native operation of the app. 

The easiest way and download the zip https://github.com/fran6t/ExhibMyDrone/archive/refs/heads/master.zip

Note: Respect for lower case letters is important.

Once unzipped on your computer, transfer the ExhibMyDrone directory and its sub directories to your accommodation with filezilla for example.
The transfer is complete if you have transferred the directory to the root of your site then http: //myomaine.xx/ExhibMyDrone must work
At the bottom of the screen below the credits you will find the link for the admin

At the first launch you will have to change the manual access key and the password, you must be redirected to a form

See wiki for more information  https://github.com/fran6t/ExhibMyDrone/wiki/Installation-or-upgrade

Do not hesitate to contact me if I can help you it is with pleasure trautmann@wse.fr

## File name and structure :

For the spheres, it suffices to place the files in the desired directories and subdirectories, however :

    - Directory names ending in .d are reserved for source images of spheres of the same name in .jpg
    - The names of spheres with the character string -p- will only be visible by a shared link
    - Thumbnails are generated automatically invisible in the admin but visible by the manager tinyfilemanager or equivalent filezilla

Example of a sphere whose file name is dji-maison.jpg placed in a Sphere / My-Maison directory with the original images that have been assembled we will have:

/Sphere/My-Maison/dji-maison.jpg

We create a directory 
/Sphere/My-Maison/dji-maison.d  (this directory mus contain all files of a sphere)

The following thumbnails will be created automatically in directory .d
/Sphere/My-Maison/dji-maison.d/dji-maison-MinX0200.jpg
/Sphere/My-Maison/dji-maison.d/dji-maison-MinX0600.jpg

We create a sub-directory named src to place the 26 DJI_0001.jpg files which gives
/Sphere/My-Maison/dji-maison.d/src/DJI_0001.jpg
/Sphere/My-Maison/dji-maison.d/src/DJI_0002.jpg
...
/Sphere/My-Maison/dji-maison.d/src/DJI_0025.jpg
/Sphere/My-Maison/dji-maison.d/src/DJI_0026.jpg

Optional
We create a sub-directory named tiles to place the 128 tiles which gives
/Sphere/My-Maison/dji-maison.d/tiles/tile_0000.jpg
/Sphere/My-Maison/dji-maison.d/tiles/tile_0001.jpg

Optional
We create a sub-directory named thmb to place the thumbnails
/Sphere/My-Maison/dji-maison.d/thmb/DJI_0000.jpg
/Sphere/My-Maison/dji-maison.d/thmb/DJI_0001.jpg


## Sphere with tiles

The principle is to build a very low resolution image of 2000x1000 pixels which is the fast representation of the sphere.
This image will then be covered by 128 tiles, these correspond to the cutting of the high resolution image of the assembly either of the original photos or of the photos in raw format

The advantage is to be able to present a high or very high resolution sphere in a very progressive way. Only the tiles visible on the screen are downloaded by the internet browser.

We therefore have two possibilities for publishing a sphere (if you have not taken raw there is only one)

In the case of shooting with raw, we therefore have 26 files with a resolution of 4000x3000 pixels (4K) and 26 .jpg files with 2000x1500 pixels.

1st case taken from the sphere without the raw mode:

    - you will assemble 26 images of 2000x1500 pixels with hugin and obtain a sphere of around 8500x3000 pixels which will therefore be cut into 128 tiles of 528x528 pixels

2nd case taken from the sphere with the raw mode:

    - you will assemble 26 images of 4000x3000 pixels with hugin and obtain a sphere of around 17000x6000 pixels which will be cut into 128 tiles of 1024x1024 pixels


Three raw scripts (no tests only a sequence of commands) are available to build the tree structure:

    - mini_dji.sh

    - minix8000.sh

    - minix17000.sh



## Todo list :
- Simplify the installation by abandoning the compatibilities with the old versions
- Move pano.db in the tree structure of the spheres so the backup will include all "live data"
- Update side video for use parameter.php
- Add link to allow sharing in a frame;
- Small explanatory documentation (wiki or extended readme ..);
- Study the re-encoding of videos because native DJI videos are not readable by browsers;
- Add the possibility of a sound track when playing a video;
- Add subtitles when playing a video.  

## Démo :

   
[Démonstration](https://d.wse.fr/ExhibMyDrone/) only the frontend

## various

__Change log__:
- 17/10/2022 Add import export sphere
- 03/09/2021 Add button in navbar to show long legend of sphere in panel
- 02/09/2021 Update view original jpg if thumbnails exists
- 28/09/2021 For big sphere add possibility loading with mode tile, now directory name-of-sphere.d contain directory src and tiles and thumbnail
- 24/09/2021 Update Longitude Lattitude Poi when panorama is assembled by Hugin or obtain with function "Share Dji app Album"
- 19/09/2021 Correction of translation, and bug in search, addition of index.html when calling gest-form.php to prevent browsing of spheres directories
- 16/09/2021 Start of implementation of multi-lingual version (For the moment only Readme.md README-fr.md, index.php, parametre.php)
- 11/09/2021 If the sphere construction source files are present then addition of respective markers allowing to consult the original .jpg
- 09/09/2021 If a marker is defined to be centered, the sphere then opens on it
- 01/08/2021 The files with the character string -p- in their name are invisible on the FrontEnd side except call via direct link
- 31/08/2021 Relocation of ckeditor in PanoDrone for increased independence of PanoDrone versus VideoDrone
- 29/08/2021 Implementation of a mini parameter manager on the spheres side
- 26/08/2021 PSwitching from native sqlite php to pdo to also accept mysql, php version management if php is <5.5 then no tinyfilemanager you have to place your files via filezilla or equivalent and call the administration manually with a parameter (see inc-config.php file )
- 20/08/2021 Add miniature and copyable sharing link from the sphere management form
- 19/08/2021 Added ckeditor to enter descriptions of markers
- 18/08/2021 Add double click to leave a sphere
- 15/08/2021 Project name change
- 12/08/2021 Merger of two projects (panodrone and video drone) to make a presentation portal for his drone shots

__Screenshots__:

Here two markers, 1 on the building and 1 on the motorhome
![2 marqueurs visibles](PanoDrone/wiki/Exemple-Marqueur.jpg "Exemple de marqueurs")


Detail pane on the open marker
![Volet détail](PanoDrone/wiki/Volet-Marqueur-Ouvert.jpg "Volet des détails du marqueur ouvert")


Navigation bar in the sphere and towards markers at the bottom and on the right
![Selection des marqueurs](PanoDrone/wiki/Volet-Selection-Marqueurs.jpg "Bar et Volet de selection des marqueurs")


Marker entry form
![Formulaire de saisie des marqueurs](PanoDrone/wiki/Formulaire-Saisie-Infos-Spheres.jpg "Formulaire de saisie des marqueurs")


Spheres manager
![Gestionnaire des sphères](PanoDrone/wiki/Gestionnaire-des-spheres.jpg "Gestionnaire des sphères")


File manager
![Gestionnaire des fichiers](PanoDrone/wiki/Gestionnaire-Fichiers.jpg "Gestionnaire des fichiers")
