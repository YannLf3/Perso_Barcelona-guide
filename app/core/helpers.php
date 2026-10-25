<?php

declare(strict_types=1);

/**
 * Fonctions utilitaires disponibles dans toutes les vues.
 */

/**
 * Échappe une valeur pour l'afficher dans du HTML ou dans un attribut.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Raccourci vers la couche de traduction.
 *
 * @param array<string, string> $params
 */
function t(string $key, array $params = []): string
{
    return Lang::t($key, $params);
}

/**
 * Rend un partial et retourne son HTML (utilisé avec array_map dans les vues).
 *
 * @param array<string, mixed> $data
 */
function partial(string $name, array $data = []): string
{
    return View::capture($name, $data);
}

/**
 * Chemin d'un fichier statique avec un paramètre de version pour casser le cache après modification.
 */
function asset(string $path): string
{
    $file = BASE_PATH . '/public' . $path;

    return $path . (is_file($file) ? '?v=' . filemtime($file) : '');
}
