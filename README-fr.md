# ExhibMyDrone

__But__: Permettre de visualiser et d'enrichir les photos sphères prisent avec son drone, permettre de diffuser les rushs videos du drone.

Pour ce faire, les logiciels suivants sont utilisés :

- [Photo Sphères Viewer](https://photo-sphere-viewer.js.org/) de Damien Sorel pour l'affichage et le marquage de point d'intérêt;
- [Cute File Browser](https://tutorialzine.com/2014/09/cute-file-browser-jquery-ajax-php) de Nick Anastasov pour parcourir les photos;
- [TinyFileManager](https://tinyfilemanager.github.io) de CCP Programmers pour gérer les fichiers devant être presentés;
- [CkEditor](https://ckeditor.com) de CKSource pour la saisie des du texte de description des points d'interêts (marqueurs).

En option pour des sphères 4K depuis DJI mini air 2
- [Rawtherapee](https://www.rawtherapee.com/) pour le developpement des fichiers raw
- [Hugin](http://hugin.sourceforge.net/) logiciel d'assemblage de panorama


## Fonctionalités :
- Visualisation sphères comprenant :
    - Ajout suppression de sphères (gestionnaire fichiers intégré);
    - Ajout de points d'intérêts avec texte complémentaire (via simple formulaire);
    - Centrage à l'ouverture sur un point d'intérêt;
    - Sphère privée;
    - Lien pour partage direct.
- Visualisation des droonies et rushs d'un drone
    - Ajout suppression de vidéos (gestionnaire fichiers intégré).

## Principe de fonctionnement : 

Cute File Browser permet de se déplacer dans l'arborescence des photos sphères, puis lors du clique sur la tuile info de la photosphère on passe la main à Photo Sphères View qui permet alors de naviguer visuellement dans la sphère et afficher les marqueurs.  

Cute File Browser est légérement modifié, il scan les fichiers .jpg, puis insert le nom du fichier dans une base de donnée sqlite qui sera alors enrichie pour donner un titre et des infos de marqueurs.  

Photo Sphère Viewer est utilisé soit pour visualiser les sphères ainsi que les points d'intérêts soit pour créer ou mettre à jour ces derniers. Pour ce faire il recupére ou écrit les infos marqueurs dans la base de données.

TinyFileManager est utilisé pour ajouter supprimer les fichiers à presenter.

## Pré-requis :
Un hergement web supportant php  

Base de données sqlite3 pour mémoriser les infos persistantes.

La fonction de scan des fichiers sphères et vidéos est en php le reste en javascript.

## Format des fichiers :


- Sphères & panorama : 
    - Actuellement ont été testés uniquement les sphères natives obtenues en exportant depuis la galerie DJI;
    - Les images raw developpées avec [rawtherapee](https://www.rawtherapee.com/) et assemblées via [Hugin](http://hugin.sourceforge.net/). 

- Vidéos : 
    - Les rushs videos bruts du DJI ne sont pas lisibles par les navigateurs, il faut pour l'instant passer 
par un transcodage il est possible que ce soit juste une histoire d'entête mp4 l'idée serait alors de l'ajouter 
à la volée en début de fichier mais je n'ai pas encore réussi.


## Installation sur son serveur :


En théorie n'importe quel serveur web disposant du langage PHP et sa librairie sqlite3 permettent le fonctionnement natif de l'appli. 

La façon la plus simple et de télécharger le zip https://github.com/fran6t/ExhibMyDrone/archive/refs/heads/master.zip
(Il n'y a plus de sphères ni de vidéo ainsi que de bdd dans le depot)

Nota: Le respect des minuscules majuscules est important.

Une fois dezippé sur votre ordi, effectuez le transfert du répertoire ExhibMyDrone et de ses sous repertoires sur votre hebergement avec filezilla par exemple.
Le transfet terminé si vous avez fait le transfert du répertoire à la racine de votre site alors http://mondomaine.xx/ExhibMyDrone doit fonctionner
Dans le bas de l'écran en dessous des crédits vous avec le lien pour l'admin en cliquant le mot ici des sphères quand vous êtes dans les sphères et l'admin vidéos quand vous êtes dans les vidéos
Le login mot de passe en dur dans l'appli est: admin avec le mot de passe admin@123  

Il vous faudra changer le mot de passe rapidement pour cela vous avez un generateur de mot de passe dans la partie aide de tinyfilemanager vous entrez le mot de passe souhaité puis vous allez remplacer la clef obtenue dans les fichiers inc-config.php présents dans les sous-repertoires PanoDrone et VideoDrone 
Oui il y a deux fichiers inc-config.php à mettre à jour car pour l'instant PanoDrone et VideoDrone sont 100% indépendant l'un de l'autre 

Sur fond blanc il s'agit du générateur de clef et sur fond noir un editeur de texte le principe est de remplacer les clefs qui sont dans le fond noir par vos clefs obtenus avec le formulaire (fond blanc) 

![Changement mot de passe](PanoDrone/wiki/Changement-Mot-Passe.jpg "Changement mot de passe")

N'hésitez pas si je peux vous aidez trautmann@wse.fr

## Nom et structure des fichiers :

Pour les sphères, il suffit de placer les fichiers dans des repertoires et sous répertoires souhaités toutefois :

    - Les noms de repertoire finissant par .d sont reservés aux images sources des sphères du même nom en .jpg
    - Les noms de sphères possédant la chaine de caratère -p- ne seront visibles que par un lien partagé
    - Les miniatures sont générées automatiquement invisible dans l'admin mais visible par le gestionnaire tinyfilemanager ou filezilla équivalent

Exemple d'une sphère dont le nom de fichier est dji-maison.jpg placée dans un repertoire Sphere/My-Maison avec les images d'origine qui ont été assemblées nous aurons :

/Sphere/My-Maison/dji-maison.jpg

Nous créons un repertoire 
/Sphere/My-Maison/dji-maison.d

Dans lequel nous plaçons les 26 fichiers DJI_0001.jpg ce qui donne
/Sphere/My-Maison/dji-maison.d/DJI_0001.jpg
/Sphere/My-Maison/dji-maison.d/DJI_0002.jpg
...
/Sphere/My-Maison/dji-maison.d/DJI_0025.jpg
/Sphere/My-Maison/dji-maison.d/DJI_0026.jpg

Les miniatures suivantes seront créées automatiquement
/Sphere/My-Maison/dji-maison-MinX0200.jpg
/Sphere/My-Maison/dji-maison-MinX0600.jpg




## Reste à faire :
- Ajouter lien pour permettre un partage dans une frame;
- Petit doc d'explications (wiki ou readme etendu..);
- Creuser le ré-encodage des vidéos car nativement les videos DJI ne sont pas lisible par les navigateurs;
- Ajouter la possibilité d'une piste son lors de la lecture d'une vidéo;
- Ajouter un éditeur de sous titrage lors de la lecture d'une vidéo.  

## Démo :

   
[Démonstration](https://d.wse.fr/ExhibMyDrone/) Juste côté affichage l'administration est laissée protégée

## divers

__Change log__:
- 11/09/2021 Si fichiers sources de construction de la sphère présent alors ajout de marqueur permettant de consulter jpg origine
- 09/09/2021 Si un marqueur est defini centrer, la sphère s'ouvre alors sur celui-ci
- 01/08/2021 Les fichiers avec la chaine de caractère -p- dans leur nom sont invisibles côté FrontEnd sauf appel via lien direct
- 31/08/2021 Déplacement cd ckeditor dans PanoDrone pour independance accrue de PanoDrone versus VideoDrone
- 29/08/2021 Mise en place d'un mini gestionnaire de paramètres côté sphères
- 26/08/2021 Passage de sqlite natif php vers pdo pour accepter aussi mysql, gestion version php si php est < 5.5 alors pas de tinyfilemanager il faut placer ses fichiers via filezilla ou équivalent et appeler l'administration manuellement avec un parametre (voir fichier inc-config.php)
- 20/08/2021 Ajout miniature et lien partage copiable depuis le formulaire gestion sphère
- 19/08/2021 Ajout ckeditor pour saisir les descriptions des marqueurs
- 18/08/2021 Ajout double clique pour quitter une sphère
- 15/08/2021 Changement nom du projet
- 12/08/2021 Fusion de deux projets pour faire un portail de présentation de ses prises de vues de drone

__Captures écrans__:

Ici deux marqueurs, 1 sur le bâtiment et 1 sur camping car
![2 marqueurs visibles](PanoDrone/wiki/Exemple-Marqueur.jpg "Exemple de marqueurs")


Volet détail sur le marqueur ouvert
![Volet détail](PanoDrone/wiki/Volet-Marqueur-Ouvert.jpg "Volet des détails du marqueur ouvert")


Bar de navigation dans la sphère et vers marqueurs en bas et à droite
![Selection des marqueurs](PanoDrone/wiki/Volet-Selection-Marqueurs.jpg "Bar et Volet de selection des marqueurs")


Formulaire de saisie des marqueurs
![Formulaire de saisie des marqueurs](PanoDrone/wiki/Formulaire-Saisie-Infos-Spheres.jpg "Formulaire de saisie des marqueurs")


Gestionnaire des sphères
![Gestionnaire des sphères](PanoDrone/wiki/Gestionnaire-des-spheres.jpg "Gestionnaire des sphères")


Gestionnaire des fichiers
![Gestionnaire des fichiers](PanoDrone/wiki/Gestionnaire-Fichiers.jpg "Gestionnaire des fichiers")
