# Gestion d'Événements Sportifs

Une application web moderne pour la gestion et l'organisation d'événements sportifs, offrant une expérience utilisateur fluide et intuitive.

## 🚀 Fonctionnalités

- Gestion complète des événements sportifs
- Système d'inscription aux événements
- Système de commentaires interactif
- Gestion des utilisateurs et authentification
- Interface moderne et responsive
- Tableau de bord administrateur

## 🛠 Technologies Utilisées

- PHP 8.2.4
- MariaDB 10.4.28
- Composer pour la gestion des dépendances
- AltoRouter pour le routing
- Twig 3.18 pour le templating
- Bootstrap pour l'interface utilisateur

## 📋 Prérequis

- PHP >= 8.2
- MySQL/MariaDB
- XAMPP ou environnement similaire
- Composer

## 🔧 Installation

1. Clonez le dépôt :
```bash
git clone [https://github.com/7Sonny/Gestion-v-nement-Sportif.git]
cd sporteventultimate
```

2. Installez les dépendances :
```bash
composer install
```

3. Configuration de la base de données :
- Créez une base de données nommée 'sportevent'
- Importez le fichier `database/base.sql`
- Vérifiez que les paramètres de connexion sont corrects

## 🎨 Design et Interface

L'application utilise un design moderne avec :
- Sections principales avec fond coloré et coins arrondis (bg-primary, rounded-4)
- Cartes interactives avec effets de survol élégants
- Boutons stylisés (rounded-pill) et formulaires modernes
- Transitions fluides (0.3s ease) pour une meilleure expérience utilisateur
- Design entièrement responsive

## 🔄 Architecture MVC

```
sporteventultimate/
├── controllers/     # Contrôleurs de l'application
├── models/         # Modèles de données
├── views/          # Templates Twig
├── database/       # Scripts SQL et configuration
├── middlewares/    # Middlewares d'authentification
├── public/         # Assets publics (CSS, JS, images)
└── vendor/         # Dépendances Composer
```

## 👥 Contribution

Pour contribuer au projet :
1. Forkez le projet
2. Créez une nouvelle branche (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements
4. Poussez vers la branche
5. Ouvrez une Pull Request
