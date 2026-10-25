<?php

declare(strict_types=1);

/**
 * Crée ou met à jour le compte d'administration à partir du .env (ADMIN_USER et ADMIN_PASSWORD).
 * Usage : php bin/create-admin.php
 */

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/core/bootstrap.php';

$username = Env::get('ADMIN_USER', 'admin');
$password = Env::get('ADMIN_PASSWORD');

if ($password === '' || strlen($password) < 12) {
    fwrite(STDERR, "ADMIN_PASSWORD manquant ou trop court (12 caractères minimum) dans le .env.\n");
    exit(1);
}

User::upsert($username, $password);
echo "Compte administrateur « {$username} » enregistré.\n";
