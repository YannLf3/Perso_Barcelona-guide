<?php

declare(strict_types=1);

/**
 * Connexion et déconnexion de l'administration.
 */
final class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_SECONDS = 900;
    private const MAX_ACCOUNT_ATTEMPTS = 20;
    private const ACCOUNT_WINDOW_SECONDS = 3600;
    private const DUMMY_HASH = '$2y$12$B8ZoPCp5zu8MafHBlNAy8OD6MgJvn7t/sSKt8Z8wfcfl46PNDZ54y';

    /**
     * GET /admin/login : formulaire de connexion.
     */
    public function showLogin(): void
    {
        if (Session::isAdmin()) {
            Response::redirect('/admin');
        }

        $this->renderLogin('admin.log.loaded');
    }

    /**
     * POST /admin/login : vérifie le mot de passe, ouvre la session et redirige vers le tableau de bord.
     */
    public function login(): void
    {
        $input = $this->input();

        if (!Csrf::isValid('admin', Csrf::fromRequest($input))) {
            $this->renderLogin('admin.log.invalid_token', 403);
        }

        // Chaque tentative réserve une place avant la vérification du mot de passe : une rafale de requêtes ne passe pas entre les mailles
        if (!RateLimiter::consume('login', self::MAX_ATTEMPTS, self::WINDOW_SECONDS)) {
            $this->renderLogin('admin.log.too_many', 429);
        }

        // Second compteur commun à toutes les adresses IP : freine une attaque répartie sur de nombreuses machines
        if (!RateLimiter::consume('login-account', self::MAX_ACCOUNT_ATTEMPTS, self::ACCOUNT_WINDOW_SECONDS, false)) {
            $this->renderLogin('admin.log.account_locked', 429);
        }

        $password = (string) ($input['password'] ?? '');
        $user = User::findByUsername(Env::get('ADMIN_USER', 'admin'));

        // Si le compte n'existe pas, on vérifie quand même un hash factice pour garder un temps de réponse constant
        $valid = $user !== null
            ? $user->verifyPassword($password)
            : password_verify($password, self::DUMMY_HASH);

        if ($user === null || !$valid) {
            $this->renderLogin('admin.log.invalid_password', 401);
        }

        RateLimiter::clear('login');
        RateLimiter::clear('login-account', false);
        Session::login($user->getId());
        $user->markLoggedIn($password);

        Response::redirect('/admin');
    }

    /**
     * POST /admin/logout : ferme la session.
     */
    public function logout(): void
    {
        if (!Csrf::isValid('admin', Csrf::fromRequest($this->input()))) {
            Response::redirect('/admin');
        }

        Session::logout();
        Response::redirect('/admin/login');
    }

    /**
     * Affiche la page de connexion avec un message dans le journal.
     *
     * @param string $messageKey Clé de traduction du message (ex. admin.log.loaded)
     */
    private function renderLogin(string $messageKey, int $status = 200): never
    {
        Lang::init(Lang::DEFAULT);
        View::render('admin/login', [
            'lang' => 'fr',
            'csrf' => Csrf::adminToken(),
            'logMessages' => [t($messageKey)],
            'meta' => ['title' => t('admin.login_title'), 'robots' => 'noindex, nofollow'],
            'stylesheet' => '/css/admin.css',
            'page' => 'admin',
        ], $status);
    }
}
