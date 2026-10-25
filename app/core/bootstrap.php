<?php

declare(strict_types=1);

/**
 * Initialisation commune au Front Controller et aux scripts en ligne de commande.
 * BASE_PATH doit être défini avant l'inclusion de ce fichier.
 */

// Chargement automatique : le fichier porte le nom de la classe, dans core, models ou controllers
spl_autoload_register(function (string $class): void {
    $candidates = array_map(
        fn (string $directory): string => BASE_PATH . '/app/' . $directory . '/' . $class . '.php',
        ['core', 'models', 'controllers']
    );
    $found = array_values(array_filter($candidates, 'is_file'));

    if ($found !== []) {
        require $found[0];
    }
});

require BASE_PATH . '/app/core/helpers.php';

Env::load(BASE_PATH . '/.env');
