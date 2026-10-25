<?php

declare(strict_types=1);

/**
 * Limitation de débit basée sur des fichiers (aucune table SQL nécessaire).
 * Chaque lecture-écriture se fait sous verrou exclusif : des requêtes envoyées en rafale ne peuvent pas dépasser la limite.
 * Un compteur est propre au client (adresse IP) ou global à l'action ($perClient = false), pour freiner une attaque répartie sur plusieurs IP.
 */
final class RateLimiter
{
    /**
     * Réserve une tentative si la limite n'est pas atteinte (vérification et enregistrement en une seule opération).
     *
     * @param string $action        Nom de l'action (login, message...)
     * @param int    $max           Nombre de tentatives autorisées dans la fenêtre
     * @param int    $windowSeconds Durée de la fenêtre glissante
     * @param bool   $perClient     true : compteur par adresse IP, false : compteur commun à tous les clients
     * @return bool true si la tentative est autorisée (et comptée), false si la limite est atteinte
     */
    public static function consume(string $action, int $max, int $windowSeconds, bool $perClient = true): bool
    {
        return self::withLock(self::file($action, $perClient), function (array $times) use ($max, $windowSeconds): array {
            $recent = self::recent($times, $windowSeconds);

            if (count($recent) >= $max) {
                return [$recent, false];
            }

            return [[...$recent, time()], true];
        });
    }

    /**
     * Indique si la limite est atteinte, sans rien enregistrer.
     */
    public static function tooMany(string $action, int $max, int $windowSeconds, bool $perClient = true): bool
    {
        return self::withLock(
            self::file($action, $perClient),
            fn (array $times): array => [null, count(self::recent($times, $windowSeconds)) >= $max]
        );
    }

    /**
     * Efface l'historique (après une connexion réussie).
     */
    public static function clear(string $action, bool $perClient = true): void
    {
        $file = self::file($action, $perClient);

        if (is_file($file)) {
            unlink($file);
        }
    }

    /**
     * Ouvre le fichier du compteur sous verrou exclusif, passe les horodatages à $callback puis écrit ceux qu'il renvoie.
     *
     * @param callable(array<int, mixed>): array{0: array<int, int>|null, 1: bool} $callback Retourne [horodatages à écrire ou null, résultat]
     * @throws RuntimeException si le fichier ne peut pas être ouvert
     */
    private static function withLock(string $file, callable $callback): bool
    {
        $handle = fopen($file, 'c+');

        if ($handle === false) {
            throw new RuntimeException('RateLimiter : impossible d\'ouvrir ' . $file);
        }

        try {
            flock($handle, LOCK_EX);
            $decoded = json_decode((string) stream_get_contents($handle), true);
            [$toWrite, $result] = $callback(is_array($decoded) ? $decoded : []);

            // Réécriture complète du fichier, toujours sous verrou
            if ($toWrite !== null) {
                ftruncate($handle, 0);
                rewind($handle);
                fwrite($handle, json_encode(array_values($toWrite)));
                fflush($handle);
            }

            return $result;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /**
     * Horodatages encore dans la fenêtre.
     *
     * @param array<int, mixed> $times
     * @return array<int, int>
     */
    private static function recent(array $times, int $windowSeconds): array
    {
        $limit = time() - $windowSeconds;

        return array_values(array_filter($times, fn ($time): bool => is_int($time) && $time > $limit));
    }

    /**
     * Fichier propre à l'action, et au client si $perClient (l'IP n'est jamais écrite en clair).
     */
    private static function file(string $action, bool $perClient): string
    {
        $scope = $perClient ? Request::clientIp() : 'global';

        return BASE_PATH . '/storage/ratelimit/' . hash('sha256', $action . '|' . $scope . '|' . Env::appKey()) . '.json';
    }
}
