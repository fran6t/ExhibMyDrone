# ExhibMyDrone
Visualisation sphères et placement de points d'intérets
Visualisation des droonies et rushs d'un drone

__But__: Permettre de visualiser et d'enrichir les photos sphères prisent avec son drone, permettre de diffuser les rushs videos du drone

Pour ce faire, trois logiciels sont utilisés :

- [Photo Sphères Viewer](https://photo-sphere-viewer.js.org/) de Damien Sorel pour l'affichage et le marquage de point d'intérêt
- [Cute File Browser](https://tutorialzine.com/2014/09/cute-file-browser-jquery-ajax-php) de Nick Anastasov pour parcourir les photos
- [TinyFileManager](https://tinyfilemanager.github.io) de CCP Programmers pour gérer les fichiers devant être presentés

## Principe de fonctionnement : 

Cute File Browser permet de se déplacer dans l'arborescence des photos sphères, puis lors du clique sur la tuile info de la photosphère on passe la main à Photo Sphères View qui permet alors de naviguer visuellement dans la sphère et afficher les marqueurs.  

Cute File Browser est légérement modifié, il scan les fichiers .jpg, puis insert le nom du fichier dans une base de donnée sqlite qui sera alors enrichie pour donner un titre et des infos de marqueurs.  

Photo Sphère Viewer est utilisé soit pour visualiser les sphères ainsi que les points d'intérêts soit pour créer ou mettre à jour ces derniers. Pour ce faire il recupére ou écrit les infos marqueurs dans la base de données.

TinyFileManager est utilisé pour ajouter supprimer les fichiers à presenter.

## Pré-requis :
Un hergement web supportant php  

Base de données sqlite3 pour mémoriser les infos persistantes.

La fonction de scan des fichiers sphères et vidéos est en php le reste en javascript.

__Format des fichiers__


- Sphères & panorama actuellement ont été testé uniquement les sphères natives obtenues en exportant depuis la galerie DJI. 
Le DJI mini 2 produit un jpg directement exploitable.
- Vidéos les rushs videos bruts du DJI ne sont pas lisibles par les navigateurs, il faut pour l'instant passer 
par un transcodage il est possible que ce soit juste une histoire d'entête mp4 l'idée serait alors de l'ajouter 
à la volée en début de fichier mais je n'ai pas encore réussi.


__Installation sur son serveur__
En théorie n'importe quel serveur web disposant du langage PHP et sa librairie sqlite3 permettent le fonctionnement natif de l'appli. 

La façon la plus simple et de télécharger le zip https://github.com/fran6t/ExhibMyDrone/archive/refs/heads/master.zip
(Attention il est gros car il y a plusieurs sphères et 2 vidéos 155Mo au moment ou j'ecris cela)

Attention le respect des minuscules majuscules est important
Une fois dezippé sur votre ordi, effectuez le transfert du répertoire ExhibMyDrone et de ses sous repertoires sur votre hebergement avec filezilla par exemple.
Le transfet terminé si vous avez fait le transfert du répertoire à la racine de votre site alors http://mondomaine.xx/ExhibMyDrone doit fonctionner
Dans le bas de l'écran en dessous des crédits vous avec le lien pour l'admin en cliquant le mot ici des sphères quand vous êtes dans les sphères et l'admin vidéos quand vous êtes dans les vidéos
Le login mot de passe en dur dans l'appli est: admin avec le mot de passe admin@123  
Il vous faudra changer le mot de passe rapidement pour cela vous avez un generateur de mot de passe dans la partie aide de tinyfilemanager vous entrez le mot de passe souhaité puis vous allez remplacer la clef obtenu dans les fichiers inc-config.php présents dans les sous-repertoires PanoDrone et VideoDrone 
Oui il y a deux fichiers inc-config.php à mettre à jour car pour l'instant PanoDrone et VideoDrone sont 100% indépendant l'un de l'autre 

__Reste à faire__:

- Petit doc d'explications (wiki ou readme etendu..)
- Ajouter un editeur html light aux descriptions des marqueurs
- Creuser le ré-encodage des vidéos car nativement les videos DJI ne sont pas lisible par les navigateurs
- Ajouter la possibilité d'une piste son lors de la lecture d'une vidéo
- Ajouter un éditeur de sous titrage lors de la lecture d'une vidéo  

## Démo ##
[Démonstration](http://www.wse.fr/ExhibMyDrone/) Juste côté affichage l'administration est laissée protégée

__Change log__:
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
