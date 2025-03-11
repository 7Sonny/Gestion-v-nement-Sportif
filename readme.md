



LightMVC - Framework PHP Minimaliste

LightMVC est une base de code simple pour développer des applications web en PHP en suivant l'architecture MVC (Modèle-Vue-Contrôleur). Ce projet fournit une structure minimale pour organiser votre code et démarrer rapidement un projet.

Prérequis

PHP 8.2 ou supérieur

Serveur web Apache avec le module mod_rewrite activé

Composer (optionnel pour la gestion des dépendances)

Installation

Clonez ce dépôt :

git clone https://github.com/votre-utilisateur/lightmvc.git

Placez le projet dans votre serveur web.

Assurez-vous que le fichier .htaccess est correctement configuré.

Accédez au projet via votre navigateur.

Configuration du fichier .htaccess

Créez un fichier .htaccess à la racine du projet et ajoutez le contenu suivant :

RewriteEngine On
RewriteBase /lightmvc/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]

Si votre projet est placé à la racine du serveur, remplacez /lightmvc/ par /.

Structure du projet

/lightmvc/
│-- app/
│   │-- controllers/
│   │-- models/
│   │-- views/
│-- public/
│   │-- css/
│   │-- js/
│-- index.php
│-- .htaccess
│-- README.md

app/ : Contient le code MVC (contrôleurs, modèles et vues)

public/ : Contient les ressources statiques (CSS, JS)

index.php : Point d'entrée principal de l'application

.htaccess : Configuration des URL pour Apache

Démarrage

Démarrez votre serveur Apache.

Accédez à http://localhost/lightmvc/ dans votre navigateur.

Licence

Ce projet est sous licence MIT. Vous êtes libre de l'utiliser et de le modifier selon vos besoins.


