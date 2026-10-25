<?php

declare(strict_types=1);

/**
 * Rendu des vues PHP avec le layout (header, vue, footer).
 */
final class View
{
    /**
     * Rend une vue entourée de layouts/header.php et layouts/footer.php et envoie la réponse.
     *
     * @param string               $view   Chemin de la vue sous app/views, sans extension
     * @param array<string, mixed> $data   Variables disponibles dans les vues
     * @param int                  $status Code HTTP
     */
    public static function render(string $view, array $data = [], int $status = 200): never
    {
        $body = self::capture('layouts/header', $data) . self::capture($view, $data) . self::capture('layouts/footer', $data);

        Response::html($body, $status);
    }

    /**
     * Rend un fragment de vue (partial) et retourne le HTML.
     *
     * @param array<string, mixed> $data
     */
    public static function capture(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require BASE_PATH . '/app/views/' . $view . '.php';

        return (string) ob_get_clean();
    }
}
