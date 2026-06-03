# Barcelona Tourist Guide 🌍

## 📖 Présentation du Projet

Ce projet est un site vitrine multilingue conçu pour une guide touristique basée à Barcelone.

**Contexte :** Je m'appelle Yann, j'ai 18 ans et je suis actuellement étudiant en **BUT MMI** (Métiers du Multimédia et de l'Internet) à l'IUT du Limousin. J'ai réalisé ce projet de A à Z pour aider ma tante, qui vit en Espagne, à digitaliser son activité de guide et à proposer ses visites à une clientèle internationale.

---

## 🛠️ Stack Technique

L'objectif de ce projet était de construire une architecture robuste et performante sans utiliser de frameworks lourds, afin de maîtriser les fondamentaux du web. J'ai utilisé tout ce que j'ai appris en BUT MMI cette année ainsi que des connaissances que j'ai développé par moi même.

### Frontend

- **HTML5 & CSS3** : Utilisation de la méthodologie **BEM** (Block Element Modifier) pour un code propre, Flexbox/Grid pour le layout, et variables CSS pour la gestion du thème.
- **JavaScript (ES6+)** : Architecture modulaire. Utilisation intensive de `async/await` et de l'API `fetch`.
- **Système de Templating Custom** : Séparation de la structure HTML et de la logique JS via un chargement dynamique de fichiers `.html` pour chaque composant.

### Backend

- **PHP 8** : Architecture respectant le pattern **MVC** (Modèle-Vue-Contrôleur) pour séparer les données de la logique métier.
- **MySQL** : Base de données relationnelle pour stocker les visites (tours), les monuments et les messages de contact.
- **Sécurité** : Requêtes préparées (**PDO**) contre les injections SQL et protection contre les failles **XSS** via un échappement systématique des données dynamiques.

---

## ✨ Fonctionnalités clés

### 🏁 Site Public

- **Hébergement** : Actuellement sur https://leflohic-sae105.mmi-limoges.fr/portfolio en tant qu'alias mais après passera en hébergement personnel grâce à un abonnement chez O2Swicth.
- **Multilingue complet** : Support de l'Anglais, Français, Espagnol et Italien.
- **Expérience Utilisateur (UX)** : Thème sombre/clair (persisté via `localStorage`), animations fluides et design responsive (mobile-first).
- **Gestion des Visites** : Affichage dynamique des tours et monuments avec modales de détails.
- **Formulaire de Contact** : Envoi de messages avec validation côté client et serveur.

### 🔐 Panneau d'Administration

- **Gestion de contenu** : Ajout, modification et suppression des tours de manière indépendante pour chaque langue.
- **Lecture des messages** : Interface dédiée pour consulter les demandes de réservation des clients.
- **Sécurisation** : Accès protégé par mot de passe et gestion de session via `sessionStorage`.

---

## 🏗️ Architecture du code

Le projet suit une structure organisée pour faciliter la maintenance :

```text
├── admin/               # Interface d'administration
│   ├── component/       # Composants admin (Logic JS + Templates HTML)
│   └── data/            # Gestion des appels API admin
├── app/                 # Site public (application front)
│   ├── component/       # Composants (NavBar, TourCard, etc.)
│   ├── data/            # Fichiers de données et traductions (i18n)
│   └── utils/           # Fonctions utilitaires (sécurité, animation)
├── server/              # Backend PHP
│   ├── model.php        # Accès à la base de données (PDO)
│   └── controller.php   # Logique métier et validation
└── BTGUIDE.sql          # Structure et données de la base de données
```

---

## 🎓 Apprentissages

Ce projet m'a permis de mettre en pratique les cours de mon BUT tout en allant plus loin :

- Gestion complexe du **DOM** en JavaScript.
- Mise en place d'un système d'**internationalisation (i18n)** dynamique.
- Création d'une API simple en PHP pour faire communiquer le Front et le Back.
- Importance de l'**accessibilité (WCAG)** avec l'utilisation des attributs ARIA et la navigation au clavier.

---

_Projet réalisé par Yann Le Flohic._
