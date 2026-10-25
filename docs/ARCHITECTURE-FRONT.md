# Architecture du front

## Rendu

Les pages sont rendues côté serveur (PHP) pour le SEO et un premier affichage rapide : visites, textes et données structurées JSON-LD sont dans le HTML. Le JavaScript gère les interactions seulement.

Vues dans `app/views/` :

- `layouts/header.php` et `footer.php` : `<head>` (titre, description, canonique, hreflang, Open Graph, JSON-LD, polices), lien d'évitement, scripts.
- `front/` : `home.php`, `legal.php`, `404.php` et `partials/` (navbar, intro, guide, tours, tour-card, tour-modal, contact).
- `admin/` : `login.php`, `dashboard.php` et `partials/` (topbar, log, tours, images, messages). Les partials admin contiennent des balises `<template>` que le JavaScript clone.

Les partials de liste sont appelés avec `array_map` et `implode`.

## JavaScript M, V, C

Dans `public/js/front/` et `public/js/admin/`, trois scripts classiques chargés dans cet ordre avec `defer` : `m.js`, `v.js`, `c.js`. Le dernier se termine par `C.init();`.

- `M` (modèle) : données et appels `fetch`. Retourne des objets, jamais de HTML, aucune référence au DOM. Front : `loadTours`, `filterTours` (filtre par type de groupe), `sendMessage`. Admin : `request`, `loadAll`, `createTour`, `updateTour`, `uploadImage`, historique du journal.
- `V` (vue) : seul endroit qui touche au DOM. `V.init()` déclare les écouteurs par délégation sur le document (attributs `data-action`, `data-form`, `data-card`). Les modales utilisent `<dialog>` et `showModal()`, clonées depuis un `<template>`. Les textes de la base sont insérés avec `textContent`.
- `C` (contrôleur) : méthodes `handler_<action>(ev)` qui interrogent `M` puis mettent à jour `V`.

Le changement de langue recharge la page vers `/{langue}/` : la langue fait partie de l'URL.

## CSS Tailwind v4

Sources dans `src/css/`, compilation avec `npm run build:css`.

- `tokens.css` : tokens primitifs (palette crème, brun, orange du back-office, palette du site public) et tokens sémantiques (`brand-primary`, `bg-page`, `bg-surface`, `bg-contrast`, `border-default`, `text-default`, `text-muted`, `text-inverse`, plus `site-*` pour le site public). Les thèmes clair et sombre du back-office échangent les tokens sémantiques via `[data-theme='dark']`. Le site public n'a qu'un thème.
- `fonts.css` : Inter et Outfit hébergées dans `public/assets/fonts/`.
- `app.css` : site public. `admin.css` : back-office, sans le preflight de Tailwind pour garder le rendu natif des contrôles de formulaire.
- Les classes utilitaires sont écrites dans les vues. Les motifs répétés (`card-surface`, `field-contact`, `admin-card`, `admin-control`, `admin-button`...) sont des `@utility`.
- Mobile first côté Tailwind, avec les variantes `mobile` (max 768 px) et `narrow` (max 480 px) pour reproduire les points de rupture d'origine.

## Images

Les images statiques sont dans `public/assets/images/`, celles envoyées depuis l'administration dans `public/assets/uploads/`. En base, `image_url` ne contient que le nom du fichier : `ImageUploader::publicUrl()` retrouve le bon dossier. Les balises `<img>` portent `width`, `height`, `alt` et `loading="lazy"`.
