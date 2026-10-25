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
        View::render('admin/dashboard', [
            'lang' => Lang::DEFAULT,
            'csrf' => Csrf::adminToken(),
            'logMessages' => [t('admin.log.restored')],
            'meta' => ['title' => t('admin.title'), 'robots' => 'noindex, nofollow'],
            'stylesheet' => '/css/admin.css',
            'page' => 'admin',
        ]);
    }
}
