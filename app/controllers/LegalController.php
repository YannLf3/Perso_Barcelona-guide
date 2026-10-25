<?php

declare(strict_types=1);

/**
 * Mentions légales.
 */
final class LegalController extends Controller
{
    /**
     * GET /{lang}/legal : mentions légales dans la langue demandée.
     *
     * @param array<string, string> $params Paramètres de route
     */
    public function show(array $params): void
    {
        $lang = $params['lang'] ?? '';

        if (!Lang::isValid($lang)) {
            (new HomeController())->notFound();
        }

        Lang::init($lang);

        View::render('front/legal', [
            'lang' => $lang,
            'pageSuffix' => 'legal',
            'meta' => [
                'title' => t('meta.legal_title'),
                'description' => t('meta.legal_description'),
                'canonical' => Seo::pageUrl($lang, 'legal'),
                'alternates' => Seo::alternates('legal'),
                'jsonLd' => [
                    Seo::jsonLd(Seo::breadcrumb([
                        ['name' => t('legal.breadcrumb_home'), 'url' => Seo::pageUrl($lang)],
                        ['name' => t('legal.title'), 'url' => Seo::pageUrl($lang, 'legal')],
                    ])),
                ],
            ],
            'stylesheet' => '/css/app.css',
            'page' => 'legal',
        ]);
    }
}
