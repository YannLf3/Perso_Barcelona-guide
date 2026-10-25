<?php

declare(strict_types=1);

/**
 * Configuration de la base de données : aucune valeur en dur, tout vient du .env.
 */
return [
    'host' => Env::get('DB_HOST', 'localhost'),
    'port' => Env::get('DB_PORT', '3306'),
    'name' => Env::get('DB_NAME'),
    'user' => Env::get('DB_USER'),
    'pass' => Env::get('DB_PASS'),
    'charset' => 'utf8mb4',
];
