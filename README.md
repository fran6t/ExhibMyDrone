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

__Reste à faire__:
Améliorer la création d'un marqueur  

## Démo ##
[Démonstration](http://www.wse.fr/ExhibMyDrone/) Juste côté affichage l'administration est laissée protégée

__Change log__:
- 15/08/2021 Changement nom du projet
- 12/08/2021 Fusion de deux projets pour faire un portail de présentation de ses prises de vues de drone
