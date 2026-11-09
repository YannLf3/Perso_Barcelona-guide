<?php

declare(strict_types=1);

/**
 * Crée ou met à jour un compte d'administration.
 *
 * Usage : php bin/create-admin.php <identifiant> <admin|editor>
 *   admin  : accès complet (apparence, textes de navigation, suppression de messages…)
 *   editor : contenu uniquement (visites, photos, messages en lecture, textes de contenu)
 *
 * Le mot de passe est demandé en ligne de commande (sans affichage) : il n'est écrit dans aucun fichier.
 * Pour un script, il peut être fourni par la variable d'environnement ADMIN_PASSWORD (12 caractères minimum).
 */

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/core/bootstrap.php';

$username = trim((string) ($argv[1] ?? ''));
$role = (string) ($argv[2] ?? '');

if ($username === '' || mb_strlen($username) > 60 || !in_array($role, User::ROLES, true)) {
    fwrite(STDERR, "Usage : php bin/create-admin.php <identifiant> <admin|editor>\n");
    exit(1);
}

$password = (string) getenv('ADMIN_PASSWORD');

if ($password === '') {
    // Saisie sans écho : le mot de passe ne reste pas affiché dans le terminal
    fwrite(STDERR, "Mot de passe pour « {$username} » (12 caractères minimum) : ");
    shell_exec('stty -echo');
    $password = rtrim((string) fgets(STDIN), "\r\n");
    shell_exec('stty echo');
    fwrite(STDERR, "\n");
}

if (strlen($password) < 12) {
    fwrite(STDERR, "Mot de passe trop court (12 caractères minimum).\n");
    exit(1);
}

User::upsert($username, $password, $role);
echo "Compte « {$username} » enregistré avec le rôle « {$role} ».\n";
