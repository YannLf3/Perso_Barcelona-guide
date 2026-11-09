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

/**
 * Affiche un texte saisi dans l'administration en conservant ses paragraphes : une ligne (ou un bloc séparé par une ligne vide) devient un <p>.
 * Sans cela, le HTML replie les retours à la ligne et tout le texte apparaît d'un seul bloc.
 * Chaque paragraphe est échappé avec e() : le texte saisi ne peut jamais injecter de HTML.
 *
 * @param string $class Classes du <p> d'origine, reprises sur chaque paragraphe
 */
function paragraphs(string $text, string $class = ''): string
{
    $lines = preg_split('/\R+/u', trim($text)) ?: [];
    $lines = array_values(array_filter(array_map('trim', $lines), fn (string $line): bool => $line !== ''));
    $last = count($lines) - 1;

    return implode("\n", array_map(
        // Tous les paragraphes sauf le dernier reçoivent un espacement fixe : le dernier garde la marge d'origine du bloc
        fn (string $line, int $index): string => '<p class="' . e(trim($class . ($index < $last ? ' mb-[0.9em]!' : ''))) . '">' . e($line) . '</p>',
        $lines,
        array_keys($lines)
    ));
}
