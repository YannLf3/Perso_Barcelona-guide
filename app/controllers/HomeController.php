<?php

declare(strict_types=1);

/**
 * Pages publiques : redirection de la racine, accueil par langue, sitemap et page 404.
 */
final class HomeController extends Controller
{
    /**
     * GET / : redirection 301 vers la langue du navigateur (anglais par défaut).
     */
    public function root(): void
    {
        $lang = Lang::fromAcceptLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');

        header('Vary: Accept-Language');
        Response::redirect('/' . $lang . '/', 301);
    }

    /**
     * GET /{lang}/ : page d'accueil rendue côté serveur.
     *
     * @param array<string, string> $params Paramètres de route
     */
    public function index(array $params): void
    {
        $lang = $params['lang'] ?? '';

        if (!Lang::isValid($lang)) {
            $this->notFound();
        }

        Lang::init($lang);

        $tours = Tour::findActive($lang);
        $description = t('meta.home_description');

        header('Cache-Control: no-cache');
        View::render('front/home', [
            'lang' => $lang,
            'tours' => $tours,
            'csrf' => Csrf::publicToken(),
            'pageSuffix' => '',
            'meta' => [
                'title' => t('meta.home_title'),
                'description' => $description,
                'canonical' => Seo::pageUrl($lang),
                'alternates' => Seo::alternates(''),
                'jsonLd' => [
                    Seo::jsonLd(Seo::webSite($lang, $description)),
                    Seo::jsonLd(Seo::business($lang, $description)),
                    Seo::jsonLd(Seo::tours($tours, $lang)),
                ],
            ],
            'stylesheet' => '/css/app.css',
            'page' => 'home',
        ]);
    }

    /**
     * GET /sitemap.xml : sitemap multilingue avec les alternates hreflang.
     */
    public function sitemap(): void
    {
        // Une entrée par page et par langue, chacune listant ses alternates
        $pages = ['', 'legal'];

        $urls = array_merge(...array_map(
            fn (string $suffix): array => array_map(
                function (string $lang) use ($suffix): string {
                    $links = implode('', array_map(
                        fn (array $alternate): string => '<xhtml:link rel="alternate" hreflang="' . e($alternate['hreflang']) . '" href="' . e($alternate['href']) . '"/>',
                        Seo::alternates($suffix)
                    ));

                    return '<url><loc>' . e(Seo::pageUrl($lang, $suffix)) . '</loc>' . $links . '</url>';
                },
                Lang::SUPPORTED
            ),
            $pages
        ));

        Response::xml(
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'
            . implode('', $urls)
            . '</urlset>'
        );
    }

    /**
     * Page 404 (JSON pour /api, HTML sinon) avec le code HTTP 404.
     */
    public function notFound(): never
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        if (str_starts_with($path, '/api/')) {
            Response::json(['success' => false, 'message' => 'Not found'], 404);
        }

        // La langue est déduite de l'URL (/fr/...) ou du navigateur
        $firstSegment = explode('/', trim($path, '/'))[0];
        $lang = Lang::isValid($firstSegment) ? $firstSegment : Lang::fromAcceptLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
        Lang::init($lang);

        View::render('front/404', [
            'lang' => $lang,
            'pageSuffix' => '',
            'meta' => ['title' => t('meta.notfound_title'), 'robots' => 'noindex, follow'],
            'stylesheet' => '/css/app.css',
            'page' => 'notfound',
        ], 404);
    }
}
