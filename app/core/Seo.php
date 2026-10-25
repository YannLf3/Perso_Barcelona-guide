<?php

declare(strict_types=1);

/**
 * Données SEO : URLs canoniques, hreflang, Open Graph et JSON-LD.
 */
final class Seo
{
    public const SITE_NAME = 'Barcelona Feel Good Tours';

    /**
     * URL de base du site, sans slash final (variable APP_URL du .env).
     */
    public static function baseUrl(): string
    {
        return rtrim(Env::get('APP_URL', 'https://barcelonafeelgoodtours.eu'), '/');
    }

    /**
     * URL absolue d'un chemin.
     */
    public static function absolute(string $path): string
    {
        return self::baseUrl() . $path;
    }

    /**
     * Liens hreflang d'une page : une entrée par langue plus x-default vers l'anglais.
     *
     * @param string $suffix Fin du chemin après la langue ("" pour l'accueil, "legal" pour les mentions)
     * @return array<int, array{hreflang: string, href: string}>
     */
    public static function alternates(string $suffix): array
    {
        $languages = array_map(
            fn (string $code): array => ['hreflang' => $code, 'href' => self::pageUrl($code, $suffix)],
            Lang::SUPPORTED
        );

        return array_merge($languages, [['hreflang' => 'x-default', 'href' => self::pageUrl(Lang::DEFAULT, $suffix)]]);
    }

    /**
     * URL absolue d'une page dans une langue.
     */
    public static function pageUrl(string $lang, string $suffix = ''): string
    {
        return self::absolute('/' . $lang . '/' . $suffix);
    }

    /**
     * Encode un bloc JSON-LD prêt à être placé dans une balise script.
     *
     * @param array<string, mixed> $data
     */
    public static function jsonLd(array $data): string
    {
        return json_encode(
            ['@context' => 'https://schema.org'] + $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR
        );
    }

    /**
     * Schéma WebSite.
     *
     * @return array<string, mixed>
     */
    public static function webSite(string $lang, string $description): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => self::absolute('/#website'),
            'name' => self::SITE_NAME,
            'url' => self::pageUrl($lang),
            'description' => $description,
            'inLanguage' => $lang,
            'publisher' => ['@id' => self::absolute('/#business')],
        ];
    }

    /**
     * Schéma TouristInformationCenter pour l'activité de guide, avec la guide en tant que Person.
     *
     * @return array<string, mixed>
     */
    public static function business(string $lang, string $description): array
    {
        return [
            '@type' => 'TouristInformationCenter',
            '@id' => self::absolute('/#business'),
            'name' => self::SITE_NAME,
            'description' => $description,
            'url' => self::pageUrl($lang),
            'image' => self::absolute('/og-image.jpg'),
            'areaServed' => ['@type' => 'City', 'name' => 'Barcelona'],
            'availableLanguage' => Lang::SUPPORTED,
            'employee' => ['@type' => 'Person', 'name' => 'Anne Barrio', 'jobTitle' => 'Tour guide', 'workLocation' => ['@type' => 'City', 'name' => 'Barcelona']],
        ];
    }

    /**
     * Schéma ItemList de TouristTrip, construit uniquement avec les données de la base.
     *
     * @param array<int, Tour> $tours
     * @return array<string, mixed>
     */
    public static function tours(array $tours, string $lang): array
    {
        return [
            '@type' => 'ItemList',
            'itemListElement' => array_map(
                fn (Tour $tour, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'item' => array_filter([
                        '@type' => 'TouristTrip',
                        'name' => $tour->getTitle(),
                        'description' => $tour->getSummary(),
                        'image' => $tour->getImageUrl() !== '' && ImageUploader::publicUrl($tour->getImageUrl()) !== ''
                            ? self::absolute(ImageUploader::publicUrl($tour->getImageUrl()))
                            : null,
                        'touristType' => $tour->getGroupType(),
                        'provider' => ['@id' => self::absolute('/#business')],
                        'url' => self::pageUrl($lang) . '#tour-list',
                    ]),
                ],
                $tours,
                array_keys($tours)
            ),
        ];
    }

    /**
     * Schéma BreadcrumbList.
     *
     * @param array<int, array{name: string, url: string}> $items
     * @return array<string, mixed>
     */
    public static function breadcrumb(array $items): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(
                fn (array $item, int $index): array => ['@type' => 'ListItem', 'position' => $index + 1, 'name' => $item['name'], 'item' => $item['url']],
                $items,
                array_keys($items)
            ),
        ];
    }
}
