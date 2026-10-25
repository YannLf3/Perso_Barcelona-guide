# Architecture du back-end

## Cycle d'une requête

1. Apache réécrit toute URL qui n'est pas un fichier existant vers `public/index.php` (Front Controller).
2. `index.php` charge `app/core/bootstrap.php` (autoload, helpers, lecture du `.env`), installe les gestionnaires d'erreurs puis déclare les routes.
3. `Router` associe méthode et chemin à une méthode de contrôleur. Les segments `{nom}` deviennent des paramètres.
4. Le contrôleur lit la requête, appelle les modèles, puis répond avec `View::render()` (HTML) ou `Response::json()` (JSON).
5. Toute route inconnue passe par `HomeController::notFound()` (HTML 404, ou JSON 404 sous `/api/`).

Le code n'utilise aucune boucle `foreach` : `array_map`, `array_filter` et `array_reduce` à la place.

## Couches

- `app/controllers/` orchestre. `Controller` fournit `input()` (JSON ou formulaire), `requireAdminApi()` et `requireCsrfApi()`.
- `app/models/` contient l'accès aux données, un modèle par table métier, avec propriétés privées, getters et setters fluents. Les traductions d'une visite sont gérées par `Tour`.
- `app/core/` contient les classes transversales : `Router`, `Response`, `Lang`, `Csrf`, `RateLimiter`, `ImageUploader`, `Seo`, `Session`, `Request`, `Database`, `Env`, `View`.
- `app/lang/` contient les textes statiques, un fichier par langue, plus `admin.php` pour le back-office.

## Base de données

Voir `sql/schema.sql`. Tables : `BT_Tour`, `BT_Tour_Translation` (clé `tour_id` + `locale`), `BT_Message`, `BT_User`. La colonne `BT_Tour.price` contient le nombre de personnes du groupe (capacité) et s'affiche avec le suffixe "pers.". Elle est exposée sous le nom `capacity` par l'API.

L'utilisateur MySQL de l'application n'a que les droits `SELECT`, `INSERT` et `UPDATE`. La connexion PDO est unique par requête, en `utf8mb4` avec `ERRMODE_EXCEPTION`.

## Routes API

| Route | Accès |
|---|---|
| `GET /api/tours?lang=` | public |
| `POST /api/messages` | public, jeton CSRF signé, limitation de débit, champ piège `website` |
| `GET /api/admin/tours` | session admin |
| `POST /api/admin/tours` | session admin, CSRF |
| `PUT /api/admin/tours/{id}` | session admin, CSRF |
| `GET /api/admin/session` | session admin (prolonge la session pendant l'activité) |
| `GET /api/admin/messages` | session admin (purge les messages de plus de 3 ans) |
| `DELETE /api/admin/messages/{id}` | session admin, CSRF |
| `GET /api/admin/images` | session admin |
| `POST /api/admin/images` | session admin, CSRF |
| `GET /api/admin/texts` | session admin |
| `PUT /api/admin/texts` | session admin, CSRF |
| `GET /api/admin/design` | session admin |
| `PUT /api/admin/design` | session admin, CSRF |
| `POST /api/admin/design/restore/{id}` | session admin, CSRF |
| `GET /theme.css` | public |

Textes : toute clé du dictionnaire anglais est modifiable par langue (`BT_Setting`, valeur vide = texte d'origine), jamais les clés `admin.*`. Apparence : `Design::sanitize()` refuse toute valeur hors liste blanche (couleurs hexadécimales, polices du catalogue, tailles et formes prévues, images existantes) ; l'historique est en ajout seul (`BT_Design_Revision`).

Les routes publiques ne renvoient que les champs d'affichage et jamais une visite inactive. Les écritures admin reçoivent du JSON, sauf l'envoi d'image (multipart). Le jeton CSRF admin est lu dans la balise `<meta name="csrf-token">` et envoyé dans l'en-tête `X-CSRF-Token`.

Exemple de corps pour `PUT /api/admin/tours/{id}` :

```json
{
  "duration": "2h",
  "capacity": "12",
  "group_type": "small",
  "is_active": 1,
  "image_url": "asset1.webp",
  "translations": { "fr": { "title": "...", "tagline": "...", "summary": "..." } }
}
```

Une langue n'est enregistrée que si titre, accroche et résumé sont tous remplis. L'anglais est obligatoire à la création.

## Sécurité

- Authentification par session PHP (`btsession`, cookie httpOnly, sameSite Lax, secure en HTTPS). `session_regenerate_id(true)` et nouveau jeton CSRF à la connexion, mode strict. Expiration après 30 minutes d'inactivité et 8 heures au maximum ; le tableau de bord prolonge la session toutes les 5 minutes tant que la cliente tape ou clique. Mot de passe haché avec `password_hash()`.
- Connexion : 5 tentatives par quart d'heure et par adresse IP, et 20 par heure tous clients confondus (`RateLimiter`, fichiers verrouillés dans `storage/ratelimit/`, IP hachée). L'IP réelle n'est lue dans `X-Forwarded-For` que si la connexion vient d'une IP listée dans `TRUSTED_PROXIES`.
- Uploads : `public/assets/uploads/.htaccess` ne sert que les images et coupe PHP ; les JPEG sont réencodés sans métadonnées EXIF.
- CSRF : jeton de session pour l'admin, jeton signé HMAC avec horodatage pour le formulaire public (aucun cookie posé aux visiteurs).
- Envoi d'images : type MIME réel via `finfo`, 5 Mo maximum, dimensions vérifiées, nom aléatoire, dossier `uploads/` sans exécution de scripts (`.htaccess`).
- Messages de contact : validés côté serveur (longueurs, e-mail), stockés bruts, échappés à l'affichage (`htmlspecialchars` en PHP, `textContent` en JS).
- En-têtes : CSP avec nonce posée par PHP (`Response`), les autres dans `public/.htaccess`.
- Erreurs : `display_errors` à 0 en production, journal dans `storage/logs/php-error.log`.
