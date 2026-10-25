<?php

declare(strict_types=1);

/**
 * Apparence du site public (table BT_Design_Revision) : couleurs, polices, taille du texte, formes et photos du carrousel.
 * Seules les valeurs qui diffèrent de l'origine sont enregistrées. L'apparence en ligne est la révision la plus récente.
 * N'affecte jamais l'administration : la feuille /theme.css n'est chargée que par les pages publiques.
 */
final class Design
{
    public const MAX_HISTORY = 30;

    private const LABEL_MAX = 120;
    private const UNICODE_LATIN = 'U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD';
    private const UNICODE_LATIN_EXT = 'U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C4, U+2113, U+2C60-2C7F, U+A720-A7FF';

    /** @var array<string, mixed>|null */
    private static ?array $config = null;

    /** @var array{id: int, data: array<string, mixed>}|null */
    private static ?array $current = null;

    /**
     * Catalogue technique (app/config/design.php), chargé une seule fois.
     *
     * @return array<string, mixed>
     */
    public static function config(): array
    {
        return self::$config ??= require BASE_PATH . '/app/config/design.php';
    }

    /**
     * Toutes les couleurs modifiables avec leur valeur d'origine, à plat.
     *
     * @return array<string, string>
     */
    public static function tokens(): array
    {
        return array_merge(...array_values(self::config()['groups']));
    }

    /**
     * Identifiant de la révision en ligne (0 si aucune publication).
     */
    public static function currentId(): int
    {
        return self::current()['id'];
    }

    /**
     * Données de la révision en ligne (tableau vide si aucune publication).
     *
     * @return array<string, mixed>
     */
    public static function currentData(): array
    {
        return self::current()['data'];
    }

    /**
     * Photos du carrousel de la section guide (5 noms de fichiers).
     *
     * @return array<int, string>
     */
    public static function guidePhotos(): array
    {
        return self::currentData()['photos'] ?? self::config()['defaultPhotos'];
    }

    /**
     * URLs des polices à précharger sur les pages publiques (sous-ensemble latin des polices de titre et de texte).
     *
     * @return array<int, string>
     */
    public static function fontPreloads(): array
    {
        $fonts = self::config()['fonts'];
        $chosen = array_unique([self::chosenFont('heading'), self::chosenFont('body')]);

        return array_values(array_map(fn (string $key): string => '/assets/fonts/' . $fonts[$key]['files'][0], $chosen));
    }

    /**
     * Valide une apparence envoyée par l'administration et ne garde que ce qui diffère de l'origine.
     * Toute valeur hors liste blanche est refusée : aucun CSS libre ne peut atteindre la feuille générée.
     *
     * @param array<string, mixed> $input
     * @return array{data: array<string, mixed>, errors: array<int, string>}
     */
    public static function sanitize(array $input): array
    {
        $config = self::config();
        $tokens = self::tokens();
        $colors = is_array($input['colors'] ?? null) ? $input['colors'] : [];
        $fonts = is_array($input['fonts'] ?? null) ? $input['fonts'] : [];
        $radius = is_array($input['radius'] ?? null) ? $input['radius'] : [];
        $photos = $input['photos'] ?? null;

        // Couleurs : clé connue, valeur hexadécimale (ou "transparent" pour les fonds de section qui l'acceptent)
        $badColors = array_keys(array_filter(
            $colors,
            fn (mixed $value, int|string $key): bool => !isset($tokens[$key]) || !is_string($value) || !self::isColorAllowed((string) $key, $value),
            ARRAY_FILTER_USE_BOTH
        ));
        $cleanColors = array_filter(
            array_map(fn (mixed $value): string => is_string($value) ? strtolower($value) : '', array_diff_key($colors, array_flip($badColors))),
            fn (string $value, string $key): bool => $value !== $tokens[$key],
            ARRAY_FILTER_USE_BOTH
        );

        $badFonts = array_filter(
            ['heading', 'body'],
            fn (string $role): bool => isset($fonts[$role]) && (!is_string($fonts[$role]) || !isset($config['fonts'][$fonts[$role]]))
        );
        $cleanFonts = array_filter(
            array_intersect_key($fonts, array_flip(array_diff(['heading', 'body'], $badFonts))),
            fn (mixed $font, string $role): bool => $font !== $config['defaultFonts'][$role],
            ARRAY_FILTER_USE_BOTH
        );

        $textSize = (string) ($input['textSize'] ?? '100');
        $badSize = !in_array($textSize, $config['textSizes'], true);

        $badRadius = array_filter(
            ['button', 'card'],
            fn (string $kind): bool => isset($radius[$kind]) && (!is_string($radius[$kind]) || !array_key_exists($radius[$kind], $config['radius'][$kind]))
        );
        $cleanRadius = array_filter(
            array_intersect_key($radius, array_flip(array_diff(['button', 'card'], $badRadius))),
            fn (mixed $shape): bool => $shape !== 'default'
        );

        // Photos : exactement 5 images existantes, conservées seulement si elles diffèrent de l'origine
        $badPhotos = $photos !== null && (
            !is_array($photos)
            || count($photos) !== count($config['defaultPhotos'])
            || array_filter($photos, fn (mixed $name): bool => !is_string($name) || !ImageUploader::exists($name)) !== []
        );
        $cleanPhotos = !$badPhotos && is_array($photos) && array_values($photos) !== $config['defaultPhotos'] ? array_values($photos) : null;

        $errors = array_merge(
            array_map(fn (int|string $key): string => 'color:' . $key, $badColors),
            array_map(fn (string $role): string => 'font:' . $role, array_values($badFonts)),
            $badSize ? ['textSize'] : [],
            array_map(fn (string $kind): string => 'radius:' . $kind, array_values($badRadius)),
            $badPhotos ? ['photos'] : []
        );

        $data = array_filter([
            'colors' => $cleanColors,
            'fonts' => $cleanFonts,
            'textSize' => !$badSize && $textSize !== '100' ? $textSize : null,
            'radius' => $cleanRadius,
            'photos' => $cleanPhotos,
        ], fn (mixed $value): bool => $value !== null && $value !== []);

        return ['data' => $data, 'errors' => $errors];
    }

    /**
     * Feuille CSS qui surcharge les variables --ui-* d'origine (src/css/tokens.css).
     * Les valeurs sont revalidées ici pour qu'une ligne corrompue en base ne produise jamais de CSS arbitraire.
     *
     * @param array<string, mixed> $data Données d'une révision
     */
    public static function css(array $data): string
    {
        $clean = self::sanitize($data)['data'];
        $config = self::config();
        $fonts = $config['fonts'];

        $fontFaces = array_map(
            fn (string $key): string => self::fontFaceCss($fonts[$key]),
            array_values(array_unique(array_diff(array_values($clean['fonts'] ?? []), ['outfit', 'inter'])))
        );

        $variables = array_merge(
            array_map(fn (string $key, string $value): string => '--ui-' . $key . ':' . $value, array_keys($clean['colors'] ?? []), $clean['colors'] ?? []),
            array_map(
                fn (string $role, string $key): string => '--font-' . $role . ':' . self::fontStack($fonts[$key]),
                array_keys($clean['fonts'] ?? []),
                $clean['fonts'] ?? []
            ),
            isset($clean['radius']['button']) ? ['--ui-radius-btn:' . $config['radius']['button'][$clean['radius']['button']]] : [],
            isset($clean['radius']['card']) ? ['--ui-radius-card:' . $config['radius']['card'][$clean['radius']['card']]] : []
        );

        return implode("\n", array_filter([
            implode("\n", $fontFaces),
            $variables === [] ? '' : ':root{' . implode(';', $variables) . '}',
            isset($clean['textSize']) ? 'html{font-size:' . $clean['textSize'] . '%}' : '',
        ]));
    }

    /**
     * Publie une apparence (nouvelle révision, qui devient l'apparence en ligne).
     *
     * @param array<string, mixed> $data Données déjà passées par sanitize()
     */
    public static function publish(array $data, string $label): bool
    {
        $stmt = Database::getConnection()->prepare('INSERT INTO BT_Design_Revision (label, data) VALUES (:label, :data)');

        return $stmt->execute([
            'label' => mb_substr($label, 0, self::LABEL_MAX),
            'data' => json_encode((object) $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * Données d'une révision précise, null si elle n'existe pas.
     *
     * @return array{label: string, created_at: string, data: array<string, mixed>}|null
     */
    public static function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare('SELECT label, data, created_at FROM BT_Design_Revision WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return ['label' => (string) $row['label'], 'created_at' => (string) $row['created_at'], 'data' => self::decode((string) $row['data'])];
    }

    /**
     * Dernières publications, la plus récente en premier.
     *
     * @return array<int, array{id: int, label: string, created_at: string}>
     */
    public static function history(): array
    {
        $stmt = Database::getConnection()->query('SELECT id, label, created_at FROM BT_Design_Revision ORDER BY id DESC LIMIT ' . self::MAX_HISTORY);

        return array_map(
            fn (array $row): array => ['id' => (int) $row['id'], 'label' => (string) $row['label'], 'created_at' => (string) $row['created_at']],
            $stmt->fetchAll()
        );
    }

    /**
     * Révision en ligne, lue une seule fois par requête.
     * Si la table manque (migration non appliquée), le site garde son apparence d'origine.
     *
     * @return array{id: int, data: array<string, mixed>}
     */
    private static function current(): array
    {
        if (self::$current !== null) {
            return self::$current;
        }

        try {
            $row = Database::getConnection()->query('SELECT id, data FROM BT_Design_Revision ORDER BY id DESC LIMIT 1')->fetch();
            self::$current = $row === false
                ? ['id' => 0, 'data' => []]
                : ['id' => (int) $row['id'], 'data' => self::sanitize(self::decode((string) $row['data']))['data']];
        } catch (Throwable $exception) {
            error_log('[Design::current] ' . $exception->getMessage());
            self::$current = ['id' => 0, 'data' => []];
        }

        return self::$current;
    }

    /**
     * @return array<string, mixed>
     */
    private static function decode(string $json): array
    {
        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    private static function isColorAllowed(string $key, string $value): bool
    {
        // Les fonds de section transparents par défaut peuvent y revenir
        return preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1
            || ($value === 'transparent' && self::tokens()[$key] === 'transparent');
    }

    /**
     * Police choisie pour un rôle ("heading" ou "body").
     */
    private static function chosenFont(string $role): string
    {
        return self::currentData()['fonts'][$role] ?? self::config()['defaultFonts'][$role];
    }

    /**
     * @param array{family: string, fallback: string} $font
     */
    private static function fontStack(array $font): string
    {
        return '"' . $font['family'] . '", ' . $font['fallback'];
    }

    /**
     * Déclarations @font-face d'une police du catalogue (sous-ensembles latin et latin étendu).
     *
     * @param array{family: string, weight: string, files: array<int, string>} $font
     */
    private static function fontFaceCss(array $font): string
    {
        return implode("\n", array_map(
            fn (string $file, string $range): string => '@font-face{font-family:"' . $font['family'] . '";font-style:normal;font-weight:' . $font['weight']
                . ';font-display:swap;src:url("/assets/fonts/' . $file . '") format("woff2");unicode-range:' . $range . '}',
            $font['files'],
            [self::UNICODE_LATIN, self::UNICODE_LATIN_EXT]
        ));
    }
}
