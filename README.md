# Barcelona Tourist Guide

Version actuelle du site vitrine multilingue pour une guide touristique a Barcelone, avec interface d'administration.

## Modifications de cette version

Cette version a ete realisee a partir de l'etat initial du projet fourni avant intervention.

### Suppression complete des monuments

La fonctionnalite monuments a ete retiree de l'ensemble du code du projet :

- suppression de la section publique dans les deux entrees du site ;
- suppression du bouton monuments dans la navigation ;
- suppression des traductions et des appels API associes ;
- suppression des composants publics `MonumentCard` et `MonumentDetailModal` ;
- suppression du formulaire monuments de l'administration ;
- suppression des fichiers de donnees monuments publics et admin ;
- suppression des routes PHP `readmonuments`, `adminmonuments` et `updatemonument` ;
- suppression des controleurs et fonctions SQL monuments.

Aucune reference aux monuments ne reste dans les fichiers du workspace.

La table SQL `BT_Monument` n'a pas ete supprimee et aucune modification de base de donnees n'a ete effectuee.

Le dossier `Images/`, la galerie d'images et la gestion des images des tours ont ete conserves, car ces elements sont partages avec les tours.

### Gestion des tours dans l'administration

La gestion des tours permet maintenant de controler strictement :

- le type du tour : groupe prive, petit groupe ou groupe scolaire/universitaire ;
- le nombre de personnes pour chaque tour ;
- la modification independante du type et de la capacite de chaque tour.

Ces controles fonctionnent lors de l'ajout comme lors de la modification d'un tour.

Le serveur refuse desormais :

- une capacite vide ;
- une capacite nulle ou negative ;
- une capacite non entiere ;
- un type de groupe non autorise.

La capacite est envoyee par l'interface sous le nom `capacity`. Le modele de donnees conserve actuellement cette valeur dans l'ancienne colonne SQL `price`.

### Ajout du `.gitignore`

Un fichier `.gitignore` a ete ajoute a la racine pour exclure notamment :

- les fichiers systeme macOS ;
- les fichiers d'environnement et secrets locaux ;
- les dependances `node_modules/` et `vendor/` ;
- les configurations d'editeurs ;
- les logs, fichiers temporaires et caches.

Le dossier `Images/` reste suivi afin de ne pas exclure les medias du site.

## Verifications effectuees

- recherche globale sans reference restante aux monuments ;
- verification syntaxique des fichiers PHP ;
- verification syntaxique des scripts JavaScript ;
- verification des diagnostics VS Code sur les fichiers modifies.

Les tests navigateur complets et les tests avec une base de donnees active n'ont pas ete executes dans cet environnement.
