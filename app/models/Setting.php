<?php

declare(strict_types=1);

/**
 * Textes du site modifiables depuis l'administration (table BT_Setting).
 * Chaque texte du dictionnaire app/lang/ peut être remplacé, langue par langue.
 * La clé du réglage est la clé du dictionnaire (ex. "intro.text"). Une valeur vide rétablit le texte d'origine.
 */
final class Setting
{
    public const MAX_VALUE = 5000;

    private const LOCKED_PREFIX = 'legal.creation_';

    /** @var array<string, string>|null */
    private static ?array $keys = null;

    /**
     * Liste blanche des clés modifiables : toutes les feuilles texte du dictionnaire anglais, à plat.
     * Les textes du back-office (app/lang/admin.php) n'en font jamais partie.
     *
     * @return array<string, string> Clé à points => texte anglais d'origine
     */
    public static function keys(): array
    {
        // Les mentions du créateur du site (legal.creation_*) ne sont pas modifiables par la cliente
        return self::$keys ??= array_filter(
            self::flatten(require BASE_PATH . '/app/lang/' . Lang::DEFAULT . '.php'),
            fn (string $key): bool => !str_starts_with($key, self::LOCKED_PREFIX),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Textes remplacés pour une langue (valeurs non vides uniquement).
     * Si la table est inaccessible, le site garde ses textes d'origine.
     *
     * @return array<string, string>
     */
    public static function overrides(string $locale): array
    {
        try {
            $stored = self::stored($locale);
        } catch (Throwable $exception) {
            error_log('[Setting::overrides] ' . $exception->getMessage());

            return [];
        }

        return array_filter(
            array_intersect_key($stored, self::keys()),
            fn (string $value): bool => $value !== ''
        );
    }

    /**
     * Tous les textes pour l'administration : texte d'origine et texte remplacé, par clé puis par langue.
     *
     * @return array<string, array{defaults: array<string, string>, values: array<string, string>}>
     */
    public static function allForAdmin(): array
    {
        $defaults = array_combine(Lang::SUPPORTED, array_map(
            fn (string $locale): array => self::flatten(require BASE_PATH . '/app/lang/' . $locale . '.php'),
            Lang::SUPPORTED
        ));
        $values = array_combine(Lang::SUPPORTED, array_map(fn (string $locale): array => self::overrides($locale), Lang::SUPPORTED));

        return array_combine(
            array_keys(self::keys()),
            array_map(
                fn (string $key): array => [
                    'defaults' => array_map(fn (array $texts): string => $texts[$key] ?? '', $defaults),
                    'values' => array_map(fn (array $texts): string => $texts[$key] ?? '', $values),
                ],
                array_keys(self::keys())
            )
        );
    }

    /**
     * Enregistre un texte pour une langue (insertion ou mise à jour). Une valeur vide rétablit l'origine.
     */
    public static function save(string $key, string $locale, string $value): bool
    {
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO BT_Setting (setting_key, locale, value) VALUES (:key, :locale, :value) ON DUPLICATE KEY UPDATE value = VALUES(value)'
        );

        return $stmt->execute(['key' => $key, 'locale' => $locale, 'value' => $value]);
    }

    /**
     * Valeurs brutes en base pour une langue.
     *
     * @return array<string, string>
     */
    private static function stored(string $locale): array
    {
        $stmt = Database::getConnection()->prepare('SELECT setting_key, value FROM BT_Setting WHERE locale = :locale');
        $stmt->execute(['locale' => $locale]);

        return array_column($stmt->fetchAll(), 'value', 'setting_key');
    }

    /**
     * Aplatit un dictionnaire imbriqué en clés à points (les listes donnent "guide.photos.0").
     *
     * @param array<int|string, mixed> $source
     * @return array<string, string>
     */
    private static function flatten(array $source, string $prefix = ''): array
    {
        $parts = array_map(
            fn (int|string $key, mixed $value): array => is_array($value)
                ? self::flatten($value, $prefix . $key . '.')
                : (is_string($value) ? [$prefix . $key => $value] : []),
            array_keys($source),
            $source
        );

        return $parts === [] ? [] : array_merge(...$parts);
    }
}
