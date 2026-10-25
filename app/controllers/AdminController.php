<?php

declare(strict_types=1);

/**
 * Tableau de bord d'administration (le contenu est chargé par le JavaScript via l'API).
 */
final class AdminController extends Controller
{
    /**
     * GET /admin : tableau de bord, réservé à la session administrateur.
     */
    public function dashboard(): void
    {
        if (!Session::isAdmin()) {
            Response::redirect('/admin/login');
        }

        Lang::init(Lang::DEFAULT);

        // Textes du back-office dont le JavaScript a besoin (messages, libellés construits dynamiquement)
        $strings = array_combine(
            ['feedback', 'savebar', 'design', 'tours', 'messages', 'texts', 'home', 'lang', 'theme'],
            array_map(fn (string $group): array => Lang::list('admin.' . $group), ['feedback', 'savebar', 'design', 'tours', 'messages', 'texts', 'home', 'lang', 'theme'])
        );
        unset($strings['texts']['fields'], $strings['design']['tokens'], $strings['design']['fonts_list']);

        View::render('admin/dashboard', [
            'lang' => 'fr',
            'csrf' => Csrf::adminToken(),
            'strings' => $strings,
            'meta' => ['title' => t('admin.title'), 'robots' => 'noindex, nofollow'],
            'stylesheet' => '/css/admin.css',
            'page' => 'admin',
        ]);
    }
}
