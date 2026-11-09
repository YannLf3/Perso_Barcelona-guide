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

        // Le compte « editor » reçoit l'espace simplifié, qui reprend l'apparence de l'ancien back-office
        if (Session::role() !== User::ROLE_ADMIN) {
            $this->simple();
        }

        // Textes du back-office dont le JavaScript a besoin (messages, libellés construits dynamiquement)
        $strings = array_combine(
            ['feedback', 'savebar', 'design', 'tours', 'messages', 'texts', 'home', 'lang', 'theme'],
            array_map(fn (string $group): array => Lang::list('admin.' . $group), ['feedback', 'savebar', 'design', 'tours', 'messages', 'texts', 'home', 'lang', 'theme'])
        );
        unset($strings['texts']['fields'], $strings['design']['tokens'], $strings['design']['fonts_list']);

        $isAdmin = Session::role() === User::ROLE_ADMIN;

        View::render('admin/dashboard', [
            'lang' => 'fr',
            'role' => Session::role(),
            'screens' => $isAdmin ? ['home', 'texts', 'design', 'tours', 'images', 'messages'] : ['home', 'texts', 'tours', 'images', 'messages'],
            'csrf' => Csrf::adminToken(),
            'strings' => $strings,
            'meta' => ['title' => t('admin.title'), 'robots' => 'noindex, nofollow'],
            'stylesheet' => '/css/admin.css',
            'page' => 'admin',
        ]);
    }

    /**
     * Espace simplifié du compte « editor » : visites, photos, messages et textes de contenu, avec l'apparence de l'ancien back-office.
     */
    private function simple(): never
    {
        View::render('admin/simple', [
            'lang' => 'fr',
            'csrf' => Csrf::adminToken(),
            'strings' => ['simple' => Lang::list('admin.simple')],
            'meta' => ['title' => t('admin.simple.title'), 'robots' => 'noindex, nofollow'],
            'stylesheet' => '/css/admin-simple.css',
            'page' => 'admin',
            'scripts' => ['/js/admin/simple/m.js', '/js/admin/simple/v.js', '/js/admin/simple/c.js'],
        ]);
    }
}
