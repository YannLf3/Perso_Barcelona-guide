# Multilingue (en par défaut, fr, es, it)

Deux types de contenu, deux endroits.

| Type | Exemples | Où |
|------|----------|----|
| Contenu métier éditable en admin | titre, accroche et résumé des visites | base de données, une ligne par langue dans `BT_Tour_Translation` |
| Interface fixe | navigation, intro, titres, formulaire, messages d'erreur | `app/lang/{en,fr,es,it}.php` |

## URLs et SEO

La langue fait partie de l'URL : `/en/`, `/fr/`, `/es/`, `/it/`. La racine `/` redirige en 301 vers la langue du navigateur, ou vers `/en/`. Chaque page porte sa canonique, ses balises `hreflang` (avec `x-default` vers `/en/`) et `<html lang>`. `Lang.php` valide la langue contre la liste blanche `en`, `fr`, `es`, `it`.

## Textes de l'interface

Ajouter ou modifier un texte : éditer la clé dans les quatre fichiers de `app/lang/`. Dans une vue, `t('tours.title')` retourne le texte de la langue de la page, avec repli sur l'anglais si la clé manque. Aucun texte visible ne doit être écrit en dur dans une vue.

## Visites

- `BT_Tour` : champs communs (durée, capacité, type de groupe, image, actif).
- `BT_Tour_Translation` : `title`, `tagline`, `summary` par `tour_id` et `locale`.
- Lecture publique : la langue demandée, avec repli sur l'anglais (`Tour::findActive()`).
- Admin : le formulaire d'édition affiche une langue à la fois, le bouton enregistre les champs communs et la langue choisie. L'anglais est obligatoire à la création, les autres langues sont facultatives (les trois champs doivent être remplis pour qu'une langue soit enregistrée).

## Ajouter une langue

1. Créer `app/lang/xx.php` en copiant `en.php`.
2. Ajouter le code dans `Lang::SUPPORTED`, `Lang::OG_LOCALES`, la contrainte `chk_tour_translation_locale` de la base et le tableau `$langs` de `app/views/admin/partials/tours.php`.
3. Ajouter les clés `admin.tours.lang_xx` et `admin.tours.flag_xx` dans `app/lang/admin.php`.
