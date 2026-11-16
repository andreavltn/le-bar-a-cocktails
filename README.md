# Le Bar à Cocktails

Application web interactive de découverte, de recherche et de gestion de recettes de cocktails.

## Accès et versions
* **Version en ligne :** le-bar-a-cocktails.freedev.app
* **Version sur ce dépôt :** Version locale du projet

## Fonctionnalités principales
* **Catalogue de recettes :** Consultation de plus de 100 recettes avec affichage des ingrédients clés et fiches détaillées.
* **Recherche multicritères :** Filtrage par sélection d'ingrédients avec un système de score en pourcentage (ingrédients requis, optionnels ou interdits).
* **Recherche hiérarchique :** Navigation par arborescence d'aliments et de sous-ingrédients.
* **Gestion des favoris :** Ajout et retrait en temps réel via une mise à jour asynchrone.
* **Espace utilisateur :** Inscription, connexion, gestion de session et persistance des favoris.

## Stack technique
* **Serveur :** XAMPP / PHP 8.2.12 / Apache 2.0
* **Base de données :** MariaDB 10.4.32 (via script d'installation automatique)
* **Frontend :** HTML5, CSS3 et JavaScript natif

## Installation et lancement en local
1. Placer les fichiers dans le répertoire racine du serveur local (ex : `htdocs` sous XAMPP).
2. Lancer les services Apache et MariaDB.
3. Ouvrir l'URL du projet dans le navigateur : le fichier de connexion initialise et remplit automatiquement la base de données si elle n'existe pas.
