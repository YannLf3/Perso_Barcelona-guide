# Déploiement

## Environnement local (Docker)

1. Copier `.env.example` vers `.env` et remplir : `APP_KEY` (64 caractères hexadécimaux, par exemple `openssl rand -hex 32`), `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_ROOT_PASSWORD`, `ADMIN_USER`, `ADMIN_PASSWORD` (12 caractères minimum).
2. Mettre `DB_HOST=db` et `APP_URL=http://localhost:8082`.
3. `docker compose up -d --build`.
4. Site : http://localhost:8082. Administration : http://localhost:8082/admin.

Pour repartir d'une base vierge : `docker compose down -v` puis relancer.

## Hostinger

Le Document Root doit pointer vers le dossier `public/`. Le `.env` et le dossier `app/` restent à la racine du projet, hors du Document Root.

1. Dans le hPanel, changer le mot de passe de l'utilisateur MySQL de production (l'ancien est dans l'historique Git). Restreindre ses droits à `SELECT`, `INSERT` et `UPDATE` sur la base.
2. Téléverser le projet (sans `node_modules`, sans `.git`). Le CSS compilé est dans `public/css/`.
3. Créer le `.env` de production à partir de `.env.example` : `APP_ENV=production`, `APP_URL=https://barcelonafeelgoodtours.eu`, `DB_HOST=localhost`, identifiants du hPanel, `APP_KEY` neuf.
4. Mettre à jour la base existante en appliquant dans l'ordre, avec phpMyAdmin, les fichiers de `sql/migrations/` (001, 002, 003). Faire une sauvegarde avant.
5. Créer le compte administrateur : `php bin/create-admin.php` (SSH), ou insérer dans `BT_User` un hash produit par `password_hash()`.
6. Vérifier que `storage/` et `public/assets/uploads/` sont accessibles en écriture.
7. Tester : `/`, `/fr/`, `/admin/login`, l'envoi du formulaire de contact, le fichier `/sitemap.xml`.

Installation neuve : appliquer `sql/schema.sql` (et `sql/seed.sql` pour des données de démonstration).

## Après la mise en ligne

- Soumettre `https://barcelonafeelgoodtours.eu/sitemap.xml` dans la Search Console.
- Faire valider les mentions légales par la cliente : elles ne mentionnent ni raison sociale, ni numéro d'entreprise, ni adresse, ni responsable de publication.
