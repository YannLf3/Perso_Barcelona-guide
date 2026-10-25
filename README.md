# Barcelona Feel Good Tours

Site vitrine multilingue (en, fr, es, it) d'une guide touristique à Barcelone, avec un back-office pour gérer les visites, les images et les messages de contact. En ligne sur https://barcelonafeelgoodtours.eu, hébergé chez Hostinger.

Je m'appelle Yann, je suis étudiant en BUT MMI à l'IUT du Limousin. J'ai réalisé ce site pour ma tante, Anne Barrio, qui vit en Espagne et propose ses visites à une clientèle internationale.

## Stack

- PHP 8 orienté objet, architecture MVC maison (routeur, contrôleurs, modèles, vues), PDO et requêtes préparées.
- MySQL 8, tables préfixées `BT_`.
- JavaScript vanilla en MVC strict avec trois objets globaux `M`, `V` et `C`.
- Tailwind CSS v4 compilé vers `public/css/`.
- Apache avec `.htaccess` (réécriture, HTTPS forcé, cache, en-têtes de sécurité).

## Structure

```text
app/
  config/        configuration (database.php lit le .env)
  controllers/   Home, Legal, Auth, Admin, Api
  core/          Router, Response, Lang, Csrf, RateLimiter, ImageUploader, Seo, Session...
  lang/          dictionnaires en.php, fr.php, es.php, it.php et admin.php
  models/        Tour, Message, User
  views/         front/, admin/, layouts/
public/          seul dossier exposé (Document Root)
  index.php      Front Controller
  assets/        images/, uploads/, fonts/, icons/
  css/           CSS compilé (ne pas modifier à la main)
  js/            front/ et admin/ (m.js, v.js, c.js)
src/css/         sources Tailwind (app.css, admin.css, tokens.css, fonts.css)
sql/             schema.sql, seed.sql, migrations/
bin/             create-admin.php
docker/          Dockerfile et scripts pour l'environnement local
docs/            documentation
storage/         logs et limitation de débit (hors Document Root)
```

## Lancer le site en local

Prérequis : Docker.

```bash
cp .env.example .env     # puis renseigner les valeurs (voir docs/DEPLOIEMENT.md)
docker compose up -d --build
```

Le site est servi sur http://localhost:8082, phpMyAdmin sur http://127.0.0.1:8083. La base est créée avec `sql/schema.sql` et `sql/seed.sql`, le compte administrateur avec `ADMIN_USER` et `ADMIN_PASSWORD` du `.env`.

## Modifier le CSS

```bash
npm install
npm run build:css        # compile src/css vers public/css
npm run watch:css        # recompile à chaque changement
```

Le CSS compilé est versionné, car l'hébergeur n'a pas Node.

## Routes

- `/` redirige en 301 vers `/{langue}/` d'après le navigateur.
- `/{langue}/` accueil, `/{langue}/legal` mentions légales, `/sitemap.xml`.
- `/admin`, `/admin/login`, `/admin/logout` pour l'administration.
- `/api/...` réponses JSON, voir docs/ARCHITECTURE-API-SERVER.md.

## Documentation

- docs/ARCHITECTURE-API-SERVER.md : back-end, API, sécurité.
- docs/ARCHITECTURE-FRONT.md : vues, JavaScript M, V, C, Tailwind.
- docs/DEPLOIEMENT.md : mise en ligne chez Hostinger et migration de la base.
- GUIDE_MULTILINGUE.md : fonctionnement du multilingue.

_Projet réalisé par EI Yann Le Flohic._
