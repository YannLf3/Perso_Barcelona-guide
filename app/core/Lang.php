<?php

declare(strict_types=1);

/**
 * Couche de traduction : valide la langue et charge le dictionnaire correspondant.
 */
final class Lang
{
    public const SUPPORTED = ['en', 'fr', 'es', 'it'];
    public const DEFAULT = 'en';

    private const OG_LOCALES = ['en' => 'en_GB', 'fr' => 'fr_FR', 'es' => 'es_ES', 'it' => 'it_IT'];

    private static string $locale = self::DEFAULT;

    /** @var array<string, mixed> */
    private static array $dictionary = [];

    /** @var array<string, mixed> */
    private static array $fallback = [];

    /**
     * Vérifie une langue contre la liste blanche.
     */
    public static function isValid(?string $locale): bool
    {
        return $locale !== null && in_array($locale, self::SUPPORTED, true);
    }

    /**
     * Charge le dictionnaire d'une langue (repli sur l'anglais si la clé manque).
     *
     * @param string $locale Langue demandée, ramenée à l'anglais si elle est invalide
     */
    public static function init(string $locale): void
    {
        self::$locale = self::isValid($locale) ? $locale : self::DEFAULT;
        $admin = ['admin' => require BASE_PATH . '/app/lang/admin.php'];
        self::$dictionary = self::withOverrides(require BASE_PATH . '/app/lang/' . self::$locale . '.php', Setting::overrides(self::$locale));
        self::$fallback = require BASE_PATH . '/app/lang/' . self::DEFAULT . '.php';

        // Les textes du back-office ne sont pas traduits : ils sont ajoutés aux deux dictionnaires
        self::$dictionary += $admin;
        self::$fallback += $admin;
    }

    public static function locale(): string
    {
        return self::$locale;
    }

    /**
     * Locale Open Graph de la langue courante (en_GB, fr_FR...).
     */
    public static function ogLocale(string $locale): string
    {
        return self::OG_LOCALES[$locale] ?? self::OG_LOCALES[self::DEFAULT];
    }

    /**
     * Retourne un texte à partir d'une clé à points, par exemple "nav.tours".
     *
     * @param string               $key    Clé du dictionnaire
     * @param array<string,string> $params Valeurs à injecter pour les marqueurs {nom}
     */
    public static function t(string $key, array $params = []): string
    {
        $value = self::find(self::$dictionary, $key) ?? self::find(self::$fallback, $key) ?? $key;
        $text = is_string($value) ? $value : $key;

        return array_reduce(
            array_keys($params),
            fn (string $carry, string $name): string => str_replace('{' . $name . '}', $params[$name], $carry),
            $text
        );
    }

    /**
     * Retourne une liste de textes (tableau) du dictionnaire.
     *
     * @return array<int|string, mixed>
     */
    public static function list(string $key): array
    {
        $value = self::find(self::$dictionary, $key) ?? self::find(self::$fallback, $key);

        return is_array($value) ? $value : [];
    }

    /**
     * Choisit la meilleure langue d'après l'en-tête Accept-Language du navigateur.
     */
    public static function fromAcceptLanguage(string $header): string
    {
        // On extrait les codes à deux lettres dans l'ordre de préférence du navigateur
        preg_match_all('/(?:^|,)\s*([a-z]{2})/i', strtolower($header), $found);

        $candidates = array_values(array_filter($found[1], fn (string $code): bool => self::isValid($code)));

        return $candidates[0] ?? self::DEFAULT;
    }

    /**
     * Remplace dans le dictionnaire les textes modifiés depuis l'administration.
     *
     * @param array<string, mixed>  $dictionary
     * @param array<string, string> $overrides  Clé à points => texte
     * @return array<string, mixed>
     */
    private static function withOverrides(array $dictionary, array $overrides): array
    {
        return array_reduce(
            array_keys($overrides),
            fn (array $carry, string $key): array => self::setPath($carry, explode('.', $key), $overrides[$key]),
            $dictionary
        );
    }

    /**
     * Écrit une valeur dans un tableau imbriqué selon un chemin de clés.
     *
     * @param array<int|string, mixed> $source
     * @param array<int, string>       $parts
     * @return array<int|string, mixed>
     */
    private static function setPath(array $source, array $parts, string $value): array
    {
        $head = array_shift($parts);
        $source[$head] = $parts === [] ? $value : self::setPath(is_array($source[$head] ?? null) ? $source[$head] : [], $parts, $value);

        return $source;
    }

    /**
     * Descend dans un tableau selon une clé à points.
     *
     * @param array<string, mixed> $source
     */
    private static function find(array $source, string $key): mixed
    {
        return array_reduce(
            explode('.', $key),
            fn (mixed $carry, string $part): mixed => is_array($carry) && array_key_exists($part, $carry) ? $carry[$part] : null,
            $source
        );
    }
}
