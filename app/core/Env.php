<?php

declare(strict_types=1);

/**
 * Lecture du fichier .env placé à la racine du projet, hors du Document Root.
 */
final class Env
{
    /** @var array<string, string> */
    private static array $values = [];

    /**
     * Charge le fichier .env et le mémorise pour la durée de la requête.
     *
     * @param string $path Chemin absolu du fichier .env
     */
    public static function load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        // On garde uniquement les lignes "CLE=valeur" qui ne sont pas des commentaires
        $pairs = array_filter($lines, fn (string $line): bool => $line[0] !== '#' && str_contains($line, '='));

        self::$values = array_reduce(
            $pairs,
            function (array $carry, string $line): array {
                [$key, $value] = explode('=', $line, 2);
                $carry[trim($key)] = trim($value, " \t\"'");
                return $carry;
            },
            self::$values
        );
    }

    /**
     * Retourne une variable d'environnement (le fichier .env prime sur l'environnement système).
     *
     * @param string $key     Nom de la variable
     * @param string $default Valeur de repli
     */
    public static function get(string $key, string $default = ''): string
    {
        if (array_key_exists($key, self::$values)) {
            return self::$values[$key];
        }

        $system = getenv($key);

        return $system === false ? $default : $system;
    }

    /**
     * Clé secrète de l'application (signature des jetons CSRF publics, noms des fichiers de limitation de débit).
     * Une clé vide ou trop courte rendrait les jetons falsifiables : on refuse de continuer.
     *
     * @throws RuntimeException si APP_KEY fait moins de 32 caractères
     */
    public static function appKey(): string
    {
        $key = self::get('APP_KEY');

        if (strlen($key) < 32) {
            throw new RuntimeException('APP_KEY manquante ou trop courte (32 caractères minimum) dans le .env.');
        }

        return $key;
    }

    /**
     * Indique si l'application tourne en production.
     */
    public static function isProduction(): bool
    {
        return self::get('APP_ENV', 'production') === 'production';
    }
}
