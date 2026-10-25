<?php

declare(strict_types=1);

/**
 * Limitation de débit basée sur des fichiers (aucune table SQL nécessaire).
 */
final class RateLimiter
{
    /**
     * Compte les tentatives récentes d'un client pour une action donnée.
     *
     * @param string $action        Nom de l'action (login, message...)
     * @param int    $windowSeconds Durée de la fenêtre glissante
     */
    public static function attempts(string $action, int $windowSeconds): int
    {
        return count(self::recent($action, $windowSeconds));
    }

    /**
     * Enregistre une tentative.
     */
    public static function hit(string $action, int $windowSeconds): void
    {
        $times = self::recent($action, $windowSeconds);
        $times[] = time();
        file_put_contents(self::file($action), json_encode($times), LOCK_EX);
    }

    /**
     * Indique si la limite est atteinte.
     */
    public static function tooMany(string $action, int $max, int $windowSeconds): bool
    {
        return self::attempts($action, $windowSeconds) >= $max;
    }

    /**
     * Efface l'historique (après une connexion réussie).
     */
    public static function clear(string $action): void
    {
        $file = self::file($action);

        if (is_file($file)) {
            unlink($file);
        }
    }

    /**
     * Horodatages des tentatives encore dans la fenêtre.
     *
     * @return array<int, int>
     */
    private static function recent(string $action, int $windowSeconds): array
    {
        $file = self::file($action);
        $times = is_file($file) ? json_decode((string) file_get_contents($file), true) : [];
        $limit = time() - $windowSeconds;

        return array_values(array_filter(is_array($times) ? $times : [], fn ($time): bool => is_int($time) && $time > $limit));
    }

    /**
     * Fichier propre au couple action + adresse IP (l'IP n'est jamais écrite en clair).
     */
    private static function file(string $action): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        return BASE_PATH . '/storage/ratelimit/' . hash('sha256', $action . '|' . $ip . '|' . Env::get('APP_KEY')) . '.json';
    }
}
