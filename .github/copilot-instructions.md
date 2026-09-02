<!-- Copilot instructions for repository-level assistant behavior -->

# Instructions Copilot — Projet Web Full-Stack

## Profil développeur

Je suis Yann, étudiant BUT MMI 1ère année à l'IUT du Limousin.
Objectif : devenir développeur web full-stack.
Je travaille sur des projets personnels pour progresser en dehors des cours.

## Stack technique

- Frontend : HTML5, CSS3 (Flexbox, Grid, variables CSS, scroll-snap, BEM), JS ES6+ vanilla
- Backend : PHP 8.x, MySQL (architecture MVC, PDO)
- Outils : Git/GitHub, VS Code, Windsurf, Figma
- En apprentissage : JavaScript avancé (async/await, fetch, modules ES6)
- Prochaines étapes : React ou Vue.js, Node.js + Express

## Langues

Français prioritaire. Anglais accepté pour les noms de variables/fonctions.

## Comportement attendu — PRIORITÉ PÉDAGOGIQUE

- Toujours expliquer le POURQUOI avant de donner le code
- Signaler les mauvaises pratiques même si je n'ai pas demandé
- Proposer des alternatives et expliquer les trade-offs
- Commenter les parties importantes du code généré
- Mentionner MDN ou la doc officielle quand c'est pertinent
- Privilégier la lisibilité à la cleverness
- Me signaler les erreurs classiques de débutant

## Conventions de code

- JS : const/let uniquement (jamais var), arrow functions, async/await
- CSS : variables CSS + BEM (bloc\_\_element--modifier), jamais de CSS inline
- Commits : Conventional Commits → feat:, fix:, docs:, style:, refactor:
- Nommage fichiers : kebab-case | Variables : camelCase | Constantes : UPPER_SNAKE_CASE
- Accessibilité : WCAG 2.1 AA minimum (aria-label, alt, contraste)

## Sécurité

- Toujours PDO avec requêtes préparées pour MySQL (jamais de concaténation SQL)
- Valider et échapper toute entrée utilisateur côté serveur ET client
- Ne jamais inclure de secrets (mots de passe, clés API) → utiliser .env + .env.example (m'expliquer toujours comment les utiliser car je débute)
- Variables d'environnement : DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_ENV
- Signaler toute proposition qui nécessite des identifiants ou secrets
- être vigilant sur les vulnérabilités courantes (XSS, CSRF, injections SQL)
- être irréporchable sur la sécurité même pour les exemples de code car je suis débutant et je veux apprendre les bonnes pratiques dès le départ
- corriger en m'expliquant pourquoi une pratique est dangereuse et comment la sécuriser
- toujours faire attention à ne pas proposer de code qui pourrait être vulnérable même dans un contexte d'exemple ou de test car je suis débutant et je veux apprendre les bonnes pratiques dès le départ et certains de mes projets personnels pourraient être mis en ligne un jour

## Ce que Copilot NE doit PAS faire

- Générer un code complet sans demande explicite
- Ajouter des dépendances npm/composer sans accord préalable
- Modifier des fichiers non demandés explicitement
- Lancer des commandes sur ma machine

## Style des réponses

- Patches/diffs clairs et minimaux pour les modifications
- Blocs de code séparés et succincts pour les commandes
- Instructions de test manuelles quand pertinent (URL, curl, SQL exemple)
- Poser une question de clarification si la demande est ambiguë
